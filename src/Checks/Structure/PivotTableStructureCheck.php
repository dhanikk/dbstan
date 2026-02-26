<?php

namespace Itpathsolutions\DBStan\Checks\Structure;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class PivotTableStructureCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Pivot Table Structure Check';
    }

    public function category(): string
    {
        return 'structure';
    }

    /**
     * Detects improper pivot table structures.
     *
     * A proper pivot table:
     * - Has exactly 2 foreign key columns (ending with _id)
     * - Should NOT have an 'id' column
     * - Should NOT have timestamps
     * - Should NOT have extra business columns
     */
    public function run(array $schema): array
    {
        $issues = [];

        foreach ($schema as $table => $data) {

            $columns = $data['columns'] ?? [];
            $columnNames = array_column($columns, 'Field');

            // Detect FK-like columns
            $fkColumns = array_filter(
                $columnNames,
                fn($col) => str_ends_with($col, '_id')
            );

            // Heuristic: pivot must have exactly 2 FK columns
            if (count($fkColumns) === 2) {

                $nonFkColumns = array_diff(
                    $columnNames,
                    $fkColumns
                );

                $hasId = in_array('id', $columnNames);

                $hasTimestamps =
                    in_array('created_at', $columnNames) ||
                    in_array('updated_at', $columnNames);

                // 1️⃣ ID column check
                if ($hasId) {
                    $issues["pivot_structure"][] =
                        "⚠️  [PIVOT] '$table' table should not contain an 'id' column";
                }

                // 2️⃣ Timestamp check
                if ($hasTimestamps) {
                    $issues["pivot_structure"][] =
                        "⚠️  [PIVOT] '$table' table should not contain timestamps";
                }

                // 3️⃣ Extra columns check
                $extraColumns = array_diff(
                    $nonFkColumns,
                    ['id', 'created_at', 'updated_at']
                );

                if (!empty($extraColumns)) {
                    $issues["pivot_structure"][] =
                        "🔗 [PIVOT DESIGN] '$table' table looks like pivot but contains extra columns: " .
                        implode(', ', $extraColumns);
                }
            }
        }

        return $issues;
    }
}