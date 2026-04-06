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

    /**
     * This check identifies tables without PRIMARY or UNIQUE constraints,
     * which may allow duplicate rows and lead to data integrity issues.
     */
    public function run(array $schema): array
    {
        $issues = [];

        foreach ($schema as $table => $data) {

            $indexes = $data['indexes'] ?? [];

            $hasPrimary = false;
            $hasUnique  = false;

            foreach ($indexes as $index) {

                // ✅ MySQL support
                if (isset($index->Key_name)) {

                    if ($index->Key_name === 'PRIMARY') {
                        $hasPrimary = true;
                    }

                    if (($index->Non_unique ?? 1) == 0) {
                        $hasUnique = true;
                    }
                }

                // ✅ PostgreSQL support
                elseif (isset($index->indexdef)) {

                    $definition = strtolower($index->indexdef);

                    if (str_contains($definition, 'primary key')) {
                        $hasPrimary = true;
                    }

                    if (str_contains($definition, 'unique')) {
                        $hasUnique = true;
                    }
                }
            }

            if (!$hasPrimary && !$hasUnique) {

                $issues["duplicate_rows_risk"][] =
                    "\033[0;30;43m[DATA INTEGRITY]\033[0m '{$table}' table has no PRIMARY or UNIQUE constraint — duplicate rows possible";
            }
        }

        return $issues;
    }
}