<?php

namespace Itpathsolutions\DBStan;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

class DBStanAnalyzer
{
    protected array $checks = [];

    public function __construct()
    {
        $this->loadChecks();
    }

    public function getDatabaseInfo(): array
    {
        $driver = DB::getDriverName();
        $database = DB::getDatabaseName();

        $config = config("database.connections.$driver");

        // Count tables
        $tablesCount = 0;

        if ($driver === 'mysql') {
            $tables = DB::select('SHOW TABLES');
            $tablesCount = count($tables);
        } elseif ($driver === 'pgsql') {
            $tables = DB::select("
                SELECT tablename 
                FROM pg_catalog.pg_tables 
                WHERE schemaname = 'public'
            ");
            $tablesCount = count($tables);
        }

        return [
            'driver' => $driver,
            'database' => $database,
            'host' => $config['host'] ?? 'N/A',
            'port' => $config['port'] ?? 'N/A',
            'tables' => $tablesCount,
            'environment' => app()->environment(),
        ];
    }
    public function getPreflightError(): ?string
    {
        $databaseName = DB::getDatabaseName();
        $migrationTableName = $this->resolveMigrationTableName();

        if (empty($databaseName)) {
            return 'Database is not configured.';
        }

        try {
            DB::connection()->getPdo();
        } catch (Throwable $e) {
            return 'Database connection failed.';
        }

        if (!DB::getSchemaBuilder()->hasTable($migrationTableName)) {
            return 'Run migrations first.';
        }

        if (DB::table($migrationTableName)->count() === 0) {
            return 'No migrations found.';
        }

        return null;
    }

    protected function resolveMigrationTableName(): string
    {
        $config = config('database.migrations', 'migrations');

        return is_array($config)
            ? ($config['table'] ?? 'migrations')
            : $config;
    }

    protected function loadChecks(): void
    {
        $path = __DIR__ . '/Checks';

        foreach (File::allFiles($path) as $file) {

            $class = $this->getClassFromFile($file);

            if (!class_exists($class)) continue;

            $reflection = new \ReflectionClass($class);

            if ($reflection->isAbstract() || $reflection->isInterface() || $reflection->isTrait()) {
                continue;
            }

            if (in_array(
                \Itpathsolutions\DBStan\Contracts\CheckInterface::class,
                $reflection->getInterfaceNames()
            )) {
                $this->checks[] = app($class);
            }
        }
    }

    protected function getClassFromFile($file): string
    {
        $relative = str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $file->getRealPath());
        $relative = str_replace(['.php', DIRECTORY_SEPARATOR], ['', '\\'], $relative);

        return __NAMESPACE__ . '\\' . $relative;
    }

    public function analyze(): array
    {
        if ($this->getPreflightError()) {
            return [
                'structure' => [],
                'integrity' => [],
                'performance' => [],
                'architecture' => [],
            ];
        }

        $schema = $this->extractSchema();

        $grouped = [
            'structure' => [],
            'integrity' => [],
            'performance' => [],
            'architecture' => [],
        ];

        foreach ($this->checks as $check) {

            $category = $check->category();
            $issues = array_filter($check->run($schema));

            if (!empty($issues)) {
                $grouped[$category] = array_merge($grouped[$category], $issues);
            }
        }

        return $grouped;
    }

    protected function extractSchema(): array
    {
        $driver = DB::getDriverName();
        $schema = [];

        if ($driver === 'mysql') {

            $tables = DB::select('SHOW TABLES');
            $key = 'Tables_in_' . DB::getDatabaseName();

            foreach ($tables as $tableObj) {

                $table = $tableObj->$key;

                $columnsRaw = DB::select("SHOW FULL COLUMNS FROM `$table`");
                $indexes = DB::select("SHOW INDEX FROM `$table`");

                $columns = array_map(fn($col) => (object)[
                    'name' => $col->Field,
                    'type' => strtolower($col->Type),
                ], $columnsRaw);

                $schema[$table] = [
                    'columns' => $columns,
                    'indexes' => $indexes,
                ];
            }

        } elseif ($driver === 'pgsql') {

            $tables = DB::select("
                SELECT tablename 
                FROM pg_catalog.pg_tables 
                WHERE schemaname = 'public'
            ");

            foreach ($tables as $tableObj) {

                $table = $tableObj->tablename;

                $columnsRaw = DB::select("
                    SELECT column_name, data_type
                    FROM information_schema.columns
                    WHERE table_name = ?
                ", [$table]);

                $indexes = DB::select("
                    SELECT indexname, indexdef
                    FROM pg_indexes
                    WHERE tablename = ?
                ", [$table]);

                $columns = array_map(fn($col) => (object)[
                    'name' => $col->column_name,
                    'type' => strtolower($col->data_type),
                ], $columnsRaw);

                $schema[$table] = [
                    'columns' => $columns,
                    'indexes' => $indexes,
                ];
            }
        }

        return $schema;
    }
}