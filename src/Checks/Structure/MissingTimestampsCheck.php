<?php

namespace Itpathsolutions\DBStan\Checks\Structure;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class MissingTimestampsCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Missing Timestamps';
    }

    public function category(): string
    {
        return 'structure';
    }

    // Add comment to explain the purpose of this check
    // This check identifies tables that do not have both "created_at" and "updated_at" timestamp columns. These columns are commonly used for tracking when records are created and last updated, which can be crucial for auditing, debugging, and understanding data changes over time. This check helps ensure that tables are designed to include these important timestamp fields for better data management and traceability.
    public function run(array $schema): array
    {
        $issues = [];

        foreach ($schema as $table => $data) {

            $columns = array_column($data['columns'] ?? [], 'Field');

            if (
                !in_array('created_at', $columns) ||
                !in_array('updated_at', $columns)
            ) {
                $issues["missing_timestamps"][] =
                    "⏰ [BEST PRACTICE] '$table' table is missing created_at/updated_at timestamp columns";
            }
        }

        return $issues;
    }
}