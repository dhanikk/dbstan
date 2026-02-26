<?php

namespace Itpathsolutions\DBStan\Checks\Performance;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class LogTableIndexingCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Log Table Indexing';
    }

    public function category(): string
    {
        return 'performance';
    }

    // Add comment to explain the purpose of this check
    // This check identifies tables that contain "log" in their name and checks if they have indexes on commonly queried columns like "created_at" and "user_id". Proper indexing on log tables can significantly improve query performance when filtering by these columns, which are often used in log analysis and monitoring.
    public function run(array $schema): array
    {
        $issues = [];

        foreach ($schema as $table => $data) {

            // Detect log tables
            if (!str_contains(strtolower($table), 'log')) {
                continue;
            }

            $columns = array_column($data['columns'] ?? [], 'Field');
            $indexColumns = array_column($data['indexes'] ?? [], 'Column_name');

            // Check created_at index
            if (in_array('created_at', $columns) &&
                !in_array('created_at', $indexColumns)) {

                $issues["log_indexing"][] =
                    "🚀 [PERFORMANCE] '$table.created_at' column should be indexed for faster log queries";
            }

            // Check user_id index (if exists)
            if (in_array('user_id', $columns) &&
                !in_array('user_id', $indexColumns)) {

                $issues["log_indexing"][] =
                    "🚀 [PERFORMANCE] '$table.user_id' column should be indexed in log table";
            }
        }

        return $issues;
    }
}