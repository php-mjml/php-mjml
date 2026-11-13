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

namespace PhpMjml\Tests\Unit\Security;

use PhpMjml\Component\ComponentInterface;
use PhpMjml\Preset\CorePreset;
use PhpMjml\Renderer\Mjml2Html;
use PhpMjml\Tests\Support\Html5Document;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * Renders every allowed attribute of every component with payloads that try to break out of
 * their output context (HTML attribute, <style> element, CSS rule, conditional comment) and
 * asserts that none of them succeeds.
 *
 * Outlook conditional comments are invisible to browsers but rendered by Outlook, so their
 * bodies are parsed as HTML and checked as well.
 */
final class AttributeInjectionTest extends TestCase
{
    private const INJECTED_ATTRIBUTE = 'xpwn';
    private const INJECTED_ELEMENT = 'pwnel';
    private const INJECTED_CSS_RULE = '.pwncss{';
    private const CONDITIONAL_COMMENT_PATTERN = '/^\[if [^\]]*\]>(.*)<!\[endif\]$/s';

    private const PAYLOADS = [
        'double quote' => 'x" xpwn="1',
        'single quote' => "x' xpwn='1",
        'tag' => 'x><pwnel></pwnel>',
        'style end tag' => 'x</style><pwnel></pwnel>',
        'comment end' => 'x--><pwnel></pwnel><!--',
        'css rule' => 'x;}.pwncss{a:b}',
        'css url' => 'x);}.pwncss{a:b}',
        'css quoted url' => "x');}.pwncss{a:b}",
        'media query' => 'x) {} .pwncss{a:b} @media (x',
    ];

    /**
     * Templates placing the target element ({T} receives the injected attribute) in a valid structure.
     */
    private const COMPONENT_TEMPLATES = [
        'mj-body' => '<mjml><mj-body {T}><mj-section><mj-column><mj-text>hi</mj-text></mj-column></mj-section></mj-body></mjml>',
        'mj-section' => '<mjml><mj-body><mj-section {T}><mj-column><mj-text>hi</mj-text></mj-column></mj-section></mj-body></mjml>',
        'mj-wrapper' => '<mjml><mj-body><mj-wrapper {T}><mj-section><mj-column><mj-text>hi</mj-text></mj-column></mj-section></mj-wrapper></mj-body></mjml>',
        'mj-hero' => '<mjml><mj-body><mj-hero {T}><mj-text>hi</mj-text></mj-hero></mj-body></mjml>',
        'mj-group' => '<mjml><mj-body><mj-section><mj-group {T}><mj-column><mj-text>hi</mj-text></mj-column></mj-group></mj-section></mj-body></mjml>',
        'mj-column' => '<mjml><mj-body><mj-section><mj-column {T}><mj-text>hi</mj-text></mj-column></mj-section></mj-body></mjml>',
        'mj-text' => '<mj-text {T}>hi</mj-text>',
        'mj-button' => '<mj-button {T}>hi</mj-button>',
        'mj-image' => '<mj-image {T} />',
        'mj-divider' => '<mj-divider {T} />',
        'mj-spacer' => '<mj-spacer {T} />',
        'mj-table' => '<mj-table {T}><tr><td>a</td></tr></mj-table>',
        'mj-raw' => '<mj-raw {T}><p>hi</p></mj-raw>',
        'mj-social' => '<mj-social {T}><mj-social-element name="facebook" href="https://x">f</mj-social-element></mj-social>',
        'mj-social-element' => '<mj-social><mj-social-element name="facebook" {T}>f</mj-social-element></mj-social>',
        'mj-navbar' => '<mj-navbar {T}><mj-navbar-link href="/a">a</mj-navbar-link></mj-navbar>',
        'mj-navbar-link' => '<mj-navbar hamburger="hamburger"><mj-navbar-link {T}>a</mj-navbar-link></mj-navbar>',
        'mj-accordion' => '<mj-accordion {T}><mj-accordion-element><mj-accordion-title>t</mj-accordion-title><mj-accordion-text>x</mj-accordion-text></mj-accordion-element></mj-accordion>',
        'mj-accordion-element' => '<mj-accordion><mj-accordion-element {T}><mj-accordion-title>t</mj-accordion-title><mj-accordion-text>x</mj-accordion-text></mj-accordion-element></mj-accordion>',
        'mj-accordion-title' => '<mj-accordion><mj-accordion-element><mj-accordion-title {T}>t</mj-accordion-title><mj-accordion-text>x</mj-accordion-text></mj-accordion-element></mj-accordion>',
        'mj-accordion-text' => '<mj-accordion><mj-accordion-element><mj-accordion-title>t</mj-accordion-title><mj-accordion-text {T}>x</mj-accordion-text></mj-accordion-element></mj-accordion>',
        'mj-carousel' => '<mj-carousel {T}><mj-carousel-image src="https://a/1.png" /><mj-carousel-image src="https://a/2.png" /></mj-carousel>',
        'mj-carousel-image' => '<mj-carousel><mj-carousel-image {T} /><mj-carousel-image src="https://a/2.png" /></mj-carousel>',
    ];

    /**
     * Head and root vectors; {P} receives the payload.
     */
    private const SPECIAL_TEMPLATES = [
        'mjml lang' => '<mjml lang="{P}"><mj-body><mj-section><mj-column><mj-text>hi</mj-text></mj-column></mj-section></mj-body></mjml>',
        'mjml dir' => '<mjml dir="{P}"><mj-body><mj-section><mj-column><mj-text>hi</mj-text></mj-column></mj-section></mj-body></mjml>',
        'mj-title' => '<mjml><mj-head><mj-title>{P}</mj-title></mj-head><mj-body></mj-body></mjml>',
        'mj-preview' => '<mjml><mj-head><mj-preview>{P}</mj-preview></mj-head><mj-body></mj-body></mjml>',
        'mj-breakpoint width' => '<mjml><mj-head><mj-breakpoint width="{P}" /></mj-head><mj-body><mj-section><mj-column><mj-image src="https://a/b.png" /></mj-column></mj-section></mj-body></mjml>',
        'mj-font href' => '<mjml><mj-head><mj-font name="Evil" href="{P}" /></mj-head><mj-body><mj-section><mj-column><mj-text font-family="Evil">hi</mj-text></mj-column></mj-section></mj-body></mjml>',
        'mj-font name' => '<mjml><mj-head><mj-font name="{P}" href="https://f/x.css" /></mj-head><mj-body><mj-section><mj-column><mj-text font-family="{P}">hi</mj-text></mj-column></mj-section></mj-body></mjml>',
        'mj-all font-family' => '<mjml><mj-head><mj-attributes><mj-all font-family="{P}" /></mj-attributes></mj-head><mj-body><mj-section><mj-column><mj-text>hi</mj-text></mj-column></mj-section></mj-body></mjml>',
        'mj-class name' => '<mjml><mj-head><mj-attributes><mj-class name="{P}" color="red" /></mj-attributes></mj-head><mj-body><mj-section><mj-column><mj-text mj-class="{P}">hi</mj-text></mj-column></mj-section></mj-body></mjml>',
        'mj-html-attribute value' => '<mjml><mj-head><mj-html-attributes><mj-selector path=".c"><mj-html-attribute name="data-x">{P}</mj-html-attribute></mj-selector></mj-html-attributes></mj-head><mj-body><mj-section><mj-column><mj-text css-class="c">hi</mj-text></mj-column></mj-section></mj-body></mjml>',
    ];

    private const COLUMN_WRAPPER = '<mjml><mj-body><mj-section><mj-column>%s</mj-column></mj-section></mj-body></mjml>';

    private Mjml2Html $renderer;

    protected function setUp(): void
    {
        $this->renderer = Mjml2Html::create();
    }

    #[DataProvider('componentAttributeProvider')]
    public function testComponentAttributeCannotEscapeItsContext(string $mjml): void
    {
        try {
            $html = $this->renderer->render($mjml)->html;
        } catch (InvalidOptionsException) {
            // Some enum attributes are validated by context resolvers, which reject the payload outright
            $this->addToAssertionCount(1);

            return;
        }

        $this->assertSame([], $this->findInjections($html));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function componentAttributeProvider(): iterable
    {
        $components = [];
        foreach (CorePreset::getComponents() as $componentClass) {
            $components[$componentClass::getComponentName()] = $componentClass;
        }

        foreach (self::COMPONENT_TEMPLATES as $tagName => $template) {
            if (!str_starts_with($template, '<mjml>')) {
                $template = \sprintf(self::COLUMN_WRAPPER, $template);
            }

            foreach (self::getTestedAttributes($components[$tagName]) as $attribute) {
                foreach (self::PAYLOADS as $payloadName => $payload) {
                    yield "{$tagName} {$attribute} ({$payloadName})" => [
                        str_replace('{T}', $attribute.'="'.self::xmlEscape($payload).'"', $template),
                    ];
                }
            }
        }
    }

    #[DataProvider('specialVectorProvider')]
    public function testHeadAndRootValuesCannotEscapeTheirContext(string $mjml): void
    {
        $this->assertSame([], $this->findInjections($this->renderer->render($mjml)->html));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function specialVectorProvider(): iterable
    {
        foreach (self::SPECIAL_TEMPLATES as $vector => $template) {
            foreach (self::PAYLOADS as $payloadName => $payload) {
                yield "{$vector} ({$payloadName})" => [str_replace('{P}', self::xmlEscape($payload), $template)];
            }
        }
    }

    public function testMjStyleContentCannotCloseTheStyleElement(): void
    {
        $html = $this->renderer->render(
            '<mjml><mj-head><mj-style>.a{color:red}</StYlE ><pwnel></pwnel></mj-style></mj-head><mj-body></mj-body></mjml>'
        )->html;

        $this->assertSame([], $this->findInjections($html));
    }

    /**
     * @param class-string<ComponentInterface> $componentClass
     *
     * @return list<string>
     */
    private static function getTestedAttributes(string $componentClass): array
    {
        $attributes = array_keys($componentClass::getAllowedAttributes());
        $attributes[] = 'css-class';

        return array_values(array_unique($attributes));
    }

    private static function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, \ENT_QUOTES | \ENT_XML1, 'UTF-8');
    }

    /**
     * @return list<string>
     */
    private function findInjections(string $html): array
    {
        $document = Html5Document::parse($html);
        $injections = $this->findInjectionsIn($document, 'html');

        foreach ($document->getComments() as $comment) {
            if (1 === preg_match(self::CONDITIONAL_COMMENT_PATTERN, $comment, $matches)) {
                $injections = [...$injections, ...$this->findInjectionsIn(Html5Document::parse($matches[1]), 'conditional comment')];
            }
        }

        return array_values(array_unique($injections));
    }

    /**
     * @return list<string>
     */
    private function findInjectionsIn(Html5Document $document, string $where): array
    {
        $injections = [];

        if ($document->hasElementWithAttribute(self::INJECTED_ATTRIBUTE)) {
            $injections[] = "{$where}: attribute injection";
        }
        if ($document->hasElement(self::INJECTED_ELEMENT)) {
            $injections[] = "{$where}: element injection";
        }
        foreach ($document->getStyleContents() as $style) {
            if (str_contains($style, self::INJECTED_CSS_RULE)) {
                $injections[] = "{$where}: CSS rule injection";
            }
        }

        return $injections;
    }
}
