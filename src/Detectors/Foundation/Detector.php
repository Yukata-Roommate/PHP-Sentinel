<?php

declare(strict_types=1);

namespace Sentinel\Detectors\Foundation;

use Sentinel\Contracts\Detector as DetectorContract;

use Sentinel\Contracts\Analyzer as AnalyzerContract;
use Sentinel\Analyzer;

use Sentinel\Issue;

use Sentinel\Exceptions\DirectoryNotFoundException;

/**
 * Detector
 *
 * @package Sentinel\Detectors\Foundation
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

        foreach ($this->getPhpFiles($directory) as $file) {
            $relativePath = $this->getRelativePath($file, $directory);

            $analyzer = new Analyzer();

            try {
                $analyzer->setFile($file);

                $this->check($analyzer, $relativePath);

                $this->filesChecked++;
            } catch (\Exception $e) {
                $this->addError($relativePath, $e->getMessage());
            }

            unset($analyzer);
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
        $this->gitignorePatterns = [];
        $this->gitignoreCache    = [];

        $this->issues       = [];
        $this->filesChecked = 0;
        $this->errors       = [];
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
     * @var array<array{pattern: string, path: string, is_negation: bool, regex: string|null}>
     */
    protected array $gitignorePatterns = [];

    /**
     * Gitignore cache
     *
     * @var array<string, bool>
     */
    protected array $gitignoreCache = [];

    /**
     * Maximum cache size
     *
     * @var int
     */
    protected int $maxCacheSize = 10000;

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

        foreach ($this->gitignorePatterns as &$entry) {
            $entry["regex"] = $this->compileGitignorePattern($entry["pattern"]);
        }

        unset($entry);
    }

    /**
     * Load gitignore files recursively
     *
     * @param string $path
     * @param int $depth
     * @return void
     */
    protected function loadGitignoreRecursively(string $path, int $depth = 0): void
    {
        if ($depth > 20) return;

        $gitignorePath = $path . DIRECTORY_SEPARATOR . ".gitignore";

        if (file_exists($gitignorePath) && is_readable($gitignorePath)) $this->parseGitignoreFile($gitignorePath, $path);

        try {
            $iterator = new \DirectoryIterator($path);

            foreach ($iterator as $item) {
                if ($item->isDot() || !$item->isDir()) continue;

                $dirName = $item->getFilename();

                if (in_array($dirName, $this->excludeDirectories(), true)) continue;

                $subPath = $item->getPathname();

                if (is_readable($subPath)) $this->loadGitignoreRecursively($subPath, $depth + 1);
            }
        } catch (\Exception $e) {
            $this->addError($path, $e->getMessage());
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
        $content = @file_get_contents($gitignorePath);

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
                "regex"       => null,
            ];
        }
    }

    /**
     * Compile gitignore pattern to regex
     *
     * @param string $pattern
     * @return string|null
     */
    protected function compileGitignorePattern(string $pattern): ?string
    {
        if (empty($pattern)) return null;

        $isDirectory = str_ends_with($pattern, "/");

        if ($isDirectory) $pattern = rtrim($pattern, "/");

        $isRootPattern = str_starts_with($pattern, "/");

        if ($isRootPattern) $pattern = substr($pattern, 1);

        $pattern = preg_quote($pattern, "#");

        $pattern = str_replace("\\*\\*", "{{DOUBLE_STAR}}", $pattern);
        $pattern = str_replace("\\*", "[^/]*", $pattern);
        $pattern = str_replace("\\?", "[^/]", $pattern);
        $pattern = str_replace("{{DOUBLE_STAR}}", ".*", $pattern);

        if ($isRootPattern) {
            $regex = "^" . $pattern;
        } else {
            $regex = "(^|/)" . $pattern;
        }

        if ($isDirectory) {
            $regex .= "(/|$)";
        } else {
            $regex .= "($|/)";
        }

        return "#" . $regex . "#";
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

        if (count($this->gitignoreCache) > $this->maxCacheSize) $this->gitignoreCache = array_slice($this->gitignoreCache, - ($this->maxCacheSize / 2), null, true);

        $ignored = false;

        foreach ($this->gitignorePatterns as $entry) {
            if (!str_starts_with($path, $entry["path"])) continue;

            $relativePath = substr($path, strlen($entry["path"]) + 1);
            $relativePath = str_replace(DIRECTORY_SEPARATOR, "/", $relativePath);

            if ($entry["regex"] && @preg_match($entry["regex"], $relativePath)) $ignored = !$entry["is_negation"];
        }

        $this->gitignoreCache[$path] = $ignored;

        return $ignored;
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
     * Errors during detection
     *
     * @var array<string, string>
     */
    protected array $errors = [];

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
     * {@inheritDoc}
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Add issue
     *
     * @param string $file
     * @param int $line
     * @param string $message
     * @return void
     */
    protected function addIssue(string $file, int $line, string $message): void
    {
        $this->issues[] = new Issue($file, $line, $message);
    }

    /**
     * Add error message
     *
     * @param string $file
     * @param string $message
     * @return void
     */
    protected function addError(string $file, string $message): void
    {
        $this->errors[$file] = $message;
    }

    /*----------------------------------------*
     * File Operations
     *----------------------------------------*/

    /**
     * Get PHP files using generator for memory efficiency
     *
     * @param string $directory
     * @return \Generator<string>
     */
    protected function getPhpFiles(string $directory): \Generator
    {
        $excludeDirs = $this->excludeDirectories();

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveCallbackFilterIterator(
                    new \RecursiveDirectoryIterator(
                        $directory,
                        \RecursiveDirectoryIterator::SKIP_DOTS | \RecursiveDirectoryIterator::FOLLOW_SYMLINKS
                    ),
                    function (\SplFileInfo $file) use ($excludeDirs) {
                        if (!$file->isDir()) return true;

                        $dirName = $file->getFilename();

                        return !in_array($dirName, $excludeDirs, true);
                    }
                ),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            $iterator->setMaxDepth(100);

            foreach ($iterator as $file) {
                if (!$file->isFile()) continue;

                $path = $file->getRealPath();

                if ($path === false) continue;

                if ($file->getExtension() !== "php") continue;

                if ($this->useGitignore && $this->isGitignored($path)) continue;

                yield $path;
            }
        } catch (\Exception $e) {
            $this->addError($directory, $e->getMessage());
        }
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

        $fileParts = explode(DIRECTORY_SEPARATOR, $filePath);
        $baseParts = explode(DIRECTORY_SEPARATOR, rtrim($baseDirectory, DIRECTORY_SEPARATOR));

        $commonLength = 0;
        for ($i = 0; $i < min(count($fileParts), count($baseParts)); $i++) {
            if ($fileParts[$i] !== $baseParts[$i]) break;

            $commonLength++;
        }

        $upCount       = count($baseParts) - $commonLength;
        $relativeParts = array_fill(0, $upCount, "..");
        $relativeParts = array_merge($relativeParts, array_slice($fileParts, $commonLength));

        return implode(DIRECTORY_SEPARATOR, $relativeParts);
    }
}
