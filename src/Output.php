<?php

declare(strict_types=1);

namespace Sentinel;

use Sentinel\Contracts\Output as OutputContract;

use Sentinel\Contracts\Issue;

/**
 * Output
 *
 * @package Sentinel
 */
class Output implements OutputContract
{
    /*----------------------------------------*
     * Write
     *----------------------------------------*/

    /**
     * {@inheritDoc}
     */
    public function write(string $message): void
    {
        if (!$this->isDisplayable()) return;

        echo $message;
    }

    /**
     * {@inheritDoc}
     */
    public function writeln(string $message): void
    {
        $this->write($message . PHP_EOL);
    }

    /**
     * {@inheritDoc}
     */
    public function success(string $message): void
    {
        if ($this->isColorable()) $message = "\033[32m✓ {$message}\033[0m";

        $this->writeln($message);
    }

    /**
     * {@inheritDoc}
     */
    public function error(string $message): void
    {
        if ($this->isColorable()) $message = "\033[31m✗ {$message}\033[0m";

        $this->writeln($message);
    }

    /**
     * {@inheritDoc}
     */
    public function warning(string $message): void
    {
        if ($this->isColorable()) $message = "\033[33m⚠ {$message}\033[0m";

        $this->writeln($message);
    }

    /**
     * {@inheritDoc}
     */
    public function info(string $message): void
    {
        if ($this->isColorable()) $message = "\033[34mℹ {$message}\033[0m";

        $this->writeln($message);
    }

    /**
     * {@inheritDoc}
     */
    public function separator(): void
    {
        $this->writeln(str_repeat("=", 50));
    }

    /**
     * {@inheritDoc}
     */
    public function summary(string $label, int $count, bool $isSuccess = false): void
    {
        $message = "{$label}: {$count}";

        $isSuccess ? $this->success($message) : $this->error($message);
    }

    /**
     * {@inheritDoc}
     */
    public function header(string $title): void
    {
        $padding = (int)((50 - strlen($title)) / 2) - 1;

        $this->separator();
        $this->writeln(str_repeat(" ", $padding) . $title);
        $this->separator();
    }

    /**
     * {@inheritDoc}
     */
    public function section(string $title): void
    {
        $this->writeln("");
        $this->writeln($title);
        $this->writeln(str_repeat("-", strlen($title)));
    }

    /**
     * {@inheritDoc}
     */
    public function issue(Issue $issue): void
    {
        $this->writeln((string)$issue);
    }

    /**
     * {@inheritDoc}
     */
    public function issues(array $issues): void
    {
        foreach ($issues as $issue) {
            $this->issue($issue);
        }
    }

    /*----------------------------------------*
     * Config
     *----------------------------------------*/

    /**
     * Displayable
     *
     * @var bool
     */
    protected bool $displayable = true;

    /**
     * Colorable
     *
     * @var bool
     */
    protected bool $colorable = true;

    /**
     * {@inheritDoc}
     */
    public function setDisplayable(bool $displayable): static
    {
        $this->displayable = $displayable;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isDisplayable(): bool
    {
        return $this->displayable;
    }

    /**
     * {@inheritDoc}
     */
    public function setColorable(bool $colorable): static
    {
        $this->colorable = $colorable;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isColorable(): bool
    {
        return $this->colorable;
    }
}
