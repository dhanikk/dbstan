<?php

namespace Itpathsolutions\DBStan\Checks\Integrity;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class DuplicateRowsCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Duplicate Rows Risk';
    }

    public function category(): string
    {
        return 'integrity';
    }

    // Add comment to explain the purpose of this check
    // This check identifies tables that do not have a PRIMARY or UNIQUE constraint, which means that duplicate rows could exist in those tables, potentially leading to data integrity issues.
    public function run(array $schema): array
    {
        $issues = [];

        foreach ($schema as $table => $data) {

            $indexes = $data['indexes'] ?? [];

            $hasPrimary = false;
            $hasUnique  = false;

            foreach ($indexes as $index) {

                if ($index->Key_name === 'PRIMARY') {
                    $hasPrimary = true;
                }

                if (($index->Non_unique ?? 1) == 0) {
                    $hasUnique = true;
                }
            }

            if (!$hasPrimary && !$hasUnique) {

                $issues["duplicate_rows_risk"][] =
                    "⚠️  [DATA INTEGRITY] '$table' table has no PRIMARY or UNIQUE constraint — duplicate rows possible";
            }
        }

        return $issues;
    }
}