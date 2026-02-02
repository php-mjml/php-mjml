# PHP MJML Library Comparison

This document compares php-mjml with other PHP libraries for working with MJML email templates.

## Overview

| Library | Approach | Node.js Required | PHP Version |
|---------|----------|------------------|-------------|
| **php-mjml** (this library) | Native PHP implementation | No | 8.2+ |
| [notfloran/mjml-bundle](https://github.com/notfloran/mjml-bundle) | Symfony bundle, calls MJML CLI | Yes | 7.4+ |
| [spatie/mjml-php](https://github.com/spatie/mjml-php) | Wraps Node.js MJML compiler | Yes (16+) | 8.1+ |
| [qferr/mjml-php](https://github.com/qferr/mjml-php) | Binary or remote API | Yes (binary) or API | 7.4+ |

## Approaches Explained

### Native PHP (php-mjml)

This library is a **complete PHP port** of the MJML rendering engine. It parses MJML markup and generates responsive HTML entirely in PHP, without calling any external processes or services.

```php
use PhpMjml\Renderer\Mjml2Html;
use PhpMjml\Parser\MjmlParser;
use PhpMjml\Component\Registry;
use PhpMjml\Preset\CorePreset;

$registry = new Registry();
$registry->registerMany(CorePreset::getComponents());

$renderer = new Mjml2Html($registry, new MjmlParser());
$result = $renderer->render('<mjml><mj-body>...</mj-body></mjml>');

echo $result->html;
```

### CLI Wrapper (notfloran/mjml-bundle, spatie/mjml-php)

These libraries execute the official MJML Node.js CLI as a subprocess. They require Node.js and the MJML npm package to be installed on the server.

### API Wrapper (qferr/mjml-php)

This library can use either a local MJML binary or a remote MJML rendering API service.

## When to Choose Each Library

### Choose php-mjml when:

- **You cannot or prefer not to install Node.js** on your server
- **Deployment simplicity** is important (just `composer require`)
- **You want to avoid subprocess overhead** for high-volume email generation
- **Offline operation** is required (no external API dependencies)
- **Container/serverless deployments** where adding Node.js is impractical

### Choose a CLI wrapper when:

- **100% output parity** with the official MJML is critical
- **New MJML features** must be available immediately upon release
- **Node.js is already part** of your deployment stack
- **Custom MJML plugins** or experimental features are needed

## Deployment Considerations

| Consideration | php-mjml | CLI Wrappers |
|---------------|----------|--------------|
| Dependencies | PHP only | PHP + Node.js + npm |
| Container size | Smaller | Larger (Node.js runtime) |
| Cold start | Fast | Slower (Node.js startup) |
| CI/CD setup | Simple | Requires Node.js in pipeline |
| Shared hosting | Works | Usually not possible |
| Memory usage | PHP only | PHP + Node.js process |

## Feature Parity

php-mjml aims for output parity with the official MJML library. The test suite includes parity tests that compare PHP output against the official MJML CLI to ensure identical HTML generation.

For the current implementation status of MJML components, see [component-todos.md](component-todos.md).
