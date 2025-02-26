<?php

namespace App\Factories;

use App\Contracts\ImageOptimizerInterface;
use App\Services\TinifyImageOptimizer;

class ImageOptimizerFactory
{
    public static function create(string $optimizer): ImageOptimizerInterface
    {
        return match (strtolower($optimizer)) {
            'tinify' => new TinifyImageOptimizer(),
        };
    }
}