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

    // Add comment to explain the purpose of this check
    // This check identifies columns that look like foreign keys (ending with "id") but do not follow the common naming convention of ending with "_id". It helps ensure that foreign key columns are consistently named, which can improve code readability and maintainability.
    public function run(array $schema): array
    {
        $issues = [];

        foreach ($schema as $table => $data) {

            foreach ($data['columns'] ?? [] as $column) {

                $field = $column->Field;
                $fieldLower = strtolower($field);

                // Detect columns ending with "id" but not "_id"
                $looksLikeForeignKey =
                    preg_match('/^[a-z0-9]+id$/i', $fieldLower);

                if (
                    $looksLikeForeignKey &&
                    !str_ends_with($fieldLower, '_id') &&
                    $fieldLower !== 'id'
                ) {
                    $issues["fk_naming"][] =
                        "🧱 [NAMING] '$table.$field' column should follow foreign key naming convention: use '{$fieldLower}_id'";
                }
            }
        }

        return $issues;
    }
}