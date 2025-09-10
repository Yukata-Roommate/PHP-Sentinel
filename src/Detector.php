<?php

declare(strict_types=1);

namespace Sentinel;

use Sentinel\Contracts\Detector as DetectorContract;

use Sentinel\Contracts\Analyzer as AnalyzerContract;
use Sentinel\Contracts\Issue;

use Sentinel\Analyzer;

use Sentinel\Exceptions\DirectoryNotFoundException;

/**
 * Detector
 *
 * @package Sentinel
 */
abstract class Detector implements DetectorContract
{
    /*----------------------------------------*
     * Name
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    abstract public function name(): string;

    /*----------------------------------------*
     * Detect
     *----------------------------------------*/

    /**
     * Detect issues in single file
     *
     * @param \Sentinel\Contracts\Analyzer $analyzer
     * @param string $relativePath
     * @return void
     */
    abstract protected function check(AnalyzerContract $analyzer, string $relativePath): void;

    /**
     * {@inheritDoc}
     */
    public function detect(string $directory): bool
    {
        if (!is_dir($directory)) throw new DirectoryNotFoundException($directory);

        $this->reset();

        $this->baseDirectory = realpath($directory);

        $phpFiles = $this->findPhpFiles($directory);

        foreach ($phpFiles as $file) {
            $relativePath = $this->getRelativePath($file, $directory);

            $analyzer = new Analyzer();

            $analyzer->setFile($file);

            $this->check($analyzer, $relativePath);

            $this->filesChecked++;
        }

        return empty($this->issues);
    }

    /*----------------------------------------*
     * Statistics
     *----------------------------------------*/

    /**
     * Issues
     *
     * @var array<\Sentinel\Contracts\Issue>
     */
    protected array $issues = [];

    /**
     * Files checked count
     *
     * @var int
     */
    protected int $filesChecked = 0;

    /**
     * Base directory
     *
     * @var string
     */
    protected string $baseDirectory = "";

    /**
     * {@inheritDoc}
     */
    public function issues(): array
    {
        return $this->issues;
    }

    /**
     * {@inheritDoc}
     */
    public function files(): int
    {
        return $this->filesChecked;
    }

    /**
     * Add issue
     *
     * @param \Sentinel\Contracts\Issue $issue
     * @return void
     */
    protected function addIssue(Issue $issue): void
    {
        $this->issues[] = $issue;
    }

    /**
     * Reset detector state
     *
     * @return void
     */
    protected function reset(): void
    {
        $this->issues            = [];
        $this->filesChecked      = 0;
        $this->baseDirectory     = "";

        $this->gitignorePatterns = [];
        $this->gitignoreCache    = [];
    }

    /*----------------------------------------*
     * Gitignore
     *----------------------------------------*/

    /**
     * Gitignore patterns
     *
     * @var array<array{pattern: string, path: string}>
     */
    protected array $gitignorePatterns = [];

    /**
     * Gitignore cache
     *
     * @var array<string, bool>
     */
    protected array $gitignoreCache = [];

    /**
     * Load gitignore patterns
     *
     * @param string $directory
     * @return void
     */
    protected function loadGitignorePatterns(string $directory): void
    {
        $gitignorePath = $directory . DIRECTORY_SEPARATOR . ".gitignore";

        if (!file_exists($gitignorePath)) return;

        $content = file_get_contents($gitignorePath);

        if ($content === false) return;

        $lines = explode(PHP_EOL, $content);

        foreach ($lines as $line) {
            $pattern = trim($line);

            if (empty($pattern) || str_starts_with($pattern, "#")) continue;

            $this->gitignorePatterns[] = [
                "pattern" => $pattern,
                "path"    => rtrim($directory, DIRECTORY_SEPARATOR),
            ];
        }
    }

    /**
     * Check if path is gitignored
     *
     * @param string $path
     * @return bool
     */
    protected function isGitignored(string $path): bool
    {
        if (empty($this->gitignorePatterns)) return false;

        if (isset($this->gitignoreCache[$path])) return $this->gitignoreCache[$path];

        $ignored = false;

        foreach ($this->gitignorePatterns as $entry) {
            if (!str_starts_with($path, $entry["path"])) continue;

            $relativePath = substr($path, strlen($entry["path"]) + 1);

            if ($this->matchesGitignorePattern($relativePath, $entry["pattern"])) {
                $ignored = true;

                break;
            }
        }

        $this->gitignoreCache[$path] = $ignored;

        return $ignored;
    }

    /**
     * Check if path matches gitignore pattern
     *
     * @param string $relativePath
     * @param string $pattern
     * @return bool
     */
    protected function matchesGitignorePattern(string $relativePath, string $pattern): bool
    {
        $relativePath = str_replace(DIRECTORY_SEPARATOR, "/", $relativePath);

        $pattern = rtrim($pattern, "/");

        if ($relativePath === $pattern) return true;

        if (str_starts_with($relativePath, $pattern . "/")) return true;

        if (str_contains($pattern, "*")) {
            if (fnmatch($pattern, $relativePath)) return true;

            $pathParts = explode("/", $relativePath);

            foreach ($pathParts as $i => $part) {
                $remainingPath = implode("/", array_slice($pathParts, $i));

                if (fnmatch($pattern, $remainingPath)) return true;
            }
        }

        if (str_contains($pattern, "/")) return false;

        $pathParts = explode("/", $relativePath);

        return in_array($pattern, $pathParts, true);
    }

    /*----------------------------------------*
     * File Operations
     *----------------------------------------*/

    /**
     * Find PHP files
     *
     * @param string $directory
     * @return array<string>
     */
    protected function findPhpFiles(string $directory): array
    {
        $this->loadGitignorePatterns($directory);

        $files = [];

        $excludeDirs = ["vendor", "node_modules", ".git"];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) continue;

            $path = $file->getRealPath();

            $excluded = false;

            foreach ($excludeDirs as $excludeDir) {
                if (!str_contains($path, DIRECTORY_SEPARATOR . $excludeDir . DIRECTORY_SEPARATOR)) continue;

                $excluded = true;

                break;
            }

            if ($excluded) continue;

            if ($this->isGitignored($path)) continue;

            if (pathinfo($path, PATHINFO_EXTENSION) === "php") $files[] = $path;
        }

        sort($files);

        return $files;
    }

    /**
     * Get relative path
     *
     * @param string $filePath
     * @param string $baseDirectory
     * @return string
     */
    protected function getRelativePath(string $filePath, string $baseDirectory): string
    {
        $baseDirectory = rtrim($baseDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (str_starts_with($filePath, $baseDirectory)) return substr($filePath, strlen($baseDirectory));

        return $filePath;
    }
}
