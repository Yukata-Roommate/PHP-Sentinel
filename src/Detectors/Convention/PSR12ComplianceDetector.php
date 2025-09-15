<?php

declare(strict_types=1);

namespace Sentinel\Detectors\Convention;

use Sentinel\Detectors\Foundation\ConventionDetector;

use Sentinel\Contracts\Analyzer;

/**
 * PSR-12 Compliance Detector
 *
 * @package Sentinel\Detectors\Convention
 */
class PSR12ComplianceDetector extends ConventionDetector
{
    /*----------------------------------------*
     * Name
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    public function name(): string
    {
        return "PSR-12 Compliance Detector";
    }

    /*----------------------------------------*
     * Detect
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    protected function check(Analyzer $analyzer, string $relativePath): void
    {
        $lines = $analyzer->lines();

        $this->checkFileStructure($analyzer, $relativePath);

        foreach ($lines as $lineNumber => $line) {
            $this->checkIndentation($line, $lineNumber + 1, $relativePath);
            $this->checkLineLength($line, $lineNumber + 1, $relativePath);
        }

        foreach ($analyzer->classes() as $class) {
            if ($this->isPascalCase($class->name())) continue;

            $this->addIssue(
                $relativePath,
                $class->start(),
                sprintf("Class name \"%s\" must be in PascalCase.", $class->name())
            );
        }

        foreach ($analyzer->functions() as $function) {
            if (!$function->className()) continue;
            if ($this->isCamelCase($function->name())) continue;
            if (str_starts_with($function->name(), "__")) continue;

            $this->addIssue(
                $relativePath,
                $function->line(),
                sprintf("Method name \"%s\" must be in camelCase.", $function->name())
            );
        }
    }

    /**
     * Check file structure
     *
     * @param \Sentinel\Contracts\Analyzer $analyzer
     * @param string $relativePath
     * @return void
     */
    protected function checkFileStructure(Analyzer $analyzer, string $relativePath): void
    {
        $lines = $analyzer->lines();

        if (!empty($lines) && !str_starts_with(trim($lines[0]), "<?php")) {
            $this->addIssue(
                $relativePath,
                1,
                "File must start with <?php tag."
            );
        }

        if (!$analyzer->hasStrictTypes()) return;

        for ($i = 0; $i < min(5, count($lines)); $i++) {
            if (!str_contains($lines[$i], "declare(strict_types=1)")) continue;

            if ($i > 1) $this->addIssue(
                $relativePath,
                $i + 1,
                "declare(strict_types=1) must be on line 2 or 3."
            );

            break;
        }
    }

    /**
     * Check indentation
     *
     * @param string $line
     * @param int $lineNumber
     * @param string $relativePath
     * @return void
     */
    protected function checkIndentation(string $line, int $lineNumber, string $relativePath): void
    {
        if (trim($line) === "") return;

        if (str_contains($line, "\t")) {
            $this->addIssue(
                $relativePath,
                $lineNumber,
                "Indentation must use spaces, not tabs."
            );
        }

        if (!preg_match("/^(\s+)/", $line, $matches)) return;

        $spaces = strlen($matches[1]);

        if ($spaces % 4 === 0) return;

        $this->addIssue(
            $relativePath,
            $lineNumber,
            "Indentation must be in multiples of 4 spaces."
        );
    }

    /**
     * Check line length
     *
     * @param string $line
     * @param int $lineNumber
     * @param string $relativePath
     * @return void
     */
    protected function checkLineLength(string $line, int $lineNumber, string $relativePath): void
    {
        $length = mb_strlen(rtrim($line));

        if ($length <= 120) return;

        $this->addIssue(
            $relativePath,
            $lineNumber,
            sprintf("Line exceeds 120 characters. (%d characters)", $length)
        );
    }
}
