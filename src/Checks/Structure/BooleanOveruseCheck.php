<?php

namespace Itpathsolutions\DBStan\Checks\Architecture;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class BooleanOveruseCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Boolean Overuse';
    }

    public function category(): string
    {
        return 'architecture';
    }

    // Add comment to explain the purpose of this check
    // This check identifies tables that have more than 4 boolean columns (e.g., tinyint(1) or boolean). Having too many boolean flags in a table can be a sign of poor database design, as it may indicate that the table is trying to represent multiple states or conditions that could be better modeled using an ENUM type or a separate state machine. This check helps encourage better database design practices by flagging potential overuse of boolean columns.
    
    public function run(array $schema): array
    {
        $issues = [];

        foreach ($schema as $table => $data) {

            $booleanColumns = array_filter(
                $data['columns'] ?? [],
                function ($column) {

                    $type = strtolower($column->Type);

                    return str_contains($type, 'tinyint(1)')
                        || str_contains($type, 'boolean');
                }
            );

            $count = count($booleanColumns);

            if ($count > 4) {
                $issues["boolean_overuse"][] =
                    "⚠️  [ARCH WARNING] '$table' table has many boolean flags ($count). Consider using status ENUM or state machine.";
            }
        }

        return $issues;
    }
}