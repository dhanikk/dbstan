<?php

namespace Itpathsolutions\DBStan\Checks\Architecture;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class StorageEngineCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Storage Engine Check';
    }

    public function category(): string
    {
        return 'architecture';
    }

    public function run(array $schema): array
    {
        $issues = [];
        $enginesByTable = [];

        foreach ($schema as $table => $data) {
            $engine = strtoupper((string) ($data['engine'] ?? ''));

            if ($engine === '') {
                continue;
            }

            $enginesByTable[$table] = $engine;

            if ($engine !== 'INNODB') {
                $issues['storage_engine_non_innodb'][] =
                    "\033[0;37;41m[ERROR]\033[0m '$table' table uses '$engine'. Use InnoDB for transactions and foreign key support";
            }
        }

        $uniqueEngines = array_values(array_unique(array_values($enginesByTable)));

        if (count($uniqueEngines) > 1) {
            $engineSummary = implode(', ', $uniqueEngines);
            $issues['storage_engine_mixed'][] =
                "\033[0;37;41m[ERROR]\033[0m Mixed storage engines detected ($engineSummary). Standardize all tables to InnoDB";
        }

        return $issues;
    }
}
