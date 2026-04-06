<?php

namespace Itpathsolutions\DBStan\Checks\Integrity;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class ForeignKeyNamingCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Foreign Key Naming Convention';
    }

    public function category(): string
    {
        return 'integrity';
    }

    /**
     * This check identifies columns that look like foreign keys (ending with "id")
     * but do not follow the standard "_id" naming convention.
     */
    public function run(array $schema): array
    {
        $issues = [];

        foreach ($schema as $table => $data) {

            foreach ($data['columns'] ?? [] as $column) {

                // ✅ Normalize column name across DBs
                if (isset($column->name)) {
                    $field = $column->name;
                } elseif (isset($column->Field)) {
                    $field = $column->Field; // MySQL fallback
                } elseif (isset($column->column_name)) {
                    $field = $column->column_name; // PostgreSQL fallback
                } else {
                    continue;
                }

                $fieldLower = strtolower($field);

                // Detect columns ending with "id" but not "_id"
                $looksLikeForeignKey = preg_match('/^[a-z0-9]+id$/i', $fieldLower);

                if (
                    $looksLikeForeignKey &&
                    !str_ends_with($fieldLower, '_id') &&
                    $fieldLower !== 'id'
                ) {
                    $issues["fk_naming"][] =
                        "\033[0;30;43m[NAMING]\033[0m '{$table}.{$field}' column should follow foreign key naming convention: use '{$fieldLower}_id'";
                }
            }
        }

        return $issues;
    }
}