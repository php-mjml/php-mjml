---
name: implement-mjml-component
description: Implement an MJML component from the JavaScript reference implementation. Port a component from JavaScript to PHP following project conventions.
argument-hint: "<component-name>"
---

# Implement MJML Component

Port an MJML component from the JavaScript reference implementation to PHP.

## Arguments

- `component-name`: The MJML component to implement (e.g., `mj-image`, `mj-button`, `mj-divider`)

## Instructions

When this skill is invoked, follow this structured workflow to port an MJML component from JavaScript to PHP.

### Phase 1: Analysis

1. **Read the JavaScript reference implementation**
   - Path: `reference/packages/mjml-<component>/src/index.js`
   - Extract: component name, allowed attributes, default attributes, styles, render logic

2. **Identify component type**
   - Body component: extends `BodyComponent` → place in `src/Components/Body/`
   - Head component: extends `HeadComponent` → place in `src/Components/Head/`

3. **Create implementation plan** using TodoWrite with these items:
   - Analyze JS reference implementation
   - Create PHP component class
   - Register in CorePreset
   - Create unit tests
   - Create parity test fixtures
   - Run verification (`composer run ca`)

### Phase 2: Implementation

#### 2.1 Create the PHP Component

**File location:** `src/Components/Body/<ComponentName>.php` or `src/Components/Head/<ComponentName>.php`

**Required structure:**
```php
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

use PhpMjml\Component\BodyComponent;

final class <ComponentName> extends BodyComponent
{
    protected static bool $endingTag = true; // true if component has no children (self-closing)

    public static function getComponentName(): string
    {
        return 'mj-<component>';
    }

    /**
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array
    {
        // Map from JS allowedAttributes
        return [
            'attribute-name' => 'type(params)',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getDefaultAttributes(): array
    {
        // Map from JS defaultAttributes
        return [
            'attribute-name' => 'default-value',
        ];
    }

    /**
     * @return array<string, array<string, string|null>>
     */
    public function getStyles(): array
    {
        // Map from JS getStyles()
        return [
            'style-name' => [
                'css-property' => $this->getAttribute('attribute'),
            ],
        ];
    }

    public function render(): string
    {
        // Port the JS render() method
    }
}
```

**Attribute type mappings (JS → PHP):**
- `'string'` → `'string'`
- `'enum(a,b,c)'` → `'enum(a,b,c)'`
- `'color'` → `'color'`
- `'unit(px)'` → `'unit(px)'`
- `'unit(px,%)'` → `'unit(px,%)'`
- `'unit(px,%){1,4}'` → `'unit(px,%){1,4}'`
- `'boolean'` → `'boolean'`
- `'integer'` → `'integer'`

**Key helper methods available in BodyComponent:**
- `$this->getAttribute('name')` - Get attribute value (with defaults applied)
- `$this->htmlAttributes(['attr' => 'value', 'style' => 'styleName'])` - Build HTML attributes string
- `$this->getBoxWidths()` - Returns `['box' => int, 'borders' => int, 'paddings' => int]`
- `$this->getContent()` - Get inner content/children HTML
- `$this->context` - Access render context (container width, styles, etc.)

#### 2.2 Register in CorePreset

**File:** `src/Preset/CorePreset.php`

Add the import and register the component:
```php
use PhpMjml\Components\Body\<ComponentName>;

// In getComponents() array:
<ComponentName>::class,
```

#### 2.3 Create Unit Tests

**File:** `tests/Unit/Components/Body/<ComponentName>Test.php`

**Required test cases:**
```php
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

namespace PhpMjml\Tests\Unit\Components\Body;

use PhpMjml\Component\Registry;
use PhpMjml\Components\Body\<ComponentName>;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class <ComponentName>Test extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-<component>', <ComponentName>::getComponentName());
    }

    public function testIsEndingTag(): void
    {
        $this->assertTrue(<ComponentName>::isEndingTag()); // or assertFalse
    }

    public function testDefaultAttributes(): void
    {
        $defaults = <ComponentName>::getDefaultAttributes();
        // Assert each default value
    }

    public function testRenderBasic(): void
    {
        $component = new <ComponentName>(
            attributes: [],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $component->render();
        // Assert expected HTML structure
    }

    // Add more test cases for specific features...

    private function createContext(): RenderContext
    {
        return new RenderContext(
            registry: new Registry(),
            options: new RenderOptions(),
            containerWidth: 600,
        );
    }
}
```

#### 2.4 Create Parity Test Fixtures

**Location:** `tests/Parity/fixtures/<component>-*.mjml`

Create fixtures for different scenarios:
- `<component>-basic.mjml` - Minimal usage
- `<component>-styled.mjml` - With styling attributes
- `<component>-<feature>.mjml` - Specific features (e.g., with-link, fluid-mobile)

**Fixture format:**
```xml
<mjml>
  <mj-body>
    <mj-section>
      <mj-column>
        <mj-<component> attribute="value">
          Content if applicable
        </mj-<component>>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
```

#### 2.5 Add Parity Tests (if needed)

If creating new fixture files, add corresponding test methods in `tests/Parity/CoreComponentsParityTest.php`:
```php
public function test<ComponentName>Basic(): void
{
    $mjml = $this->loadFixture('<component>-basic');
    $this->assertHtmlEquals(
        $this->renderWithJs($mjml),
        $this->renderWithPhp($mjml)
    );
}
```

### Phase 3: Verification

Run all checks in sequence:

```bash
# 1. Run unit tests for the new component
./vendor/bin/phpunit --filter=<ComponentName>Test

# 2. Run parity tests
composer run test:parity

# 3. Run static analysis
composer run phpstan

# 4. Run code style check
composer run cs

# 5. If code style issues, fix them
composer run cs:fix

# 6. Run all checks
composer run ca
```

### Common Patterns

#### Fluid-on-mobile Support
```php
private function addFluidMobileStyle(): void
{
    $css = '@media only screen and (max-width:479px) { table.mj-full-width-mobile { width: 100% !important; } td.mj-full-width-mobile { width: auto !important; } }';

    foreach ($this->context->styles as $style) {
        if (str_contains($style, 'mj-full-width-mobile')) {
            return;
        }
    }

    $this->context->styles[] = $css;
}
```

#### Content Width Calculation
```php
private function getContentWidth(): int
{
    $widthAttr = $this->getAttribute('width');
    $width = null !== $widthAttr ? (int) $widthAttr : \PHP_INT_MAX;
    $boxWidths = $this->getBoxWidths();

    return min($boxWidths['box'], $width);
}
```

#### Wrapping in Anchor Tag
```php
private function renderWithLink(string $content): string
{
    $href = $this->getAttribute('href');

    if (null === $href) {
        return $content;
    }

    return \sprintf(
        '<a %s>%s</a>',
        $this->htmlAttributes([
            'href' => $href,
            'target' => $this->getAttribute('target'),
            'rel' => $this->getAttribute('rel'),
        ]),
        $content
    );
}
```

### Reference Files

When implementing, always refer to:
- **JS Reference:** `reference/packages/mjml-<component>/src/index.js`
- **Similar PHP Component:** `src/Components/Body/Text.php` (simple), `src/Components/Body/Section.php` (complex)
- **Base Class:** `src/Component/BodyComponent.php`
- **Context:** `src/Renderer/RenderContext.php`
