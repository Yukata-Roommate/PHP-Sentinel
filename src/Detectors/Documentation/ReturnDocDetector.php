<?php

declare(strict_types=1);

namespace Sentinel\Detectors\Documentation;

use Sentinel\Detectors\Foundation\DocumentationDetector;

use Sentinel\Contracts\Analyzer;

use Sentinel\Nodes\FunctionNode;
use Sentinel\Nodes\PHPDocNode;

/**
 * Return Doc Detector
 *
 * @package Sentinel\Detectors\Documentation
 */
class ReturnDocDetector extends DocumentationDetector
{
    /*----------------------------------------*
     * Name
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    public function name(): string
    {
        return "Return Doc Detector";
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
            if (in_array($function->name(), ["__construct", "__destruct"], true)) continue;

            if (!$function->hasPHPDoc()) continue;

            $phpDoc = $function->phpDoc();

            if (!$this->checkHasReturnTag($relativePath, $function, $phpDoc)) continue;

            if (!$function->hasReturnType()) continue;

            $this->checkReturnTypeConsistency($relativePath, $function, $phpDoc);
        }
    }

    /**
     * Check if has @return tag in function/method PHPDoc
     *
     * @param string $relativePath
     * @param \Sentinel\Nodes\FunctionNode $function
     * @param \Sentinel\Nodes\PHPDocNode $phpDoc
     * @return bool
     */
    protected function checkHasReturnTag(string $relativePath, FunctionNode $function, PHPDocNode $phpDoc): bool
    {
        if ($phpDoc->hasReturn()) return true;

        if ($function->hasReturnType() && $function->returnType() !== "void") {
            $this->addIssue(
                $relativePath,
                $phpDoc->start(),
                sprintf(
                    "Missing @return tag for %s \"%s\"",
                    $function->className() ? "method" : "function",
                    $function->classFunctionName()
                )
            );
        }

        return false;
    }

    /**
     * Check consistency between PHPDoc @return and actual return type
     *
     * @param string $relativePath
     * @param \Sentinel\Nodes\FunctionNode $function
     * @param \Sentinel\Nodes\PHPDocNode $phpDoc
     * @return void
     */
    protected function checkReturnTypeConsistency(string $relativePath, FunctionNode $function, PHPDocNode $phpDoc): void
    {
        $returnTag = $phpDoc->return();

        if ($returnTag === null) return;

        $docType    = $returnTag->type();
        $actualType = $function->returnType();

        if ($actualType === null) return;

        $docType    = ltrim($docType, "?");
        $actualType = ltrim($actualType, "?");

        if (!$this->isObviousMismatch($docType, $actualType)) return;

        $this->addIssue(
            $relativePath,
            $phpDoc->start(),
            sprintf(
                "@return type mismatch in \"%s\": documented \"%s\" vs actual \"%s\".",
                $function->classFunctionName(),
                $returnTag->type(),
                $function->returnType()
            )
        );
    }

    /**
     * Check if types are obviously mismatched
     *
     * @param string $docType
     * @param string $actualType
     * @return bool
     */
    protected function isObviousMismatch(string $docType, string $actualType): bool
    {
        $typeMap = [
            "boolean" => "bool",
            "integer" => "int",
            "double"  => "float",
            "real"    => "float",
        ];

        $docType    = $typeMap[strtolower($docType)] ?? $docType;
        $actualType = $typeMap[strtolower($actualType)] ?? $actualType;

        if ($docType === $actualType) return false;

        if ($docType === "mixed" || $actualType === "mixed") return false;

        $isDoctypeArrayable = $actualType === "array" || $actualType === "iterable";
        $isDocArrayable     = $docType === "array" || $docType === "iterable" || str_contains($docType, "[]");

        if ($isDoctypeArrayable && $isDocArrayable) return false;

        if ($this->isClassType($docType) && $this->isClassType($actualType)) return $this->getShortName($docType) !== $this->getShortName($actualType);

        return true;
    }

    /**
     * Check if type is class type
     *
     * @param string $type
     * @return bool
     */
    protected function isClassType(string $type): bool
    {
        $scalarTypes = [
            "bool",
            "int",
            "float",
            "string",
            "array",
            "callable",
            "iterable",
            "void",
            "mixed",
            "never",
            "null",
            "object"
        ];

        return !in_array(strtolower($type), $scalarTypes, true);
    }
}
