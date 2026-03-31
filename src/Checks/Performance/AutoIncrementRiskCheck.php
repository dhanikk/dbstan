<?php

namespace Itpathsolutions\DBStan\Checks\Performance;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class AutoIncrementRiskCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Auto Increment Risk';
    }

    public function category(): string
    {
        return 'performance';
    }

    public function run(array $schema): array
    {
        $issues = [];

        $warnUsageRatio = (float) ($this->config['autoincrement_warn_ratio'] ?? 0.7);
        $criticalUsageRatio = (float) ($this->config['autoincrement_critical_ratio'] ?? 0.9);
        $growthRowsThreshold = (int) ($this->config['autoincrement_growth_rows_threshold'] ?? 5000000);

        foreach ($schema as $table => $data) {
            $columns = $data['columns'] ?? [];
            $nextValue = $data['auto_increment'] ?? null;
            $tableRows = (int) ($data['table_rows'] ?? 0);

            if ($nextValue === null) {
                continue;
            }

            foreach ($columns as $column) {
                $extra = strtolower((string) ($column->Extra ?? ''));
                if (!str_contains($extra, 'auto_increment')) {
                    continue;
                }

                $type = strtolower((string) ($column->Type ?? ''));
                $maxValue = $this->resolveIntegerMaxValue($type);

                if ($maxValue === null) {
                    continue;
                }

                $usageRatio = $maxValue > 0 ? ($nextValue / $maxValue) : 0;

                if ($usageRatio >= $criticalUsageRatio) {
                    $issues['autoincrement_near_limit'][] =
                        "\033[0;37;41m[ERROR]\033[0m '$table.{$column->Field}' auto_increment is at " . round($usageRatio * 100, 2) . "% of max range. Overflow risk is imminent";
                } elseif ($usageRatio >= $warnUsageRatio) {
                    $issues['autoincrement_near_limit'][] =
                        "\033[0;30;43m[WARNING]\033[0m '$table.{$column->Field}' auto_increment is at " . round($usageRatio * 100, 2) . "% of max range";
                }

                if ($usageRatio >= 0.4 && $tableRows >= $growthRowsThreshold && str_contains($type, 'int') && !str_contains($type, 'bigint')) {
                    $issues['autoincrement_growth_risk'][] =
                        "\033[0;30;43m[GROWTH RISK]\033[0m '$table.{$column->Field}' uses INT with large row count (~$tableRows). Consider BIGINT before future overflow";
                }
            }
        }

        return $issues;
    }

    protected function resolveIntegerMaxValue(string $type): ?float
    {
        $isUnsigned = str_contains($type, 'unsigned');

        if (str_starts_with($type, 'tinyint')) {
            return $isUnsigned ? 255.0 : 127.0;
        }

        if (str_starts_with($type, 'smallint')) {
            return $isUnsigned ? 65535.0 : 32767.0;
        }

        if (str_starts_with($type, 'mediumint')) {
            return $isUnsigned ? 16777215.0 : 8388607.0;
        }

        if (str_starts_with($type, 'int')) {
            return $isUnsigned ? 4294967295.0 : 2147483647.0;
        }

        if (str_starts_with($type, 'bigint')) {
            return $isUnsigned ? 18446744073709551615.0 : 9223372036854775807.0;
        }

        return null;
    }
}
