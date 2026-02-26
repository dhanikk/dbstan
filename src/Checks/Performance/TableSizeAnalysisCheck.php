<?php

namespace Itpathsolutions\DBStan\Checks\Performance;

use Itpathsolutions\DBStan\Checks\BaseCheck;
use Illuminate\Support\Facades\DB;

class TableSizeAnalysisCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Table Size Analysis';
    }

    public function category(): string
    {
        return 'performance';
    }

    // Add comment to explain the purpose of this check
    // This check identifies tables that exceed a certain size threshold (e.g., 100MB) and flags them for review. Large tables can lead to performance issues, especially if they are not properly indexed or if they contain historical data that could be archived. This check helps highlight tables that may need attention for optimization, such as indexing, archiving old data, or implementing partitioning strategies.
    
    public function run(array $schema): array
    {
        $issues = [];
        $thresholdMB = 100;

        $database = DB::getDatabaseName();

        // Fetch all table sizes in one query (avoid N+1)
        $tables = DB::select("
            SELECT 
                TABLE_NAME,
                ROUND((DATA_LENGTH + INDEX_LENGTH)/1024/1024, 2) AS size_mb
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = ?
        ", [$database]);

        // Convert result to associative array
        $sizes = [];
        foreach ($tables as $row) {
            $sizes[$row->TABLE_NAME] = $row->size_mb ?? 0;
        }

        foreach (array_keys($schema) as $table) {

            $sizeMB = $sizes[$table] ?? 0;

            if ($sizeMB > $thresholdMB) {
                $issues["size_alert"][] =
                    "📊 [SIZE ALERT] '$table' table is {$sizeMB}MB — review indexing, archiving, or partitioning strategy";
            }
        }

        return $issues;
    }
}