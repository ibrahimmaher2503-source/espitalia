<?php
/**
 * Generate Postman Collection v2.1 from docs/api-endpoints.md
 *
 * Parses the curated markdown reference into a Postman collection.
 * Usage:  php tools/generate_postman_from_endpoints.php
 */

declare(strict_types=1);

$root       = realpath(__DIR__ . '/..');
$docPath    = $root . '/docs/api-endpoints.md';
$outPath    = $root . '/postman_collection.json';

if (!is_file($docPath)) {
    fwrite(STDERR, "Missing doc: $docPath\n");
    exit(1);
}

$lines = file($docPath, FILE_IGNORE_NEW_LINES);

// --- Header blurb (first 6 lines) -----------------------------------------
$blurb = implode("\n", array_slice($lines, 0, 6));

// --- Parse sections --------------------------------------------------------
/**
 * Section shape:
 *   number, name, prefix, auth, subsections => [ name => [endpoints...] ],
 *   loose_endpoints => [], filter_tables => [...]
 */
$sections        = [];
$currentSection  = null;
$currentSubName  = null;   // null = attach to section directly
$inToc           = false;

// Table state machine
$tableState      = 'none'; // none | header | separator | rows
$tableHeaders    = [];
$currentTableKind = null;  // 'endpoint' | 'filter' | 'other'
// For filter tables: we attach params to the LAST endpoint parsed, matched by method+uri if possible
$lastEndpointKey = null;   // [sectionIdx, subName, endpointIdx]
$pendingFilterForUri = null; // optional: {method, uri} from subsection title OR nearby bold marker
$lastBoldFilterTarget = null; // {method, uri} extracted from a recent **METHOD /uri — ...:** marker

$skippedReports = [
    'suspicious_subsections' => [],
    'unmatched_filter_tables' => [],
    'skipped_sections'       => [],
];

function normalizePath(string $p): string {
    // collapse double slashes but keep leading slash
    $p = preg_replace('#/+#', '/', $p);
    return $p;
}

function stripBackticks(string $s): string {
    return trim($s, " \t`");
}

function parseTableRow(string $line): ?array {
    $line = trim($line);
    if ($line === '' || $line[0] !== '|') return null;
    // Trim leading/trailing |
    $inner = trim($line, " \t|");
    // Split on | but not inside backticks? Tables in this doc don't nest pipes inside backticks, so naive split works.
    $cells = array_map('trim', explode('|', $inner));
    return $cells;
}

function isSeparatorRow(array $cells): bool {
    foreach ($cells as $c) {
        if (!preg_match('/^:?-{2,}:?$/', trim($c))) return false;
    }
    return true;
}

function headerKind(array $headers): string {
    if (empty($headers)) return 'other';
    $first = strtolower(trim($headers[0]));
    if ($first === 'method') return 'endpoint';
    if ($first === 'param' || $first === 'parameter') return 'filter';
    return 'other';
}

function closeTable(&$tableState, &$tableHeaders, &$currentTableKind, &$pendingFilterForUri) {
    $tableState       = 'none';
    $tableHeaders     = [];
    $currentTableKind = null;
    $pendingFilterForUri = null;
}

function extractFilterTarget(string $subsectionTitle): ?array {
    // e.g. "8.3 Doctor List Filters (`GET /get-doctor-list`)"
    if (preg_match('/\(`(GET|POST|PUT|PATCH|DELETE)\s+([^`]+)`\)/i', $subsectionTitle, $m)) {
        return ['method' => strtoupper($m[1]), 'uri' => trim($m[2])];
    }
    return null;
}

function addEndpoint(array &$sections, int $sectionIdx, ?string $subName, array $endpoint): array {
    if ($subName === null) {
        $sections[$sectionIdx]['loose_endpoints'][] = $endpoint;
        $idx = count($sections[$sectionIdx]['loose_endpoints']) - 1;
        return [$sectionIdx, null, $idx];
    }
    if (!isset($sections[$sectionIdx]['subsections'][$subName])) {
        $sections[$sectionIdx]['subsections'][$subName] = [];
    }
    $sections[$sectionIdx]['subsections'][$subName][] = $endpoint;
    $idx = count($sections[$sectionIdx]['subsections'][$subName]) - 1;
    return [$sectionIdx, $subName, $idx];
}

function &endpointRef(array &$sections, array $key) {
    [$sIdx, $sub, $eIdx] = $key;
    if ($sub === null) {
        $ref = &$sections[$sIdx]['loose_endpoints'][$eIdx];
    } else {
        $ref = &$sections[$sIdx]['subsections'][$sub][$eIdx];
    }
    return $ref;
}

// -------------------------------------------------------------------------
// Main parse loop
// -------------------------------------------------------------------------
$n = count($lines);
for ($i = 0; $i < $n; $i++) {
    $line = $lines[$i];
    $trim = rtrim($line);

    // Detect TOC section (skip until next ## non-TOC)
    if (preg_match('/^##\s+Table of Contents\b/i', $trim)) {
        $inToc = true;
        continue;
    }

    // New top-level section?
    if (preg_match('/^##\s+(\d+)\.\s+(.+)$/', $trim, $m)) {
        // Close any open table
        closeTable($tableState, $tableHeaders, $currentTableKind, $pendingFilterForUri);
        $inToc = false;
        unset($currentSection); // break any prior reference binding
        $currentSection = null;
        $sections[] = [
            'number'          => (int) $m[1],
            'name'            => trim($m[2]),
            'prefix'          => '',
            'auth'            => '',
            'subsections'     => [],
            'loose_endpoints' => [],
        ];
        $currentSection = &$sections[count($sections) - 1];
        $currentSubName = null;
        $lastEndpointKey = null;
        $lastBoldFilterTarget = null;
        continue;
    }

    // Other ## heading (not numbered) — Scheduled Commands, Permissions Reference, etc.
    if (preg_match('/^##\s+/', $trim) && !preg_match('/^##\s+\d+\./', $trim)) {
        // Close current section; subsequent content is ignored until another numbered ## appears
        closeTable($tableState, $tableHeaders, $currentTableKind, $pendingFilterForUri);
        unset($currentSection);
        $currentSection = null;
        $currentSubName = null;
        $lastEndpointKey = null;
        continue;
    }

    if ($inToc) continue;
    if ($currentSection === null) continue;

    // Prefix / Auth markers
    if (preg_match('/^\*\*Prefix:\*\*\s*`?([^`]+?)`?\s*$/', $trim, $m)) {
        $currentSection['prefix'] = stripBackticks($m[1]);
        continue;
    }
    if (preg_match('/^\*\*Auth:\*\*\s*(.+)$/', $trim, $m)) {
        $currentSection['auth'] = trim($m[1]);
        continue;
    }
    // Bold marker like **GET /products — Query Params:** or **POST /orders — Request:**
    if (preg_match('/^\*\*(GET|POST|PUT|PATCH|DELETE)\s+([^\s*]+)\s*(?:—|-|:).*\*\*/u', $trim, $m)) {
        $lastBoldFilterTarget = ['method' => strtoupper($m[1]), 'uri' => trim($m[2])];
        continue;
    }

    // Subsection heading
    if (preg_match('/^###\s+(.+)$/', $trim, $m)) {
        closeTable($tableState, $tableHeaders, $currentTableKind, $pendingFilterForUri);
        $subTitle = trim($m[1]);
        $currentSubName = $subTitle;
        // If this subsection describes filters for a specific endpoint, remember target
        $filterTarget = extractFilterTarget($subTitle);
        $pendingFilterForUri = $filterTarget; // may be null
        $lastBoldFilterTarget = null;
        continue;
    }

    // Blank line -> close table (but keep state otherwise)
    if ($trim === '') {
        if ($tableState === 'rows') {
            closeTable($tableState, $tableHeaders, $currentTableKind, $pendingFilterForUri);
        } else {
            closeTable($tableState, $tableHeaders, $currentTableKind, $pendingFilterForUri);
        }
        continue;
    }

    // Horizontal rule
    if (preg_match('/^---+\s*$/', $trim)) {
        closeTable($tableState, $tableHeaders, $currentTableKind, $pendingFilterForUri);
        continue;
    }

    // Table row?
    if ($trim !== '' && $trim[0] === '|') {
        $cells = parseTableRow($trim);
        if ($cells === null) continue;

        if ($tableState === 'none') {
            // new header
            $tableHeaders = $cells;
            $currentTableKind = headerKind($cells);
            $tableState = 'header';
            continue;
        }

        if ($tableState === 'header') {
            // expecting separator
            if (isSeparatorRow($cells)) {
                $tableState = 'rows';
            } else {
                // Not a real table header; abandon
                closeTable($tableState, $tableHeaders, $currentTableKind, $pendingFilterForUri);
            }
            continue;
        }

        if ($tableState === 'rows') {
            // process row based on kind
            if ($currentTableKind === 'endpoint') {
                // Map cells to header fields (case-insensitive)
                $row = [];
                foreach ($tableHeaders as $ci => $h) {
                    $row[strtolower(trim($h))] = $cells[$ci] ?? '';
                }
                $methodRaw = strtoupper(stripBackticks($row['method'] ?? ''));
                $uri       = stripBackticks($row['uri'] ?? '');
                $desc      = $row['description'] ?? '';
                // Fall back: use "name"/"controller"/"permission"/"roles" as extra description
                $extras = [];
                foreach (['name', 'permission', 'roles', 'controller'] as $k) {
                    if (!empty($row[$k])) $extras[] = ucfirst($k) . ': ' . $row[$k];
                }
                if ($desc === '' && !empty($extras)) {
                    $desc = implode(' | ', $extras);
                } elseif (!empty($extras)) {
                    $desc = trim($desc) . ($desc !== '' ? ' — ' : '') . implode(' | ', $extras);
                }
                if ($methodRaw === '' || $uri === '') continue;

                // Method may be combined like "GET/POST/PUT/DELETE" or "GET-POST-PUT-DELETE"
                $methods = preg_split('#[/\-]#', $methodRaw);
                $methods = array_values(array_filter($methods, fn($x) => in_array($x, ['GET','POST','PUT','PATCH','DELETE','HEAD','OPTIONS'], true)));
                if (empty($methods)) $methods = [$methodRaw];

                foreach ($methods as $method) {
                    $endpoint = [
                        'method'      => $method,
                        'uri'         => $uri,
                        'description' => trim($desc),
                        'query'       => [],
                    ];
                    $sIdx = count($sections) - 1;
                    $lastEndpointKey = addEndpoint($sections, $sIdx, $currentSubName, $endpoint);
                }
                continue;
            }

            if ($currentTableKind === 'filter') {
                $row = [];
                foreach ($tableHeaders as $ci => $h) {
                    $row[strtolower(trim($h))] = $cells[$ci] ?? '';
                }
                $pname = stripBackticks($row['param'] ?? $row['parameter'] ?? '');
                if ($pname === '') continue;
                // Strip [] suffix for arrays
                $pnameClean = $pname;
                $desc       = $row['description'] ?? '';
                $type       = $row['type'] ?? '';
                if ($type !== '') {
                    $desc = ($desc !== '' ? $desc . ' ' : '') . '(' . $type . ')';
                }

                // Determine target endpoint
                $targetKey = null;
                $candidates = array_values(array_filter([$pendingFilterForUri, $lastBoldFilterTarget]));
                foreach ($candidates as $target) {
                    $sIdx = count($sections) - 1;
                    $found = null;
                    $search = function() use (&$sections, $sIdx, $target, &$found) {
                        foreach ($sections[$sIdx]['subsections'] as $sname => $eps) {
                            foreach ($eps as $eIdx => $ep) {
                                if ($ep['method'] === $target['method']) {
                                    // match by exact uri OR suffix (doc sometimes uses short form)
                                    if ($ep['uri'] === $target['uri'] || str_ends_with($ep['uri'], $target['uri'])) {
                                        $found = [$sIdx, $sname, $eIdx];
                                        return;
                                    }
                                }
                            }
                        }
                        foreach ($sections[$sIdx]['loose_endpoints'] as $eIdx => $ep) {
                            if ($ep['method'] === $target['method']) {
                                if ($ep['uri'] === $target['uri'] || str_ends_with($ep['uri'], $target['uri'])) {
                                    $found = [$sIdx, null, $eIdx];
                                    return;
                                }
                            }
                        }
                    };
                    $search();
                    if ($found !== null) { $targetKey = $found; break; }
                }
                // Fallback: attach to last endpoint added
                if ($targetKey === null && $lastEndpointKey !== null) {
                    $targetKey = $lastEndpointKey;
                }
                if ($targetKey === null) {
                    global $skippedReports;
                    $skippedReports['unmatched_filter_tables'][] = sprintf(
                        'Section %d (%s), subsection "%s", param "%s"',
                        $sections[count($sections)-1]['number'] ?? 0,
                        $sections[count($sections)-1]['name'] ?? '',
                        $currentSubName ?? '(loose)',
                        $pname
                    );
                    continue;
                }
                $ref = &endpointRef($sections, $targetKey);
                $ref['query'][] = [
                    'key'         => $pnameClean,
                    'value'       => '',
                    'description' => trim($desc),
                    'disabled'    => false,
                ];
                unset($ref);
                continue;
            }

            // 'other' kind — request body fields, response fields etc. Ignore.
            continue;
        }
    }
    // End of line processing
}

// -------------------------------------------------------------------------
// Post-process: sort sections by number
// -------------------------------------------------------------------------
usort($sections, fn($a, $b) => $a['number'] <=> $b['number']);

// -------------------------------------------------------------------------
// Build Postman JSON
// -------------------------------------------------------------------------

function buildUrl(string $fullPath): array {
    // $fullPath should begin with /
    if ($fullPath === '' || $fullPath[0] !== '/') $fullPath = '/' . $fullPath;
    // Convert {var} -> :var
    $postmanPath = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', function($m) {
        return ':' . $m[1];
    }, $fullPath);
    $segments = array_values(array_filter(explode('/', ltrim($postmanPath, '/')), fn($s) => $s !== ''));
    $raw = '{{base_url}}' . $postmanPath;
    $url = [
        'raw'  => $raw,
        'host' => ['{{base_url}}'],
        'path' => $segments,
    ];
    // path variables
    if (preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', $fullPath, $vm)) {
        $variables = [];
        foreach (array_unique($vm[1]) as $v) {
            $variables[] = [
                'key'         => $v,
                'value'       => '',
                'description' => '',
            ];
        }
        if (!empty($variables)) $url['variable'] = $variables;
    }
    return $url;
}

function buildRequest(array $ep, string $prefix, string $auth): array {
    $fullPath = normalizePath($prefix . $ep['uri']);
    $url      = buildUrl($fullPath);

    // Query params
    if (!empty($ep['query'])) {
        $url['query'] = $ep['query'];
    }

    $headers = [
        ['key' => 'Accept', 'value' => 'application/json'],
    ];
    $requiresAuth = !preg_match('/^\s*none(\s+(required|\(public\)))?\s*$/i', trim($auth)) && trim($auth) !== '';
    if ($requiresAuth) {
        $headers[] = ['key' => 'Authorization', 'value' => 'Bearer {{auth_token}}'];
    }
    $method = strtoupper($ep['method']);
    $hasBody = in_array($method, ['POST', 'PUT', 'PATCH'], true);
    if ($hasBody) {
        $headers[] = ['key' => 'Content-Type', 'value' => 'application/json'];
    }

    // Request name: prefer description if short and non-empty, else "METHOD /uri"
    $desc = trim($ep['description'] ?? '');
    $name = $method . ' ' . $ep['uri'];
    if ($desc !== '' && strlen($desc) < 60 && !str_contains($desc, '|')) {
        $name = $method . ' — ' . $desc;
    }

    $req = [
        'method' => $method,
        'header' => $headers,
        'url'    => $url,
    ];
    if ($hasBody) {
        $req['body'] = [
            'mode'    => 'raw',
            'raw'     => '{}',
            'options' => [ 'raw' => [ 'language' => 'json' ] ],
        ];
    }

    return [
        'name'        => $name,
        'request'     => $req,
        'response'    => [],
        'description' => $desc,
    ];
}

$collectionItems = [];
$totalEndpoints  = 0;
$sectionCounts   = [];

foreach ($sections as $section) {
    $count = 0;
    $folder = [
        'name' => sprintf('%d. %s', $section['number'], $section['name']),
        'item' => [],
    ];
    if ($section['auth'] !== '') {
        $folder['description'] = "Prefix: {$section['prefix']}\nAuth: {$section['auth']}";
    } elseif ($section['prefix'] !== '') {
        $folder['description'] = "Prefix: {$section['prefix']}";
    }

    // Loose endpoints (no subsection)
    foreach ($section['loose_endpoints'] as $ep) {
        $item = buildRequest($ep, $section['prefix'], $section['auth']);
        $folder['item'][] = $item;
        $count++;
    }
    // Subsections
    foreach ($section['subsections'] as $subName => $eps) {
        if (empty($eps)) {
            // Record suspicious subsection — looks like an endpoint subsection but no endpoints parsed
            // Only flag if the name suggests endpoints (exclude known non-endpoint subsections)
            if (!preg_match('/\bFilters?\b|\bFields?\b|\bCreate\b|\bOffer Fields?\b|\bOffers\/[a-z]/i', $subName)
                && !preg_match('/^POST\s|^GET\s|^PUT\s|^DELETE\s|^PATCH\s/i', $subName)) {
                $GLOBALS['skippedReports']['suspicious_subsections'][] =
                    sprintf('Section %d: "%s"', $section['number'], $subName);
            }
            continue;
        }
        $sub = [
            'name' => $subName,
            'item' => [],
        ];
        foreach ($eps as $ep) {
            $item = buildRequest($ep, $section['prefix'], $section['auth']);
            $sub['item'][] = $item;
            $count++;
        }
        $folder['item'][] = $sub;
    }
    if ($count === 0) {
        $skippedReports['skipped_sections'][] = sprintf('Section %d (%s): no parseable endpoints', $section['number'], $section['name']);
        continue;
    }
    $collectionItems[] = $folder;
    $sectionCounts[]   = [$section['number'], $section['name'], $count];
    $totalEndpoints   += $count;
}

// -------------------------------------------------------------------------
// Collection wrapper
// -------------------------------------------------------------------------
$collection = [
    'info' => [
        'name'        => 'KiviCare API Reference',
        'description' => $blurb,
        'schema'      => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
        '_postman_id' => sprintf('kivicare-%s', substr(md5($blurb . time()), 0, 12)),
    ],
    'item'     => $collectionItems,
    'variable' => [
        ['key' => 'base_url',   'value' => 'http://localhost:8000', 'type' => 'string'],
        ['key' => 'auth_token', 'value' => '',                       'type' => 'string'],
    ],
];

$json = json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($json === false) {
    fwrite(STDERR, "JSON encode failed: " . json_last_error_msg() . "\n");
    exit(1);
}
file_put_contents($outPath, $json);

// -------------------------------------------------------------------------
// Report
// -------------------------------------------------------------------------
echo "Wrote: $outPath\n";
echo "Total endpoints: $totalEndpoints\n";
echo "Per-section:\n";
foreach ($sectionCounts as [$num, $name, $cnt]) {
    echo sprintf("  %2d. %s — %d\n", $num, $name, $cnt);
}
if (!empty($skippedReports['skipped_sections'])) {
    echo "\nSkipped sections:\n";
    foreach ($skippedReports['skipped_sections'] as $s) echo "  - $s\n";
}
if (!empty($skippedReports['suspicious_subsections'])) {
    echo "\nSuspicious subsections (looked like endpoints but had no table):\n";
    foreach (array_unique($skippedReports['suspicious_subsections']) as $s) echo "  - $s\n";
}
if (!empty($skippedReports['unmatched_filter_tables'])) {
    echo "\nUnmatched filter tables:\n";
    foreach ($skippedReports['unmatched_filter_tables'] as $s) echo "  - $s\n";
}
