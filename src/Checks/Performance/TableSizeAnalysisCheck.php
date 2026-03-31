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
        $tableThresholdMB = (float) ($this->config['large_table_mb'] ?? 100);
        $databaseThresholdMB = (float) ($this->config['database_size_mb'] ?? 2048);
        $dominanceRatio = (float) ($this->config['table_dominance_ratio'] ?? 0.40);
        $unusualTableRatio = (float) ($this->config['unusually_large_table_ratio'] ?? 0.20);

        $database = DB::getDatabaseName();

        // Fetch all table sizes in one query (avoid N+1)
        $tables = DB::select("
            SELECT 
                TABLE_NAME,
                ROUND((DATA_LENGTH)/1024/1024, 2) AS data_mb,
                ROUND((INDEX_LENGTH)/1024/1024, 2) AS index_mb,
                ROUND((DATA_LENGTH + INDEX_LENGTH)/1024/1024, 2) AS total_mb
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = ?
        ", [$database]);

        $dbTotalMB = 0.0;

        // Convert result to associative array
        $sizes = [];
        foreach ($tables as $row) {
            $tableTotalMB = (float) ($row->total_mb ?? 0);
            $sizes[$row->TABLE_NAME] = [
                'data_mb' => (float) ($row->data_mb ?? 0),
                'index_mb' => (float) ($row->index_mb ?? 0),
                'total_mb' => $tableTotalMB,
            ];
            $dbTotalMB += $tableTotalMB;
        }

        if ($dbTotalMB > $databaseThresholdMB) {
            $issues['database_size_alert'][] =
                "\033[0;30;43m[SIZE ALERT]\033[0m Database size is " . round($dbTotalMB, 2) . "MB (threshold: {$databaseThresholdMB}MB). Consider archiving historical data, partitioning large tables, and validating index bloat";
        }

        foreach (array_keys($schema) as $table) {

            $sizeMeta = $sizes[$table] ?? ['data_mb' => 0.0, 'index_mb' => 0.0, 'total_mb' => 0.0];
            $sizeMB = (float) $sizeMeta['total_mb'];
            $ratio = $dbTotalMB > 0 ? ($sizeMB / $dbTotalMB) : 0;

            if ($sizeMB > $tableThresholdMB) {
                $issues["size_alert"][] =
                   "\033[0;30;43m[SIZE ALERT]\033[0m '$table' table is {$sizeMB}MB (data: {$sizeMeta['data_mb']}MB, indexes: {$sizeMeta['index_mb']}MB) — review indexing, archiving, or partitioning strategy";
            }

            if ($ratio >= $unusualTableRatio) {
                $issues['unusually_large_table'][] =
                    "\033[0;30;43m[WARNING]\033[0m '$table' stores " . round($ratio * 100, 2) . "% of total database size. Investigate retention/archival and query/index strategy";
            }

            if ($ratio >= $dominanceRatio) {
                $issues['table_storage_dominance'][] =
                    "\033[0;37;41m[ERROR]\033[0m '$table' dominates storage at " . round($ratio * 100, 2) . "% of database size. Prioritize optimization (partitioning, archival, and index cleanup)";
            }
        }

        return $issues;
    }
}