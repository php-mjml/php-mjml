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

namespace PhpMjml\Components\Body;

use PhpMjml\Component\Context\AccordionContextResolver;

/**
 * Shared attribute resolution for accordion components.
 *
 * Accordion settings are passed from mj-accordion → mj-accordion-element →
 * mj-accordion-title/text via the render context. Own attributes always win
 * over inherited settings.
 */
trait AccordionSettingsTrait
{
    /**
     * Get accordion settings from parent context.
     *
     * @return array<string, string|null>|null
     */
    private function getAccordionSettings(): ?array
    {
        return $this->context?->getComponentData(AccordionContextResolver::KEY);
    }

    /**
     * Get an icon attribute from own attributes or parent accordion context.
     */
    private function getIconAttribute(string $name): ?string
    {
        // First check own attributes
        $value = $this->getAttribute($name);
        if (null !== $value) {
            return $value;
        }

        // Fall back to parent accordion context
        $settingsKey = AccordionContextResolver::ATTRIBUTE_MAP[$name] ?? null;
        if (null === $settingsKey) {
            return null;
        }

        return $this->getAccordionSettings()[$settingsKey] ?? null;
    }

    /**
     * Resolve font-family from own attribute, element settings, or accordion settings.
     */
    private function resolveFontFamily(): ?string
    {
        // First check if explicitly set on this component
        $fontFamily = $this->getAttribute('font-family');
        if (null !== $fontFamily) {
            return $fontFamily;
        }

        $settings = $this->getAccordionSettings() ?? [];

        // Element font family (from AccordionElement) wins over the accordion's
        return $settings['elementFontFamily'] ?? $settings['fontFamily'] ?? null;
    }
}
