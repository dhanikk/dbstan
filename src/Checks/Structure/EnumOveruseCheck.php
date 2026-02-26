<?php

namespace Itpathsolutions\DBStan\Checks\Structure;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class EnumOveruseCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Enum Overuse';
    }

    public function category(): string
    {
        return 'structure';
    }

    // Add comment to explain the purpose of this check
    // This check identifies tables that have multiple ENUM columns or ENUM columns with a large number of values. Overusing ENUM types can lead to maintenance challenges and may indicate that a lookup table would be a better design choice. This check helps encourage better database design by flagging potential overuse of ENUM types, which can improve flexibility and scalability in the long run.
    public function run(array $schema): array
    {
        $issues = [];

        foreach ($schema as $table => $data) {

            $enumColumns = [];

            foreach ($data['columns'] ?? [] as $column) {

                if (str_contains(strtolower($column->Type), 'enum')) {
                    $enumColumns[] = $column;
                }
            }

            // Rule 1: Too many ENUM columns in one table
            if (count($enumColumns) > 2) {
                $issues["enum_overuse"][] =
                    "⚠️  [ENUM OVERUSE] '$table' table has multiple ENUM columns (" . count($enumColumns) . ")";
            }

            // Rule 2: ENUM with too many values
            foreach ($enumColumns as $column) {

                preg_match_all("/'([^']+)'/", $column->Type, $matches);
                $valuesCount = count($matches[1] ?? []);

                if ($valuesCount > 5) {
                    $issues["enum_overuse"][] =
                        "⚠️  [ENUM SIZE] '$table.{$column->Field}' column has many ENUM values ($valuesCount) — consider lookup table";
                }
            }
        }

        return $issues;
    }
}