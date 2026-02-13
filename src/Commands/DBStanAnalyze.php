<?php

namespace Itpathsolutions\DBStan\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DBStanAnalyze extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbstan:analyze';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyze database structure for design issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Running DBStan Analysis...\n");

        $schema = $this->extractSchema();

        $issues = array_merge(
            $this->checkTooManyColumns($schema),
            $this->checkMissingIndexes($schema),
            $this->checkJsonOveruse($schema),
            $this->checkMissingTimestamps($schema),
            $this->checkNullableOveruse($schema),
            $this->checkMixedDomainColumns($schema),
            $this->checkRepeatedCommonFields($schema),
            $this->checkLargeTextColumns($schema),
            $this->checkPivotTableStructure($schema),
            $this->checkLogTableIndexing($schema),
            $this->checkEnumOveruse($schema),
            $this->checkPossibleOrphanRisk($schema),
            $this->checkUnboundedGrowthRisk($schema),
            $this->checkMissingSoftDeletes($schema),
            $this->checkBooleanOveruse($schema),
            $this->checkStatusIndex($schema),
            $this->checkAuditTrail($schema),
            $this->checkForeignKeyNaming($schema),
            $this->checkWideVarchars($schema),
            $this->checkPolymorphicOveruse($schema)
        );

        if (empty($issues)) {
            $this->info("✅ No major DB design issues found.");
            return;
        }

        foreach ($issues as $issue) {
            $this->line($issue);
        }
    }

    private function extractSchema()
    {
        $tables = DB::select('SHOW TABLES');
        $dbNameKey = 'Tables_in_' . DB::getDatabaseName();

        $schema = [];

        foreach ($tables as $tableObj) {
            $tableName = $tableObj->$dbNameKey;

            $columns = DB::select("SHOW COLUMNS FROM $tableName");
            $indexes = DB::select("SHOW INDEX FROM $tableName");

            $schema[$tableName] = [
                'columns' => $columns,
                'indexes' => $indexes
            ];
        }

        return $schema;
    }

    private function checkTooManyColumns($schema)
    {
        $issues = [];

        foreach ($schema as $table => $data) {
            if (count($data['columns']) > 25) {
                $issues[] = "⚠️  [WARNING] '$table' has too many columns (" . count($data['columns']) . ")";
            }
        }

        return $issues;
    }

    private function checkMissingIndexes($schema)
    {
        $issues = [];

        foreach ($schema as $table => $data) {
            $indexColumns = array_column($data['indexes'], 'Column_name');

            foreach ($data['columns'] as $column) {
                if (str_ends_with($column->Field, '_id') && !in_array($column->Field, $indexColumns)) {
                    $issues[] = "❌ [ERROR] '$table.{$column->Field}' looks like FK but has no index";
                }
            }
        }

        return $issues;
    }

    private function checkJsonOveruse($schema)
    {
        $issues = [];

        foreach ($schema as $table => $data) {
            $jsonCols = array_filter($data['columns'], fn($c) => str_contains($c->Type, 'json'));

            if (count($jsonCols) > 2) {
                $issues[] = "⚠️  [WARNING] '$table' uses too many JSON columns (" . count($jsonCols) . ")";
            }
        }

        return $issues;
    }

    private function checkMissingTimestamps($schema)
    {
        $issues = [];

        foreach ($schema as $table => $data) {
            $columnNames = array_column($data['columns'], 'Field');

            if (!in_array('created_at', $columnNames) || !in_array('updated_at', $columnNames)) {
                $issues[] = "ℹ️  [SUGGESTION] '$table' missing timestamps";
            }
        }

        return $issues;
    }

    private function checkNullableOveruse($schema)
    {
        $issues = [];

        foreach ($schema as $table => $data) {
            $nullable = array_filter($data['columns'], fn($c) => $c->Null === 'YES');

            if (count($nullable) > (count($data['columns']) * 0.6)) {
                $issues[] = "⚠️  [WARNING] '$table' has too many nullable columns";
            }
        }

        return $issues;
    }

    private function checkMixedDomainColumns($schema)
    {
        $issues = [];

        foreach ($schema as $table => $data) {
            $columnNames = array_column($data['columns'], 'Field');

            $patterns = ['billing_', 'shipping_', 'profile_', 'meta_', 'extra_'];
            $matches = 0;

            foreach ($patterns as $pattern) {
                foreach ($columnNames as $col) {
                    if (str_starts_with($col, $pattern)) $matches++;
                }
            }

            if ($matches >= 3) {
                $issues[] = "🧩 [ARCH WARNING] '$table' appears to serve multiple domains (possible god object table)";
            }
        }

        return $issues;
    }

    private function checkRepeatedCommonFields($schema)
    {
        $issues = [];
        $commonFields = ['email', 'phone', 'address', 'name'];

        $map = [];

        foreach ($schema as $table => $data) {
            foreach ($data['columns'] as $col) {
                if (in_array($col->Field, $commonFields)) {
                    $map[$col->Field][] = $table;
                }
            }
        }

        foreach ($map as $field => $tables) {
            if (count($tables) > 3) {
                $issues[] = "🔁 [NORMALIZATION] Field '$field' repeated in many tables (" . implode(', ', $tables) . ")";
            }
        }

        return $issues;
    }

    private function checkLargeTextColumns($schema)
    {
        $issues = [];

        foreach ($schema as $table => $data) {
            foreach ($data['columns'] as $col) {
                if (str_contains($col->Type, 'text')) {
                    $issues[] = "📦 [PERF RISK] '$table.{$col->Field}' is TEXT — consider separating to detail table";
                }
            }
        }

        return $issues;
    }

    private function checkPivotTableStructure($schema)
    {
        $issues = [];

        foreach ($schema as $table => $data) {
            $fkColumns = array_filter($data['columns'], fn($c) => str_ends_with($c->Field, '_id'));

            if (count($fkColumns) == 2 && count($data['columns']) > 4) {
                $issues[] = "🔗 [DESIGN] '$table' looks like pivot but has extra columns";
            }
        }

        return $issues;
    }

    private function checkLogTableIndexing($schema)
    {
        $issues = [];

        foreach ($schema as $table => $data) {
            if (str_contains($table, 'log') || str_contains($table, 'history')) {
                $indexCols = array_column($data['indexes'], 'Column_name');

                if (!in_array('created_at', $indexCols)) {
                    $issues[] = "📈 [SCALING RISK] '$table' is log table but 'created_at' not indexed";
                }
            }
        }

        return $issues;
    }

    private function checkEnumOveruse($schema)
    {
        $issues = [];

        foreach ($schema as $table => $data) {
            $enums = array_filter($data['columns'], fn($c) => str_contains($c->Type, 'enum'));

            if (count($enums) > 3) {
                $issues[] = "🧬 [ARCH WARNING] '$table' uses many ENUM columns — consider lookup tables";
            }
        }

        return $issues;
    }

    private function checkPossibleOrphanRisk($schema)
    {
        $issues = [];

        foreach ($schema as $table => $data) {
            foreach ($data['columns'] as $col) {
                if (str_ends_with($col->Field, '_id') && $col->Null === 'YES') {
                    $issues[] = "👻 [DATA RISK] '$table.{$col->Field}' nullable FK may create orphan records";
                }
            }
        }

        return $issues;
    }

    private function checkUnboundedGrowthRisk($schema)
    {
        $issues = [];
        $growthTables = ['log', 'event', 'message', 'activity', 'lead', 'tracking'];

        foreach ($schema as $table => $data) {
            foreach ($growthTables as $keyword) {
                if (str_contains($table, $keyword)) {
                    $indexCols = array_column($data['indexes'], 'Column_name');

                    if (!in_array('created_at', $indexCols)) {
                        $issues[] = "💣 [GROWTH RISK] '$table' will grow large but has no index on created_at";
                    }
                }
            }
        }

        return $issues;
    }

    private function checkMissingSoftDeletes($schema)
    {
        $issues = [];
        $businessTables = ['users', 'orders', 'leads', 'customers', 'subscriptions'];

        foreach ($schema as $table => $data) {
            if (in_array($table, $businessTables)) {
                $cols = array_column($data['columns'], 'Field');
                if (!in_array('deleted_at', $cols)) {
                    $issues[] = "🗑️ [DATA SAFETY] '$table' should probably have soft deletes";
                }
            }
        }

        return $issues;
    }

    private function checkBooleanOveruse($schema)
    {
        $issues = [];

        foreach ($schema as $table => $data) {
            $booleans = array_filter($data['columns'], fn($c) =>
                str_contains($c->Type, 'tinyint(1)')
            );

            if (count($booleans) > 5) {
                $issues[] = "🧠 [DESIGN SMELL] '$table' has many boolean flags — may need status system";
            }
        }

        return $issues;
    }

    private function checkStatusIndex($schema)
    {
        $issues = [];

        foreach ($schema as $table => $data) {
            $indexCols = array_column($data['indexes'], 'Column_name');

            foreach ($data['columns'] as $col) {
                if (str_contains($col->Field, 'status') && !in_array($col->Field, $indexCols)) {
                    $issues[] = "⚡ [PERF] '$table.{$col->Field}' likely filtered but not indexed";
                }
            }
        }

        return $issues;
    }

    private function checkAuditTrail($schema)
    {
        $issues = [];
        $criticalTables = ['users', 'orders', 'payments'];

        foreach ($schema as $table => $data) {
            if (in_array($table, $criticalTables)) {
                $cols = array_column($data['columns'], 'Field');

                if (!in_array('updated_at', $cols)) {
                    $issues[] = "📜 [AUDIT] '$table' missing update tracking";
                }
            }
        }

        return $issues;
    }

    private function checkForeignKeyNaming($schema)
    {
        $issues = [];

        foreach ($schema as $table => $data) {
            foreach ($data['columns'] as $col) {
                if (str_ends_with($col->Field, '_id')) {
                    $related = str_replace('_id', '', $col->Field);

                    if (!isset($schema[$related]) && !isset($schema[$related.'s'])) {
                        $issues[] = "🧭 [CONVENTION] '$table.{$col->Field}' FK name may not match any table";
                    }
                }
            }
        }

        return $issues;
    }

    private function checkWideVarchars($schema)
    {
        $issues = [];

        foreach ($schema as $table => $data) {
            foreach ($data['columns'] as $col) {
                if (preg_match('/varchar\((\d+)\)/', $col->Type, $matches)) {
                    if ((int)$matches[1] > 500) {
                        $issues[] = "📏 [PERF] '$table.{$col->Field}' VARCHAR too large";
                    }
                }
            }
        }

        return $issues;
    }

    private function checkPolymorphicOveruse($schema)
    {
        $issues = [];

        foreach ($schema as $table => $data) {
            $fields = array_column($data['columns'], 'Field');

            foreach ($fields as $f) {
                if (str_ends_with($f, '_type') && in_array(str_replace('_type','_id',$f), $fields)) {
                    $issues[] = "🧬 [ARCH RISK] '$table' uses polymorphic relation — ensure indexing & performance checks";
                }
            }
        }

        return $issues;
    }

}