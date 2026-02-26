<?php

namespace Itpathsolutions\DBStan\Checks\Architecture;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class PolymorphicOveruseCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Polymorphic Relation Overuse';
    }

    public function category(): string
    {
        return 'architecture';
    }

    // Add comment to explain the purpose of this check
    // This check identifies tables that use polymorphic relations (i.e., pairs of columns like "xxx_type" and "xxx_id") and checks if they have appropriate composite indexes. Polymorphic relations can lead to performance issues if not indexed properly, especially when filtering by the related type and ID. Additionally, this check flags tables that have multiple polymorphic relations, which can be a sign of complex and potentially problematic database design. By ensuring that polymorphic relations are indexed and not overused, this check helps promote better database performance and design practices.
    
    public function run(array $schema): array
    {
        $issues = [];

        foreach ($schema as $table => $data) {

            $fields = array_map(
                fn($col) => strtolower($col->Field),
                $data['columns'] ?? []
            );

            $indexes = array_column($data['indexes'] ?? [], 'Column_name');

            $polymorphicPairs = [];

            foreach ($fields as $field) {

                if (str_ends_with($field, '_type')) {

                    $relatedId = str_replace('_type', '_id', $field);

                    if (in_array($relatedId, $fields)) {
                        $polymorphicPairs[] = [$field, $relatedId];

                        // Check composite index existence
                        $hasIndex =
                            in_array($field, $indexes) &&
                            in_array($relatedId, $indexes);

                        if (!$hasIndex) {
                            $issues["polymorphic_index"][] =
                                "⚡ [PERF] '$table' table has unindexed polymorphic relation ($field, $relatedId)";
                        }
                    }
                }
            }

            // Overuse warning
            if (count($polymorphicPairs) > 1) {
                $issues["polymorphic_overuse"][] =
                    "🧬 [ARCH RISK] '$table' table uses multiple polymorphic relations (" . count($polymorphicPairs) . ")";
            }
        }

        return $issues;
    }
}