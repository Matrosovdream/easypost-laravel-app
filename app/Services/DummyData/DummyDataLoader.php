<?php

namespace App\Services\DummyData;

use RuntimeException;

class DummyDataLoader
{
    private string $basePath;

    public function __construct()
    {
        $this->basePath = base_path('tests/DummyData/Integrations/EasyPost');
    }

    public function load(string $file): array
    {
        $path = $this->basePath.'/'.$file;

        if (!is_file($path)) {
            throw new RuntimeException("Dummy data file not found: $path");
        }

        $decoded = json_decode(file_get_contents($path), true);

        if (!is_array($decoded)) {
            throw new RuntimeException("Dummy data file is not valid JSON: $path");
        }

        return $decoded;
    }
}
