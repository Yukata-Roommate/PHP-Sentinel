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
        $types = array_map("trim", explode("|", str_replace(["?", "[]", "..."], "", $type)));

        if (str_starts_with($type, "?") || str_contains($type, "null")) $types[] = "null";

        return array_filter($types, fn($t) => $t !== "");
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
