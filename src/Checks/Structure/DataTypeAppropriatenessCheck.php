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

                if (
                    str_contains(strtolower($column->Field), 'price') &&
                    str_contains(strtolower($column->Type), 'int')
                ) {
                    $issues["datatype_issue"][] =
                        "💰 [DATA TYPE] '$table.{$column->Field}' column should use DECIMAL instead of INT";
                }
            }
        }

        return $issues;
    }
}