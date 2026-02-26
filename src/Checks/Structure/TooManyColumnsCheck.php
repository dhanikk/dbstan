<?php
namespace Itpathsolutions\DBStan\Checks\Structure;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class TooManyColumnsCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Too Many Columns';
    }

    public function category(): string
    {
        return 'structure';
    }

    // Add comment to explain the purpose of this check
    // This check identifies tables that have an excessive number of columns, which can be a sign of poor database design. Tables with too many columns can be difficult to maintain, understand, and query efficiently. They may indicate that the table is trying to store too much information in a single entity, which can lead to performance issues and make it harder for developers to work with the database. This check helps encourage better database design by flagging tables that may benefit from normalization or splitting into multiple related tables.
    
    public function run(array $schema): array
    {
        $issues = [];

        $maxColumns = $this->config['max_columns'] ?? 25;

        foreach ($schema as $table => $data) {

            $columnCount = count($data['columns']);

            if ($columnCount > $maxColumns) {

                $issues["too_many_columns"][] = "⚠️  [WARNING] '$table' table has too many columns ($columnCount)";
            }
        }

        return $issues;
    }
}