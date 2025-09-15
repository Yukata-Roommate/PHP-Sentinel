<?php

declare(strict_types=1);

namespace Sentinel\Nodes;

/**
 * Use Node
 *
 * @package Sentinel\Nodes
 */
class UseNode
{
    /*----------------------------------------*
     * Constructor
     *----------------------------------------*/

    /**
     * Constructor
     *
     * @param int $line
     * @param string $fullName
     * @param string|null $alias
     */
    public function __construct(int $line, string $fullName, string|null $alias = null)
    {
        $this->line  = $line;
        $this->alias = $alias;

        $this->setNames($fullName);
    }

    /*----------------------------------------*
     * Line
     *----------------------------------------*/

    /**
     * Line number
     *
     * @var int
     */
    protected int $line;

    /**
     * Get line number
     *
     * @return int
     */
    public function line(): int
    {
        return $this->line;
    }

    /*----------------------------------------*
     * Name
     *----------------------------------------*/

    /**
     * Full name
     *
     * @var string
     */
    protected string $fullName;

    /**
     * Short name
     *
     * @var string
     */
    protected string $shortName;

    /**
     * Namespace
     *
     * @var string
     */
    protected string $namespace;

    /**
     * Set names
     *
     * @param string $fullName
     * @return void
     */
    protected function setNames(string $fullName): void
    {
        $this->setFullName($fullName);
        $this->setShortName($fullName);
        $this->setNamespace($fullName);
    }

    /**
     * Set full name
     *
     * @param string $fullName
     * @return void
     */
    protected function setFullName(string $fullName): void
    {
        $this->fullName = ltrim($fullName, "\\");
    }

    /**
     * Set short name
     *
     * @param string $fullName
     * @return void
     */
    protected function setShortName(string $fullName): void
    {
        $parts = explode("\\", ltrim($fullName, "\\"));

        $this->shortName = end($parts);
    }

    /**
     * Set namespace
     *
     * @param string $fullName
     * @return void
     */
    protected function setNamespace(string $fullName): void
    {
        $parts = explode("\\", ltrim($fullName, "\\"));

        array_pop($parts);

        $this->namespace = implode("\\", $parts);
    }

    /**
     * Get full name
     *
     * @return string
     */
    public function fullName(): string
    {
        return $this->fullName;
    }

    /*----------------------------------------*
     * Alias
     *----------------------------------------*/

    /**
     * Alias name
     *
     * @var string|null
     */
    protected string|null $alias;

    /**
     * Get alias name
     *
     * @return string|null
     */
    public function alias(): string|null
    {
        return $this->alias;
    }

    /**
     * Check if has alias
     *
     * @return bool
     */
    public function hasAlias(): bool
    {
        return $this->alias !== null;
    }

    /**
     * Get effective name
     *
     * @return string
     */
    public function effectiveName(): string
    {
        return $this->alias ?? $this->shortName;
    }

    /*----------------------------------------*
     * Used
     *----------------------------------------*/

    /**
     * Whether is actually used
     *
     * @var bool
     */
    protected bool $isUsed = false;

    /**
     * Check if used
     *
     * @return bool
     */
    public function isUsed(): bool
    {
        return $this->isUsed;
    }

    /**
     * Mark as used
     *
     * @return static
     */
    public function used(): static
    {
        $this->isUsed = true;

        return $this;
    }
}
