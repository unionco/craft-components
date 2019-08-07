<?php

namespace unionco\components\services;

interface GeneratorInterface
{
    public function generate($name, $opts = []): array;
}
