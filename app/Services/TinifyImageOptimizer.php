<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use App\Contracts\ImageOptimizerInterface;

class TinifyImageOptimizer implements ImageOptimizerInterface
{
    public function processImage($file): string
    {
        \Tinify\setKey(env('TINYPNG_API_KEY'));

        $filePath = $file->store('', 'public');
        $fullPath = Storage::disk('public')->path($filePath);

        if (!file_exists($fullPath)) {
            throw new \Exception("File not found: $fullPath");
        }

        $source = \Tinify\fromFile($fullPath);
        $resized = $source->resize([
            "method" => "cover",
            "width" => 70,
            "height" => 70
        ]);

        $newFileName = uniqid() . '.jpg';
        Storage::disk('public')->put($newFileName, $resized->toBuffer());

        Storage::disk('public')->delete($filePath);

        return $newFileName;
    }
}