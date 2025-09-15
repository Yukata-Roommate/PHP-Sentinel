<?php

declare(strict_types=1);

namespace Sentinel\Detectors\Structure;

use Sentinel\Detectors\Foundation\StructureDetector;

use Sentinel\Contracts\Analyzer;

use Sentinel\Nodes\FunctionNode;

/**
 * Cyclomatic Complexity Detector
 *
 * @package Sentinel\Detectors\Structure
 */
class CyclomaticComplexityDetector extends StructureDetector
{
    /*----------------------------------------*
     * Name
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    public function name(): string
    {
        return "Cyclomatic Complexity Detector";
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

        foreach ($analyzer->functions() as $function) {
            $complexity = $this->calculateComplexity($function, $lines);

            if ($complexity <= $this->maxComplexity) continue;

            $this->addIssue(
                $relativePath,
                $function->line(),
                sprintf(
                    "Function \"%s\" has cyclomatic complexity of %d. (max: %d)",
                    $function->classFunctionName(),
                    $complexity,
                    $this->maxComplexity
                )
            );
        }
    }

    /**
     * Calculate cyclomatic complexity
     *
     * @param \Sentinel\Nodes\FunctionNode $function
     * @param array<string> $lines
     * @return int
     */
    protected function calculateComplexity(FunctionNode $function, array $lines): int
    {
        $complexity = 1;

        $startLine = $function->line() - 1;
        $endLine   = $this->findFunctionEndLine($lines, $startLine);

        for ($i = $startLine; $i <= $endLine && $i < count($lines); $i++) {
            $line = $lines[$i];

            $complexity += preg_match_all("/\b(if|elseif|for|foreach|while|case)\b/", $line, $matches);

            $complexity += preg_match_all("/\bcatch\b/", $line, $matches);

            $complexity += preg_match_all("/(\&\&|\|\|)\s/", $line, $matches);

            $complexity += preg_match_all("/\?.*:/", $line, $matches);
        }

        return $complexity;
    }

    /**
     * Find end line of function
     *
     * @param array<string> $lines
     * @param int $startLine
     * @return int
     */
    protected function findFunctionEndLine(array $lines, int $startLine): int
    {
        $braceLevel = 0;
        $foundStart = false;

        for ($i = $startLine; $i < count($lines); $i++) {
            $line = $lines[$i];

            $cleanLine = $this->removeStringsAndComments($line);

            $openBraces  = substr_count($cleanLine, "{");
            $closeBraces = substr_count($cleanLine, "}");

            $braceLevel += $openBraces - $closeBraces;

            if ($openBraces > 0) $foundStart = true;

            if ($foundStart && $braceLevel === 0) return $i;
        }

        return count($lines) - 1;
    }

    /**
     * Remove strings and comments from line
     *
     * @param string $line
     * @return string
     */
    protected function removeStringsAndComments(string $line): string
    {
        $line = preg_replace("~//.*~", "", $line);

        $line = preg_replace("~/\*.*?\*/~", "", $line);

        $line = preg_replace("/\"[^\"]*\"/", "\"\"", $line);
        $line = preg_replace("/'[^']*'/", "''", $line);

        return $line;
    }

    /*----------------------------------------*
     * Max Complexity
     *----------------------------------------*/

    /**
     * Maximum allowed complexity
     *
     * @var int
     */
    protected int $maxComplexity = 10;

    /**
     * Get maximum allowed complexity
     *
     * @return int
     */
    public function maxComplexity(): int
    {
        return $this->maxComplexity;
    }

    /**
     * Set maximum allowed complexity
     *
     * @param int $maxComplexity
     * @return static
     */
    public function setMaxComplexity(int $maxComplexity): static
    {
        $this->maxComplexity = $maxComplexity;

        return $this;
    }
}
