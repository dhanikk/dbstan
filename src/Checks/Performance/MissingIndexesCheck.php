<?php

namespace Itpathsolutions\DBStan\Checks\Performance;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class MissingIndexesCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Missing Foreign Key Indexes';
    }

    public function category(): string
    {
        return 'performance';
    }

    // Add comment to explain the purpose of this check
    /* This check identifies columns that look like foreign keys (ending with "_id") but do not have an index. Missing indexes on foreign key columns can lead to slow query performance, especially when joining tables or filtering by those columns. This check helps ensure that foreign key columns are properly indexed for optimal performance. */
    public function run(array $schema): array
    {
        $issues = [];

        foreach ($schema as $table => $data) {

            // Collect indexed columns
            $indexColumns = array_column($data['indexes'] ?? [], 'Column_name');

            foreach ($data['columns'] as $column) {

                if (
                    str_ends_with($column->Field, '_id') &&
                    !in_array($column->Field, $indexColumns)
                ) {
                    $issues["missing_index"][] =
                        "❌ [ERROR] '$table.{$column->Field}' column looks like FK but has no index";
                }
            }
        }

        return $issues;
    }
}