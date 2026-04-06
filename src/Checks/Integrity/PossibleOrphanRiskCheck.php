<?php

namespace Itpathsolutions\DBStan\Checks\Integrity;

use Itpathsolutions\DBStan\Checks\BaseCheck;
use Illuminate\Support\Facades\DB;

class PossibleOrphanRiskCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Possible Orphan Risk Check';
    }

    public function category(): string
    {
        return 'integrity';
    }

    /**
     * Detects:
     * 1. _id columns without foreign key constraints
     * 2. Nullable foreign keys (possible orphan logic risk)
     */
    public function run(array $schema): array
    {
        $issues = [];
        $driver = DB::getDriverName();
        $database = DB::getDatabaseName();

        // ✅ Fetch FK metadata (DB-specific)
        if ($driver === 'mysql') {

            $foreignKeys = DB::select("
                SELECT TABLE_NAME, COLUMN_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = ?
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$database]);

        } elseif ($driver === 'pgsql') {

            $foreignKeys = DB::select("
                SELECT
                    tc.table_name,
                    kcu.column_name
                FROM information_schema.table_constraints tc
                JOIN information_schema.key_column_usage kcu
                    ON tc.constraint_name = kcu.constraint_name
                WHERE tc.constraint_type = 'FOREIGN KEY'
                AND tc.table_schema = 'public'
            ");

        } else {
            return [];
        }

        // ✅ Normalize FK map
        $fkMap = [];
        foreach ($foreignKeys as $fk) {

            $table = $fk->TABLE_NAME ?? $fk->table_name;
            $column = $fk->COLUMN_NAME ?? $fk->column_name;

            $fkMap[$table][$column] = true;
        }

        foreach ($schema as $table => $data) {

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

                // ✅ Normalize nullable flag
                if (isset($column->nullable)) {
                    $isNullable = $column->nullable;
                } elseif (isset($column->Null)) {
                    $isNullable = strtoupper($column->Null) === 'YES';
                } elseif (isset($column->is_nullable)) {
                    $isNullable = strtoupper($column->is_nullable) === 'YES';
                } else {
                    $isNullable = false;
                }

                if (!str_ends_with($field, '_id')) {
                    continue;
                }

                $hasFkConstraint = isset($fkMap[$table][$field]);

                // Case 1: No FK constraint
                if (!$hasFkConstraint) {
                    $issues["orphan_missing_constraint"][] =
                        "\033[0;30;43m[ORPHAN RISK]\033[0m '{$table}.{$field}' column has no foreign key constraint";
                }

                // Case 2: Nullable FK
                if ($hasFkConstraint && $isNullable) {
                    $issues["orphan_nullable_fk"][] =
                        "\033[0;37;41m[DATA RISK]\033[0m '{$table}.{$field}' column is nullable foreign key — may allow logical orphans";
                }

                // Case 3: Worst case
                if (!$hasFkConstraint && $isNullable) {
                    $issues["orphan_high_risk"][] =
                        "\033[0;37;41m[HIGH RISK]\033[0m '{$table}.{$field}' column is nullable and has no FK constraint";
                }
            }
        }

        return $issues;
    }
}