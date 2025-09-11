<?php

declare(strict_types=1);

namespace Sentinel\Nodes\Leaves;

/**
 * PHP Doc Tag
 *
 * @package Sentinel\Nodes\Leaves
 */
abstract class PHPDocTag
{
    /*----------------------------------------*
     * Constructor
     *----------------------------------------*/

    /**
     * Constructor
     *
     * @param string|null $description
     */
    public function __construct(string|null $description)
    {
        if ($description !== null) {
            $description = trim($description);

            if ($description === "") $description = null;
        }

        $this->description = $description;
    }

    /*----------------------------------------*
     * Type
     *----------------------------------------*/

    /**
     * Parse type
     *
     * @param string $type
     * @return array<int, string>
     */
    protected function parseType(string $type): array
    {
        if (empty($type)) return [];

        $cleanType = $type;
        $cleanType = preg_replace("/\[\]$/", "", $cleanType);
        $cleanType = preg_replace("/^array<(.+)>$/i", "$1", $cleanType);

        $cleanType = str_replace("...", "", $cleanType);

        $isNullable = false;

        if (str_starts_with($cleanType, "?")) {
            $isNullable = true;
            $cleanType  = substr($cleanType, 1);
        }

        $types = match (true) {
            str_contains($cleanType, "|") => $this->splitUnionTypes($cleanType),

            default => [$cleanType],
        };

        $types = array_map("trim", $types);
        $types = array_filter($types, fn($t) => $t !== "");

        if ($isNullable && !in_array("null", $types, true)) $types[] = "null";

        $types = array_map(fn($type) => $this->normalizeType($type), $types);

        return array_unique($types);
    }

    /**
     * Split union types handling parentheses
     *
     * @param string $type
     * @return array<string>
     */
    protected function splitUnionTypes(string $type): array
    {
        $types = [];
        $current = "";
        $depth = 0;

        for ($i = 0; $i < strlen($type); $i++) {
            $char = $type[$i];

            if ($char === "(" || $char === "<") {
                $depth++;
            } elseif ($char === ")" || $char === ">") {
                $depth--;
            } elseif ($char === "|" && $depth === 0) {
                if ($current !== "") {
                    $types[] = $current;
                    $current = "";
                }

                continue;
            }

            $current .= $char;
        }

        if ($current !== "") $types[] = $current;

        return $types;
    }

    /**
     * Normalize type name
     *
     * @param string $type
     * @return string
     */
    protected function normalizeType(string $type): string
    {
        $typeMap = [
            "boolean"  => "bool",
            "integer"  => "int",
            "double"   => "float",
            "real"     => "float",
            "callback" => "callable",
            "NULL"     => "null",
        ];

        $lowerType = strtolower($type);

        if (isset($typeMap[$lowerType])) return $typeMap[$lowerType];

        if ($this->isBuiltInType($type)) return $lowerType;

        return $type;
    }

    /**
     * Check if type is nullable
     *
     * @param array<int, string> $types
     * @return bool
     */
    protected function isNullableType(array $types): bool
    {
        return in_array("null", $types, true);
    }

    /**
     * Check if type is variadic
     *
     * @param string $type
     * @return bool
     */
    protected function isVariadicType(string $type): bool
    {
        return str_ends_with($type, "[]") || str_contains($type, "...");
    }

    /**
     * Check if type is built-in
     *
     * @param string $type
     * @return bool
     */
    protected function isBuiltInType(string $type): bool
    {
        $builtInTypes = [
            "bool",
            "boolean",
            "int",
            "integer",
            "float",
            "double",
            "real",
            "string",

            "array",
            "object",
            "callable",
            "callback",
            "iterable",

            "resource",
            "null",
            "void",
            "mixed",
            "never",

            "true",
            "false",

            "self",
            "parent",
            "static",

            "scalar",
            "number",
            "numeric"
        ];

        return in_array(strtolower($type), $builtInTypes, true);
    }

    /*----------------------------------------*
     * Description
     *----------------------------------------*/

    /**
     * Description
     *
     * @var string|null
     */
    protected string|null $description;

    /**
     * Get description
     *
     * @return string|null
     */
    public function description(): string|null
    {
        return $this->description;
    }
}
