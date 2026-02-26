<?php 

namespace Itpathsolutions\DBStan\Checks;

use Itpathsolutions\DBStan\Contracts\CheckInterface;

abstract class BaseCheck implements CheckInterface
{
    protected array $config;

    public function __construct()
    {
        $this->config = (array) config('dbstan', []);
    }
}