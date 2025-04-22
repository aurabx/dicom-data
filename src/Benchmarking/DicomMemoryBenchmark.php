<?php

namespace Aurabx\DicomData\Benchmarking;

use Exception;
use Aurabx\DicomData\DicomTagLoader;

class DicomMemoryBenchmark
{
    /**
     * @var string|null
     */
    private ?string $path;

    /**
     * @var string 'php' or 'json'
     */
    private string $mode;

    /**
     * @param string|null $path  Path to a JSON or PHP file
     * @param string $mode       One of: 'php', 'json'
     */
    public function __construct(?string $path = null, string $mode = 'php')
    {
        $this->path = $path;
        $this->mode = $mode;
    }

    /**
     * Runs the memory benchmark
     *
     * @return array<string, mixed>
     * @throws \Throwable
     */
    public function run(): array
    {
        gc_collect_cycles();
        gc_mem_caches();
        gc_enable();

        $initial = memory_get_usage(true);
        $start = hrtime(true); // nanoseconds

        $loader = new DicomTagLoader();

        if ($this->mode === 'json') {
            $loader->loadFromFile($this->path ?? $this->getDefaultJsonPath());
        } else {
            $loader->loadFromFile($this->path ?? $this->getDefaultPhpPath());
        }

        $end = hrtime(true);
        $afterLoad = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);

        return [
            'file' => $this->path ?? "default ({$this->mode})",
            'mode' => $this->mode,
            'initial' => $initial,
            'after_load' => $afterLoad,
            'peak' => $peak,
            'delta' => $afterLoad - $initial,
            'time_ns' => $end - $start,
            'time_ms' => round(($end - $start) / 1e6, 3),
            'num_attributes' => count($loader->getAllAttributes()),
        ];
    }

    public function report(): void
    {
        try {
            $result = $this->run();
        } catch (Exception $e) {
            echo "❌ Benchmark failed: " . $e->getMessage() . PHP_EOL;
            return;
        }

        echo "🧪 Benchmark Mode : {$result['mode']}" . PHP_EOL;
        echo "📦 File           : {$result['file']}" . PHP_EOL;
        echo "----------------------------------------" . PHP_EOL;
        echo "Initial RAM       : " . $this->formatBytes($result['initial']) . PHP_EOL;
        echo "After Load RAM    : " . $this->formatBytes($result['after_load']) . PHP_EOL;
        echo "Peak RAM Usage    : " . $this->formatBytes($result['peak']) . PHP_EOL;
        echo "Memory Delta      : " . $this->formatBytes($result['delta']) . PHP_EOL;
        echo "Total Attributes  : " . number_format($result['num_attributes']) . PHP_EOL;
        echo "Execution Time     : {$result['time_ms']} ms" . PHP_EOL;

        echo PHP_EOL;
    }

    private function getDefaultPhpPath(): string
    {
        return dirname(__DIR__) . '/../resources/dicom/php/standard/attributes.php';
    }

    private function getDefaultJsonPath(): string
    {
        return dirname(__DIR__) . '/../resources/dicom/innolitics/standard/attributes.json';
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes > 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes > 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
