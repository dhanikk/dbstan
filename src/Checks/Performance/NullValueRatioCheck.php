<?php

namespace Itpathsolutions\DBStan\Checks\Performance;

use Itpathsolutions\DBStan\Checks\BaseCheck;
use Illuminate\Support\Facades\DB;

class NullValueRatioCheck extends BaseCheck
{
    public function name(): string
    {
        return 'High NULL Value Ratio';
    }

    public function category(): string
    {
        return 'performance';
    }

    /**
     * Detect columns with high NULL ratio (>50%)
     */
    public function run(array $schema): array
    {
        $issues = [];

        foreach ($schema as $table => $data) {

            // ✅ Get total row count once
            $totalCount = DB::table($table)->count();

            if ($totalCount === 0) {
                continue;
            }

            foreach ($data['columns'] ?? [] as $column) {

                // ✅ Normalize column name
                if (isset($column->name)) {
                    $field = $column->name;
                } elseif (isset($column->Field)) {
                    $field = $column->Field; // MySQL
                } elseif (isset($column->column_name)) {
                    $field = $column->column_name; // PostgreSQL
                } else {
                    continue;
                }

                // ✅ Normalize nullable flag
                if (isset($column->nullable)) {
                    $isNullable = $column->nullable;
                } elseif (isset($column->Null)) {
                    $isNullable = strtoupper($column->Null) === 'YES'; // MySQL
                } elseif (isset($column->is_nullable)) {
                    $isNullable = strtoupper($column->is_nullable) === 'YES'; // PostgreSQL
                } else {
                    $isNullable = false;
                }

                // Only check nullable columns
                if (!$isNullable) {
                    continue;
                }

                // ✅ Optimized: single query instead of 2
                $result = DB::table($table)
                    ->selectRaw("
                        COUNT(*) as total,
                        COUNT({$field}) as non_null
                    ")
                    ->first();

                if (!$result || $result->total == 0) {
                    continue;
                }

                $nullCount = $result->total - $result->non_null;
                $ratio = $nullCount / $result->total;

                if ($ratio > 0.5) {

                    $percentage = round($ratio * 100, 2);

                    $issues["high_null_ratio"][] =
                        "\033[0;30;43m[DATA QUALITY]\033[0m '{$table}.{$field}' column has high NULL ratio ({$percentage}%)";
                }
            }
        }

        return $issues;
    }
}