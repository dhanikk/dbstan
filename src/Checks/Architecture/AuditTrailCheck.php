<?php

namespace Itpathsolutions\DBStan\Checks\Architecture;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class AuditTrailCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Audit Trail Check';
    }

    public function category(): string
    {
        return 'architecture';
    }

    // Add comment to explain the purpose of this check
    // This check ensures that tables in the database have proper audit trail columns (created_by, updated_by, deleted_by)
    // to support tracking changes and maintaining data integrity over time.
    
    public function run(array $schema): array
    {
        $issues = [];

        foreach ($schema as $table => $data) {

            $tableLower = strtolower($table);

            // Skip pivot and log-like tables
            if (
                str_contains($tableLower, 'pivot') ||
                str_contains($tableLower, 'log') ||
                str_contains($tableLower, 'event')
            ) {
                continue;
            }

            $columns = array_column($data['columns'] ?? [], 'Field');

            $hasCreatedAt = in_array('created_by', $columns);
            $hasUpdatedAt = in_array('updated_by', $columns);
            $hasDeletedAt = in_array('deleted_by', $columns);

            // Rule 1: Missing timestamps
            if (!$hasCreatedAt || !$hasUpdatedAt) {
                $issues["audit_missing_timestamps"][] =
                    "🕵️ [AUDIT] '$table' table should have created_by and updated_by for audit tracking";
            }

            // Rule 2: Soft delete without timestamps
            if ($hasDeletedAt && (!$hasUpdatedAt || !$hasCreatedAt)) {
                $issues["audit_inconsistent"][] =
                    "⚠️  [AUDIT] '$table' table has deleted_by but missing created_by or updated_by";
            }

            // Rule 3: Timestamps without soft delete
            if (($hasCreatedAt || $hasUpdatedAt) && !$hasDeletedAt) {
                $issues["audit_no_soft_delete"][] =
                    "⚠️  [AUDIT] '$table' table has created_by/updated_by but missing deleted_by for soft delete";
            }
        }

        return $issues;
    }
}