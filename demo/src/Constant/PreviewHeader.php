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

namespace App\Constant;

/**
 * Response headers for endpoints that return HTML rendered from user-supplied MJML.
 *
 * That HTML can contain scripts (mj-raw, mj-text, ...), so it must never be interpreted
 * as a page on the application's origin.
 */
final class PreviewHeader
{
    public const string CONTENT_TYPE = 'Content-Type';
    public const string CONTENT_TYPE_OPTIONS = 'X-Content-Type-Options';
    public const string CONTENT_SECURITY_POLICY = 'Content-Security-Policy';

    /** Served as text so a browser navigating to the endpoint never renders it. */
    public const string PLAIN_TEXT = 'text/plain; charset=UTF-8';

    public const string NOSNIFF = 'nosniff';

    /** Opaque origin, no scripts, no plugins, no forms: even if rendered, the content is inert. */
    public const string SANDBOX_POLICY = "sandbox; default-src 'none'; img-src * data:; style-src 'unsafe-inline' *; font-src *";
}
