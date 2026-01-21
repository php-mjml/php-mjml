<?php

declare(strict_types=1);

namespace PhpMjml\Renderer;

use PhpMjml\Component\BodyComponent;
use PhpMjml\Component\Registry;
use PhpMjml\Parser\MjmlParser;
use PhpMjml\Parser\Node;

final class Mjml2Html
{
    public function __construct(
        private readonly Registry $registry,
        private readonly MjmlParser $parser,
    ) {}

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
        if ($head === null) {
            return;
        }

        foreach ($head->children as $child) {
            $componentClass = $this->registry->get($child->tagName);
            if ($componentClass === null) {
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
        if ($body === null) {
            return '';
        }

        $componentClass = $this->registry->get($body->tagName);
        if ($componentClass === null) {
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
     * @param Node[] $nodes
     * @param RenderContext $parentChildContext The child context from parent component
     * @param RenderContext $rootContext The root context (for media query registration)
     */
    private function buildChildrenWithContext(array $nodes, RenderContext $parentChildContext, RenderContext $rootContext): array
    {
        $children = [];

        // Count non-raw siblings for column width calculation
        $nonRawSiblings = 0;
        foreach ($nodes as $node) {
            $componentClass = $this->registry->get($node->tagName);
            if ($componentClass !== null && !$componentClass::isRawElement()) {
                $nonRawSiblings++;
            }
        }

        $index = 0;
        foreach ($nodes as $node) {
            $componentClass = $this->registry->get($node->tagName);
            if ($componentClass === null) {
                continue;
            }

            $props = [
                'first' => $index === 0,
                'index' => $index,
                'last' => $index + 1 === count($nodes),
                'sibling' => count($nodes),
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
            $index++;
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

        if (!method_exists($component, 'getChildContext')) {
            return $baseContext;
        }

        $childContextArray = $component->getChildContext();

        // Handle containerWidth specially - parse it if it's a string with unit
        if (isset($childContextArray['containerWidth'])) {
            $width = $childContextArray['containerWidth'];
            if (is_string($width)) {
                $childContextArray['containerWidth'] = (int) $width;
            }
        }

        return RenderContext::fromArray($childContextArray, $baseContext);
    }

    private function buildSkeleton(string $bodyHtml, RenderContext $context): string
    {
        $title = htmlspecialchars($context->title, ENT_QUOTES, 'UTF-8');
        $preview = $context->preview !== '' ? $this->buildPreview($context->preview) : '';
        $fonts = $this->buildFonts($context->fonts);
        $styles = $this->buildStyles($context);
        $bodyStyle = $this->buildBodyStyle($context);

        // Build html tag attributes
        $htmlAttrs = 'xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office"';
        if ($context->lang !== null) {
            $htmlAttrs .= sprintf(' lang="%s"', htmlspecialchars($context->lang, ENT_QUOTES, 'UTF-8'));
        }
        if ($context->dir !== null) {
            $htmlAttrs .= sprintf(' dir="%s"', htmlspecialchars($context->dir, ENT_QUOTES, 'UTF-8'));
        }

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
{$fonts}{$styles}</head>
<body{$bodyStyle}>
{$preview}{$bodyHtml}
</body>
</html>
HTML;
    }

    private function buildBodyStyle(RenderContext $context): string
    {
        $styles = ['word-spacing:normal'];

        if ($context->backgroundColor !== null && $context->backgroundColor !== '') {
            $styles[] = "background-color:{$context->backgroundColor}";
        }

        return ' style="' . implode(';', $styles) . ';"';
    }

    private function buildPreview(string $preview): string
    {
        $escaped = htmlspecialchars($preview, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<div style="display:none;font-size:1px;color:#ffffff;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;">
{$escaped}
</div>
HTML;
    }

    private function buildFonts(array $fonts): string
    {
        if ($fonts === []) {
            return '';
        }

        $links = '';
        foreach ($fonts as $font) {
            $links .= sprintf('<link href="%s" rel="stylesheet" type="text/css">' . "\n", htmlspecialchars($font, ENT_QUOTES, 'UTF-8'));
        }

        return <<<HTML
<!--[if !mso]><!-->
{$links}<!--<![endif]-->
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
<!--[if lte mso 11]>
<style type="text/css">
.mj-outlook-group-fix { width:100% !important; }
</style>
<![endif]-->
CSS;

        if ($context->styles !== []) {
            $customStyles = implode("\n", $context->styles);
            $css .= "\n<style type=\"text/css\">\n{$customStyles}\n</style>";
        }

        // Add media queries for responsive columns
        $css .= $this->buildMediaQueries($context);

        return $css;
    }

    private function buildMediaQueries(RenderContext $context): string
    {
        if ($context->mediaQueries === []) {
            return '';
        }

        $breakpoint = $context->breakpoint;
        $queries = [];
        $thunderbirdQueries = [];

        foreach ($context->mediaQueries as $className => $cssValue) {
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
