<?php
/**
 * Static route parser -> Postman Collection v2.1 generator.
 * Usage: php tools/generate_postman.php
 */

declare(strict_types=1);

$ROOT = realpath(__DIR__ . '/..');
if ($ROOT === false) {
    fwrite(STDERR, "Cannot resolve project root\n");
    exit(1);
}
$ROOT = str_replace('\\', '/', $ROOT);

$MODULES_DIR = $ROOT . '/Modules';
$CORE_API = $ROOT . '/routes/api.php';

$errors = [];
$endpoints = []; // module => list of endpoints

// -----------------------------------------------------------------------------
// 1. Collect target route files
// -----------------------------------------------------------------------------
$routeFiles = [];
if (is_file($CORE_API)) {
    $routeFiles['Core'] = $CORE_API;
}

if (is_dir($MODULES_DIR)) {
    $dh = opendir($MODULES_DIR);
    while (($entry = readdir($dh)) !== false) {
        if ($entry === '.' || $entry === '..') continue;
        $modulePath = $MODULES_DIR . '/' . $entry;
        if (!is_dir($modulePath)) continue;
        $candidates = [
            $modulePath . '/Routes/api.php',
            $modulePath . '/routes/api.php',
        ];
        foreach ($candidates as $c) {
            if (is_file($c)) {
                $routeFiles[$entry] = $c;
                break;
            }
        }
    }
    closedir($dh);
}

// -----------------------------------------------------------------------------
// 2. Index form-request classes for body generation
// -----------------------------------------------------------------------------
$requestFiles = []; // class short name => full file path
function indexRequestFiles(string $root, array &$out, array &$errors): void {
    $dirs = [$root . '/app/Http/Requests', $root . '/Modules'];
    foreach ($dirs as $d) {
        if (!is_dir($d)) continue;
        try {
            $it = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($d, FilesystemIterator::SKIP_DOTS)
            );
            foreach ($it as $file) {
                if (!$file->isFile()) continue;
                $path = str_replace('\\', '/', $file->getPathname());
                if (substr($path, -4) !== '.php') continue;
                if (strpos($path, '/Http/Requests/') === false) continue;
                $base = basename($path, '.php');
                $out[$base] = $path;
            }
        } catch (Throwable $e) {
            $errors[] = "indexRequestFiles($d): " . $e->getMessage();
        }
    }
}
indexRequestFiles($ROOT, $requestFiles, $errors);

// -----------------------------------------------------------------------------
// 3. Index controller files so we can resolve class -> path and parse methods
// -----------------------------------------------------------------------------
$controllerFiles = []; // short class name => file path (first found)
function indexControllerFiles(string $root, array &$out, array &$errors): void {
    $dirs = [$root . '/app/Http/Controllers', $root . '/Modules'];
    foreach ($dirs as $d) {
        if (!is_dir($d)) continue;
        try {
            $it = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($d, FilesystemIterator::SKIP_DOTS)
            );
            foreach ($it as $file) {
                if (!$file->isFile()) continue;
                $path = str_replace('\\', '/', $file->getPathname());
                if (substr($path, -14) !== 'Controller.php') continue;
                $base = basename($path, '.php');
                if (!isset($out[$base])) $out[$base] = $path;
            }
        } catch (Throwable $e) {
            $errors[] = "indexControllerFiles($d): " . $e->getMessage();
        }
    }
}
indexControllerFiles($ROOT, $controllerFiles, $errors);

// -----------------------------------------------------------------------------
// 4. Route parser
// -----------------------------------------------------------------------------
/**
 * Parse a route file. Returns list of routes with effective prefix/middleware.
 */
function parseRouteFile(string $filePath, array &$errors): array {
    $src = @file_get_contents($filePath);
    if ($src === false) {
        $errors[] = "Cannot read $filePath";
        return [];
    }
    // Strip comments to avoid matching Route:: in comments.
    $src = preg_replace('!/\*.*?\*/!s', '', $src);
    $src = preg_replace('!//[^\n]*!', '', $src);

    $out = [];
    $len = strlen($src);
    $i = 0;

    // Stack of groups: ['prefix' => '', 'middleware' => []]
    $groupStack = [];
    // Pending group attributes collected before encountering ->group(...) or array form
    // We'll walk by scanning tokens: when we see Route::prefix('x')->middleware([...])->group(function(){
    // we push, when the matching closing }); appears we pop.
    // Simpler strategy: scan line-by-line tracking brace depth and pending attrs.

    // We'll use a regex-based walker over statements.
    // Approach: find all occurrences of interesting tokens in order:
    //   - Route::prefix('...')      -> pending.prefix
    //   - Route::middleware([...])  -> pending.middleware
    //   - ->prefix('...')
    //   - ->middleware([...])
    //   - ->group(function ...)     -> push pending, track opening { ... matching });
    //   - Route::group([...], function...)
    //   - Route::get|post|put|patch|delete|any|options ('uri', ...)
    //   - Route::apiResource('uri', Ctrl::class)
    //   - Route::resource('uri', Ctrl::class)
    //   - closing }); decreases group depth

    // Track brace depth after each group opening to know when to pop.
    // We'll walk char-by-char only enough to catch `{` / `}` outside strings.
    // But building a full PHP lexer is heavy — use a lighter approach:
    // Parse sequential tokens using regex, and treat each `->group(function ... {` as
    // pushing, and track a depth counter that decrements on `});` matching top-level.

    // For reliability we'll use PHP's token_get_all.
    $tokens = @token_get_all('<?php ' . $src);

    // Convert to a flat list: [type, value, line]
    $flat = [];
    foreach ($tokens as $t) {
        if (is_array($t)) {
            $flat[] = [token_name($t[0]), $t[1], $t[2] ?? 0];
        } else {
            $flat[] = ['PUNCT', $t, 0];
        }
    }

    $n = count($flat);
    $groups = []; // stack of ['prefix'=>..., 'middleware'=>[], 'brace_depth_at_open'=>int]
    $braceDepth = 0;

    // Helper to get effective prefix/middleware
    $effective = function() use (&$groups) {
        $prefix = '';
        $mw = [];
        foreach ($groups as $g) {
            $p = trim($g['prefix'] ?? '', '/');
            if ($p !== '') $prefix = trim($prefix . '/' . $p, '/');
            if (!empty($g['middleware'])) $mw = array_merge($mw, $g['middleware']);
        }
        return [$prefix, array_values(array_unique($mw))];
    };

    // Pending attrs attached to chain we are currently building.
    // We collect them when we see "Route::xxx(...)" starting a chain, then accumulate
    // ->prefix / ->middleware / ->name / ->group / etc.
    // Strategy: find start of a Route:: statement, scan until we close it with ; or newline at depth 0.

    $k = 0;
    while ($k < $n) {
        $tk = $flat[$k];
        // Track braces
        if ($tk[0] === 'PUNCT') {
            if ($tk[1] === '{') $braceDepth++;
            elseif ($tk[1] === '}') {
                $braceDepth--;
                // Pop groups that were opened at this depth
                while (!empty($groups) && $groups[count($groups)-1]['brace_depth_at_open'] >= $braceDepth + 1) {
                    array_pop($groups);
                }
            }
        }

        // Detect a Route::... statement
        if ($tk[0] === 'T_STRING' && $tk[1] === 'Route'
            && $k+1 < $n && $flat[$k+1][0] === 'T_DOUBLE_COLON') {
            // Collect the full statement until matching ';' at current paren depth 0
            $stmtStart = $k;
            $parenDepth = 0;
            $bracketDepth = 0;
            $curlyDepthLocal = 0;
            $j = $k;
            $stmt = '';
            while ($j < $n) {
                $t2 = $flat[$j];
                $v = $t2[1];
                $stmt .= $v;
                if ($t2[0] === 'PUNCT') {
                    if ($v === '(') $parenDepth++;
                    elseif ($v === ')') $parenDepth--;
                    elseif ($v === '[') $bracketDepth++;
                    elseif ($v === ']') $bracketDepth--;
                    elseif ($v === '{') { $curlyDepthLocal++; $braceDepth++; }
                    elseif ($v === '}') { $curlyDepthLocal--; $braceDepth--;
                        // Group pop handled in main loop
                    }
                    elseif ($v === ';' && $parenDepth === 0 && $bracketDepth === 0 && $curlyDepthLocal === 0) {
                        $j++;
                        break;
                    }
                }
                $j++;
            }

            // Now interpret $stmt
            // Check if this is a group-defining call that opens a callable
            // We'll look at the original $stmt for patterns.
            list($pref, $mwArr) = $effective();

            // apiResource / resource
            if (preg_match('/Route::(apiResource|resource)\s*\(\s*([\'"])([^\'"]+)\2\s*,\s*([A-Za-z0-9_\\\\]+)(::class)?/s', $stmt, $m)) {
                $kind = $m[1];
                $uri = $m[3];
                $ctrl = $m[4];
                $fullUri = trim(($pref === '' ? '' : $pref . '/') . ltrim($uri, '/'), '/');
                $restMap = [
                    'index'   => ['GET', $fullUri],
                    'store'   => ['POST', $fullUri],
                    'show'    => ['GET', $fullUri . '/{id}'],
                    'update'  => ['PUT', $fullUri . '/{id}'],
                    'destroy' => ['DELETE', $fullUri . '/{id}'],
                ];
                // Check for ->only([...]) / ->except([...])
                $only = null; $except = null;
                if (preg_match('/->only\s*\(\s*\[([^\]]*)\]/s', $stmt, $mm)) {
                    $only = array_map(fn($s)=>trim($s, "'\" \t"), explode(',', $mm[1]));
                    $only = array_filter($only);
                }
                if (preg_match('/->except\s*\(\s*\[([^\]]*)\]/s', $stmt, $mm)) {
                    $except = array_map(fn($s)=>trim($s, "'\" \t"), explode(',', $mm[1]));
                    $except = array_filter($except);
                }
                foreach ($restMap as $method => $spec) {
                    if ($only !== null && !in_array($method, $only)) continue;
                    if ($except !== null && in_array($method, $except)) continue;
                    [$verb, $u] = $spec;
                    $out[] = [
                        'method' => $verb,
                        'uri' => $u,
                        'controller' => trim($ctrl, '\\'),
                        'action' => $method,
                        'middleware' => $mwArr,
                    ];
                }
                $k = $j; continue;
            }

            // Route::group([...], function() { ... })  OR  Route::prefix(...)->middleware(...)->group(function(){...})
            $isGroup = (bool)preg_match('/->group\s*\(|Route::group\s*\(/', $stmt);

            // Extract attributes (prefix, middleware) from chain or array attributes
            $thisPrefix = '';
            $thisMw = [];

            // Chained ->prefix('...') and ->middleware([...]) or ->middleware('...')
            if (preg_match_all('/->prefix\s*\(\s*([\'"])([^\'"]+)\1\s*\)/s', $stmt, $mm)) {
                foreach ($mm[2] as $p) {
                    $thisPrefix = trim($thisPrefix . '/' . trim($p, '/'), '/');
                }
            }
            if (preg_match('/Route::prefix\s*\(\s*([\'"])([^\'"]+)\1\s*\)/s', $stmt, $mm)) {
                $thisPrefix = trim(trim($mm[2], '/') . '/' . $thisPrefix, '/');
            }
            if (preg_match_all('/->middleware\s*\(\s*(\[[^\]]*\]|[\'"][^\'"]+[\'"])/s', $stmt, $mm)) {
                foreach ($mm[1] as $expr) {
                    if (preg_match_all('/[\'"]([^\'"]+)[\'"]/', $expr, $mmx)) {
                        foreach ($mmx[1] as $one) $thisMw[] = $one;
                    }
                }
            }
            // Route::group(['prefix'=>'x','middleware'=>[...]], ...)
            if (preg_match('/Route::group\s*\(\s*\[(.*?)\]\s*,/s', $stmt, $mm)) {
                $arr = $mm[1];
                if (preg_match('/[\'"]prefix[\'"]\s*=>\s*[\'"]([^\'"]+)[\'"]/', $arr, $mx)) {
                    $thisPrefix = trim(trim($mx[1], '/') . '/' . $thisPrefix, '/');
                }
                if (preg_match('/[\'"]middleware[\'"]\s*=>\s*(\[[^\]]*\]|[\'"][^\'"]+[\'"])/s', $arr, $mx)) {
                    if (preg_match_all('/[\'"]([^\'"]+)[\'"]/', $mx[1], $mxx)) {
                        foreach ($mxx[1] as $one) $thisMw[] = $one;
                    }
                }
            }

            if ($isGroup) {
                // Push group context. Find the opening `{` of the closure inside this
                // statement (between $stmtStart and $j) and advance $k past it, so we
                // descend into the closure body. The closing `}` is handled by the
                // main loop via $braceDepth and will pop the group.
                $openIdx = -1;
                for ($p = $stmtStart; $p < $j; $p++) {
                    if ($flat[$p][0] === 'PUNCT' && $flat[$p][1] === '{') {
                        $openIdx = $p; break;
                    }
                }
                if ($openIdx < 0) {
                    // No closure found (maybe group with already-defined callback ref). Skip.
                    $k = $j; continue;
                }
                $braceDepth++; // account for the opening brace we're about to step over
                $groups[] = [
                    'prefix' => $thisPrefix,
                    'middleware' => $thisMw,
                    'brace_depth_at_open' => $braceDepth,
                ];
                $k = $openIdx + 1;
                continue;
            }

            // Standalone verb route: Route::get/post/... (or Route::middleware(...)->get(...), ->prefix(...)->get(...))
            if (preg_match('/Route::(?:[a-zA-Z]+\s*\([^)]*\)\s*->\s*)*(get|post|put|patch|delete|any|options|match)\s*\(\s*(.*)$/s', $stmt, $m)) {
                $verb = strtoupper($m[1]);
                $rest = $m[2];
                // Extract URI as first string literal
                if (!preg_match('/([\'"])([^\'"]*)\1/s', $rest, $mu)) { $k = $j; continue; }
                $uri = $mu[2];
                // Extract controller action
                $ctrl = null; $action = null;
                if (preg_match('/\[\s*([A-Za-z0-9_\\\\]+)::class\s*,\s*([\'"])([A-Za-z0-9_]+)\2\s*\]/s', $rest, $mc)) {
                    $ctrl = $mc[1];
                    $action = $mc[3];
                } elseif (preg_match('/([\'"])([A-Za-z0-9_\\\\]+)@([A-Za-z0-9_]+)\1/s', $rest, $mc)) {
                    $ctrl = $mc[2];
                    $action = $mc[3];
                } elseif (preg_match('/,\s*function\s*\(/s', $rest)) {
                    $ctrl = 'Closure';
                    $action = 'handle';
                }

                // Combine this route's chained prefix with group prefix
                $fullPref = trim(($pref === '' ? '' : $pref . '/') . ltrim($thisPrefix, '/'), '/');
                $fullUri = trim(($fullPref === '' ? '' : $fullPref . '/') . ltrim($uri, '/'), '/');

                $mwCombined = array_values(array_unique(array_merge($mwArr, $thisMw)));

                $verbs = [$verb];
                if ($verb === 'MATCH') {
                    // skip, too ambiguous
                    $verbs = ['GET'];
                }
                if ($verb === 'ANY') {
                    $verbs = ['GET', 'POST'];
                }
                foreach ($verbs as $v) {
                    $out[] = [
                        'method' => $v,
                        'uri' => $fullUri,
                        'controller' => $ctrl ? trim($ctrl, '\\') : null,
                        'action' => $action,
                        'middleware' => $mwCombined,
                    ];
                }
            }

            $k = $j;
            continue;
        }

        $k++;
    }

    return $out;
}

// -----------------------------------------------------------------------------
// 5. Parse all route files
// -----------------------------------------------------------------------------
foreach ($routeFiles as $module => $file) {
    try {
        $routes = parseRouteFile($file, $errors);
        $endpoints[$module] = $routes;
    } catch (Throwable $e) {
        $errors[] = "parseRouteFile($file): " . $e->getMessage();
        $endpoints[$module] = [];
    }
}

// -----------------------------------------------------------------------------
// 6. For each endpoint, try to find a FormRequest to derive body
// -----------------------------------------------------------------------------
function findRequestClassForAction(string $controllerFile, string $action): ?string {
    $src = @file_get_contents($controllerFile);
    if ($src === false) return null;
    // Remove comments
    $src = preg_replace('!/\*.*?\*/!s', '', $src);
    $src = preg_replace('!//[^\n]*!', '', $src);
    // Find function $action(...)
    $pattern = '/function\s+' . preg_quote($action, '/') . '\s*\(([^)]*)\)/s';
    if (!preg_match($pattern, $src, $m)) return null;
    $params = $m[1];
    // Look for XxxRequest type-hint
    if (preg_match('/([A-Za-z0-9_\\\\]*Request)\s+\$/', $params, $mm)) {
        $cls = $mm[1];
        $short = substr($cls, strrpos($cls, '\\') !== false ? strrpos($cls, '\\')+1 : 0);
        // Skip Illuminate base Request class
        if ($short === 'Request' || $short === 'HttpRequest') return null;
        return $short;
    }
    return null;
}

function parseRulesFromRequestFile(string $path): array {
    $src = @file_get_contents($path);
    if ($src === false) return [];
    // Find rules() body
    if (!preg_match('/function\s+rules\s*\([^)]*\)\s*(?::\s*[A-Za-z_\\\\|]+\s*)?\{/s', $src, $m, PREG_OFFSET_CAPTURE)) {
        return [];
    }
    $start = $m[0][1] + strlen($m[0][0]) - 1; // position of '{'
    // Walk braces to find matching }
    $depth = 0; $end = -1;
    $len = strlen($src);
    for ($i = $start; $i < $len; $i++) {
        $c = $src[$i];
        if ($c === '{') $depth++;
        elseif ($c === '}') { $depth--; if ($depth === 0) { $end = $i; break; } }
    }
    if ($end < 0) return [];
    $body = substr($src, $start+1, $end-$start-1);
    // Find return [...] or return array(...)
    if (!preg_match('/return\s*(\[.*?\])\s*;/s', $body, $mm)) {
        return [];
    }
    $arr = $mm[1];
    $rules = [];
    // Match 'field' => 'rule|rule' or 'field' => [rule, rule]
    if (preg_match_all('/[\'"]([A-Za-z0-9_.*]+)[\'"]\s*=>\s*([\'"][^\'"]*[\'"]|\[[^\]]*\])/s', $arr, $ms, PREG_SET_ORDER)) {
        foreach ($ms as $pair) {
            $field = $pair[1];
            $rhs = $pair[2];
            if ($rhs[0] === '[') {
                $parts = [];
                if (preg_match_all('/[\'"]([^\'"]+)[\'"]/', $rhs, $pm)) {
                    $parts = $pm[1];
                }
                $ruleStr = implode('|', $parts);
            } else {
                $ruleStr = trim($rhs, "'\"");
            }
            $rules[$field] = $ruleStr;
        }
    }
    return $rules;
}

function sampleValueForRule(string $rule, string $field): mixed {
    $parts = explode('|', $rule);
    foreach ($parts as $p) {
        $p = strtolower(trim($p));
        if ($p === 'email') return 'user@example.com';
        if ($p === 'boolean' || $p === 'bool') return true;
        if ($p === 'integer' || $p === 'numeric' || $p === 'int') return 0;
        if ($p === 'array') return [];
        if (strpos($p, 'date') === 0) return '2026-04-17';
        if ($p === 'url') return 'https://example.com';
        if (strpos($p, 'in:') === 0) {
            $vals = explode(',', substr($p, 3));
            return trim($vals[0] ?? '');
        }
    }
    // Fallback by field name
    $f = strtolower($field);
    if (str_contains($f, 'email')) return 'user@example.com';
    if (str_contains($f, '_id') || $f === 'id') return 1;
    if (str_contains($f, 'password')) return 'secret123';
    if (str_contains($f, 'phone') || str_contains($f, 'mobile')) return '+10000000000';
    return 'example';
}

function buildBody(array $rules): array {
    $body = [];
    foreach ($rules as $field => $rule) {
        // Handle nested (field.*.sub) — create nested arrays
        if (strpos($field, '.') !== false) {
            $segments = explode('.', $field);
            $cursor = &$body;
            for ($i = 0; $i < count($segments); $i++) {
                $seg = $segments[$i];
                $isLast = $i === count($segments) - 1;
                if ($seg === '*') {
                    if (!is_array($cursor)) $cursor = [];
                    if (empty($cursor)) $cursor[] = [];
                    $cursor = &$cursor[0];
                    continue;
                }
                if ($isLast) {
                    $cursor[$seg] = sampleValueForRule($rule, $seg);
                } else {
                    if (!isset($cursor[$seg]) || !is_array($cursor[$seg])) $cursor[$seg] = [];
                    $cursor = &$cursor[$seg];
                }
            }
            unset($cursor);
        } else {
            $body[$field] = sampleValueForRule($rule, $field);
        }
    }
    return $body;
}

// -----------------------------------------------------------------------------
// 7. Assemble Postman collection
// -----------------------------------------------------------------------------
$postmanItems = [];
$moduleCounts = [];

ksort($endpoints);

foreach ($endpoints as $module => $routes) {
    if (empty($routes)) {
        $moduleCounts[$module] = 0;
        continue;
    }
    $items = [];
    foreach ($routes as $r) {
        $method = $r['method'];
        $rawUri = $r['uri'];
        // Ensure api/v1 prefix. Core api.php already has no prefix in RouteServiceProvider, modules use Route prefix v1.
        // We'll ensure everything sits under api/v1 for display consistency.
        // Module RouteServiceProviders mount everything under /api. Only prepend
        // 'api/' if the parsed URI doesn't already start with it. Do NOT inject
        // 'v1/' — many routes live outside the v1 group.
        $uri = $rawUri;
        if (strpos($uri, 'api/') !== 0) {
            $uri = 'api/' . ltrim($uri, '/');
        }

        // Path segments
        $path = explode('/', trim($uri, '/'));
        // Convert {param} -> :param style is unnecessary; Postman supports {param}

        $headers = [
            ['key' => 'Authorization', 'value' => 'Bearer {{auth_token}}'],
            ['key' => 'Accept', 'value' => 'application/json'],
        ];
        $body = null;
        $bodyData = null;
        if (in_array($method, ['POST','PUT','PATCH'])) {
            $headers[] = ['key' => 'Content-Type', 'value' => 'application/json'];
            // Try to find form request for this action
            if (!empty($r['controller']) && !empty($r['action'])) {
                $cls = $r['controller'];
                $short = (strpos($cls, '\\') !== false) ? substr($cls, strrpos($cls, '\\')+1) : $cls;
                global $controllerFiles, $requestFiles;
                if (isset($controllerFiles[$short])) {
                    $reqClass = findRequestClassForAction($controllerFiles[$short], $r['action']);
                    if ($reqClass !== null && isset($requestFiles[$reqClass])) {
                        $rules = parseRulesFromRequestFile($requestFiles[$reqClass]);
                        if (!empty($rules)) {
                            $bodyData = buildBody($rules);
                        }
                    }
                }
            }
            if ($bodyData === null) $bodyData = [];
            $body = [
                'mode' => 'raw',
                'raw' => json_encode($bodyData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                'options' => ['raw' => ['language' => 'json']],
            ];
        }

        $ctrlShort = $r['controller'] ? ((strpos($r['controller'],'\\')!==false) ? substr($r['controller'], strrpos($r['controller'],'\\')+1) : $r['controller']) : 'Unknown';
        $name = strtoupper($method) . ' ' . $uri . ' (' . $ctrlShort . '@' . ($r['action'] ?? '?') . ')';

        $request = [
            'method' => $method,
            'header' => $headers,
            'url' => [
                'raw' => '{{base_url}}/' . $uri,
                'host' => ['{{base_url}}'],
                'path' => $path,
            ],
        ];
        if ($body !== null) $request['body'] = $body;

        $items[] = [
            'name' => $name,
            'request' => $request,
            'response' => [],
        ];
    }
    $moduleCounts[$module] = count($routes);
    $postmanItems[] = [
        'name' => $module,
        'item' => $items,
    ];
}

$collection = [
    'info' => [
        'name' => 'KiviCare API',
        '_postman_id' => bin2hex(random_bytes(8)) . '-' . bin2hex(random_bytes(4)),
        'description' => 'Auto-generated from static route parsing of ' . count($routeFiles) . ' route files.',
        'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
    ],
    'item' => $postmanItems,
    'variable' => [
        ['key' => 'base_url', 'value' => 'http://localhost:8000'],
        ['key' => 'auth_token', 'value' => ''],
    ],
];

file_put_contents($ROOT . '/postman_collection_full.json',
    json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// Environment
$env = [
    'id' => bin2hex(random_bytes(8)) . '-' . bin2hex(random_bytes(4)),
    'name' => 'KiviCare Local',
    'values' => [
        ['key' => 'base_url',  'value' => 'http://localhost:8000', 'enabled' => true, 'type' => 'default'],
        ['key' => 'auth_token','value' => '',                     'enabled' => true, 'type' => 'secret'],
        ['key' => 'api_key',   'value' => '',                     'enabled' => true, 'type' => 'secret'],
        ['key' => 'page',      'value' => '1',                    'enabled' => true, 'type' => 'default'],
        ['key' => 'per_page',  'value' => '15',                   'enabled' => true, 'type' => 'default'],
    ],
    '_postman_variable_scope' => 'environment',
];
file_put_contents($ROOT . '/postman_environment.json',
    json_encode($env, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// api_endpoints.md
$md = "# KiviCare API Endpoints\n\n";
$md .= "Auto-generated from static route parsing. Base URL: `{{base_url}}` (default `http://localhost:8000`).\n\n";
foreach ($endpoints as $module => $routes) {
    $md .= "## $module\n\n";
    if (empty($routes)) { $md .= "_No API routes found._\n\n"; continue; }
    $md .= "| Method | Endpoint | Controller@method | Auth |\n";
    $md .= "|--------|----------|-------------------|------|\n";
    foreach ($routes as $r) {
        $uri = $r['uri'];
        if (strpos($uri, 'api/') !== 0) {
            $uri = 'api/' . ltrim($uri, '/');
        }
        $ctrl = $r['controller'] ? ((strpos($r['controller'],'\\')!==false) ? substr($r['controller'], strrpos($r['controller'],'\\')+1) : $r['controller']) : 'Unknown';
        $auth = '';
        foreach ($r['middleware'] ?? [] as $mw) {
            if (stripos($mw, 'auth') !== false) { $auth = $mw; break; }
        }
        if ($auth === '') $auth = 'public';
        $md .= "| {$r['method']} | `/$uri` | {$ctrl}@" . ($r['action'] ?? '?') . " | $auth |\n";
    }
    $md .= "\n";
}
file_put_contents($ROOT . '/api_endpoints.md', $md);

// IMPORT_GUIDE.md
$guide = <<<MD
# Postman Import Guide

## Import files

1. Open Postman (desktop).
2. Click **Import** (top-left).
3. Drop in both files from the repo root:
   - `postman_collection_full.json`
   - `postman_environment.json`
4. In the top-right environment selector, choose **KiviCare Local**.

## Configure variables

Open the environment (eye icon) and set:

- `base_url` — your app URL, e.g. `http://localhost:8000`
- `auth_token` — a Sanctum token from `POST /api/v1/login` (or your auth endpoint). Paste the raw token only; the `Bearer ` prefix is already added by each request.

## Try it

1. Expand the **KiviCare API** collection.
2. Pick any `GET` request under, e.g., **Clinic** or **Appointment**.
3. Click **Send**. You should get `200 OK` (after setting a valid `auth_token`).

## Notes

- All requests send `Authorization: Bearer {{auth_token}}` and `Accept: application/json`.
- POST/PUT/PATCH bodies are pre-filled from Laravel `FormRequest::rules()` when one was detected; otherwise the body is `{}` — adjust before sending.
- Path parameters such as `{id}` are preserved as Postman path variables.
MD;
file_put_contents($ROOT . '/IMPORT_GUIDE.md', $guide);

// -----------------------------------------------------------------------------
// 8. Report
// -----------------------------------------------------------------------------
echo "=== Endpoint counts per module ===\n";
$total = 0;
ksort($moduleCounts);
foreach ($moduleCounts as $m => $c) {
    echo sprintf("  %-22s %d\n", $m, $c);
    $total += $c;
}
echo "TOTAL endpoints: $total\n";
echo "TOTAL route files parsed: " . count($routeFiles) . "\n";
$empty = array_keys(array_filter($moduleCounts, fn($v)=>$v===0));
if (!empty($empty)) {
    echo "Modules with zero API routes: " . implode(', ', $empty) . "\n";
} else {
    echo "Modules with zero API routes: none\n";
}
if (!empty($errors)) {
    echo "\n=== Parse errors (skipped) ===\n";
    foreach ($errors as $e) echo "  - $e\n";
} else {
    echo "\nNo parse errors.\n";
}
echo "\nFiles written:\n";
foreach (['postman_collection_full.json','postman_environment.json','api_endpoints.md','IMPORT_GUIDE.md'] as $f) {
    $p = $ROOT . '/' . $f;
    echo "  $f: " . (file_exists($p) ? filesize($p) . ' bytes' : 'MISSING') . "\n";
}
