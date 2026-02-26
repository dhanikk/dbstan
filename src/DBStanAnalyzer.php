<?php
namespace Itpathsolutions\DBStan;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Itpathsolutions\DBStan\Contracts\CheckInterface;

class DBStanAnalyzer
{
    /**
     * Registered check instances
     */
    protected array $checks = [];

    public function __construct()
    {
        $this->loadChecks();
    }

    /**
     * Auto-discover and load all check classes
     */
    protected function loadChecks(): void
    {
        $path = __DIR__ . '/Checks';

        foreach (File::allFiles($path) as $file) {

            $class = $this->getClassFromFile($file);

            if (!class_exists($class)) {
                continue;
            }

            $reflection = new \ReflectionClass($class);

            // Skip abstract classes, interfaces, traits
            if (
                $reflection->isAbstract() ||
                $reflection->isInterface() ||
                $reflection->isTrait()
            ) {
                continue;
            }

            // Ensure it implements CheckInterface
            if (
                in_array(
                    \Itpathsolutions\DBStan\Contracts\CheckInterface::class,
                    $reflection->getInterfaceNames()
                )
            ) {
                $this->checks[] = app($class);
            }
        }
    }

    /**
     * Convert file path to fully qualified class name
     */
    protected function getClassFromFile($file): string
    {
        $path = $file->getRealPath();

        $relative = str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $path);
        $relative = str_replace('.php', '', $relative);
        $relative = str_replace(DIRECTORY_SEPARATOR, '\\', $relative);

        return __NAMESPACE__ . '\\' . $relative;
    }

    /**
     * Run full DB analysis
     */
    public function analyze(): array
    {
        $schema = $this->extractSchema();

        $groupedIssues = [
            'structure' => [],
            'integrity' => [],
            'performance' => [],
            'architecture' => [],
        ];

        $enabledCategories = config('dbstan.enabled_checks', [
            'structure',
            'integrity',
            'performance',
            'architecture',
        ]);
        foreach ($this->checks as $check) {

            $category = $check->category();

            if (!in_array($category, $enabledCategories)) {
                continue;
            }

            $issues = array_filter($check->run($schema));
            if (!empty($issues)) {
                $groupedIssues[$category] = array_merge(
                    $groupedIssues[$category],
                    $issues
                );
            }
        }
        return $groupedIssues;
    }

    /**
     * Extract full DB schema
     */
    protected function extractSchema(): array
    {
        $tables = DB::select('SHOW TABLES');
        $dbNameKey = 'Tables_in_' . DB::getDatabaseName();

        $schema = [];

        foreach ($tables as $tableObj) {

            $tableName = $tableObj->$dbNameKey;

            $schema[$tableName] = [
                'columns' => DB::select("SHOW COLUMNS FROM `$tableName`"),
                'indexes' => DB::select("SHOW INDEX FROM `$tableName`"),
            ];
        }

        return $schema;
    }

}
