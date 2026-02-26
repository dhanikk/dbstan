<?php

namespace Itpathsolutions\DBStan\Checks\Integrity;

use Itpathsolutions\DBStan\Checks\BaseCheck;
use Illuminate\Support\Facades\DB;

class CascadingActionsCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Foreign Key Cascading Rules';
    }

    public function category(): string
    {
        return 'integrity';
    }

    // Add comment to explain the purpose of this check
    // This check analyzes foreign key constraints in the database to identify any that use 'NO ACTION' or 'RESTRICT' for delete operations. Such rules can lead to orphaned records and data integrity issues if not carefully managed. The check encourages developers to review their cascading strategies and consider using 'CASCADE' or 'SET NULL' where appropriate to maintain referential integrity.
    public function run(array $schema): array
    {
        $issues = [];
        $database = DB::getDatabaseName();

        // Fetch all FK delete rules in one query (avoid N+1)
        $foreignKeys = DB::select("
            SELECT 
                kcu.TABLE_NAME,
                kcu.COLUMN_NAME,
                rc.DELETE_RULE
            FROM information_schema.REFERENTIAL_CONSTRAINTS rc
            JOIN information_schema.KEY_COLUMN_USAGE kcu
                ON rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
                AND rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
            WHERE rc.CONSTRAINT_SCHEMA = ?
        ", [$database]);

        // Convert schema table list for quick lookup
        $validTables = array_keys($schema);

        foreach ($foreignKeys as $fk) {

            if (!in_array($fk->TABLE_NAME, $validTables)) {
                continue;
            }

            $deleteRule = strtoupper($fk->DELETE_RULE);

            if (in_array($deleteRule, ['NO ACTION', 'RESTRICT'])) {

                $issues["cascade_missing"][] =
                    "🧨 [INTEGRITY] '{$fk->TABLE_NAME}.{$fk->COLUMN_NAME}' column has {$deleteRule} on delete — review cascading strategy";
            }
        }

        return $issues;
    }
}