<?php

namespace Itpathsolutions\DBStan\Contracts;

interface CheckInterface
{
    public function run(array $schema): array;

    public function name(): string;

    public function category(): string;
}