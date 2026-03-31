<?php

namespace Itpathsolutions\DBStan\Checks\Architecture;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class CharsetCollationConsistencyCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Charset & Collation Consistency';
    }

    public function category(): string
    {
        return 'architecture';
    }

    public function run(array $schema): array
    {
        $issues = [];
        $charsets = [];
        $tableCollations = [];

        foreach ($schema as $table => $data) {
            $tableCollation = strtolower((string) ($data['table_collation'] ?? ''));

            if ($tableCollation !== '') {
                $tableCollations[$table] = $tableCollation;
                $charset = strstr($tableCollation, '_', true);
                if ($charset !== false && $charset !== '') {
                    $charsets[$charset] = true;
                }
            }

            $columnCollations = [];
            foreach ($data['columns'] ?? [] as $column) {
                $collation = strtolower((string) ($column->Collation ?? ''));
                if ($collation !== '') {
                    $columnCollations[$collation] = true;
                }
            }

            if (count($columnCollations) > 1) {
                $issues['collation_inconsistent_table'][] =
                    "\033[0;30;43m[WARNING]\033[0m '$table' table mixes collations at column level (" . implode(', ', array_keys($columnCollations)) . ')';
            }
        }

        if (isset($charsets['utf8']) && isset($charsets['utf8mb4'])) {
            $issues['charset_mismatch'][] =
                "\033[0;37;41m[ERROR]\033[0m utf8 vs utf8mb4 mismatch detected across tables. Standardize to utf8mb4 to avoid comparison/sorting bugs";
        }

        $distinctCollations = array_values(array_unique(array_values($tableCollations)));
        if (count($distinctCollations) > 1) {
            $issues['collation_mismatch'][] =
                "\033[0;30;43m[WARNING]\033[0m Multiple table collations detected (" . implode(', ', $distinctCollations) . '). Keep a single collation for predictable comparisons';
        }

        return $issues;
    }
}
