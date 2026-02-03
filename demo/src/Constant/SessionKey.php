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

final class SessionKey
{
    public const string VERIFIED_EMAILS = 'email_verified_list';
    public const string PENDING_VERIFICATION = 'email_pending_verification';
}
