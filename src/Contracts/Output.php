<?php

declare(strict_types=1);

namespace Sentinel\Contracts;

use Sentinel\Contracts\Issue;

/**
 * Output Contract
 *
 * @package Sentinel\Contracts
 */
interface Output
{
    /*----------------------------------------*
     * Write
     *----------------------------------------*/

    /**
     * Write to output
     *
     * @param string $message
     * @return void
     */
    public function write(string $message): void;

    /**
     * Write line to output
     *
     * @param string $message
     * @return void
     */
    public function writeln(string $message): void;

    /**
     * Write success message
     *
     * @param string $message
     * @return void
     */
    public function success(string $message): void;

    /**
     * Write error message
     *
     * @param string $message
     * @return void
     */
    public function error(string $message): void;

    /**
     * Write warning message
     *
     * @param string $message
     * @return void
     */
    public function warning(string $message): void;

    /**
     * Write info message
     *
     * @param string $message
     * @return void
     */
    public function info(string $message): void;

    /**
     * Write separator
     *
     * @return void
     */
    public function separator(): void;

    /**
     * Write summary
     *
     * @param string $label
     * @param int $count
     * @param bool $isSuccess
     * @return void
     */
    public function summary(string $label, int $count, bool $isSuccess = false): void;

    /**
     * Write header
     *
     * @param string $title
     * @return void
     */
    public function header(string $title): void;

    /**
     * Write section
     *
     * @param string $title
     * @return void
     */
    public function section(string $title): void;

    /**
     * Write issue
     *
     * @param \Sentinel\Contracts\Issue $issue
     * @return void
     */
    public function issue(Issue $issue): void;

    /**
     * Write issues
     *
     * @param array<\Sentinel\Contracts\Issue> $issues
     * @return void
     */
    public function issues(array $issues): void;

    /*----------------------------------------*
     * Config
     *----------------------------------------*/

    /**
     * Set is displayable
     *
     * @param bool $displayable
     * @return static
     */
    public function setDisplayable(bool $displayable): static;

    /**
     * Whether is displayable
     *
     * @return bool
     */
    public function isDisplayable(): bool;

    /**
     * Set is colorable
     *
     * @param bool $colorable
     * @return static
     */
    public function setColorable(bool $colorable): static;

    /**
     * Whether is colorable
     *
     * @return bool
     */
    public function isColorable(): bool;
}
