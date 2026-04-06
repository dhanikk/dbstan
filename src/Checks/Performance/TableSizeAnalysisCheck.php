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

    /**
     * Detect large tables and database size issues
     */
    public function run(array $schema): array
    {
        $issues = [];

        $tableThresholdMB = (float) ($this->config['large_table_mb'] ?? 100);
        $databaseThresholdMB = (float) ($this->config['database_size_mb'] ?? 2048);
        $dominanceRatio = (float) ($this->config['table_dominance_ratio'] ?? 0.40);
        $unusualTableRatio = (float) ($this->config['unusually_large_table_ratio'] ?? 0.20);

        $driver = DB::getDriverName();
        $database = DB::getDatabaseName();

        // ✅ Fetch table sizes based on DB driver
        if ($driver === 'mysql') {

            $tables = DB::select("
                SELECT 
                    TABLE_NAME,
                    ROUND((DATA_LENGTH)/1024/1024, 2) AS data_mb,
                    ROUND((INDEX_LENGTH)/1024/1024, 2) AS index_mb,
                    ROUND((DATA_LENGTH + INDEX_LENGTH)/1024/1024, 2) AS total_mb
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = ?
            ", [$database]);

        } elseif ($driver === 'pgsql') {

            $tables = DB::select("
                SELECT
                    relname AS table_name,
                    pg_relation_size(relid) / 1024 / 1024 AS data_mb,
                    pg_indexes_size(relid) / 1024 / 1024 AS index_mb,
                    pg_total_relation_size(relid) / 1024 / 1024 AS total_mb
                FROM pg_catalog.pg_statio_user_tables
            ");

        } else {
            return [];
        }

        $dbTotalMB = 0.0;
        $sizes = [];

        foreach ($tables as $row) {

            // ✅ Normalize table name
            $tableName = $row->TABLE_NAME ?? $row->table_name;

            $dataMB = (float) ($row->data_mb ?? 0);
            $indexMB = (float) ($row->index_mb ?? 0);
            $totalMB = (float) ($row->total_mb ?? 0);

            $sizes[$tableName] = [
                'data_mb' => $dataMB,
                'index_mb' => $indexMB,
                'total_mb' => $totalMB,
            ];

            $dbTotalMB += $totalMB;
        }

        // ✅ Database size check
        if ($dbTotalMB > $databaseThresholdMB) {
            $issues['database_size_alert'][] =
                "\033[0;30;43m[SIZE ALERT]\033[0m Database size is " . round($dbTotalMB, 2) . "MB (threshold: {$databaseThresholdMB}MB). Consider archiving, partitioning, and index optimization";
        }

        foreach (array_keys($schema) as $table) {

            $sizeMeta = $sizes[$table] ?? [
                'data_mb' => 0.0,
                'index_mb' => 0.0,
                'total_mb' => 0.0
            ];

            $sizeMB = (float) $sizeMeta['total_mb'];
            $ratio = $dbTotalMB > 0 ? ($sizeMB / $dbTotalMB) : 0;

            // Large table check
            if ($sizeMB > $tableThresholdMB) {
                $issues["size_alert"][] =
                    "\033[0;30;43m[SIZE ALERT]\033[0m '{$table}' table is {$sizeMB}MB (data: {$sizeMeta['data_mb']}MB, indexes: {$sizeMeta['index_mb']}MB)";
            }

            // Unusual size ratio
            if ($ratio >= $unusualTableRatio) {
                $issues['unusually_large_table'][] =
                    "\033[0;30;43m[WARNING]\033[0m '{$table}' uses " . round($ratio * 100, 2) . "% of DB size";
            }

            // Dominant table
            if ($ratio >= $dominanceRatio) {
                $issues['table_storage_dominance'][] =
                    "\033[0;37;41m[ERROR]\033[0m '{$table}' dominates storage (" . round($ratio * 100, 2) . "%)";
            }
        }

        return $issues;
    }
}