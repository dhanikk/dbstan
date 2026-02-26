<?php
namespace Itpathsolutions\DBStan\Checks\Architecture;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class JsonOveruseCheck extends BaseCheck
{
    public function name(): string
    {
        return 'JSON Column Overuse';
    }

    public function category(): string
    {
        return 'architecture';
    }

    // Add comment to explain the purpose of this check
    // This check identifies tables that have an excessive number of JSON columns, which can lead to performance issues and maintenance challenges. It encourages developers to consider more structured data models when appropriate.
    public function run(array $schema): array
    {
        $issues = [];

        $maxJsonColumns = $this->config['max_json_columns'] ?? 2;

        foreach ($schema as $table => $data) {

            $jsonColumns = array_filter(
                $data['columns'] ?? [],
                fn ($col) => str_contains(strtolower($col->Type), 'json')
            );

            if (count($jsonColumns) > $maxJsonColumns) {

                $issues["json_overuse"][] =
                    "⚠️  [WARNING] '$table' table uses too many JSON columns (" . count($jsonColumns) . ")";
            }
        }

        return $issues;
    }
}