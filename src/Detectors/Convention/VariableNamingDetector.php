<?php

declare(strict_types=1);

namespace Sentinel\Detectors\Convention;

use Sentinel\Detectors\Foundation\ConventionDetector;

use Sentinel\Contracts\Analyzer;

/**
 * Variable Naming Detector
 *
 * @package Sentinel\Detectors\Convention
 */
class VariableNamingDetector extends ConventionDetector
{
    /*----------------------------------------*
     * Name
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    public function name(): string
    {
        return "Variable Naming Detector";
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

        foreach ($lines as $lineNumber => $line) {
            if (preg_match("/^\s*(\/\/|\/\*|\*)/", $line)) continue;

            if (!preg_match_all("/\$([a-zA-Z_][a-zA-Z0-9_]*)/", $line, $matches)) continue;

            foreach ($matches[1] as $varName) {
                if ($this->isSuperglobal($varName)) continue;

                if ($varName === "this") continue;

                if ($this->isCamelCase($varName)) continue;
                if ($this->isSnakeCase($varName)) continue;

                $this->addIssue(
                    $relativePath,
                    $lineNumber + 1,
                    sprintf(
                        "Variable \"$%s\" should be in camelCase.",
                        $varName
                    )
                );
            }
        }
    }

    /**
     * Check if variable is PHP superglobal
     *
     * @param string $name
     * @return bool
     */
    protected function isSuperglobal(string $name): bool
    {
        $superglobals = [
            "GLOBALS",
            "_SERVER",
            "_GET",
            "_POST",
            "_FILES",
            "_COOKIE",
            "_SESSION",
            "_REQUEST",
            "_ENV"
        ];

        return in_array($name, $superglobals, true);
    }
}
