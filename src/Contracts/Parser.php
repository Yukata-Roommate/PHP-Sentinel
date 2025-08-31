<?php

declare(strict_types=1);

namespace Sentinel\Contracts;

/**
 * Parser Contract
 *
 * @package Sentinel\Contracts
 */
interface Parser
{
    /**
     * Check if file has strict types declaration
     *
     * @return bool
     */
    public function hasStrictTypes(): bool;

    /**
     * Get namespace
     *
     * @return string|null
     */
    public function namespace(): string|null;

    /**
     * Get use statements
     *
     * @return array<int, \Sentinel\Nodes\UseNode>
     */
    public function uses(): array;

    /**
     * Get classes
     *
     * @return array<int, \Sentinel\Nodes\ClassNode>
     */
    public function classes(): array;

    /**
     * Get properties
     *
     * @return array<int, \Sentinel\Nodes\PropertyNode>
     */
    public function properties(): array;

    /**
     * Get functions
     *
     * @return array<int, \Sentinel\Nodes\FunctionNode>
     */
    public function functions(): array;
}
