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

    /**
     * This check identifies "_id" columns that do not have indexes.
     * Missing indexes on FK-like columns can slow down joins and queries.
     */
    public function run(array $schema): array
    {
        $issues = [];

        foreach ($schema as $table => $data) {

            $indexes = $data['indexes'] ?? [];
            $indexColumns = [];

            // ✅ Normalize index columns for MySQL + PostgreSQL
            foreach ($indexes as $index) {

                // MySQL
                if (isset($index->Column_name)) {
                    $indexColumns[] = $index->Column_name;
                }

                // PostgreSQL (parse index definition)
                elseif (isset($index->indexdef)) {
                    if (preg_match('/\((.*?)\)/', $index->indexdef, $matches)) {
                        $cols = explode(',', $matches[1]);

                        foreach ($cols as $col) {
                            $indexColumns[] = trim(str_replace('"', '', $col));
                        }
                    }
                }
            }

            foreach ($data['columns'] ?? [] as $column) {

                // ✅ Normalize column name
                if (isset($column->name)) {
                    $field = $column->name;
                } elseif (isset($column->Field)) {
                    $field = $column->Field;
                } elseif (isset($column->column_name)) {
                    $field = $column->column_name;
                } else {
                    continue;
                }

                if (
                    str_ends_with($field, '_id') &&
                    !in_array($field, $indexColumns)
                ) {
                    $issues["missing_index"][] =
                        "\033[0;37;41m[ERROR]\033[0m '{$table}.{$field}' column looks like FK but has no index";
                }
            }
        }

        return $issues;
    }
}