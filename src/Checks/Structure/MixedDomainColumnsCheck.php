<?php

namespace Itpathsolutions\DBStan\Checks\Structure;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class MixedDomainColumnsCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Mixed Domain Columns';
    }

    public function category(): string
    {
        return 'structure';
    }

    public function run(array $schema): array
    {
        $issues = [];

        foreach ($schema as $table => $data) {

            foreach ($data['columns'] ?? [] as $column) {

                if ($this->isMixedDomain($column)) {

                    $issues["mixed_domain"][] =
                        "🔀 [DOMAIN MIX] '$table.{$column->Field}' column mixes different types of data";
                }
            }
        }

        return $issues;
    }

    /**
     * Heuristic detection of mixed domain columns
     * Example: columns storing comma separated values, JSON in varchar, etc.
     */
    protected function isMixedDomain($column): bool
    {
        $type = strtolower($column->Type);

        // Example heuristics (you can improve later)
        return
            str_contains($type, 'varchar') &&
            (
                str_contains($column->Field, 'data') ||
                str_contains($column->Field, 'info') ||
                str_contains($column->Field, 'details')
            );
    }
}