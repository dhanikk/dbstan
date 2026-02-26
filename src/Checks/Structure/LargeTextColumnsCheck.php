<?php

namespace Itpathsolutions\DBStan\Checks\Structure;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class LargeTextColumnsCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Large TEXT Columns';
    }

    public function category(): string
    {
        return 'structure';
    }

    // Add comment to explain the purpose of this check
    // This check identifies columns that use the TEXT data type, which can lead to performance issues if they are frequently queried or if they contain large amounts of data. Large TEXT columns can also indicate that the table may be trying to store too much information in a single row, which can be a sign of poor database design. This check helps highlight potential performance risks and encourages better database design by suggesting that large text data might be better stored in a separate table or using a different data type.
    public function run(array $schema): array
    {
        $issues = [];

        foreach ($schema as $table => $data) {

            foreach ($data['columns'] ?? [] as $column) {

                if (str_contains(strtolower($column->Type), 'text')) {

                    $issues["large_text"][] =
                        "📦 [PERF RISK] '$table.{$column->Field}' column is TEXT — consider splitting table";
                }
            }
        }

        return $issues;
    }
}