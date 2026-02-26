<?php

namespace Itpathsolutions\DBStan\Checks\Structure;

use Itpathsolutions\DBStan\Checks\BaseCheck;

class WideVarcharsCheck extends BaseCheck
{
    public function name(): string
    {
        return 'Wide Varchar Columns';
    }

    public function category(): string
    {
        return 'structure';
    }

    // Add comment to explain the purpose of this check
    // This check identifies columns that use the VARCHAR data type with a length that exceeds a specified limit (default is 191). Wide VARCHAR columns can lead to performance issues, especially in MySQL with InnoDB, where the maximum index length is 767 bytes. Using VARCHAR columns that exceed this limit can cause problems with indexing and may lead to inefficient queries. This check helps ensure that VARCHAR columns are designed with appropriate lengths to maintain good database performance and avoid potential issues with indexing.
    
    public function run(array $schema): array
    {
        $issues = [];

        $maxLength = $this->config['max_varchar_length'] ?? 191;

        foreach ($schema as $table => $data) {

            foreach ($data['columns'] as $column) {

                if (preg_match('/varchar\((\d+)\)/i', $column->Type, $matches)) {

                    $length = (int) $matches[1];

                    if ($length > $maxLength) {

                        $issues["wide_varchar"][] = "📏 [WARNING] '$table.{$column->Field}' column is VARCHAR($length) exceeds recommended limit ($maxLength)";
                    }
                }
            }
        }

        return $issues;
    }
}