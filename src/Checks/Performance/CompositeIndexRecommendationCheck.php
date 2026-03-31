<?php

namespace Itpathsolutions\DBStan\Checks\Performance;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class CompositeIndexRecommendationCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Composite Index Recommendation';
    }

    public function category(): string
    {
        return 'performance';
    }

    public function run(array $schema): array
    {
        $issues = [];

        foreach ($schema as $table => $data) {
            $columns = array_map(
                fn($col) => strtolower((string) ($col->Field ?? '')),
                $data['columns'] ?? []
            );

            if (!in_array('user_id', $columns, true)) {
                continue;
            }

            $statusColumn = null;
            foreach (['status', 'state'] as $candidate) {
                if (in_array($candidate, $columns, true)) {
                    $statusColumn = $candidate;
                    break;
                }
            }

            if ($statusColumn === null) {
                continue;
            }

            $indexesByName = [];
            foreach ($data['indexes'] ?? [] as $index) {
                $keyName = (string) ($index->Key_name ?? '');
                $seq = (int) ($index->Seq_in_index ?? 1);
                $column = strtolower((string) ($index->Column_name ?? ''));

                if ($keyName === '') {
                    continue;
                }

                $indexesByName[$keyName][$seq] = $column;
            }

            $hasComposite = false;
            foreach ($indexesByName as $parts) {
                ksort($parts);
                $ordered = array_values($parts);

                if (count($ordered) >= 2 && $ordered[0] === 'user_id' && $ordered[1] === $statusColumn) {
                    $hasComposite = true;
                    break;
                }
            }

            if (!$hasComposite) {
                $issues['composite_index_recommendation'][] =
                    "\033[0;30;43m[PERF]\033[0m '$table' often filters by user_id + $statusColumn. Consider adding INDEX(user_id, $statusColumn)";
            }
        }

        return $issues;
    }
}
