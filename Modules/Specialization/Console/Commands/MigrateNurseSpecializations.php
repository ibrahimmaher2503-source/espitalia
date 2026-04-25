<?php

namespace Modules\Specialization\Console\Commands;

use Illuminate\Console\Command;
use Modules\RequestNurse\Models\Nurse;
use Modules\Specialization\Models\Specialization;

class MigrateNurseSpecializations extends Command
{
    protected $signature = 'specialization:migrate-nurses';

    protected $description = 'Migrate nurse free-text specialization values to structured pivot records';

    public function handle(): int
    {
        $specializations = Specialization::all();

        $lookupAr = [];
        $lookupEn = [];
        foreach ($specializations as $spec) {
            if ($spec->name_ar) {
                $lookupAr[mb_strtolower(trim($spec->name_ar))] = $spec->id;
            }
            if ($spec->name_en) {
                $lookupEn[mb_strtolower(trim($spec->name_en))] = $spec->id;
            }
        }

        $nurses = Nurse::whereNotNull('specialization_ar')
            ->orWhereNotNull('specialization_en')
            ->get();

        $matched = 0;
        $unmatched = 0;

        foreach ($nurses as $nurse) {
            $specIds = [];

            $arText = mb_strtolower(trim($nurse->specialization_ar ?? ''));
            $enText = mb_strtolower(trim($nurse->specialization_en ?? ''));

            if ($arText && isset($lookupAr[$arText])) {
                $specIds[] = $lookupAr[$arText];
            }
            if ($enText && isset($lookupEn[$enText])) {
                $specIds[] = $lookupEn[$enText];
            }

            $specIds = array_unique($specIds);

            if (! empty($specIds)) {
                $nurse->specializations()->syncWithoutDetaching($specIds);
                $matched++;
                $this->info("Matched nurse #{$nurse->id}: {$nurse->specialization_en}");
            } else {
                $unmatched++;
                $this->warn("Unmatched nurse #{$nurse->id}: AR=\"{$nurse->specialization_ar}\" EN=\"{$nurse->specialization_en}\"");
            }
        }

        $this->newLine();
        $this->info("Migration complete: {$matched} matched, {$unmatched} unmatched (flagged for manual review).");

        return self::SUCCESS;
    }
}
