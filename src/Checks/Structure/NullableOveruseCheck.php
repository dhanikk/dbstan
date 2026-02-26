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

    // Add comment to explain the purpose of this check
    // This check identifies columns that are marked as nullable without a clear justification. Overusing nullable columns can lead to data integrity issues and may indicate that the database design is not well thought out. Nullable columns can make it harder to enforce data consistency and can lead to unexpected null values in queries. This check helps encourage better database design by flagging columns that may not need to be nullable, prompting developers to reconsider their schema design and ensure that nullability is used appropriately.
    
    public function run(array $schema): array
    {
        $issues = [];

        foreach ($schema as $table => $data) {

            foreach ($data['columns'] ?? [] as $column) {

                if (
                    $column->Null === 'YES' &&
                    !$this->isNullableJustified($column)
                ) {
                    $issues["nullable_overuse"][] =
                        "❓ [NULLABLE] '$table.{$column->Field}' column is nullable without clear reason";
                }
            }
        }

        return $issues;
    }

    /**
     * Basic heuristic to allow some commonly nullable columns
     */
    protected function isNullableJustified($column): bool
    {
        $allowedNullable = [
            'deleted_at',
            'email_verified_at',
            'remember_token',
        ];

        return in_array($column->Field, $allowedNullable);
    }
}