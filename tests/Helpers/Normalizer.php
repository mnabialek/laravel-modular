<?php

namespace Tests\Helpers;

use Mnabialek\LaravelModular\Traits\Normalizer as NormalizerTrait;

class Normalizer
{
    use NormalizerTrait;

    public function runNormalizePath($path)
    {
        return $this->normalizePath($path);
    }
}
