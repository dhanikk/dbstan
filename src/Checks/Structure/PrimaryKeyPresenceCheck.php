<?php

namespace Itpathsolutions\DBStan\Checks\Structure;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class PrimaryKeyPresenceCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Primary Key Presence';
    }

    public function category(): string
    {
        return 'structure';
    }

    public function run(array $schema): array
    {
        $issues = [];   

        foreach ($schema as $table => $data) {
            $hasPrimary = false;

            foreach ($data['indexes'] ?? [] as $index) {
                if (strtoupper((string) ($index->Key_name ?? '')) === 'PRIMARY') {
                    $hasPrimary = true;
                    break;
                }
            }

            if (!$hasPrimary) {
                $issues['missing_primary_key'][] =
                    "\033[0;37;41m[ERROR]\033[0m '$table' table has no PRIMARY KEY. Add a primary key for integrity and safe updates";
            }
        }

        return $issues;
    }
}
