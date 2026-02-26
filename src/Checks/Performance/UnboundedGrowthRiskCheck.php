<?php

namespace Itpathsolutions\DBStan\Checks\Performance;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class UnboundedGrowthRiskCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Unbounded Growth Risk';
    }

    public function category(): string
    {
        return 'performance';
    }

    // Add comment to explain the purpose of this check
    // This check identifies tables that are likely to grow indefinitely (e.g., those containing "log", "event", "activity", etc.) and checks if they have an index on the "created_at" column. Tables that grow without bounds can lead to performance degradation over time, especially if they are frequently queried by date. Indexing the "created_at" column can help maintain query performance as the table grows, making it easier to filter and manage large datasets. This check helps ensure that growth-prone tables are optimized for long-term performance.
    public function run(array $schema): array
    {
        $issues = [];

        $growthPatterns = [
            'log',
            'event',
            'activity',
            'audit',
            'notification',
            'session',
            'job',
        ];

        foreach ($schema as $table => $data) {

            $tableLower = strtolower($table);

            $isGrowthTable = collect($growthPatterns)
                ->contains(fn($pattern) => str_contains($tableLower, $pattern));

            if (!$isGrowthTable) {
                continue;
            }

            $columns = array_column($data['columns'] ?? [], 'Field');
            $indexColumns = array_column($data['indexes'] ?? [], 'Column_name');

            // Only warn if created_at exists but not indexed
            if (
                in_array('created_at', $columns) &&
                !in_array('created_at', $indexColumns)
            ) {
                $issues["growth_risk"][] =
                    "📈 [GROWTH RISK] '$table.created_at' column should be indexed to support high-growth queries";
            }
        }

        return $issues;
    }
}