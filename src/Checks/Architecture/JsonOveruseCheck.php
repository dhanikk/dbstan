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

    /**
     * This check identifies tables that have an excessive number of JSON columns,
     * which can lead to performance issues and maintenance challenges.
     */
    public function run(array $schema): array
    {
        $issues = [];

        $maxJsonColumns = $this->config['max_json_columns'] ?? 2;

        foreach ($schema as $table => $data) {

            $jsonColumns = array_filter(
                $data['columns'] ?? [],
                function ($col) {

                    // ✅ Preferred (normalized schema)
                    if (isset($col->type)) {
                        return str_contains(strtolower($col->type), 'json');
                    }

                    // 🔁 Fallback (backward compatibility for MySQL old structure)
                    if (isset($col->Type)) {
                        return str_contains(strtolower($col->Type), 'json');
                    }

                    // 🔁 Fallback (PostgreSQL raw structure)
                    if (isset($col->data_type)) {
                        return str_contains(strtolower($col->data_type), 'json');
                    }

                    return false;
                }
            );

            if (count($jsonColumns) > $maxJsonColumns) {

                $issues["json_overuse"][] =
                    "\033[0;30;43m[WARNING]\033[0m '{$table}' table uses too many JSON columns (" . count($jsonColumns) . ")";
            }
        }

        return $issues;
    }
}