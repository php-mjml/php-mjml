# MJML Component Implementation Todo List

This document tracks the implementation status of MJML components in the PHP-MJML library.

## Implemented Components

| Component | Type | Status |
|-----------|------|--------|
| `mj-body` | Body | ✅ Done |
| `mj-section` | Body | ✅ Done |
| `mj-column` | Body | ✅ Done |
| `mj-text` | Body | ✅ Done |
| `mj-image` | Body | ✅ Done |
| `mj-button` | Body | ✅ Done |
| `mj-divider` | Body | ✅ Done |
| `mj-spacer` | Body | ✅ Done |
| `mj-raw` | Body | ✅ Done |
| `mj-hero` | Body | ✅ Done |
| `mj-head` | Head | ✅ Done |
| `mj-title` | Head | ✅ Done |
| `mj-preview` | Head | ✅ Done |
| `mj-font` | Head | ✅ Done |
| `mj-style` | Head | ✅ Done |
| `mj-attributes` | Head | ✅ Done |
| `mj-class` | Head | ✅ Done (handled within mj-attributes) |
| `mj-breakpoint` | Head | ✅ Done |

---

## Components To Implement (Ordered by Priority)

### Priority 1: Head Components (Essential for Email Configuration)

These components are critical for proper email rendering, font loading, and styling.

| # | Component | Type | Description | Complexity |
|---|-----------|------|-------------|------------|
| 1 | `mj-html-attributes` | Head | Custom HTML attributes for components | Medium |

### Priority 2: Common Body Components

Frequently used components in email templates.

| # | Component | Type | Description | Complexity |
|---|-----------|------|-------------|------------|
| 2 | `mj-social` | Body | Container for social media icons/links | Medium |
| 3 | `mj-social-element` | Body | Individual social icon (Facebook, Twitter, etc.) | Medium |
| 4 | `mj-table` | Body | HTML table rendering with styling | Medium |
| 5 | `mj-wrapper` | Body | Full-width section wrapper with gap support | Medium |
| 6 | `mj-group` | Body | Groups columns horizontally with direction control | Medium |

### Priority 3: Interactive Components

More complex components with interactive features.

| # | Component | Type | Description | Complexity |
|---|-----------|------|-------------|------------|
| 7 | `mj-navbar` | Body | Navigation bar with responsive hamburger menu | High |
| 8 | `mj-navbar-link` | Body | Individual navigation link | Low |
| 9 | `mj-accordion` | Body | Expandable/collapsible content sections | High |
| 10 | `mj-accordion-element` | Body | Individual accordion item | Medium |
| 11 | `mj-accordion-title` | Body | Accordion item title/header | Low |
| 12 | `mj-accordion-text` | Body | Accordion item content body | Low |
| 13 | `mj-carousel` | Body | Image carousel/slider with navigation | High |
| 14 | `mj-carousel-image` | Body | Individual carousel slide | Medium |

---

## Implementation Notes

### Reference Implementation
The JavaScript reference implementation is located at `reference/packages/mjml-<component>/src/index.js`.

### Component Base Classes
- **Body components**: Extend `PhpMjml\Component\BodyComponent`
- **Head components**: Extend `PhpMjml\Component\HeadComponent`

### Testing Requirements
For each component:
1. Create unit tests in `tests/Unit/Components/Body/` or `tests/Unit/Components/Head/`
2. Create parity test fixtures in `tests/Parity/fixtures/`
3. Run `composer run ca` to verify code style, static analysis, and tests

### Registration
Register new components in `src/Preset/CorePreset.php`.

---

## Progress Tracker

- **Total Components**: 32
- **Implemented**: 18 (56%)
- **Remaining**: 14 (44%)

### Milestones

- [x] **Milestone 1**: All head components (1 remaining: mj-html-attributes)
- [ ] **Milestone 2**: Social and table components (4 components)
- [ ] **Milestone 3**: Layout components - wrapper, group (2 components)
- [ ] **Milestone 4**: Navigation components (2 components)
- [ ] **Milestone 5**: Accordion components (4 components)
- [ ] **Milestone 6**: Carousel components (2 components)
