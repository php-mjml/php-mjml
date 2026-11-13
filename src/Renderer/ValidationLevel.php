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

/**
 * How attribute validation failures are handled, mirroring the JS validationLevel option.
 */
enum ValidationLevel: string
{
    /** Report violations in RenderResult::$errors and render anyway (JS default). */
    case Soft = 'soft';

    /** Throw a ValidationException before rendering when any violation is found. */
    case Strict = 'strict';

    /** Do not validate attributes. */
    case Skip = 'skip';
}
