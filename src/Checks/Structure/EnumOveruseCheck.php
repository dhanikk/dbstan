<?php

namespace Itpathsolutions\DBStan\Checks\Structure;

use Itpathsolutions\DBStan\Checks\BaseCheck;
use Illuminate\Support\Facades\DB;

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

    /**
     * Detect overuse of ENUM columns
     */
    public function run(array $schema): array
    {
        $issues = [];
        $driver = DB::getDriverName();

        foreach ($schema as $table => $data) {

            $enumColumns = [];

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

                // ✅ Detect ENUM
                if (
                    ($driver === 'mysql' && str_contains($type, 'enum')) ||
                    ($driver === 'pgsql' && $type === 'user-defined')
                ) {
                    $enumColumns[] = [
                        'field' => $field,
                        'type'  => $type,
                        'raw'   => $column
                    ];
                }
            }

            // Rule 1: Too many ENUM columns
            if (count($enumColumns) > 2) {
                $issues["enum_overuse"][] =
                    "\033[0;30;43m[ENUM OVERUSE]\033[0m '{$table}' table has multiple ENUM columns (" . count($enumColumns) . ")";
            }

            // Rule 2: ENUM with too many values
            foreach ($enumColumns as $col) {

                $field = $col['field'];
                $raw = $col['raw'];

                $valuesCount = 0;

                // ✅ MySQL ENUM values extraction
                if (isset($raw->Type)) {
                    preg_match_all("/'([^']+)'/", $raw->Type, $matches);
                    $valuesCount = count($matches[1] ?? []);
                }

                // ✅ PostgreSQL ENUM values extraction
                elseif ($driver === 'pgsql') {

                    $enumValues = DB::select("
                        SELECT e.enumlabel
                        FROM pg_type t
                        JOIN pg_enum e ON t.oid = e.enumtypid
                        JOIN pg_namespace n ON n.oid = t.typnamespace
                        WHERE t.typname = ?
                    ", [$raw->udt_name ?? '']);

                    $valuesCount = count($enumValues);
                }

                if ($valuesCount > 5) {
                    $issues["enum_overuse"][] =
                        "\033[0;30;43m[ENUM SIZE]\033[0m '{$table}.{$field}' has many ENUM values ({$valuesCount}) — consider lookup table";
                }
            }
        }

        return $issues;
    }
}