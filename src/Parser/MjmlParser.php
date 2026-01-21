<?php

declare(strict_types=1);

namespace PhpMjml\Parser;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

final class MjmlParser
{
    public function parse(string $mjml): Node
    {
        $dom = new DOMDocument();

        // Suppress warnings for custom elements
        libxml_use_internal_errors(true);
        $dom->loadXML($mjml, LIBXML_NONET | LIBXML_NOBLANKS);
        libxml_clear_errors();

        $root = $dom->documentElement;
        if ($root === null) {
            throw new ParserException('Invalid MJML: no root element found');
        }

        return $this->parseNode($root);
    }

    private function parseNode(DOMNode $domNode): Node
    {
        $tagName = $domNode->nodeName;
        $attributes = [];
        $children = [];
        $content = '';

        if ($domNode instanceof DOMElement) {
            // Parse attributes
            foreach ($domNode->attributes as $attr) {
                $attributes[$attr->nodeName] = $attr->nodeValue;
            }

            // Parse children
            foreach ($domNode->childNodes as $child) {
                if ($child instanceof DOMElement) {
                    $children[] = $this->parseNode($child);
                } elseif ($child instanceof DOMText) {
                    $text = trim($child->textContent);
                    if ($text !== '') {
                        $content .= $text;
                    }
                }
            }
        }

        return new Node(
            tagName: $tagName,
            attributes: $attributes,
            children: $children,
            content: $content,
        );
    }
}
