<?php

namespace Itpathsolutions\DBStan\Checks\Structure;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class RepeatedCommonFieldsCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Repeated Common Fields';
    }

    public function category(): string
    {
        return 'structure';
    }

    // Add comment to explain the purpose of this check
    // This check identifies tables that have common field names such as "email", "phone", "address", "name", "description", "status", "type", "code", and "slug". The presence of these common fields across multiple tables can indicate potential issues with database design, such as a lack of normalization or the need for a shared lookup table. This check helps highlight areas where the database schema may benefit from refactoring to improve maintainability and reduce redundancy.
    public function run(array $schema): array
    {
        $issues = [];

        $commonFields = ['email', 'phone', 'address', 'name', 'description', 'status', 'type', 'code', 'slug'];

        foreach ($schema as $table => $data) {

            $columns = array_column($data['columns'] ?? [], 'Field');

            foreach ($commonFields as $field) {

                if (in_array($field, $columns)) {

                    $issues["repeated_common_field"][] =
                        "🔁 [REPEATED FIELD] '$table' table has common field '$field'";
                }
            }
        }

        return $issues;
    }
}