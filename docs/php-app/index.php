<?php

/*
 * This file is part of the PHP-MJML package.
 *
 * (c) David Gorges
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/vendor/autoload.php';

use PhpMjml\Component\Registry;
use PhpMjml\Parser\MjmlParser;
use PhpMjml\Preset\CorePreset;
use PhpMjml\Renderer\Mjml2Html;

header('Content-Type: text/html; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ('OPTIONS' === $_SERVER['REQUEST_METHOD']) {
    http_response_code(204);
    exit;
}

$mjml = file_get_contents('php://input');
if (empty($mjml)) {
    $mjml = getDefaultMjml();
}

try {
    $registry = new Registry();
    CorePreset::register($registry);

    $parser = new MjmlParser($registry);
    $renderer = new Mjml2Html($registry);

    $node = $parser->parse($mjml);
    echo $renderer->render($node);
} catch (Throwable $e) {
    http_response_code(500);
    echo '<pre>Error: '.htmlspecialchars($e->getMessage()).'</pre>';
}

function getDefaultMjml(): string
{
    return <<<'MJML'
<mjml>
  <mj-head>
    <mj-title>PHP-MJML Demo</mj-title>
    <mj-attributes>
      <mj-all font-family="Helvetica, Arial, sans-serif" />
      <mj-text font-size="16px" color="#555555" line-height="1.5" />
    </mj-attributes>
  </mj-head>
  <mj-body background-color="#f4f4f4">
    <mj-section background-color="#ffffff" padding="40px 20px">
      <mj-column>
        <mj-text font-size="28px" color="#333333" font-weight="bold" align="center">
          Hello from PHP-MJML!
        </mj-text>
        <mj-divider border-color="#1a73e8" border-width="2px" padding="20px 100px" />
        <mj-text>
          This demo runs entirely in your browser using PHP-WASM.
          Edit the MJML source on the left and click "Render" to see the output.
        </mj-text>
        <mj-button background-color="#1a73e8" href="https://github.com/php-mjml/php-mjml">
          View on GitHub
        </mj-button>
      </mj-column>
    </mj-section>
    <mj-section padding="20px">
      <mj-column>
        <mj-text font-size="12px" color="#888888" align="center">
          PHP-MJML - Native PHP port of the MJML email framework
        </mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML;
}
