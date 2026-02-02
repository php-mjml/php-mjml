# PHP-MJML

> [!WARNING]
> **Work in Progress** — This library is currently under active development with [Claude Code](https://claude.ai/code) and is not yet ready for production use. APIs may change without notice. Use with care.

Native PHP port of the [MJML](https://mjml.io) email templating library. Convert MJML markup into responsive HTML emails without Node.js.

**Key Features:**
- Zero JavaScript dependencies — pure PHP implementation
- Parity tested against the official MJML library
- PHP 8.2+ with strict typing (PHPStan level 8)

## Installation

```bash
composer require php-mjml/php-mjml
```

## Usage

```php
use PhpMjml\Renderer\Mjml2Html;
use PhpMjml\Parser\MjmlParser;
use PhpMjml\Component\Registry;
use PhpMjml\Preset\CorePreset;

$registry = new Registry();
$registry->registerMany(CorePreset::getComponents());

$renderer = new Mjml2Html($registry, new MjmlParser());

$mjml = <<<MJML
<mjml>
  <mj-body>
    <mj-section>
      <mj-column>
        <mj-text>Hello World!</mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML;

$result = $renderer->render($mjml);
echo $result->html;
```

### Multi-Column Layout

```php
$mjml = <<<MJML
<mjml>
  <mj-body background-color="#f4f4f4">
    <mj-section background-color="#ffffff" padding="20px">
      <mj-column width="50%">
        <mj-text font-size="18px" color="#333">Left column</mj-text>
      </mj-column>
      <mj-column width="50%">
        <mj-text font-size="18px" color="#333">Right column</mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML;

$result = $renderer->render($mjml);
```

### Render Options

```php
use PhpMjml\Renderer\RenderOptions;

$options = new RenderOptions(
    fonts: [
        'Open Sans' => 'https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,700',
        'Custom Font' => 'https://example.com/custom-font.css',
    ],
);

$result = $renderer->render($mjml, $options);
```

## Post-Processing (Minify, Beautify)

Like the JavaScript MJML library, post-processing (minification, beautification) is not
handled by the core library. We recommend using dedicated tools:

### Minification

```bash
composer require pfaciana/tiny-html-minifier
```

```php
use TinyHtmlMinifier\TinyMinify;

$result = $renderer->render($mjml);
$minified = TinyMinify::html($result->html, ['collapse_whitespace' => true]);
```

### Beautification

```bash
composer require gajus/dindent
```

```php
use Gajus\Dindent\Indenter;

$result = $renderer->render($mjml);
$indenter = new Indenter();
$beautified = $indenter->indent($result->html);
```

### Stripping Comments

To remove HTML comments while preserving Outlook conditional comments:

```php
$html = preg_replace('/<!--(?!\[if\s)(?!<!\[endif\]).*?-->/s', '', $result->html);
```

## Security

When processing untrusted content (user input, external APIs), use the built-in sanitizer:

```php
use PhpMjml\Security\EmailContentSanitizer;

$sanitizer = new EmailContentSanitizer();
$safeContent = $sanitizer->sanitize($untrustedHtml);

$mjml = "<mj-text>{$safeContent}</mj-text>";
```

See [docs/SECURITY.md](docs/SECURITY.md) for comprehensive security guidance.

## Available Components

### Body Components

| Component | Description |
|-----------|-------------|
| `mj-body` | Root container for email content |
| `mj-section` | Horizontal section with background support |
| `mj-column` | Column within a section (auto-width distribution) |
| `mj-group` | Groups columns together for consistent mobile behavior |
| `mj-wrapper` | Wraps multiple sections with shared background |
| `mj-text` | Text content with full typography control |
| `mj-button` | Call-to-action button |
| `mj-image` | Responsive image |
| `mj-divider` | Horizontal divider line |
| `mj-spacer` | Vertical spacing |
| `mj-table` | HTML table for tabular data |
| `mj-social` | Social media icon links |
| `mj-social-element` | Individual social media icon |
| `mj-navbar` | Navigation bar |
| `mj-navbar-link` | Navigation link |
| `mj-hero` | Hero section with background image |
| `mj-carousel` | Image carousel/slideshow |
| `mj-carousel-image` | Individual carousel image |
| `mj-accordion` | Expandable accordion container |
| `mj-accordion-element` | Individual accordion item |
| `mj-accordion-title` | Accordion item title |
| `mj-accordion-text` | Accordion item content |
| `mj-raw` | Raw HTML passthrough |

### Head Components

| Component | Description |
|-----------|-------------|
| `mj-head` | Container for head elements |
| `mj-title` | Email title (shown in browser tab) |
| `mj-preview` | Preview text (shown in inbox) |
| `mj-attributes` | Default attribute values |
| `mj-breakpoint` | Responsive breakpoint configuration |
| `mj-font` | Custom web font registration |
| `mj-style` | Custom CSS styles |
| `mj-html-attributes` | Add attributes to rendered HTML elements |

## Requirements

- PHP 8.2+
- Extensions: `dom`, `libxml`

## Development

```bash
# Run all checks (style, static analysis, tests)
composer run ca

# Individual commands
composer run test          # All tests
composer run test:unit     # Unit tests
composer run test:parity   # Parity tests (requires npx mjml)
composer run cs:fix        # Fix code style
composer run phpstan       # Static analysis
```

### Parity Testing

Tests compare PHP output against the official MJML CLI to ensure identical HTML generation:

```bash
composer run test:parity
```

Requires Node.js with MJML available via `npx mjml`.

## Contributing

This library was developed with AI assistance. Contributions are welcome — especially new component implementations!

See `CLAUDE.md` for architecture details and component implementation guidelines.

## License

MIT
