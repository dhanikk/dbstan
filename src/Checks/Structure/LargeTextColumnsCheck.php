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

    /**
     * Detect TEXT-like columns that may impact performance
     */
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

                // ✅ Normalize column type
                if (isset($column->type)) {
                    $type = strtolower($column->type);
                } elseif (isset($column->Type)) {
                    $type = strtolower($column->Type); // MySQL
                } elseif (isset($column->data_type)) {
                    $type = strtolower($column->data_type); // PostgreSQL
                } else {
                    continue;
                }

                // ✅ Detect TEXT-like types
                if (
                    str_contains($type, 'text') ||   // MySQL: text, longtext, mediumtext
                    $type === 'text'                 // PostgreSQL: text
                ) {
                    $issues["large_text"][] =
                        "\033[0;30;43m[PERF RISK]\033[0m '{$table}.{$field}' column is TEXT — consider splitting table or optimizing usage";
                }
            }
        }

        return $issues;
    }
}