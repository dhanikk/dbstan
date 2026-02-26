<?php

namespace Itpathsolutions\DBStan\Checks\Structure;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class MissingSoftDeletesCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Missing Soft Deletes';
    }

    public function category(): string
    {
        return 'structure';
    }

    // Add comment to explain the purpose of this check
    // This check identifies tables that do not have a "deleted_at" column, which is commonly used for implementing soft deletes. Soft deletes allow for records to be marked as deleted without actually removing them from the database, providing the ability to restore them later if needed. This check helps ensure that tables that could benefit from soft delete functionality are designed to support it, which can improve data integrity and provide more flexibility in data management.
    public function run(array $schema): array
    {
        $issues = [];

        foreach ($schema as $table => $data) {

            $columns = array_column($data['columns'] ?? [], 'Field');

            if (
                !in_array('deleted_at', $columns)
            ) {
                $issues["missing_soft_deletes"][] =
                    "⏰ [BEST PRACTICE] '$table' table is missing deleted_at column for soft deletes";
            }
        }

        return $issues;
    }
}