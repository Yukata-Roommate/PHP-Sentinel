<?php

declare(strict_types=1);

namespace Sentinel;

use Sentinel\Contracts\Detector as DetectorContract;

use Sentinel\Contracts\Analyzer as AnalyzerContract;
use Sentinel\Analyzer;

use Sentinel\Contracts\Issue;

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

        if ($this->useGitignore) $this->loadGitignorePatterns($directory);

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

    /**
     * Reset detector state
     *
     * @return void
     */
    protected function reset(): void
    {
        $this->issues       = [];
        $this->filesChecked = 0;

        $this->gitignorePatterns = [];
        $this->gitignoreCache = [];
    }

    /*----------------------------------------*
     * Exclude Directories
     *----------------------------------------*/

    /**
     * Exclude directories
     *
     * @var array<string>
     */
    protected array $excludeDirectories = ["vendor", "node_modules", ".git", ".svn", ".hg"];

    /**
     * {@inheritDoc}
     */
    public function excludeDirectories(): array
    {
        return $this->excludeDirectories;
    }

    /**
     * {@inheritDoc}
     */
    public function setExcludeDirectories(array $directories): static
    {
        $this->excludeDirectories = $directories;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addExcludeDirectory(string $directory): static
    {
        if (in_array($directory, $this->excludeDirectories, true)) return $this;

        $this->excludeDirectories[] = $directory;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function removeExcludeDirectory(string $directory): static
    {
        $this->excludeDirectories = array_values(
            array_filter(
                $this->excludeDirectories,
                fn($dir) => $dir !== $directory
            )
        );

        return $this;
    }

    /*----------------------------------------*
     * Gitignore
     *----------------------------------------*/

    /**
     * Whether to use gitignore
     *
     * @var bool
     */
    protected bool $useGitignore = true;

    /**
     * Whether to preload all gitignores
     *
     * @var bool
     */
    protected bool $preloadGitignores = true;

    /**
     * Gitignore patterns
     *
     * @var array<array{pattern: string, path: string, is_negation: bool}>
     */
    protected array $gitignorePatterns = [];

    /**
     * Gitignore cache
     *
     * @var array<string, bool>
     */
    protected array $gitignoreCache = [];

    /**
     * {@inheritDoc}
     */
    public function setUseGitignore(bool $useGitignore): static
    {
        $this->useGitignore = $useGitignore;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function useGitignore(): static
    {
        return $this->setUseGitignore(true);
    }

    /**
     * {@inheritDoc}
     */
    public function notUseGitignore(): static
    {
        return $this->setUseGitignore(false);
    }

    /**
     * {@inheritDoc}
     */
    public function setPreloadGitignores(bool $preloadGitignores): static
    {
        $this->preloadGitignores = $preloadGitignores;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function preloadGitignores(): static
    {
        return $this->setPreloadGitignores(true);
    }

    /**
     * {@inheritDoc}
     */
    public function notPreloadGitignores(): static
    {
        return $this->setPreloadGitignores(false);
    }

    /**
     * Load gitignore patterns
     *
     * @param string $directory
     * @return void
     */
    protected function loadGitignorePatterns(string $directory): void
    {
        $this->gitignorePatterns = [];
        $this->gitignoreCache    = [];

        if ($this->preloadGitignores) {
            $this->loadGitignoreRecursively($directory);
        } else {
            $gitignorePath = $directory . DIRECTORY_SEPARATOR . ".gitignore";

            if (file_exists($gitignorePath)) $this->parseGitignoreFile($gitignorePath, $directory);
        }
    }

    /**
     * Load gitignore files recursively
     *
     * @param string $path
     * @return void
     */
    protected function loadGitignoreRecursively(string $path): void
    {
        $gitignorePath = $path . DIRECTORY_SEPARATOR . ".gitignore";

        if (file_exists($gitignorePath) && is_readable($gitignorePath)) $this->parseGitignoreFile($gitignorePath, $path);

        $iterator = new \DirectoryIterator($path);

        foreach ($iterator as $item) {
            if ($item->isDot() || !$item->isDir()) continue;

            $dirName = $item->getFilename();

            if (in_array($dirName, $this->excludeDirectories(), true)) continue;

            $subPath = $item->getPathname();

            if (is_readable($subPath)) $this->loadGitignoreRecursively($subPath);
        }
    }

    /**
     * Parse gitignore file
     *
     * @param string $gitignorePath
     * @param string $basePath
     * @return void
     */
    protected function parseGitignoreFile(string $gitignorePath, string $basePath): void
    {
        $content = file_get_contents($gitignorePath);

        if ($content === false) return;

        $lines = explode(PHP_EOL, $content);

        foreach ($lines as $line) {
            $pattern = trim($line);

            if (empty($pattern) || str_starts_with($pattern, "#")) continue;

            $isNegation = str_starts_with($pattern, "!");

            if ($isNegation) $pattern = substr($pattern, 1);

            $this->gitignorePatterns[] = [
                "pattern"     => $pattern,
                "path"        => rtrim($basePath, DIRECTORY_SEPARATOR),
                "is_negation" => $isNegation,
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

            if ($this->matchesGitignorePattern($relativePath, $entry["pattern"])) $ignored = !$entry["is_negation"];
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

        $isDirectoryPattern = str_ends_with($pattern, "/");

        if ($isDirectoryPattern) {
            $pattern = rtrim($pattern, "/");

            if ($relativePath === $pattern || str_starts_with($relativePath, $pattern . "/")) return true;
        }

        $isRootPattern = str_starts_with($pattern, "/");

        if ($isRootPattern) {
            $pattern = ltrim($pattern, "/");

            if ($relativePath === $pattern) return true;

            if (str_starts_with($relativePath, $pattern . "/")) return true;

            return false;
        }

        if ($relativePath === $pattern) return true;

        if (str_contains($pattern, "*") || str_contains($pattern, "?")) {
            if (str_contains($pattern, "**")) {
                $regexPattern = str_replace("**", "{{DOUBLE_STAR}}", $pattern);
                $regexPattern = str_replace("*", "{{STAR}}", $regexPattern);
                $regexPattern = str_replace("?", "{{QUESTION}}", $regexPattern);

                $regexPattern = preg_quote($regexPattern, "#");
                $regexPattern = str_replace("{{DOUBLE_STAR}}", ".*", $regexPattern);
                $regexPattern = str_replace("{{STAR}}", "[^/]*", $regexPattern);
                $regexPattern = str_replace("{{QUESTION}}", "[^/]", $regexPattern);

                if (preg_match("#(^|/)" . $regexPattern . "($|/)#", $relativePath)) {
                    return true;
                }
            } else {
                if (fnmatch($pattern, $relativePath)) return true;

                $pathParts = explode("/", $relativePath);
                foreach ($pathParts as $i => $part) {
                    $remainingPath = implode("/", array_slice($pathParts, $i));

                    if (fnmatch($pattern, $remainingPath)) return true;
                }
            }

            return false;
        }

        $pathParts = explode("/", $relativePath);

        if (!str_contains($pattern, "/")) return in_array($pattern, $pathParts, true);

        $patternParts  = explode("/", $pattern);
        $patternLength = count($patternParts);

        for ($i = 0; $i <= count($pathParts) - $patternLength; $i++) {
            $pathSegment = implode("/", array_slice($pathParts, $i, $patternLength));

            if ($pathSegment === $pattern) return true;
        }

        return false;
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
        $files = [];

        $excludeDirs = $this->excludeDirectories();

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

            if ($this->useGitignore && $this->isGitignored($path)) continue;

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
