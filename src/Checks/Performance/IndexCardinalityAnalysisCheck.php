<?php

namespace Itpathsolutions\DBStan\Checks\Performance;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class IndexCardinalityAnalysisCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Index Cardinality Analysis';
    }

    public function category(): string
    {
        return 'performance';
    }

    public function run(array $schema): array
    {
        $issues = [];

        $minRowsToEvaluate = (int) ($this->config['index_cardinality_min_rows'] ?? 1000);
        $lowCardinalityRatio = (float) ($this->config['index_cardinality_ratio_threshold'] ?? 0.05);
        $lowCardinalityAbsolute = (int) ($this->config['index_cardinality_absolute_threshold'] ?? 20);

        foreach ($schema as $table => $data) {
            $tableRows = max((int) ($data['table_rows'] ?? 0), 0);

            if ($tableRows < $minRowsToEvaluate) {
                continue;
            }

            $indexColumnsByName = [];
            foreach ($data['indexes'] ?? [] as $index) {
                $indexName = (string) ($index->Key_name ?? '');
                $seq = (int) ($index->Seq_in_index ?? 1);
                $column = strtolower((string) ($index->Column_name ?? ''));

                if ($indexName === '') {
                    continue;
                }

                $indexColumnsByName[$indexName][$seq] = $column;
            }

            foreach ($data['indexes'] ?? [] as $index) {
                $indexName = (string) ($index->Key_name ?? '');
                $columnName = (string) ($index->Column_name ?? '');
                $columnLower = strtolower($columnName);
                $seq = (int) ($index->Seq_in_index ?? 1);
                $nonUnique = (int) ($index->Non_unique ?? 1);
                $cardinality = (int) ($index->Cardinality ?? 0);

                if ($indexName === 'PRIMARY' || $nonUnique === 0 || $seq !== 1) {
                    continue;
                }

                $ratio = $tableRows > 0 ? ($cardinality / $tableRows) : 0;

                if ($cardinality <= $lowCardinalityAbsolute || $ratio <= $lowCardinalityRatio) {
                    $issues['index_low_cardinality'][] =
                        "\033[0;30;43m[PERF]\033[0m '$table.$columnName' index '$indexName' has low cardinality ($cardinality/$tableRows). Review if this index is useful";
                }

                // Heuristic for common misuse: standalone index on status/flag columns.
                $isStatusOrFlag =
                    $columnLower === 'status' ||
                    $columnLower === 'state' ||
                    str_starts_with($columnLower, 'is_') ||
                    str_starts_with($columnLower, 'has_') ||
                    str_ends_with($columnLower, '_status');

                if ($isStatusOrFlag && isset($indexColumnsByName[$indexName])) {
                    $parts = $indexColumnsByName[$indexName];
                    ksort($parts);
                    $orderedParts = array_values($parts);

                    if (count($orderedParts) === 1) {
                        $issues['index_cardinality_misuse'][] =
                            "\033[0;30;43m[PERF]\033[0m '$table.$columnName' has a standalone index '$indexName'. Low-cardinality status/flag columns are usually better as part of a composite index";
                    }
                }
            }
        }

        return $issues;
    }
}
