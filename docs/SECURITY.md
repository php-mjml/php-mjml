# Security Guide

This guide documents security considerations when using PHP-MJML and provides best practices for handling untrusted content.

## Table of Contents

- [Threat Model](#threat-model)
- [Security Architecture](#security-architecture)
- [Using the Sanitizer](#using-the-sanitizer)
- [URL Validation](#url-validation)
- [Best Practices](#best-practices)
- [Known Limitations](#known-limitations)

## Threat Model

### Trusted vs Untrusted Input

PHP-MJML is designed for **trusted input** by default. Like the JavaScript MJML library, it assumes MJML templates are created by trusted developers.

**Trusted Input** (safe to use directly):
- Templates created by your development team
- Static MJML files in your codebase
- Content from your CMS with proper access controls

**Untrusted Input** (requires sanitization):
- User-submitted content (comments, reviews, messages)
- Content from external APIs
- Anything from form submissions or user input

### Attack Vectors

| Vector | Risk Level | Mitigation |
|--------|------------|------------|
| XSS via mj-text content | High if untrusted | Use EmailContentSanitizer |
| XSS via mj-raw content | High if untrusted | Use EmailContentSanitizer |
| JavaScript URL injection | Medium | Use UrlValidator |
| CSS injection via style | Low | Email clients strip most CSS |
| XXE attacks | None | PHP 8+ disables external entities by default |
| Attribute injection | None | All attributes are escaped |

## Security Architecture

### What's Automatically Secured

1. **HTML Attributes**: All attribute values are escaped using `htmlspecialchars(ENT_QUOTES, 'UTF-8')`
2. **Title and Preview**: Head component content is properly escaped
3. **XML Parser**: Uses PHP 8+ secure defaults (external entity loading disabled)

### What Requires Manual Sanitization

Components that render content as-is (to support rich HTML):

- `mj-text` - Renders inner HTML content directly
- `mj-button` - Renders button text directly
- `mj-raw` - Renders raw HTML (intentionally)

## Using the Sanitizer

### Basic Usage

```php
use PhpMjml\Security\EmailContentSanitizer;

$sanitizer = new EmailContentSanitizer();

// Sanitize untrusted content BEFORE embedding in MJML
$userInput = $_POST['email_body']; // DANGEROUS!
$safeContent = $sanitizer->sanitize($userInput);

$mjml = <<<MJML
<mjml>
  <mj-body>
    <mj-section>
      <mj-column>
        <mj-text>{$safeContent}</mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML;

$result = $renderer->render($mjml);
```

### Security Levels

#### Default Configuration

Allows common HTML elements used in emails while blocking dangerous elements:

```php
$sanitizer = new EmailContentSanitizer();

// Allowed: p, br, strong, em, a, ul, ol, li, table, img, headings
// Blocked: script, style, iframe, object, embed, form elements
```

#### Strict Configuration

Only allows basic text formatting:

```php
$sanitizer = new EmailContentSanitizer(
    EmailContentSanitizer::createStrictConfig()
);

// Allowed: p, br, strong, em, a, ul, ol, li
// Blocked: Everything else including tables, images, styles
```

#### Permissive Configuration

For trusted sources that need more HTML support:

```php
$sanitizer = new EmailContentSanitizer(
    EmailContentSanitizer::createPermissiveConfig()
);

// Allows style elements in addition to default config
// Use with caution!
```

### What Gets Sanitized

| Input | Output |
|-------|--------|
| `<script>alert(1)</script>` | `` (removed) |
| `<img src="x" onerror="alert(1)">` | `<img src="x">` |
| `<a href="javascript:alert(1)">` | `<a>` (href removed) |
| `<iframe src="evil.com">` | `` (removed) |
| `<p onclick="alert(1)">Text</p>` | `<p>Text</p>` |
| `<p style="color:red">Text</p>` | `<p style="color:red">Text</p>` (preserved) |

## URL Validation

For validating URLs in background images or custom href values:

### Basic Usage

```php
use PhpMjml\Security\UrlValidator;

$validator = new UrlValidator();

// Check if URL is safe
if ($validator->isValid($url)) {
    // URL is safe to use
}

// Throws InvalidUrlException if unsafe
$validator->assertValid($url);

// Returns empty string if unsafe
$safeUrl = $validator->sanitize($url);
```

### Blocked Schemes

The following URL schemes are blocked:
- `javascript:` - Script execution
- `vbscript:` - VBScript execution
- `data:` - Data URI (can embed HTML/JS)
- `file:` - Local file access
- `mhtml:` - MHTML vulnerability
- `x-javascript:` - Alternative JS scheme

### Allowed Schemes (Default)

- `https:` - Secure HTTP
- `http:` - HTTP
- `mailto:` - Email links
- `tel:` - Phone links
- Relative URLs (starting with `/`, `./`, `#`, `?`)

### Restrictive Validators

```php
// HTTPS only (no HTTP)
$validator = UrlValidator::httpsOnly();

// Web URLs only (no mailto/tel)
$validator = UrlValidator::webUrls();

// Custom schemes
$validator = new UrlValidator(['https', 'http', 'ftp']);
```

## Best Practices

### 1. Always Sanitize User Content

```php
// BAD - Direct injection
$mjml = "<mj-text>{$_POST['content']}</mj-text>";

// GOOD - Sanitize first
$safe = $sanitizer->sanitize($_POST['content']);
$mjml = "<mj-text>{$safe}</mj-text>";
```

### 2. Validate URLs

```php
// BAD - Direct URL usage
$bgUrl = $_POST['background_url'];
$mjml = "<mj-section background-url=\"{$bgUrl}\">";

// GOOD - Validate URL
$validator = new UrlValidator();
$bgUrl = $validator->sanitize($_POST['background_url']);
$mjml = "<mj-section background-url=\"{$bgUrl}\">";
```

### 3. Use Typed Placeholders

```php
// GOOD - Define a safe template with placeholders
function buildEmail(string $recipientName, string $messageBody): string
{
    $sanitizer = new EmailContentSanitizer();

    // Escape name for attribute context
    $safeName = htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8');

    // Sanitize HTML content
    $safeBody = $sanitizer->sanitize($messageBody);

    return <<<MJML
    <mjml>
      <mj-body>
        <mj-section>
          <mj-column>
            <mj-text>Hello, {$safeName}!</mj-text>
            <mj-text>{$safeBody}</mj-text>
          </mj-column>
        </mj-section>
      </mj-body>
    </mjml>
    MJML;
}
```

### 4. Content Security Policy

While email clients don't support CSP headers, you can still limit damage:

```php
// Avoid inline event handlers in your templates
// BAD
<mj-raw><button onclick="doSomething()">Click</button></mj-raw>

// GOOD - Use links instead
<mj-button href="https://yoursite.com/action">Click</mj-button>
```

### 5. Audit User Content Sources

Document where user content can enter your email templates:

```php
/**
 * @security User content sources:
 * - $userName: From user profile, sanitize for text context
 * - $messageBody: User-submitted, requires full HTML sanitization
 * - $imageUrl: User-uploaded, validate URL scheme
 */
```

## Known Limitations

### 1. CSS Background URLs

Background URLs in `mj-section` and `mj-hero` are not automatically validated:

```php
// You must validate these manually
$validator = new UrlValidator();
$bgUrl = $validator->sanitize($userProvidedUrl);
```

### 2. Font URLs

Font URLs in `@import` statements are not validated. Only use fonts from trusted sources.

### 3. mj-style Content

The `mj-style` component renders CSS directly. Never include user content in style blocks.

### 4. Sanitizer Length Limits

The default sanitizer has a 50,000 character limit. For larger content:

```php
$config = EmailContentSanitizer::createDefaultConfig()
    ->withMaxInputLength(100000);
$sanitizer = new EmailContentSanitizer($config);
```

## Reporting Security Issues

If you discover a security vulnerability in PHP-MJML, please report it responsibly:

1. **Do not** open a public GitHub issue
2. Email the maintainers directly with details
3. Include steps to reproduce if possible
4. Allow reasonable time for a fix before disclosure

## Further Reading

- [OWASP XSS Prevention Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html)
- [Symfony HTML Sanitizer Documentation](https://symfony.com/doc/current/html_sanitizer.html)
- [Email Security Best Practices](https://www.mailgun.com/blog/email/email-security-best-practices/)
