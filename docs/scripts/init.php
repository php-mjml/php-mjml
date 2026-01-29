<?php

/*
 * This file is part of the PHP-MJML package.
 *
 * (c) David Gorges
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$docroot = '/persist/www';

if (!file_exists($docroot)) {
    mkdir($docroot, 0777, true);
}

$zip = new ZipArchive();
if (true === $zip->open('/persist/restore.zip', ZipArchive::RDONLY)) {
    $zip->extractTo($docroot);
    $zip->close();
    unlink('/persist/restore.zip');
    echo 'Installation complete!';
} else {
    echo 'Failed to open ZIP';
}
