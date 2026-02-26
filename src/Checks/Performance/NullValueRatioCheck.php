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

    // Add comment to explain the purpose of this check
    // This check identifies columns that allow NULL values and have a high ratio of NULLs compared to total rows (greater than 50%). A high NULL ratio can indicate potential data quality issues or that the column may not be necessary. It can also impact query performance, as NULL values can affect indexing and query optimization. This check helps highlight columns that may need to be reviewed for data quality and performance improvements.
    public function run(array $schema): array
    {
        $issues = [];

        foreach ($schema as $table => $data) {

            // Get total row count once per table
            $totalCount = DB::table($table)->count();

            if ($totalCount === 0) {
                continue;
            }

            foreach ($data['columns'] ?? [] as $column) {

                // Only check nullable columns
                if (strtoupper($column->Null) !== 'YES') {
                    continue;
                }

                $nullCount = DB::table($table)
                    ->whereNull($column->Field)
                    ->count();

                $ratio = $nullCount / $totalCount;

                if ($ratio > 0.5) {

                    $percentage = round($ratio * 100, 2);

                    $issues["high_null_ratio"][] =
                        "⚠️  [DATA QUALITY] '$table.{$column->Field}' column has high NULL ratio ({$percentage}%)";
                }
            }
        }

        return $issues;
    }
}