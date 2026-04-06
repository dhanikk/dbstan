<?php

namespace Itpathsolutions\DBStan\Checks\Structure;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class NullableOveruseCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Nullable Column Overuse';
    }

    public function category(): string
    {
        return 'structure';
    }

    /**
     * Detect unnecessary nullable columns
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

                // ✅ Normalize nullable flag
                if (isset($column->nullable)) {
                    $isNullable = $column->nullable;
                } elseif (isset($column->Null)) {
                    $isNullable = strtoupper($column->Null) === 'YES'; // MySQL
                } elseif (isset($column->is_nullable)) {
                    $isNullable = strtoupper($column->is_nullable) === 'YES'; // PostgreSQL
                } else {
                    $isNullable = false;
                }

                if (
                    $isNullable &&
                    !$this->isNullableJustified($field)
                ) {
                    $issues["nullable_overuse"][] =
                        "\033[0;30;43m[NULLABLE]\033[0m '{$table}.{$field}' column is nullable without clear reason";
                }
            }
        }

        return $issues;
    }

    /**
     * Basic heuristic to allow some commonly nullable columns
     */
    protected function isNullableJustified(string $field): bool
    {
        $allowedNullable = [
            'deleted_at',
            'email_verified_at',
            'remember_token',
        ];

        return in_array($field, $allowedNullable);
    }
}