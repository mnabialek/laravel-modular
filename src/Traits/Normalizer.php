<?php

namespace Mnabialek\LaravelSimpleModules\Traits;

trait Normalizer
{
    /**
     * Normalize path (removes trailing directory separators)
     *
     * @param string $path
     *
     * @return string
     */
    protected function normalizePath($path)
    {
        return rtrim($path, '/\\');
    }
}
