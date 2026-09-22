<table>
  <tr>
    <td><img src="demo/assets/images/php-mjml-logo.png" alt="PHP-MJML" width="80"></td>
    <td>
      <h1>PHP-MJML</h1>
      Native PHP port of the <a href="https://mjml.io">MJML</a> email templating library.<br>
      Convert MJML markup into responsive HTML emails without Node.js.
    </td>
  </tr>
</table>

<table>
  <tr>
    <td>
      <a href="https://php-mjml.on-forge.com">
        <img src="docs/live-demo.png" alt="PHP-MJML Live Demo" width="80">
      </a>
    </td>
    <td>
      <h3>Try it in your browser</h3>
      <p>Edit MJML markup and see the rendered HTML email output in real time — no installation required.</p>
      <br>
      <a href="https://php-mjml.on-forge.com">
        <img src="https://img.shields.io/badge/%F0%9F%9A%80_Try_it_Live-Demo-blue?style=for-the-badge&labelColor=4a154b&color=007bff&scale=2" alt="Try it Live" height="40">
      </a>
    </td>
  </tr>
</table>

## Why PHP-MJML?

- **No Node.js, no subprocess, no API calls** — `composer require` and render. Works on
  shared hosting, in slim containers and in serverless functions.
- **Identical output to official MJML** — every component is parity tested against the
  MJML CLI, so templates render the same as they do in the MJML ecosystem.
- **Every MJML component** — all body and head components are implemented, including
  `mj-hero`, `mj-carousel`, `mj-accordion`, `mj-navbar` and `mj-social`.
- **Full styling toolkit** — global defaults with `mj-attributes` and `mj-class`, CSS
  inlining with `mj-style inline="inline"`, custom web fonts, responsive breakpoints and
  arbitrary HTML attributes via `mj-html-attributes`.
- **Outlook-ready HTML** — generates the MSO conditional comments and VML fallbacks that
  Outlook needs, and merges adjacent conditionals to keep the output lean.
- **Forgiving parser** — accepts HTML inside `mj-text`, `mj-button` and friends, HTML
  entities such as `&nbsp;`, bare `&` characters and duplicate attributes.
- **Attribute validation** — unknown attributes and unsupported values (e.g.
  `align="middle"`) are reported instead of silently producing broken markup.
- **Security built in** — an email-focused HTML sanitizer and URL validator for
  user-supplied content.
- **Extensible** — register your own components alongside the core set.
- **Strictly typed** — PHP 8.2+, PHPStan at maximum level, tested on PHP 8.2–8.5.

## Installation

```bash
composer require php-mjml/php-mjml
```

## Quick Start

```php
use PhpMjml\Renderer\Mjml2Html;

$renderer = Mjml2Html::create();

$result = $renderer->render(<<<MJML
<mjml>
  <mj-body>
    <mj-section>
      <mj-column>
        <mj-text>Hello World!</mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML);

echo $result->html;
```

`Mjml2Html::create()` returns a renderer wired with every core component. The renderer is
stateless between calls, so create it once and reuse it for all your emails.

## Features

### Responsive Layouts

Sections, columns, groups and wrappers build layouts that stack on mobile and sit side by
side on desktop. Column widths are distributed automatically unless you set them.

```xml
<mjml>
  <mj-body background-color="#f4f4f4">
    <mj-section background-color="#ffffff" padding="20px">
      <mj-column width="50%">
        <mj-image src="https://example.com/product.png" alt="Product" />
      </mj-column>
      <mj-column width="50%">
        <mj-text font-size="18px" color="#333">New arrivals</mj-text>
        <mj-button href="https://example.com/shop">Shop now</mj-button>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
```

Use `mj-group` to keep columns side by side on mobile, and `mj-wrapper` to share a
background or border across several sections.

### Global Styles with `mj-attributes` and `mj-class`

Set defaults once in the head instead of repeating attributes on every element. Defaults
can target a component type or a reusable class that elements opt into with `mj-class`.

```xml
<mjml>
  <mj-head>
    <mj-attributes>
      <mj-text font-family="Helvetica, Arial, sans-serif" font-size="16px" color="#333333" />
      <mj-button font-family="Helvetica, Arial, sans-serif" />
      <mj-class name="primary" background-color="#4a154b" color="#ffffff" />
    </mj-attributes>
  </mj-head>
  <mj-body>
    <mj-section>
      <mj-column>
        <mj-text>Uses the mj-text defaults.</mj-text>
        <mj-button mj-class="primary" href="https://example.com">Styled by mj-class</mj-button>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
```

### Custom CSS and CSS Inlining

`mj-style` adds CSS to the document head. With `inline="inline"`, the rules are inlined
into matching elements, which is what many email clients need.

```xml
<mj-head>
  <mj-style>
    @media (max-width: 480px) { .hide-mobile { display: none !important; } }
  </mj-style>
  <mj-style inline="inline">
    .highlight { color: #e85034; font-weight: bold; }
  </mj-style>
</mj-head>
```

Combine it with `css-class` on any component to target your own selectors.

### Fonts, Title, Preview Text and Breakpoints

```xml
<mj-head>
  <mj-title>Your order has shipped</mj-title>
  <mj-preview>Track your package and see the delivery estimate</mj-preview>
  <mj-font name="Raleway" href="https://fonts.googleapis.com/css?family=Raleway" />
  <mj-breakpoint width="600px" />
</mj-head>
```

- `mj-title` sets the document title, `mj-preview` the inbox preview text.
- `mj-font` imports a web font. Only fonts that are actually used are included.
- `mj-breakpoint` changes the width at which columns switch to the mobile layout.

Common Google Fonts (Open Sans, Droid Sans, Lato, Roboto, Ubuntu) are known out of the box.
Register more for every render with `RenderOptions`:

```php
use PhpMjml\Renderer\RenderOptions;

$options = new RenderOptions(
    fonts: [
        ...RenderOptions::DEFAULT_FONTS,
        'Custom Font' => 'https://example.com/custom-font.css',
    ],
);

$result = $renderer->render($mjml, $options);
```

### Custom HTML Attributes

`mj-html-attributes` adds attributes to rendered elements using CSS selectors — useful for
tracking attributes, accessibility or test hooks.

```xml
<mj-head>
  <mj-html-attributes>
    <mj-selector path=".cta a">
      <mj-html-attribute name="data-tracking-id">hero-cta</mj-html-attribute>
    </mj-selector>
  </mj-html-attributes>
</mj-head>
<mj-body>
  <mj-section>
    <mj-column>
      <mj-button css-class="cta" href="https://example.com">Get started</mj-button>
    </mj-column>
  </mj-section>
</mj-body>
```

### Interactive Components

`mj-carousel` (image slideshow), `mj-accordion` (expandable sections) and `mj-navbar`
(navigation with an optional mobile hamburger menu) render as interactive, CSS-only widgets in clients
that support them and fall back gracefully in the ones that don't.

### Validation and Error Handling

Unknown attributes and unsupported attribute values do not stop rendering; they are
collected on the result so you can log them or fail your build:

```php
$result = $renderer->render($mjml);

if ($result->hasErrors()) {
    foreach ($result->errors as $error) {
        // e.g. mj-image: The option "align" with value "middle" is invalid. Accepted values are: "left", "center", "right".
        echo $error, PHP_EOL;
    }
}
```

Markup that cannot be parsed at all throws a `PhpMjml\Parser\ParserException`.

### Custom Components

Add your own tags by registering components next to the core preset. Extend
`BodyComponent` (or `HeadComponent`), declare the allowed and default attributes, and
implement `render()`:

```php
use PhpMjml\Component\BodyComponent;
use PhpMjml\Component\Registry;
use PhpMjml\Parser\MjmlParser;
use PhpMjml\Preset\CorePreset;
use PhpMjml\Renderer\Mjml2Html;

final class Badge extends BodyComponent
{
    protected static bool $endingTag = true;

    public static function getComponentName(): string
    {
        return 'mj-badge';
    }

    public static function getAllowedAttributes(): array
    {
        return ['color' => 'color', 'background-color' => 'color'];
    }

    public static function getDefaultAttributes(): array
    {
        return ['color' => '#ffffff', 'background-color' => '#4a154b'];
    }

    public function render(): string
    {
        return \sprintf(
            '<span style="color:%s;background-color:%s;padding:2px 8px;border-radius:4px">%s</span>',
            $this->getAttribute('color'),
            $this->getAttribute('background-color'),
            $this->getContent(),
        );
    }
}

$registry = new Registry();
$registry->registerMany(CorePreset::getComponents());
$registry->register(Badge::class);

// Pass the same registry to the renderer and the parser.
$renderer = new Mjml2Html($registry, new MjmlParser(registry: $registry));
```

See `CLAUDE.md` and the classes in `src/Components/` for complete examples.

## Framework Integration

PHP-MJML is framework agnostic. The pattern is the same everywhere: register the renderer as
a shared service, write your emails as MJML templates in your usual template engine, render
the template, and pass the resulting HTML to your mailer.

### Laravel

Install the package:

```bash
composer require php-mjml/php-mjml
```

Register the renderer as a singleton in `app/Providers/AppServiceProvider.php`:

```php
use PhpMjml\Renderer\Mjml2Html;

public function register(): void
{
    $this->app->singleton(Mjml2Html::class, fn () => Mjml2Html::create());
}
```

Write the email as MJML in a Blade view, e.g. `resources/views/emails/welcome.blade.php`.
Blade escapes `{{ }}` output, so user data is safe to interpolate:

```blade
<mjml>
  <mj-body>
    <mj-section>
      <mj-column>
        <mj-text font-size="20px">Welcome, {{ $user->name }}!</mj-text>
        <mj-button href="{{ url('/dashboard') }}">Open your dashboard</mj-button>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
```

> [!TIP]
> Blade treats `@` as a directive prefix. Inside `<mj-style>`, write CSS at-rules as
> `@@media` so Blade outputs a literal `@media`.

Render it in a Mailable:

```php
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use PhpMjml\Renderer\Mjml2Html;

class WelcomeMail extends Mailable
{
    public function __construct(public User $user)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Welcome!');
    }

    public function content(): Content
    {
        $mjml = view('emails.welcome', ['user' => $this->user])->render();

        return new Content(htmlString: app(Mjml2Html::class)->render($mjml)->html);
    }
}
```

Send it as usual with `Mail::to($user)->send(new WelcomeMail($user));`.

### Symfony

Install the package:

```bash
composer require php-mjml/php-mjml
```

Register the renderer in `config/services.yaml` using its factory method, so it can be
autowired anywhere:

```yaml
services:
    PhpMjml\Renderer\Mjml2Html:
        factory: ['PhpMjml\Renderer\Mjml2Html', 'create']
```

Write the email as MJML in a Twig template, e.g. `templates/emails/welcome.mjml.twig`.
Twig auto-escapes variables as HTML for this file:

```twig
<mjml>
  <mj-body>
    <mj-section>
      <mj-column>
        <mj-text font-size="20px">Welcome, {{ user.name }}!</mj-text>
        <mj-button href="{{ url('app_dashboard') }}">Open your dashboard</mj-button>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
```

Render and send it with Symfony Mailer:

```php
use PhpMjml\Renderer\Mjml2Html;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

final class WelcomeMailer
{
    public function __construct(
        private readonly Environment $twig,
        private readonly Mjml2Html $mjml,
        private readonly MailerInterface $mailer,
    ) {
    }

    public function send(User $user): void
    {
        $mjml = $this->twig->render('emails/welcome.mjml.twig', ['user' => $user]);

        $email = (new Email())
            ->to($user->getEmail())
            ->subject('Welcome!')
            ->html($this->mjml->render($mjml)->html);

        $this->mailer->send($email);
    }
}
```

### Tips for Both Frameworks

- Log `$result->errors` (or fail your tests on `$result->hasErrors()`) to catch invalid
  attributes in your templates early.
- The renderer holds no per-email state, so a single shared instance can render any number
  of emails.
- For HTML that comes from users rather than your templates (e.g. rich-text content
  inserted with `{!! !!}` or `|raw`), run it through the [`EmailContentSanitizer`](#security)
  first.

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

`mj-include` is not supported. Compose templates in PHP (for example with your templating
engine) before passing the final MJML to the renderer.

## Requirements

- PHP 8.2+
- Extensions: `dom`, `libxml`

## Development

Install development dependencies with `composer install`. Static analysis uses
PHPStan `^2.2` with `level: max` in `phpstan.dist.neon`, covering both `src/` and
`tests/`.

```bash
# Run all checks (style, static analysis, tests)
composer run ca

# Individual commands
composer run test          # All tests, without coverage
composer run test:unit     # Unit tests
composer run test:parity   # Parity tests (requires npx mjml)
composer run cs            # Check code style
composer run cs:fix        # Fix code style
composer run phpstan       # PHPStan v2, maximum level
```

CI runs these Composer commands, checks code style and maximum-level analysis,
and tests PHP 8.2–8.5, including the lowest supported dependencies on PHP 8.2.

Coverage is optional and requires Xdebug or PCOV:

```bash
XDEBUG_MODE=coverage composer run test:coverage
```

Coverage reports are written to `.phpunit.cache/`.

### Parity Testing

Tests compare PHP output against the official MJML CLI to ensure identical HTML generation:

```bash
composer run test:parity
```

Requires Node.js with MJML available via `npx mjml`. Install the CLI before
running the parity suite or `composer run ca`:

```bash
npm install -g mjml
npx mjml --version
```

Local parity tests are skipped when the CLI is unavailable. CI installs it and
fails on skipped tests so parity checks always run.

## Architecture: Why XML Parsing?

PHP-MJML parses MJML as **XML** rather than HTML. The main reason: HTML5 parsers do not honor self-closing syntax on custom elements. `<mj-spacer />` would be parsed as an unclosed `<mj-spacer>` tag, breaking the tree structure. XML handles this correctly.

The tradeoff is that XML is stricter than HTML — it rejects things like duplicate attributes, bare `&` characters, and HTML-named entities (`&nbsp;`). The parser includes a preprocessing pipeline to bridge these gaps:

1. **Ending-tag extraction** — Content inside tags like `<mj-text>` (which may contain raw HTML) is replaced with safe placeholders before XML parsing, then restored afterward.
2. **Attribute deduplication** — Duplicate attributes (e.g., `font-size` declared twice) are reduced to the first occurrence, matching HTML behavior.
3. **Entity conversion** — HTML entities like `&nbsp;` are converted to XML-compatible numeric equivalents (`&#160;`), and bare `&` characters are escaped.

An alternative would be switching to the HTML5 parser (`Dom\HTMLDocument`, available since PHP 8.4), which handles all of the above natively. However, that would require its own preprocessing step to expand self-closing custom tags into explicit open/close pairs. For now, the XML approach with targeted fixups is the simpler path.

## Contributing

This library was developed with AI assistance. Contributions are welcome — especially new component implementations!

See `CLAUDE.md` for architecture details and component implementation guidelines.

## License

MIT
