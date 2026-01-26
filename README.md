# PHP-MJML

Native PHP port of the [MJML](https://mjml.io) email templating library. Convert MJML markup into responsive HTML emails without Node.js.

**Key Features:**
- Zero JavaScript dependencies — pure PHP implementation
- Parity tested against the official MJML library
- PHP 8.4+ with strict typing (PHPStan level 8)

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
    minify: true,        // Minify HTML output
    beautify: false,     // Beautify HTML output
    keepComments: false, // Strip HTML comments
);

$result = $renderer->render($mjml, $options);
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

| Component | Description |
|-----------|-------------|
| `mj-body` | Root container for email content |
| `mj-section` | Horizontal section with background support |
| `mj-column` | Column within a section (auto-width distribution) |
| `mj-text` | Text content with full typography control |

More components coming soon. Contributions welcome!

## Requirements

- PHP 8.4+
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
