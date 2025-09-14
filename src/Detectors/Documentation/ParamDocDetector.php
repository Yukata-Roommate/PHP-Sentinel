<?php

declare(strict_types=1);

namespace Sentinel\Detectors\Documentation;

use Sentinel\Detectors\Foundation\DocumentationDetector;

use Sentinel\Contracts\Analyzer;

/**
 * Param Doc Detector
 *
 * @package Sentinel\Detectors\Documentation
 */
class ParamDocDetector extends DocumentationDetector
{
    /*----------------------------------------*
     * Name
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    public function name(): string
    {
        return "Param Doc Detector";
    }

    /*----------------------------------------*
     * Detect
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    protected function check(Analyzer $analyzer, string $relativePath): void
    {
        foreach ($analyzer->functions() as $function) {
            if (!$function->hasPHPDoc()) continue;

            $phpDoc     = $function->phpDoc();
            $parameters = $function->parameters();

            foreach ($parameters as $param) {
                if ($phpDoc->hasParam($param->name())) continue;

                $this->addIssue(
                    $relativePath,
                    $phpDoc->start(),
                    sprintf(
                        "Missing @param tag for parameter \"%s\" in %s \"%s\".",
                        $param->variableName(),
                        $function->className() ? "method" : "function",
                        $function->classFunctionName()
                    )
                );
            }

            foreach ($phpDoc->params() as $paramName => $paramTag) {
                if (isset($parameters[$paramName])) continue;

                $this->addIssue(
                    $relativePath,
                    $phpDoc->start(),
                    sprintf(
                        "Documented parameter \"%s\" does not exist in %s \"%s\".",
                        $paramTag->variableName(),
                        $function->className() ? "method" : "function",
                        $function->classFunctionName()
                    )
                );
            }
        }
    }
}
