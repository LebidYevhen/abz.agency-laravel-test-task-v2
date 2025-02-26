<?php

namespace App\Contracts;

interface ImageOptimizerInterface
{
    public function processImage($file): string;
}
