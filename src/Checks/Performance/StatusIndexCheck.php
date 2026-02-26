<?php

namespace Itpathsolutions\DBStan\Checks\Performance;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class StatusIndexCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Status Column Index';
    }

    public function category(): string
    {
        return 'performance';
    }

    // Add comment to explain the purpose of this check
    // This check identifies columns that are likely used for status or state (e.g., "status", "order_status", "state") but do not have an index. Indexing status columns can significantly improve query performance when filtering by those columns, which is common in many applications. This check helps ensure that status-related columns are properly indexed for optimal performance.
    public function run(array $schema): array
    {
        $issues = [];

        foreach ($schema as $table => $data) {

            $columns = array_column($data['columns'] ?? [], 'Field');
            $indexes = array_column($data['indexes'] ?? [], 'Column_name');

            foreach ($columns as $column) {

                $columnLower = strtolower($column);

                $isStatusColumn =
                    $columnLower === 'status' ||
                    str_ends_with($columnLower, '_status') ||
                    $columnLower === 'state';

                if ($isStatusColumn && !in_array($column, $indexes)) {
                    $issues["status_not_indexed"][] =
                        "📌 [PERF] '$table.$column' column should be indexed for filtering queries";
                }
            }
        }

        return $issues;
    }
}