<?php

declare(strict_types=1);

namespace Sentinel;

use Sentinel\Contracts\Runner as RunnerContract;
use Sentinel\Contracts\Detector;
use Sentinel\Contracts\Output as OutputContract;

use Sentinel\Output;
use Sentinel\Exceptions\DirectoryNotFoundException;

/**
 * Runner
 *
 * @package Sentinel
 */
class Runner implements RunnerContract
{
    /*----------------------------------------*
     * Constructor
     *----------------------------------------*/

    /**
     * Constructor
     *
     * @param \Sentinel\Contracts\Output|null $output
     */
    public function __construct(OutputContract|null $output = null)
    {
        $this->output = $output ?? new Output();
    }

    /*----------------------------------------*
     * Output
     *----------------------------------------*/

    /**
     * Output
     *
     * @var \Sentinel\Contracts\Output
     */
    protected OutputContract $output;

    /*----------------------------------------*
     * Detectors
     *----------------------------------------*/

    /**
     * Detectors
     *
     * @var array<\Sentinel\Contracts\Detector>
     */
    protected array $detectors = [];

    /**
     * {@inheritDoc}
     */
    public function addDetector(Detector $detector): static
    {
        $this->detectors[] = $detector;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function detectors(): array
    {
        return $this->detectors;
    }

    /*----------------------------------------*
     * Run
     *----------------------------------------*/

    /**
     * Results
     *
     * @var array<string, array<\Sentinel\Contracts\Issue>>
     */
    protected array $results = [];

    /**
     * {@inheritDoc}
     */
    public function run(string $directory): bool
    {
        if (!is_dir($directory)) throw new DirectoryNotFoundException($directory);

        $this->results = [];

        $totalDetectors = count($this->detectors);

        if ($totalDetectors === 0) {
            $this->output->warning("No detectors configured");

            return true;
        }

        $this->output->header("Sentinel - PHP Code Quality Detector");
        $this->output->writeln("");
        $this->output->info("Target: " . realpath($directory));
        $this->output->info("Detectors: " . $totalDetectors);
        $this->output->writeln("");

        $allPassed       = true;
        $currentDetector = 0;

        foreach ($this->detectors as $detector) {
            $currentDetector++;

            $detectorName = $detector->name();

            $this->output->write(sprintf(
                "[%d/%d] %s... ",
                $currentDetector,
                $totalDetectors,
                $detectorName
            ));

            $startTime = microtime(true);

            try {
                $passed  = $detector->detect($directory);
                $elapsed = round(microtime(true) - $startTime, 2);

                $issues = $detector->issues();

                $this->results[$detectorName] = $issues;

                if ($passed) {
                    $this->output->success(sprintf("(%.2fs)", $elapsed));
                } else {
                    $allPassed  = false;
                    $issueCount = count($issues);

                    $this->output->error(sprintf("%d issue(s) (%.2fs)", $issueCount, $elapsed));
                }
            } catch (\Exception $e) {
                $this->output->error("Exception: " . $e->getMessage());

                $allPassed = false;
            }
        }

        $this->displaySummary($allPassed);

        return $allPassed;
    }

    /**
     * Display summary
     *
     * @param bool $allPassed
     * @return void
     */
    protected function displaySummary(bool $allPassed): void
    {
        $this->output->section("Summary");

        $totalIssues = 0;
        $passedCount = 0;
        $failedCount = 0;

        foreach ($this->results as $detectorName => $issues) {
            $issueCount = count($issues);
            $totalIssues += $issueCount;

            if ($issueCount === 0) {
                $passedCount++;
            } else {
                $failedCount++;
                $this->output->writeln(sprintf("  ✗ %s (%d issues)", $detectorName, $issueCount));
            }
        }

        $this->output->writeln("");
        $this->output->writeln(sprintf(
            "Detectors: %d passed, %d failed",
            $passedCount,
            $failedCount
        ));

        if ($totalIssues > 0) {
            $this->output->writeln(sprintf("Issues: %d total", $totalIssues));
        }

        $this->output->writeln("");

        if ($allPassed) {
            $this->output->header("✓ All quality checks passed!");
        } else {
            $this->output->header("✗ Quality checks failed");
        }
    }

    /*----------------------------------------*
     * Results
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    public function issues(): array
    {
        $allIssues = [];

        foreach ($this->results as $issues) {
            $allIssues = array_merge($allIssues, $issues);
        }

        return $allIssues;
    }

    /**
     * {@inheritDoc}
     */
    public function totalFiles(): int
    {
        $total = 0;

        foreach ($this->detectors as $detector) {
            $total += $detector->files();
        }

        return $total;
    }

    /**
     * {@inheritDoc}
     */
    public function passed(): bool
    {
        return empty($this->issues());
    }
}
