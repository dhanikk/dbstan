<?php

namespace Itpathsolutions\DBStan\Checks\Structure;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class DataTypeAppropriatenessCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Data Type Appropriateness';
    }

    public function category(): string
    {
        return 'structure';
    }

    // Add comment to explain the purpose of this check
    // This check identifies columns that have "price" in their name but are using an integer data type. Prices typically require decimal precision to accurately represent monetary values, so using an integer can lead to issues with rounding and loss of precision. This check helps ensure that columns intended to store price information are using an appropriate data type (like DECIMAL) for accurate financial calculations.
    public function run(array $schema): array
    {
        $issues = [];

        foreach ($schema as $table => $data) {

            foreach ($data['columns'] ?? [] as $column) {
                $name = strtolower((string) ($column->Field ?? ''));
                $type = strtolower((string) ($column->Type ?? ''));

                // Monetary fields should avoid integer types.
                if (
                    (
                        str_contains($name, 'price') ||
                        str_contains($name, 'amount') ||
                        str_contains($name, 'total')
                    ) &&
                    str_contains($type, 'int')
                ) {
                    $issues['datatype_issue'][] =
                        "\033[0;30;43m[DATA TYPE]\033[0m '$table.{$column->Field}' column should use DECIMAL instead of INT";
                }

                // Boolean-like fields should use boolean/tinyint(1).
                if (
                    (
                        str_starts_with($name, 'is_') ||
                        str_starts_with($name, 'has_')
                    ) &&
                    !str_contains($type, 'tinyint(1)') &&
                    !str_contains($type, 'boolean')
                ) {
                    $issues['datatype_issue'][] =
                        "\033[0;30;43m[DATA TYPE]\033[0m '$table.{$column->Field}' looks boolean but type is '$type'. Consider BOOLEAN/TINYINT(1)";
                }

                // *_at fields are usually temporal columns.
                if (
                    str_ends_with($name, '_at') &&
                    !str_contains($type, 'timestamp') &&
                    !str_contains($type, 'datetime') &&
                    !str_contains($type, 'date')
                ) {
                    $issues['datatype_issue'][] =
                        "\033[0;30;43m[DATA TYPE]\033[0m '$table.{$column->Field}' ends with _at but type is '$type'. Consider TIMESTAMP/DATETIME";
                }

                // Email-like fields should prefer varchar/text over numeric/date types.
                if (
                    str_contains($name, 'email') &&
                    (
                        str_contains($type, 'int') ||
                        str_contains($type, 'date') ||
                        str_contains($type, 'decimal')
                    )
                ) {
                    $issues['datatype_issue'][] =
                        "\033[0;30;43m[DATA TYPE]\033[0m '$table.{$column->Field}' looks like email but type is '$type'. Consider VARCHAR";
                }
            }
        }

        return $issues;
    }
}