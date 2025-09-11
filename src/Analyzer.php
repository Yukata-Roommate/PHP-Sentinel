<?php

declare(strict_types=1);

namespace Sentinel;

use Sentinel\Contracts\Analyzer as AnalyzerContract;

use Sentinel\Contracts\Parser as ParserContract;
use Sentinel\Parser;

use Sentinel\Exceptions\FileNotFoundException;
use Sentinel\Exceptions\FileReadException;
use Sentinel\Exceptions\OutOfRangeLineException;

/**
 * Analyzer
 *
 * @package Sentinel
 */
class Analyzer implements AnalyzerContract
{
    /*----------------------------------------*
     * Constructor
     *----------------------------------------*/

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->parser = new Parser([]);
    }

    /*----------------------------------------*
     * File
     *----------------------------------------*/

    /**
     * File path
     *
     * @var string
     */
    protected string $file = "";

    /**
     * File content
     *
     * @var string
     */
    protected string $content = "";

    /**
     * File lines
     *
     * @var array<int, string>
     */
    protected array $lines = [];

    /**
     * {@inheritDoc}
     */
    public function setFile(string $file): static
    {
        if (!file_exists($file)) throw new FileNotFoundException($file);

        if (!is_file($file)) throw new FileNotFoundException($file);

        if (!is_readable($file)) throw new FileReadException($file);

        $this->file = $file;

        $content = @file_get_contents($file);

        if ($content === false) throw new FileReadException($file);

        $this->content = $content;
        $this->lines   = array_values(preg_split("/\r\n|\r|\n/", $this->content));
        $this->parser  = new Parser($this->lines);

        return $this;
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
    public function content(): string
    {
        return $this->content;
    }

    /**
     * {@inheritDoc}
     */
    public function lines(): array
    {
        return $this->lines;
    }

    /**
     * {@inheritDoc}
     */
    public function line(int $line): string
    {
        if ($line < 1 || $line > count($this->lines)) throw new OutOfRangeLineException($this->file, $line);

        return $this->lines[$line - 1];
    }

    /*----------------------------------------*
     * Parse
     *----------------------------------------*/

    /**
     * Parser
     *
     * @var \Sentinel\Contracts\Parser
     */
    protected ParserContract $parser;

    /**
     * {@inheritDoc}
     */
    public function hasStrictTypes(): bool
    {
        return $this->parser->hasStrictTypes();
    }

    /**
     * {@inheritDoc}
     */
    public function namespace(): string|null
    {
        return $this->parser->namespace();
    }

    /**
     * {@inheritDoc}
     */
    public function uses(): array
    {
        return $this->parser->uses();
    }

    /**
     * {@inheritDoc}
     */
    public function classes(): array
    {
        return $this->parser->classes();
    }

    /**
     * {@inheritDoc}
     */
    public function properties(): array
    {
        return $this->parser->properties();
    }

    /**
     * {@inheritDoc}
     */
    public function functions(): array
    {
        return $this->parser->functions();
    }
}
