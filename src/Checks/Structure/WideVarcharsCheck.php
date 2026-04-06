<?php

namespace Itpathsolutions\DBStan\Checks\Structure;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class WideVarcharsCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Wide Varchar Columns';
    }

    public function category(): string
    {
        return 'structure';
    }

    /**
     * Detect VARCHAR columns exceeding recommended length
     */
    public function run(array $schema): array
    {
        $issues = [];

        $maxLength = $this->config['max_varchar_length'] ?? 191;

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

                $length = null;

                // ✅ MySQL: extract from varchar(255)
                if (preg_match('/varchar\((\d+)\)/i', $type, $matches)) {
                    $length = (int) $matches[1];
                }

                // ✅ PostgreSQL: use character_maximum_length
                elseif (
                    ($type === 'character varying' || $type === 'varchar') &&
                    isset($column->character_maximum_length)
                ) {
                    $length = (int) $column->character_maximum_length;
                }

                if ($length !== null && $length > $maxLength) {

                    $issues["wide_varchar"][] =
                        "\033[0;30;43m[WARNING]\033[0m '{$table}.{$field}' column is VARCHAR({$length}) exceeds recommended limit ({$maxLength})";
                }
            }
        }

        return $issues;
    }
}