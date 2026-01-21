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

use PhpMjml\Component\BodyComponent;
use PhpMjml\Component\ComponentInterface;
use PhpMjml\Component\Registry;
use PhpMjml\Helper\OutlookConditionalHelper;
use PhpMjml\Parser\MjmlParser;
use PhpMjml\Parser\Node;

final class Mjml2Html
{
    public function __construct(
        private readonly Registry $registry,
        private readonly MjmlParser $parser,
    ) {
    }

    public function render(string $mjml, ?RenderOptions $options = null): RenderResult
    {
        $options ??= new RenderOptions();

        $ast = $this->parser->parse($mjml);

        $context = new RenderContext(
            registry: $this->registry,
            options: $options,
        );

        // Process head components first
        $this->processHead($ast, $context);

        // Render body
        $bodyHtml = $this->processBody($ast, $context);

        // Merge adjacent MSO conditionals
        $bodyHtml = OutlookConditionalHelper::mergeConditionals($bodyHtml);

        // Build final HTML
        $html = $this->buildSkeleton($bodyHtml, $context);

        return new RenderResult(
            html: $html,
            errors: [],
        );
    }

    private function processHead(Node $ast, RenderContext $context): void
    {
        $head = $ast->findChild('mj-head');
        if (null === $head) {
            return;
        }

        foreach ($head->children as $child) {
            $componentClass = $this->registry->get($child->tagName);
            if (null === $componentClass) {
                continue;
            }

            $component = new $componentClass(
                attributes: $child->attributes,
                children: [],
                content: $child->content,
                context: $context,
            );

            if (method_exists($component, 'handle')) {
                $component->handle($context);
            }
        }
    }

    private function processBody(Node $ast, RenderContext $context): string
    {
        $body = $ast->findChild('mj-body');
        if (null === $body) {
            return '';
        }

        $componentClass = $this->registry->get($body->tagName);
        if (null === $componentClass) {
            return '';
        }

        // Create the body component
        $bodyComponent = new $componentClass(
            attributes: $body->attributes,
            children: [],
            content: $body->content,
            context: $context,
        );

        // Get the child context from body component (sets containerWidth)
        $childContext = $this->getChildContext($bodyComponent, $context);

        // Build children with the proper context
        $children = $this->buildChildrenWithContext($body->children, $childContext, $context);

        // Recreate body component with built children
        $bodyComponent = new $componentClass(
            attributes: $body->attributes,
            children: $children,
            content: $body->content,
            context: $context,
        );

        return $bodyComponent->render();
    }

    /**
     * Build children with proper context propagation.
     *
     * @param Node[]        $nodes              Child nodes to build
     * @param RenderContext $parentChildContext The child context from parent component
     * @param RenderContext $rootContext        The root context (for media query registration)
     *
     * @return list<ComponentInterface>
     */
    private function buildChildrenWithContext(array $nodes, RenderContext $parentChildContext, RenderContext $rootContext): array
    {
        $children = [];

        // Count non-raw siblings for column width calculation
        $nonRawSiblings = 0;
        foreach ($nodes as $node) {
            $componentClass = $this->registry->get($node->tagName);
            if (null !== $componentClass && is_subclass_of($componentClass, BodyComponent::class) && !$componentClass::isRawElement()) {
                ++$nonRawSiblings;
            }
        }

        $index = 0;
        foreach ($nodes as $node) {
            $componentClass = $this->registry->get($node->tagName);
            if (null === $componentClass) {
                continue;
            }

            $props = [
                'first' => 0 === $index,
                'index' => $index,
                'last' => $index + 1 === \count($nodes),
                'sibling' => \count($nodes),
                'nonRawSiblings' => $nonRawSiblings,
            ];

            // Create temporary component to get its child context
            $tempComponent = new $componentClass(
                attributes: $node->attributes,
                children: [],
                content: $node->content,
                context: $parentChildContext,
                props: $props,
            );

            // Get child context for this component's children
            $childContext = $this->getChildContext($tempComponent, $parentChildContext);

            // Build grandchildren with the child context
            $grandchildren = $this->buildChildrenWithContext($node->children, $childContext, $rootContext);

            // Create the actual component with all children
            $component = new $componentClass(
                attributes: $node->attributes,
                children: $grandchildren,
                content: $node->content,
                context: $parentChildContext,
                props: $props,
            );

            $children[] = $component;
            ++$index;
        }

        return $children;
    }

    /**
     * Get child context from a component.
     */
    private function getChildContext(object $component, RenderContext $baseContext): RenderContext
    {
        if (!($component instanceof BodyComponent)) {
            return $baseContext;
        }

        $childContextArray = $component->getChildContext();

        // Handle containerWidth specially - parse it if it's a string with unit
        if (isset($childContextArray['containerWidth'])) {
            $width = $childContextArray['containerWidth'];
            if (\is_string($width)) {
                $childContextArray['containerWidth'] = (int) $width;
            }
        }

        return RenderContext::fromArray($childContextArray, $baseContext);
    }

    private function buildSkeleton(string $bodyHtml, RenderContext $context): string
    {
        $title = htmlspecialchars($context->title, \ENT_QUOTES, 'UTF-8');
        $preview = '' !== $context->preview ? $this->buildPreview($context->preview) : '';
        $styles = $this->buildStyles($context);
        $fonts = $this->buildFonts($bodyHtml, $context);
        $mediaQueries = $this->buildMediaQueriesStyles($context);
        $componentHeadStyles = $this->buildComponentHeadStyles($context);
        $bodyStyle = $this->buildBodyStyle($context);

        // Build html tag attributes - lang and dir always included with defaults
        $lang = htmlspecialchars($context->lang, \ENT_QUOTES, 'UTF-8');
        $dir = htmlspecialchars($context->dir, \ENT_QUOTES, 'UTF-8');
        $htmlAttrs = \sprintf(
            'lang="%s" dir="%s" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office"',
            $lang,
            $dir
        );

        return <<<HTML
<!doctype html>
<html {$htmlAttrs}>
<head>
<title>{$title}</title>
<!--[if !mso]><!-->
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<!--<![endif]-->
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
{$styles}{$fonts}{$mediaQueries}{$componentHeadStyles}</head>
<body{$bodyStyle}>
{$preview}{$bodyHtml}
</body>
</html>
HTML;
    }

    private function buildBodyStyle(RenderContext $context): string
    {
        $styles = ['word-spacing:normal'];

        if (null !== $context->backgroundColor && '' !== $context->backgroundColor) {
            $styles[] = "background-color:{$context->backgroundColor}";
        }

        return ' style="'.implode(';', $styles).';"';
    }

    private function buildPreview(string $preview): string
    {
        $escaped = htmlspecialchars($preview, \ENT_QUOTES, 'UTF-8');

        return '<div style="display:none;font-size:1px;color:#ffffff;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;">'.$escaped.'</div>';
    }

    /**
     * Build font tags by detecting which fonts are used in the content.
     *
     * @param string $content The rendered body HTML content
     */
    private function buildFonts(string $content, RenderContext $context): string
    {
        $fonts = $context->options->fonts;

        if ([] === $fonts) {
            return '';
        }

        $toImport = [];

        foreach ($fonts as $fontName => $fontUrl) {
            // Check if font is used in font-family declarations
            $pattern = '/font-family:[^;}]*'.preg_quote($fontName, '/').'/i';
            if (preg_match($pattern, $content)) {
                $toImport[] = $fontUrl;
            }
        }

        if ([] === $toImport) {
            return '';
        }

        $links = '';
        $imports = '';
        foreach ($toImport as $url) {
            $escapedUrl = htmlspecialchars($url, \ENT_QUOTES, 'UTF-8');
            $links .= \sprintf('<link href="%s" rel="stylesheet" type="text/css">', $escapedUrl);
            $imports .= \sprintf('@import url(%s);', $url);
        }

        return <<<HTML
<!--[if !mso]><!-->
{$links}
<style type="text/css">
{$imports}
</style>
<!--<![endif]-->
HTML;
    }

    private function buildStyles(RenderContext $context): string
    {
        $css = <<<CSS
<style type="text/css">
#outlook a { padding:0; }
body { margin:0;padding:0;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%; }
table, td { border-collapse:collapse;mso-table-lspace:0pt;mso-table-rspace:0pt; }
img { border:0;height:auto;line-height:100%; outline:none;text-decoration:none;-ms-interpolation-mode:bicubic; }
p { display:block;margin:13px 0; }
</style>
<!--[if mso]>
<noscript>
<xml>
<o:OfficeDocumentSettings>
<o:AllowPNG/>
<o:PixelsPerInch>96</o:PixelsPerInch>
</o:OfficeDocumentSettings>
</xml>
</noscript>
<![endif]-->
<!--[if lte mso 11]>
<style type="text/css">
.mj-outlook-group-fix { width:100% !important; }
</style>
<![endif]-->
CSS;

        $styles = $context->getStyles();
        if ([] !== $styles) {
            $customStyles = implode("\n", $styles);
            $css .= "\n<style type=\"text/css\">\n{$customStyles}\n</style>";
        }

        return $css;
    }

    private function buildMediaQueriesStyles(RenderContext $context): string
    {
        return $this->buildMediaQueries($context);
    }

    private function buildComponentHeadStyles(RenderContext $context): string
    {
        $styles = $context->getComponentHeadStyles();

        if ([] === $styles) {
            return '';
        }

        $content = implode("\n", $styles);

        return "<style type=\"text/css\">\n{$content}\n</style>";
    }

    private function buildMediaQueries(RenderContext $context): string
    {
        $mediaQueries = $context->getMediaQueries();

        if ([] === $mediaQueries) {
            return '';
        }

        $breakpoint = $context->breakpoint;
        $queries = [];
        $thunderbirdQueries = [];

        foreach ($mediaQueries as $className => $cssValue) {
            $queries[] = ".{$className} {$cssValue}";
            $thunderbirdQueries[] = ".moz-text-html .{$className} {$cssValue}";
        }

        $baseQueries = implode("\n", $queries);
        $mozQueries = implode("\n", $thunderbirdQueries);

        return <<<CSS

<style type="text/css">
@media only screen and (min-width:{$breakpoint}) {
{$baseQueries}
}
</style>
<style media="screen and (min-width:{$breakpoint})">
{$mozQueries}
</style>
CSS;
    }
}
