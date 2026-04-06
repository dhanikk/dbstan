<?php

namespace Itpathsolutions\DBStan\Checks\Structure;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class MixedDomainColumnsCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Mixed Domain Columns';
    }

    public function category(): string
    {
        return 'structure';
    }

    public function run(array $schema): array
    {
        $issues = [];

        foreach ($schema as $table => $data) {

            foreach ($data['columns'] ?? [] as $column) {

                // ✅ Normalize column name
                if (isset($column->name)) {
                    $field = $column->name;
                } elseif (isset($column->Field)) {
                    $field = $column->Field; // MySQL
                } elseif (isset($column->column_name)) {
                    $field = $column->column_name; // PostgreSQL
                } else {
                    continue;
                }

                // ✅ Normalize type
                if (isset($column->type)) {
                    $type = strtolower($column->type);
                } elseif (isset($column->Type)) {
                    $type = strtolower($column->Type); // MySQL
                } elseif (isset($column->data_type)) {
                    $type = strtolower($column->data_type); // PostgreSQL
                } else {
                    continue;
                }

                if ($this->isMixedDomain($field, $type)) {

                    $issues["mixed_domain"][] =
                        "\033[0;37;45m[DOMAIN MIX]\033[0m '{$table}.{$field}' column mixes different types of data";
                }
            }
        }

        return $issues;
    }

    /**
     * Heuristic detection of mixed domain columns
     */
    protected function isMixedDomain(string $field, string $type): bool
    {
        // Example heuristics
        return
            str_contains($type, 'varchar') &&
            (
                str_contains($field, 'data') ||
                str_contains($field, 'info') ||
                str_contains($field, 'details')
            );
    }
}