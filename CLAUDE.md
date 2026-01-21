# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PHP-MJML is a native PHP port of the MJML email templating library. It converts MJML markup into responsive HTML emails without requiring Node.js. The goal is output parity with the official JavaScript MJML library.

## Commands

```bash
# Run all quality checks (code style, static analysis, tests)
composer run ca

# Run tests
composer run test              # All tests
composer run test:unit         # Unit tests only
composer run test:parity       # Parity tests only (compares PHP output to JS MJML)
./vendor/bin/phpunit --filter=TestClassName  # Single test class

# Code style
composer run cs                # Check code style (dry-run)
composer run cs:fix            # Fix code style

# Static analysis
composer run phpstan           # PHPStan level 8
```

## Architecture

### Core Pipeline

1. **Parser** (`src/Parser/MjmlParser.php`) - Parses MJML XML into a Node tree using PHP's DOMDocument
2. **Registry** (`src/Component/Registry.php`) - Maps MJML tag names to component classes
3. **Renderer** (`src/Renderer/Mjml2Html.php`) - Orchestrates the conversion process:
   - Processes head components first (for configuration like fonts, styles)
   - Builds body component tree with proper context propagation (container widths)
   - Generates final HTML skeleton

### Component System

- `ComponentInterface` - Contract for all components
- `AbstractComponent` - Base class with attribute handling
- `BodyComponent` - Extended base for body elements (sections, columns, text, etc.) with:
  - Style generation (`getStyles()`)
  - Child context propagation (`getChildContext()`)
  - Box model calculations (`getBoxWidths()`)
  - HTML attribute/style builders
- `HeadComponent` - Base for head elements (title, fonts, styles, etc.)

### Adding New Components

1. Create a class in `src/Components/Body/` or `src/Components/Head/`
2. Extend `BodyComponent` or `HeadComponent`
3. Implement `getComponentName()` returning the MJML tag name (e.g., `'mj-button'`)
4. Define `getAllowedAttributes()` and `getDefaultAttributes()`
5. Implement `render()` method
6. Register in `src/Preset/CorePreset.php`

### Reference Implementation

The `reference/` directory contains a clone of the official MJML JavaScript repository for comparison. Use it to understand the original implementation when porting components.

## Testing

### Parity Tests

Located in `tests/Parity/`. These tests compare PHP output against the official MJML CLI to ensure identical HTML generation.

- `ParityTestCase` provides `renderWithPhp()` and `renderWithJs()` helpers
- Requires `npx mjml` available (Node.js with MJML installed globally or via npx)
- Use `assertHtmlEquals()` for normalized comparison (ignores whitespace, normalizes attributes)

### Unit Tests

Located in `tests/Unit/`. Standard PHPUnit tests for individual component logic.

## Code Style

- Follows Symfony coding standards (`@Symfony` and `@Symfony:risky` rules)
- PHPStan level 8 strict type checking
- All files require the standard file header comment
- Use `$this->` for PHPUnit assertions (not `self::`)
