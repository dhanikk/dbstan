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
        $database = DB::getDatabaseName();

        // 🔥 Fetch all FK metadata in one query
        $foreignKeys = DB::select("
            SELECT TABLE_NAME, COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$database]);

        // Convert to fast lookup map
        $fkMap = [];
        foreach ($foreignKeys as $fk) {
            $fkMap[$fk->TABLE_NAME][$fk->COLUMN_NAME] = true;
        }

        foreach ($schema as $table => $data) {

            foreach ($data['columns'] ?? [] as $column) {

                if (!str_ends_with($column->Field, '_id')) {
                    continue;
                }

                $hasFkConstraint =
                    isset($fkMap[$table][$column->Field]);

                $isNullable =
                    strtoupper($column->Null ?? '') === 'YES';

                // Case 1: No FK constraint at all
                if (!$hasFkConstraint) {
                    $issues["orphan_missing_constraint"][] =
                        "⚠️  [ORPHAN RISK] '$table.{$column->Field}' column has no foreign key constraint";
                }

                // Case 2: Nullable FK (soft orphan risk)
                if ($hasFkConstraint && $isNullable) {
                    $issues["orphan_nullable_fk"][] =
                        "👻 [DATA RISK] '$table.{$column->Field}' column is nullable foreign key — may allow logical orphans";
                }

                // Case 3: Worst case (nullable + no constraint)
                if (!$hasFkConstraint && $isNullable) {
                    $issues["orphan_high_risk"][] =
                        "🔥 [HIGH RISK] '$table.{$column->Field}' column is nullable and has no FK constraint";
                }
            }
        }

        return $issues;
    }
}