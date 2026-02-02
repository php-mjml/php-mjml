<?php

/*
 * This file is part of the PHP-MJML package.
 *
 * (c) David Gorges
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * PHPStan stubs for PHP 8.4+ DOM classes.
 *
 * These stubs allow PHPStan to analyze code that uses the new Dom\* classes
 * when running on PHP 8.2 or 8.3 where these classes don't exist.
 */

namespace Dom;

class Node
{
    public readonly string $nodeName;
    public readonly ?string $textContent;
    /** @var NodeList<Node> */
    public readonly NodeList $childNodes;
}

class Element extends Node
{
    public readonly string $localName;
    public readonly string $innerHTML;
    /** @var NamedNodeMap<Attr> */
    public readonly NamedNodeMap $attributes;
}

class Text extends Node
{
}

class Attr extends Node
{
    public readonly string $nodeValue;
}

class XMLDocument
{
    public readonly ?Element $documentElement;

    public static function createFromString(string $source, int $options = 0): self
    {
    }
}

/**
 * @template TNode of Node
 *
 * @implements \IteratorAggregate<int, TNode>
 */
class NodeList implements \IteratorAggregate, \Countable
{
    public readonly int $length;

    /**
     * @return \Iterator<int, TNode>
     */
    public function getIterator(): \Iterator
    {
    }

    public function count(): int
    {
    }
}

/**
 * @template TNode of Node
 *
 * @implements \IteratorAggregate<string, TNode>
 */
class NamedNodeMap implements \IteratorAggregate, \Countable
{
    public readonly int $length;

    /**
     * @return \Iterator<string, TNode>
     */
    public function getIterator(): \Iterator
    {
    }

    public function count(): int
    {
    }
}
