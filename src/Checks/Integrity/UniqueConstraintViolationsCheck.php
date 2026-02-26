<?php

namespace Itpathsolutions\DBStan\Checks\Integrity;

use Itpathsolutions\DBStan\Checks\BaseCheck;
use Illuminate\Support\Facades\DB;

class UniqueConstraintViolationsCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Unique Constraint Violations';
    }

    public function category(): string
    {
        return 'integrity';
    }

    // Add comment to explain the purpose of this check
    // This check identifies columns that are commonly expected to be unique (like "email" or "slug") but do not have a UNIQUE constraint, and checks if there are duplicate values in those columns. It helps catch potential data integrity issues where uniqueness is expected but not enforced at the database level.
    public function run(array $schema): array
    {
        $issues = [];

        $uniqueCandidates = ['email', 'slug'];

        foreach ($schema as $table => $data) {

            $columns = array_column($data['columns'] ?? [], 'Field');

            foreach ($uniqueCandidates as $field) {

                if (!in_array($field, $columns)) {
                    continue;
                }

                // Check if duplicate exists (optimized)
                $duplicateExists = DB::table($table)
                    ->select($field)
                    ->groupBy($field)
                    ->havingRaw('COUNT(*) > 1')
                    ->limit(1)
                    ->exists();

                if ($duplicateExists) {

                    $issues["duplicate_values"][] =
                        "🚨 [UNIQUE VIOLATION] '$table.$field' column has duplicate values";
                }
            }
        }

        return $issues;
    }
}