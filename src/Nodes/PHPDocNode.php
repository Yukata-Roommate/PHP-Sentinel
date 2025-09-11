<?php

declare(strict_types=1);

namespace Sentinel\Nodes;

use Sentinel\Nodes\Leaves\ParamTag;
use Sentinel\Nodes\Leaves\ReturnTag;
use Sentinel\Nodes\Leaves\ThrowsTag;
use Sentinel\Nodes\Leaves\VarTag;

use Sentinel\Exceptions\ParamTagNotFoundException;
use Sentinel\Exceptions\ThrowsTagNotFoundException;

/**
 * PHP Doc Node
 *
 * @package Sentinel\Nodes
 */
class PHPDocNode
{
    /*----------------------------------------*
     * Constructor
     *----------------------------------------*/

    /**
     * Constructor
     *
     * @param string $content
     * @param int $start
     * @param int $end
     */
    public function __construct(string $content, int $start, int $end)
    {
        $this->content = $content;
        $this->start   = $start;
        $this->end     = $end;

        $this->parse($content);
    }

    /*----------------------------------------*
     * Content
     *----------------------------------------*/

    /**
     * Content
     *
     * @var string
     */
    protected string $content;

    /**
     * Start line number
     *
     * @var int
     */
    protected int $start;

    /**
     * End line number
     *
     * @var int
     */
    protected int $end;

    /**
     * Get content
     *
     * @return string
     */
    public function content(): string
    {
        return $this->content;
    }

    /**
     * Get start line number
     *
     * @return int
     */
    public function start(): int
    {
        return $this->start;
    }

    /**
     * Get end line number
     *
     * @return int
     */
    public function end(): int
    {
        return $this->end;
    }

    /*----------------------------------------*
     * Param
     *----------------------------------------*/

    /**
     * Param tags
     *
     * @var array<string, \Sentinel\Nodes\Leaves\ParamTag>
     */
    protected array $params = [];

    /**
     * Get param tags
     *
     * @return array<string, \Sentinel\Nodes\Leaves\ParamTag>
     */
    public function params(): array
    {
        return $this->params;
    }

    /**
     * Check if has any params
     *
     * @return bool
     */
    public function hasAnyParams(): bool
    {
        return !empty($this->params);
    }

    /**
     * Get param tag by name
     *
     * @param string $name
     * @return \Sentinel\Nodes\Leaves\ParamTag
     * @throws \Sentinel\Exceptions\ParamTagNotFoundException
     */
    public function param(string $name): ParamTag
    {
        if (!$this->hasParam($name)) throw new ParamTagNotFoundException($name);

        return $this->params[ltrim($name, "$")];
    }

    /**
     * Check if has param
     *
     * @param string $name
     * @return bool
     */
    public function hasParam(string $name): bool
    {
        return isset($this->params[ltrim($name, "$")]);
    }

    /*----------------------------------------*
     * Return
     *----------------------------------------*/

    /**
     * Return tag
     *
     * @var \Sentinel\Nodes\Leaves\ReturnTag|null
     */
    protected ReturnTag|null $return = null;

    /**
     * Get return tag
     *
     * @return \Sentinel\Nodes\Leaves\ReturnTag|null
     */
    public function return(): ReturnTag|null
    {
        return $this->return;
    }

    /**
     * Check if has return tag
     *
     * @return bool
     */
    public function hasReturn(): bool
    {
        return $this->return !== null;
    }

    /*----------------------------------------*
     * Throws
     *----------------------------------------*/

    /**
     * Throws tags
     *
     * @var array<string, \Sentinel\Nodes\Leaves\ThrowsTag>
     */
    protected array $throws = [];

    /**
     * Get throws tags
     *
     * @return array<string, \Sentinel\Nodes\Leaves\ThrowsTag>
     */
    public function throwses(): array
    {
        return $this->throws;
    }

    /**
     * Check if has any throws
     *
     * @return bool
     */
    public function hasAnyThrows(): bool
    {
        return !empty($this->throws);
    }

    /**
     * Get throws tag by exception name
     *
     * @param string $exception
     * @return \Sentinel\Nodes\Leaves\ThrowsTag
     * @throws \Sentinel\Exceptions\ThrowsTagNotFoundException
     */
    public function throws(string $exception): ThrowsTag
    {
        if (!$this->hasThrows($exception)) throw new ThrowsTagNotFoundException($exception);

        return $this->throws[ltrim($exception, "\\")];
    }

    /**
     * Check if has throws
     *
     * @param string $exception
     * @return bool
     */
    public function hasThrows(string $exception): bool
    {
        return isset($this->throws[ltrim($exception, "\\")]);
    }

    /*----------------------------------------*
     * Var
     *----------------------------------------*/

    /**
     * Var tag
     *
     * @var \Sentinel\Nodes\Leaves\VarTag|null
     */
    protected VarTag|null $var = null;

    /**
     * Get var tag
     *
     * @return \Sentinel\Nodes\Leaves\VarTag|null
     */
    public function var(): VarTag|null
    {
        return $this->var;
    }

    /**
     * Check if has var tag
     *
     * @return bool
     */
    public function hasVar(): bool
    {
        return $this->var !== null;
    }

    /*----------------------------------------*
     * Deprecated
     *----------------------------------------*/

    /**
     * Whether is deprecated
     *
     * @var bool
     */
    protected bool $isDeprecated = false;

    /**
     * Deprecated message
     *
     * @var string|null
     */
    protected string|null $deprecatedMessage = null;

    /**
     * Check if is deprecated
     *
     * @return bool
     */
    public function isDeprecated(): bool
    {
        return $this->isDeprecated;
    }

    /**
     * Get deprecated message
     *
     * @return string|null
     */
    public function deprecatedMessage(): string|null
    {
        return $this->deprecatedMessage;
    }

    /*----------------------------------------*
     * Summary
     *----------------------------------------*/

    /**
     * Summary
     *
     * @var string|null
     */
    protected string|null $summary = null;

    /**
     * Get summary
     *
     * @return string|null
     */
    public function summary(): string|null
    {
        return $this->summary;
    }

    /*----------------------------------------*
     * Description
     *----------------------------------------*/

    /**
     * Description
     *
     * @var string|null
     */
    protected string|null $description = null;

    /**
     * Get description
     *
     * @return string|null
     */
    public function description(): string|null
    {
        return $this->description;
    }

    /*----------------------------------------*
     * Other Tags
     *----------------------------------------*/

    /**
     * Other tags
     *
     * @var array<string, array<string>>
     */
    protected array $otherTags = [];

    /**
     * Get other tags by name
     *
     * @param string $tagName
     * @return array<string>
     */
    public function getTagsByName(string $tagName): array
    {
        return $this->otherTags[$tagName] ?? [];
    }

    /**
     * Check if has tag
     *
     * @param string $tagName
     * @return bool
     */
    public function hasTag(string $tagName): bool
    {
        return isset($this->otherTags[$tagName]) && !empty($this->otherTags[$tagName]);
    }

    /**
     * Get all tags
     *
     * @return array<string, array<string>>
     */
    public function getAllTags(): array
    {
        return $this->otherTags;
    }

    /*----------------------------------------*
     * Parse
     *----------------------------------------*/

    /**
     * Parse PHPDoc content
     *
     * @param string $content
     * @return void
     */
    protected function parse(string $content): void
    {
        $lines = explode("\n", $content);

        $summaryLines            = [];
        $descriptionLines        = [];
        $inDescription           = false;
        $currentMultilineTag     = null;
        $currentMultilineContent = "";

        foreach ($lines as $line) {
            $line = preg_replace("/^\s*\/?\*+\/?/", "", $line);
            $line = trim($line);

            if ($currentMultilineTag !== null) {
                if (preg_match("/^@\w+/", $line) || preg_match("/^\*\/$/", $line)) {
                    $this->parseTag($currentMultilineTag, trim($currentMultilineContent));

                    $currentMultilineTag     = null;
                    $currentMultilineContent = "";
                } else {
                    $currentMultilineContent .= " " . $line;

                    continue;
                }
            }

            if (empty($line)) {
                if (!empty($summaryLines) && !$inDescription) $inDescription = true;

                continue;
            }

            if (preg_match("/^@(\w+)(?:\s+(.*))?$/", $line, $matches)) {
                $tagName    = $matches[1];
                $tagContent = $matches[2] ?? "";

                if ($this->mightBeMultilineTag($tagName, $tagContent)) {
                    $currentMultilineTag     = $tagName;
                    $currentMultilineContent = $tagContent;
                } else {
                    $this->parseTag($tagName, $tagContent);
                }

                continue;
            }

            if (!$inDescription && empty($summaryLines)) {
                $summaryLines[] = $line;
            } elseif ($inDescription || !empty($summaryLines)) {
                $descriptionLines[] = $line;
            }
        }

        if ($currentMultilineTag !== null) $this->parseTag($currentMultilineTag, trim($currentMultilineContent));

        $this->summary     = !empty($summaryLines) ? implode(" ", $summaryLines) : null;
        $this->description = !empty($descriptionLines) ? implode("\n", $descriptionLines) : null;
    }

    /**
     * Check if tag might be multiline
     *
     * @param string $tagName
     * @param string $content
     * @return bool
     */
    protected function mightBeMultilineTag(string $tagName, string $content): bool
    {
        $multilineTags = ["param", "return", "throws", "var", "deprecated", "see", "example"];

        if (!in_array($tagName, $multilineTags, true)) return false;

        if ($tagName === "param" && preg_match("/^\S+\s+\$\w+$/", $content)) return true;

        if ($tagName === "return" && preg_match("/^\S+$/", $content)) return true;

        return false;
    }

    /**
     * Parse PHPDoc tag
     *
     * @param string $name
     * @param string $content
     * @return void
     */
    protected function parseTag(string $name, string $content): void
    {
        switch ($name) {
            case "param":
                $this->parseParamTag($content);

                break;
            case "return":
                $this->parseReturnTag($content);

                break;
            case "throws":
                $this->parseThrowsTag($content);

                break;
            case "var":
                $this->parseVarTag($content);

                break;
            case "deprecated":
                $this->parseDeprecatedTag($content);

                break;
            default:
                if (!isset($this->otherTags[$name])) $this->otherTags[$name] = [];

                $this->otherTags[$name][] = $content;

                break;
        }
    }

    /**
     * Parse param tag
     *
     * @param string $content
     * @return void
     */
    protected function parseParamTag(string $content): void
    {
        $patternStandard = "/^([^$\s]+(?:\|[^$\s]+)*(?:\[\])?)\s+\$([a-zA-Z_][a-zA-Z0-9_]*)(?:\s+(.*))?$/";
        $patternVariadic = "/^([^$\s]+(?:\|[^$\s]+)*)\s+\.\.\.\$([a-zA-Z_][a-zA-Z0-9_]*)(?:\s+(.*))?$/";

        if (!preg_match($patternStandard, $content, $matches) && !preg_match($patternVariadic, $content, $matches)) return;

        $name        = $matches[2];
        $type        = $matches[1];
        $description = $matches[3] ?? null;

        $this->params[$name] = new ParamTag($name, $type, $description);
    }

    /**
     * Parse return tag
     *
     * @param string $content
     * @return void
     */
    protected function parseReturnTag(string $content): void
    {
        if (!preg_match("/^(\S+(?:\|\S+)*(?:\[\])?)(?:\s+(.*))?$/", $content, $matches)) return;

        $type        = $matches[1];
        $description = $matches[2] ?? null;

        $this->return = new ReturnTag($type, $description);
    }

    /**
     * Parse throws tag
     *
     * @param string $content
     * @return void
     */
    protected function parseThrowsTag(string $content): void
    {
        if (!preg_match("/^(\\\\?[a-zA-Z_][a-zA-Z0-9_\\\\]*)(?:\s+(.*))?$/", $content, $matches)) return;

        $exception   = ltrim($matches[1], "\\");
        $description = $matches[2] ?? null;

        $this->throws[$exception] = new ThrowsTag($exception, $description);
    }

    /**
     * Parse var tag
     *
     * @param string $content
     * @return void
     */
    protected function parseVarTag(string $content): void
    {
        $patternTypeNameDesc = "/^(\S+(?:\|\S+)*(?:\[\])?)\s+\$([a-zA-Z_][a-zA-Z0-9_]*)(?:\s+(.*))?$/";
        $patternNameTypeDesc = "/^\$([a-zA-Z_][a-zA-Z0-9_]*)\s+(\S+(?:\|\S+)*(?:\[\])?)(?:\s+(.*))?$/";
        $patternTypeDesc     = "/^(\S+(?:\|\S+)*(?:\[\])?)(?:\s+(.*))?$/";

        if (preg_match($patternTypeNameDesc, $content, $matches)) {
            $type = $matches[1];
            $name = $matches[2];
            $description = $matches[3] ?? null;
        } elseif (preg_match($patternNameTypeDesc, $content, $matches)) {
            $name = $matches[1];
            $type = $matches[2];
            $description = $matches[3] ?? null;
        } elseif (preg_match($patternTypeDesc, $content, $matches)) {
            $type = $matches[1];
            $name = null;
            $description = $matches[2] ?? null;
        } else {
            return;
        }

        $this->var = new VarTag($type, $name, $description);
    }

    /**
     * Parse deprecated tag
     *
     * @param string $content
     * @return void
     */
    protected function parseDeprecatedTag(string $content): void
    {
        $this->isDeprecated      = true;
        $this->deprecatedMessage = !empty($content) ? trim($content) : null;
    }
}
