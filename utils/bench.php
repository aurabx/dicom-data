<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Aurabx\DicomData\Benchmarking\DicomMemoryBenchmark;

echo "=== PHP ARRAY ===\n";
(new DicomMemoryBenchmark(mode: 'php'))->report();

echo "=== JSON VERSION ===\n";
(new DicomMemoryBenchmark(mode: 'json'))->report();
