<?php

declare(strict_types=1);

namespace Sentinel;

use Sentinel\Contracts\Parser as ParserContract;

use Sentinel\Nodes\PHPDocNode;
use Sentinel\Nodes\UseNode;
use Sentinel\Nodes\ClassNode;
use Sentinel\Nodes\PropertyNode;
use Sentinel\Nodes\FunctionNode;
use Sentinel\Nodes\Leaves\Parameter;

/**
 * Parser
 *
 * @package Sentinel
 */
class Parser implements ParserContract
{
    /*----------------------------------------*
     * Constructor
     *----------------------------------------*/

    /**
     * Constructor
     *
     * @param array<int, string> $lines
     */
    public function __construct(array $lines)
    {
        if (empty($lines)) return;

        $this->lines = array_values($lines);

        $this->parse();
    }

    /*----------------------------------------*
     * Parse
     *----------------------------------------*/

    /**
     * File lines
     *
     * @var array<int, string>
     */
    protected array $lines;

    /**
     * Whether is in class
     *
     * @var bool
     */
    protected bool $inClass = false;

    /**
     * Current class
     *
     * @var \Sentinel\Nodes\ClassNode|null
     */
    protected ClassNode|null $currentClass = null;

    /**
     * Brace level
     *
     * @var int
     */
    protected int $braceLevel = 0;

    /**
     * Class brace level
     *
     * @var int
     */
    protected int $classBraceLevel = 0;

    /**
     * Function brace level stack
     *
     * @var array<int>
     */
    protected array $functionBraceLevels = [];

    /**
     * In multiline declaration
     *
     * @var bool
     */
    protected bool $inMultilineDeclaration = false;

    /**
     * Multiline buffer
     *
     * @var string
     */
    protected string $multilineBuffer = "";

    /**
     * Multiline start line
     *
     * @var int
     */
    protected int $multilineStartLine = 0;

    /**
     * Parse lines
     *
     * @return void
     */
    protected function parse(): void
    {
        $totalLines = count($this->lines);

        for ($i = 0; $i < $totalLines; $i++) {
            $line = $this->lines[$i];
            $lineNumber = $i + 1;

            if ($this->inMultilineDeclaration) {
                $this->multilineBuffer .= " " . trim($line);

                if ($this->isMultilineEnd($line)) {
                    $this->processMultilineLine($this->multilineBuffer, $this->multilineStartLine);

                    $this->inMultilineDeclaration = false;
                    $this->multilineBuffer        = "";
                }

                $this->updateBraceLevel($line);

                continue;
            }

            if ($this->isMultilineStart($line)) {
                $this->inMultilineDeclaration = true;
                $this->multilineBuffer        = trim($line);
                $this->multilineStartLine     = $lineNumber;

                $this->updateBraceLevel($line);

                continue;
            }

            $this->updateBraceLevel($line);
            $this->parseStrictTypes($line);
            $this->parseNamespace($line);
            $this->parseUseNode($line, $lineNumber);
            $this->parseClassNode($line, $lineNumber);
            $this->parsePropertyNode($line, $lineNumber);
            $this->parseFunctionNode($line, $lineNumber);

            $this->trackClassEnd($lineNumber);
        }
    }

    /**
     * Check if line starts a multiline declaration
     *
     * @param string $line
     * @return bool
     */
    protected function isMultilineStart(string $line): bool
    {
        if (preg_match("/^\s*(?:public|private|protected|static|abstract|final)*\s*function\s+\w+\s*\(/", $line)) return substr_count($line, "(") > substr_count($line, ")");

        if (preg_match("/^\s*(?:abstract|final)?\s*class\s+\w+/", $line)) return !str_contains($line, "{");

        return false;
    }

    /**
     * Check if line ends a multiline declaration
     *
     * @param string $line
     * @return bool
     */
    protected function isMultilineEnd(string $line): bool
    {
        if (str_contains($this->multilineBuffer, "function")) {
            $openParen  = substr_count($this->multilineBuffer, "(");
            $closeParen = substr_count($this->multilineBuffer, ")");

            if ($openParen === $closeParen && $openParen > 0) return preg_match("/[)]\s*(?::\s*[\w\\\|?]+)?\s*(?:\{|;|$)/", $line) === 1;
        }

        if (str_contains($this->multilineBuffer, "class")) return str_contains($line, "{");

        return false;
    }

    /**
     * Process multiline declaration
     *
     * @param string $fullLine
     * @param int $lineNumber
     * @return void
     */
    protected function processMultilineLine(string $fullLine, int $lineNumber): void
    {
        if (str_contains($fullLine, "function")) {
            $this->parseFunctionNode($fullLine, $lineNumber);
        } elseif (str_contains($fullLine, "class")) {
            $this->parseClassNode($fullLine, $lineNumber);
        }
    }

    /**
     * Update brace level
     *
     * @param string $line
     * @return void
     */
    protected function updateBraceLevel(string $line): void
    {
        $cleanLine = $this->removeStringsAndComments($line);

        $this->braceLevel += substr_count($cleanLine, "{") - substr_count($cleanLine, "}");
    }

    /**
     * Remove strings and comments from line
     *
     * @param string $line
     * @return string
     */
    protected function removeStringsAndComments(string $line): string
    {
        $line = preg_replace("~//.*~", "", $line);

        $line = preg_replace("~/\*.*?\*/~s", "", $line);

        $line = preg_replace('/"(?:[^"\\\\]|\\\\.)*"/', '""', $line);
        $line = preg_replace("/'(?:[^'\\\\]|\\\\.)*'/", "''", $line);

        return $line;
    }

    /**
     * Track class end
     *
     * @param int $lineNumber
     * @return void
     */
    protected function trackClassEnd(int $lineNumber): void
    {
        if (!$this->inClass) return;

        if ($this->braceLevel <= $this->classBraceLevel) {
            if ($this->currentClass !== null) $this->currentClass->setEnd($lineNumber);

            $this->inClass      = false;
            $this->currentClass = null;
        }
    }

    /**
     * Extract PHPDoc before line
     *
     * @param int $lineNumber
     * @return \Sentinel\Nodes\PHPDocNode|null
     */
    protected function extractPHPDocNode(int $lineNumber): PHPDocNode|null
    {
        $phpDocLines = [];
        $startLine = 0;
        $endLine = 0;
        $inDoc = false;

        for ($i = $lineNumber - 2; $i >= 0; $i--) {
            $line = $this->lines[$i];

            if (!$inDoc) {
                if (preg_match("/^\s*\*\/\s*$/", $line)) {
                    $inDoc   = true;
                    $endLine = $i + 1;

                    array_unshift($phpDocLines, $line);
                } elseif (!preg_match("/^\s*$/", $line)) {
                    break;
                }
            } else {
                array_unshift($phpDocLines, $line);

                if (preg_match("/^\s*\/\*\*/", $line)) {
                    $startLine = $i + 1;

                    break;
                }
            }
        }

        if ($startLine > 0 && $endLine > 0 && !empty($phpDocLines)) {
            $content = implode("\n", $phpDocLines);

            return new PHPDocNode($content, $startLine, $endLine);
        }

        return null;
    }

    /*----------------------------------------*
     * Has Strict Types
     *----------------------------------------*/

    /**
     * Whether has strict types declaration
     *
     * @var bool
     */
    protected bool $hasStrictTypes = false;

    /**
     * {@inheritDoc}
     */
    public function hasStrictTypes(): bool
    {
        return $this->hasStrictTypes;
    }

    /**
     * Parse strict types declaration
     *
     * @param string $line
     * @return void
     */
    protected function parseStrictTypes(string $line): void
    {
        if (!preg_match("/declare\s*\(\s*strict_types\s*=\s*1\s*\)/", $line)) return;

        $this->hasStrictTypes = true;
    }

    /*----------------------------------------*
     * Namespace
     *----------------------------------------*/

    /**
     * Namespace
     *
     * @var string|null
     */
    protected string|null $namespace = null;

    /**
     * {@inheritDoc}
     */
    public function namespace(): string|null
    {
        return $this->namespace;
    }

    /**
     * Parse namespace declaration
     *
     * @param string $line
     * @return void
     */
    protected function parseNamespace(string $line): void
    {
        if (!preg_match("/^\s*namespace\s+([a-zA-Z_\\\\][a-zA-Z0-9_\\\\]*)\s*;/", $line, $matches)) return;

        $this->namespace = $matches[1];
    }

    /*----------------------------------------*
     * Use
     *----------------------------------------*/

    /**
     * Use statements
     *
     * @var array<int, \Sentinel\Nodes\UseNode>
     */
    protected array $uses = [];

    /**
     * {@inheritDoc}
     */
    public function uses(): array
    {
        return $this->uses;
    }

    /**
     * Parse use node
     *
     * @param string $line
     * @param int $lineNumber
     * @return void
     */
    protected function parseUseNode(string $line, int $lineNumber): void
    {
        if (preg_match("/^\s*use\s+([a-zA-Z_\\\\][a-zA-Z0-9_\\\\]*)\\\\\{([^}]+)\}/", $line, $matches)) {
            $namespace = $matches[1];
            $items     = explode(",", $matches[2]);

            foreach ($items as $item) {
                $item = trim($item);

                if (preg_match("/^([a-zA-Z_][a-zA-Z0-9_]*)(?:\s+as\s+([a-zA-Z_][a-zA-Z0-9_]*))?$/", $item, $itemMatches)) {
                    $fullName = $namespace . "\\" . $itemMatches[1];
                    $alias    = $itemMatches[2] ?? null;

                    $this->uses[] = new UseNode($lineNumber, $fullName, $alias);
                }
            }
            return;
        }

        if (!preg_match("/^\s*use\s+([a-zA-Z_\\\\][a-zA-Z0-9_\\\\]*(?:\\\\[a-zA-Z_][a-zA-Z0-9_]*)*)\s*(?:as\s+([a-zA-Z_][a-zA-Z0-9_]*))?\s*;/", $line, $matches)) return;

        $fullName = $matches[1];
        $alias    = $matches[2] ?? null;

        $this->uses[] = new UseNode($lineNumber, $fullName, $alias);
    }

    /*----------------------------------------*
     * Class
     *----------------------------------------*/

    /**
     * Classes
     *
     * @var array<int, \Sentinel\Nodes\ClassNode>
     */
    protected array $classes = [];

    /**
     * {@inheritDoc}
     */
    public function classes(): array
    {
        return $this->classes;
    }

    /**
     * Parse class node
     *
     * @param string $line
     * @param int $lineNumber
     * @return void
     */
    protected function parseClassNode(string $line, int $lineNumber): void
    {
        if (!preg_match("/^\s*(?:(abstract|final|readonly)\s+)?(class|interface|trait|enum)\s+([a-zA-Z_][a-zA-Z0-9_]*)/", $line, $matches)) return;

        if ($this->inClass && $this->braceLevel > $this->classBraceLevel) return;

        $this->inClass         = true;
        $this->classBraceLevel = $this->braceLevel;

        $modifier = $matches[1] ?? null;
        $type     = $matches[2];
        $name     = $matches[3];

        $isReadonly = $modifier === "readonly";

        if ($isReadonly) $modifier = null;

        $class = new ClassNode($name, $lineNumber, $modifier, $type);

        if ($isReadonly) $class->setIsReadonly(true);

        $class->setNamespace($this->namespace);

        $this->setExtendsToClassNode($class, $line);
        $this->addImplementsToClassNode($class, $line);

        $this->currentClass = $class;
        $this->classes[]    = $class;
    }

    /**
     * Set extends to class node
     *
     * @param \Sentinel\Nodes\ClassNode $class
     * @param string $line
     * @return void
     */
    protected function setExtendsToClassNode(ClassNode $class, string $line): void
    {
        if (!preg_match("/extends\s+([a-zA-Z_\\\\][a-zA-Z0-9_\\\\]*)/", $line, $matches)) return;

        $extends = $matches[1];

        $class->setExtends($extends);
    }

    /**
     * Add implements to class node
     *
     * @param \Sentinel\Nodes\ClassNode $class
     * @param string $line
     * @return void
     */
    protected function addImplementsToClassNode(ClassNode $class, string $line): void
    {
        if (!preg_match("/implements\s+(.+?)(?:\s*\{|$)/", $line, $matches)) return;

        $interfaces = preg_split("/,\s*/", trim($matches[1]));

        foreach ($interfaces as $interface) {
            $interface = trim($interface);

            if (!preg_match("/^[a-zA-Z_\\\\][a-zA-Z0-9_\\\\]*$/", $interface)) continue;

            $class->addImplements($interface);
        }
    }

    /*----------------------------------------*
     * Property
     *----------------------------------------*/

    /**
     * Properties
     *
     * @var array<int, \Sentinel\Nodes\PropertyNode>
     */
    protected array $properties = [];

    /**
     * {@inheritDoc}
     */
    public function properties(): array
    {
        return $this->properties;
    }

    /**
     * Parse property node
     *
     * @param string $line
     * @param int $lineNumber
     * @return void
     */
    protected function parsePropertyNode(string $line, int $lineNumber): void
    {
        if (!$this->inClass || !$this->isPropertyLine($line)) return;

        if (!preg_match("/\$([a-zA-Z_][a-zA-Z0-9_]*)/", $line, $matches)) return;

        $name       = $matches[1];
        $visibility = preg_match("/(public|private|protected)/", $line, $matches) ? $matches[1] : "public";
        $isStatic   = str_contains($line, "static");
        $isReadonly = str_contains($line, "readonly");

        $property = new PropertyNode($lineNumber, $name, $visibility, $isStatic, $isReadonly);

        $this->setClassNameToPropertyNode($property);
        $this->setTypeToPropertyNode($property, $line);
        $this->setPHPDocToPropertyNode($property, $lineNumber);

        $this->properties[] = $property;
    }

    /**
     * Check if line contains property declaration
     *
     * @param string $line
     * @return bool
     */
    protected function isPropertyLine(string $line): bool
    {
        if (!preg_match("/^\s*(public|private|protected|var|static|readonly)\s+/", $line)) return false;

        if (preg_match("/\s+function\s+/", $line)) return false;

        if (preg_match("/\s+const\s+/", $line)) return false;

        if (!preg_match("/\$/", $line)) return false;

        return true;
    }

    /**
     * Set class name to property node
     *
     * @param \Sentinel\Nodes\PropertyNode $property
     * @return void
     */
    protected function setClassNameToPropertyNode(PropertyNode $property): void
    {
        if ($this->currentClass === null) return;

        $property->setClassName($this->currentClass->name());
    }

    /**
     * Set type to property node
     *
     * @param \Sentinel\Nodes\PropertyNode $property
     * @param string $line
     * @return void
     */
    protected function setTypeToPropertyNode(PropertyNode $property, string $line): void
    {
        if (!preg_match("/(?:(?:public|private|protected|readonly)\s+){1,2}((?:\?)?[a-zA-Z0-9_\\\|&()]+)\s+\$/", $line, $matches)) return;

        $type = $matches[1];

        $property->setType($type);
    }

    /**
     * Set phpdoc to property node
     *
     * @param \Sentinel\Nodes\PropertyNode $property
     * @param int $lineNumber
     * @return void
     */
    protected function setPHPDocToPropertyNode(PropertyNode $property, int $lineNumber): void
    {
        $phpDoc = $this->extractPHPDocNode($lineNumber);

        if ($phpDoc === null) return;

        $property->setPHPDoc($phpDoc);
    }

    /*----------------------------------------*
     * Function
     *----------------------------------------*/

    /**
     * Functions
     *
     * @var array<int, \Sentinel\Nodes\FunctionNode>
     */
    protected array $functions = [];

    /**
     * Current function name
     *
     * @var string|null
     */
    protected string|null $currentFunctionName = null;

    /**
     * {@inheritDoc}
     */
    public function functions(): array
    {
        return $this->functions;
    }

    /**
     * Parse function node
     *
     * @param string $line
     * @param int $lineNumber
     * @return void
     */
    protected function parseFunctionNode(string $line, int $lineNumber): void
    {
        if (!preg_match("/^\s*(?:(public|private|protected)\s+)?(?:(static)\s+)?function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(([^)]*)/", $line, $matches)) return;

        $this->currentFunctionName = $matches[3];

        $name       = $matches[3];
        $visibility = $matches[1] ?? ($this->inClass ? "public" : null);
        $isStatic   = !empty($matches[2]);
        $params     = $this->parseFunctionParameters($matches[4]);

        $function = new FunctionNode($lineNumber, $name, $visibility, $isStatic, $params);

        $this->setClassNameToFunctionNode($function);
        $this->setReturnTypeToFunctionNode($function, $line);
        $this->setPHPDocToFunctionNode($function, $lineNumber);

        $this->functions[] = $function;
    }

    /**
     * Parse function parameters
     *
     * @param string $paramString
     * @return array<int, \Sentinel\Nodes\Leaves\Parameter>
     */
    protected function parseFunctionParameters(string $paramString): array
    {
        if (trim($paramString) === "") return [];

        $params     = $this->splitParameters($paramString);
        $parameters = [];

        $isConstructor = $this->currentFunctionName === "__construct";

        foreach ($params as $param) {
            $part = trim($param);

            if (!preg_match("/(?:(public|private|protected|readonly)\s+)?(?:(\?)?([a-zA-Z0-9_\\\|&()]+)\s+)?(?:(\.\.\.))?\s*(?:(&))?\s*\$([a-zA-Z_][a-zA-Z0-9_]*)(?:\s*=\s*(.*))?/", $part, $matches)) continue;

            $promotionModifier = $matches[1] ?? null;
            $isNullable        = !empty($matches[2]);
            $type              = $matches[3] ?? null;
            $isVariadic        = !empty($matches[4]);
            $isReference       = !empty($matches[5]);
            $name              = $matches[6];
            $defaultValue      = $matches[7] ?? null;

            $isPromoted = $promotionModifier !== null && $isConstructor && $this->currentClass !== null;

            $parameters[$name] = new Parameter($name, $isNullable, $type, $defaultValue, $isVariadic, $isReference, $isPromoted);
        }

        return $parameters;
    }

    /**
     * Split parameters handling nested generics
     *
     * @param string $paramString
     * @return array<string>
     */
    protected function splitParameters(string $paramString): array
    {
        $params     = [];
        $current    = "";
        $depth      = 0;
        $parenDepth = 0;

        for ($i = 0; $i < strlen($paramString); $i++) {
            $char = $paramString[$i];

            if ($char === "<") {
                $depth++;
            } elseif ($char === ">") {
                $depth--;
            } elseif ($char === "(") {
                $parenDepth++;
            } elseif ($char === ")") {
                $parenDepth--;
            } elseif ($char === "," && $depth === 0 && $parenDepth === 0) {
                $params[] = $current;
                $current = "";
                continue;
            }

            $current .= $char;
        }

        if ($current !== "") $params[] = $current;

        return $params;
    }

    /**
     * Set class name to function node
     *
     * @param \Sentinel\Nodes\FunctionNode $function
     * @return void
     */
    protected function setClassNameToFunctionNode(FunctionNode $function): void
    {
        if (!$this->inClass || $this->currentClass === null) return;

        $function->setClassName($this->currentClass->name());
    }

    /**
     * Set return type to function node
     *
     * @param \Sentinel\Nodes\FunctionNode $function
     * @param string $line
     * @return void
     */
    protected function setReturnTypeToFunctionNode(FunctionNode $function, string $line): void
    {
        if (!preg_match("/\)\s*:\s*((?:\?)?[a-zA-Z0-9_\\\|&()]+)/", $line, $matches)) return;

        $returnType = $matches[1];

        $function->setReturnType($returnType);
    }

    /**
     * Set phpdoc to function node
     *
     * @param \Sentinel\Nodes\FunctionNode $function
     * @param int $lineNumber
     * @return void
     */
    protected function setPHPDocToFunctionNode(FunctionNode $function, int $lineNumber): void
    {
        $phpDoc = $this->extractPHPDocNode($lineNumber);

        if ($phpDoc === null) return;

        $function->setPHPDoc($phpDoc);
    }
}
