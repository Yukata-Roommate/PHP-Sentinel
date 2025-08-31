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

        $this->lines = $lines;

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
     * Parse lines
     *
     * @return void
     */
    protected function parse(): void
    {
        foreach ($this->lines as $lineNum => $line) {
            $lineNumber = $lineNum + 1;

            $this->updateBraceLevel($line);
            $this->parseStrictTypes($line);
            $this->parseNamespace($line);
            $this->parseUseNode($line, $lineNumber);
            $this->parseClassNode($line, $lineNumber);
            $this->parsePropertyNode($line, $lineNumber);
            $this->parseFunctionNode($line, $lineNumber);
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
        $this->braceLevel += substr_count($line, "{") - substr_count($line, "}");
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

        for ($i = $lineNumber - 1; $i >= 1; $i--) {
            $line = $this->lines[$i - 1] ?? null;

            if ($line === null) continue;

            if (!$inDoc) {
                if (preg_match("/^\s*\*\/\s*$/", $line)) {
                    $inDoc   = true;
                    $endLine = $i;

                    array_unshift($phpDocLines, $line);
                } elseif (!preg_match("/^\s*$/", $line)) {
                    break;
                }
            } else {
                array_unshift($phpDocLines, $line);

                if (preg_match("/^\s*\/\*\*/", $line)) {
                    $startLine = $i;

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
        if (!preg_match("/^\s*(?:(abstract|final)\s+)?class\s+([a-zA-Z_][a-zA-Z0-9_]*)/", $line, $matches)) return;

        $this->inClass         = true;
        $this->classBraceLevel = $this->braceLevel - 1;

        $name     = $matches[2];
        $modifier = $matches[1] ?? null;

        $class = new ClassNode($name, $lineNumber, $modifier);

        $class->setNamespace($this->namespace);

        $this->currentClass = $class;

        $this->setExtendsToClassNode($class, $line);
        $this->addImplementsToClassNode($class, $line);
        $this->setEndToClassNode($class, $lineNumber);

        $this->classes[] = $class;
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
        if (!preg_match("/implements\s+(.+)/", $line, $matches)) return;

        $interfacesList = preg_replace("/\s*\{\s*$/", "", trim($matches[1]));
        $interfaces     = preg_split("/,\s*/", $interfacesList);

        foreach ($interfaces as $interface) {
            $interface = trim($interface);

            if (!preg_match("/^[a-zA-Z_\\\\][a-zA-Z0-9_\\\\]*$/", $interface)) continue;

            $class->addImplements($interface);
        }
    }

    /**
     * Set end to class node
     *
     * @param \Sentinel\Nodes\ClassNode $class
     * @param int $lineNumber
     * @return void
     */
    protected function setEndToClassNode(ClassNode $class, int $lineNumber): void
    {
        if ($this->braceLevel > $this->classBraceLevel) return;

        $class->setEnd($lineNumber);

        $this->currentClass = null;
        $this->inClass      = false;
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

        $property = new PropertyNode($lineNumber, $name, $visibility, $isStatic);

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
        if (!preg_match("/^\s*(public|private|protected)\s+/", $line)) return false;
        if (preg_match("/\s+function\s+/", $line)) return false;
        if (preg_match("/\s+const\s+/", $line)) return false;

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
        if (!preg_match("/(public|private|protected)\s+(?:static\s+)?(\??[a-zA-Z_\\\\][a-zA-Z0-9_\\\\|]*)\s+\$/", $line, $matches)) return;

        $type = $matches[2];

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
        if (!preg_match("/^\s*(?:(public|private|protected)\s+)?(?:(static)\s+)?function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(([^)]*)\)/", $line, $matches)) return;

        $name       = $matches[3];
        $visibility = $matches[1] ?? "public";
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

        $params = preg_split("/,(?![^<>]*>)/", $paramString);

        $parameters = [];

        foreach ($params as $param) {
            $part = trim($param);

            if (!preg_match("/(?:(\?)?([a-zA-Z_\\\\][a-zA-Z0-9_\\\\|]*)\s+)?(?:\.\.\.)?(?:&)?\$([a-zA-Z_][a-zA-Z0-9_]*)(?:\s*=\s*(.*))?/", $part, $matches)) continue;

            $name         = $matches[3];
            $isNullable   = !empty($matches[1]);
            $type         = $matches[2] ?? null;
            $defaultValue = $matches[4] ?? null;
            $isVariadic   = str_contains($part, "...");
            $isReference  = str_contains($part, "&");

            $parameters[$name] = new Parameter($name, $isNullable, $type, $defaultValue, $isVariadic, $isReference);
        }

        return $parameters;
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
        if (!preg_match("/\)\s*:\s*(\??[a-zA-Z_\\\\][a-zA-Z0-9_\\\\|]*)/", $line, $matches)) return;

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
