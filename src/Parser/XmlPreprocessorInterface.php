<?php

declare(strict_types=1);

/*
 * This file is part of the PHP-MJML package.
 *
 * (c) David Gorges
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMjml\Parser;

/**
 * Interface for XML preprocessing before parsing.
 *
 * Implementations can transform raw MJML/XML strings to make them
 * compatible with PHP's XML parser (e.g., converting HTML entities,
 * escaping special characters).
 */
interface XmlPreprocessorInterface
{
    /**
     * Preprocess XML/MJML content before parsing.
     *
     * @param string $xml the raw XML/MJML content
     *
     * @return string the preprocessed content ready for XML parsing
     */
    public function preprocess(string $xml): string;
}
