<?php

declare(strict_types=1);

namespace Sentinel;

use Sentinel\Contracts\Issue as IssueContract;

/**
 * Issue
 *
 * @package Sentinel
 */
class Issue implements IssueContract
{
    /**
     * File path
     *
     * @var string
     */
    protected string $file;

    /**
     * Line number
     *
     * @var int
     */
    protected int $line;

    /**
     * Issue message
     *
     * @var string
     */
    protected string $message;

    /**
     * Constructor
     *
     * @param string $file
     * @param int $line
     * @param string $message
     */
    public function __construct(string $file, int $line, string $message)
    {
        $this->file    = $file;
        $this->line    = $line;
        $this->message = $message;
    }

    /**
     * {@inheritDoc}
     */
    public function file(): string
    {
        return $this->file;
    }

    /**
     * {@inheritDoc}
     */
    public function line(): int
    {
        return $this->line;
    }

    /**
     * {@inheritDoc}
     */
    public function message(): string
    {
        return $this->message;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray(): array
    {
        return [
            "file"    => $this->file(),
            "line"    => $this->line(),
            "message" => $this->message(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return sprintf(
            "[%s:%d] %s",
            $this->file(),
            $this->line(),
            $this->message(),
        );
    }
}
