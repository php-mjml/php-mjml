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

namespace PhpMjml\Renderer;

use PhpMjml\Security\UrlValidator;

final readonly class RenderOptions
{
    public const DEFAULT_FONTS = [
        'Open Sans' => 'https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,700',
        'Droid Sans' => 'https://fonts.googleapis.com/css?family=Droid+Sans:300,400,500,700',
        'Lato' => 'https://fonts.googleapis.com/css?family=Lato:300,400,500,700',
        'Roboto' => 'https://fonts.googleapis.com/css?family=Roboto:300,400,500,700',
        'Ubuntu' => 'https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700',
    ];

    /** Maximum MJML input size in bytes (2 MiB). */
    public const DEFAULT_MAX_INPUT_LENGTH = 2 * 1024 * 1024;

    /** Maximum images per mj-carousel; carousel CSS grows with the square of this number. */
    public const DEFAULT_MAX_CAROUSEL_IMAGES = 50;

    /** Maximum number of rules in mj-style inline="inline"; each rule is matched against the whole document. */
    public const DEFAULT_MAX_INLINE_CSS_RULES = 1000;

    /**
     * @param array<string, string> $fonts             Font URLs indexed by font name
     * @param ValidationLevel       $validationLevel   How invalid attributes are handled
     * @param bool                  $keepComments      Keep XML comments in the output (as the JS keepComments option)
     * @param UrlValidator|null     $urlValidator      Validates URL attributes (href, src, background-url, ...);
     *                                                 values it rejects are removed. Null disables URL validation.
     * @param int|null              $maxInputLength    Maximum MJML size in bytes, null for no limit
     * @param int|null              $maxCarouselImages Maximum images per mj-carousel, null for no limit
     * @param int|null              $maxInlineCssRules Maximum rules in inline mj-style blocks, null for no limit
     */
    public function __construct(
        public array $fonts = self::DEFAULT_FONTS,
        public ValidationLevel $validationLevel = ValidationLevel::Soft,
        public bool $keepComments = true,
        public ?UrlValidator $urlValidator = new UrlValidator([], allowDataImages: true),
        public ?int $maxInputLength = self::DEFAULT_MAX_INPUT_LENGTH,
        public ?int $maxCarouselImages = self::DEFAULT_MAX_CAROUSEL_IMAGES,
        public ?int $maxInlineCssRules = self::DEFAULT_MAX_INLINE_CSS_RULES,
    ) {
    }

    /**
     * Options for rendering MJML written by untrusted users.
     *
     * Rejects templates with invalid attribute values, drops comments (which can carry
     * Outlook conditional markup) and only allows http, https, mailto and tel URLs.
     *
     * The output can still contain arbitrary HTML from mj-raw, mj-text and other ending
     * tags; see docs/SECURITY.md for how to display it safely.
     *
     * @param array<string, string> $fonts Font URLs indexed by font name
     */
    public static function forUntrustedInput(array $fonts = self::DEFAULT_FONTS): self
    {
        return new self(
            fonts: $fonts,
            validationLevel: ValidationLevel::Strict,
            keepComments: false,
            urlValidator: new UrlValidator(allowDataImages: true),
        );
    }
}
