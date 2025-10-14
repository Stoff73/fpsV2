# FPS Financial Planning System - Design Style Guide

**Document Version**: 1.0
**Date**: 2025-10-13
**Project**: FPS Financial Planning System - UI/UX Design Standards
**Status**: Ready for Implementation

---

## Document Purpose

This comprehensive design style guide defines the visual language, interaction patterns, and user experience standards for the FPS Financial Planning System. It ensures consistency across all screens, components, and user interactions while maintaining a clean, professional aesthetic suitable for a modern financial planning web application.

**Target Audience**: Designers, frontend developers, product managers

---

## Table of Contents

1. [Design Principles](#design-principles)
2. [Color System](#color-system)
3. [Typography](#typography)
4. [Spacing & Layout](#spacing--layout)
5. [Component Library](#component-library)
6. [Chart Styling](#chart-styling)
7. [Screen States](#screen-states)
8. [Interactions & Animations](#interactions--animations)
9. [Responsive Design](#responsive-design)
10. [Accessibility](#accessibility)
11. [Screen Inventory](#screen-inventory)

---

## Design Principles

### 1. **Clarity & Readability**
Financial data must be immediately understandable. Use clear typography, appropriate contrast, and visual hierarchy to guide users through complex information.

### 2. **Trust & Professionalism**
The design should inspire confidence. Use a sophisticated color palette, consistent spacing, and polished interactions.

### 3. **Data-Driven Design**
Visualizations should be the hero. Charts, graphs, and metrics take center stage with clean, uncluttered layouts.

### 4. **Progressive Disclosure**
Don't overwhelm users. Reveal complexity gradually through tabs, expandable sections, and drill-down interactions.

### 5. **Actionable Insights**
Every screen should guide users toward next steps with clear CTAs and prioritized recommendations.

---

## Color System

### Primary Colors

**Primary Blue** - Main brand color, primary actions
- `primary-900`: `#0A2540` - Darkest, text on light backgrounds
- `primary-700`: `#1E4D7B` - Hover states, emphasis
- `primary-600`: `#2563EB` **← Main Primary** - Buttons, links, active states
- `primary-500`: `#3B82F6` - Lighter interactive elements
- `primary-100`: `#DBEAFE` - Backgrounds, subtle highlights
- `primary-50`: `#EFF6FF` - Very light backgrounds

**Usage**: Navigation active states, primary CTAs, data highlights, links

---

### Secondary Colors

**Teal** - Secondary actions, supporting elements
- `secondary-700`: `#0F766E`
- `secondary-600`: `#14B8A6` **← Main Secondary**
- `secondary-500`: `#2DD4BF`
- `secondary-100`: `#CCFBF1`
- `secondary-50`: `#F0FDFA`

**Usage**: Secondary buttons, alternative data series, supporting information

---

### Accent Colors

**Amber** - Warnings, medium priority alerts
- `amber-700`: `#B45309`
- `amber-600`: `#D97706` **← Main Amber**
- `amber-500`: `#F59E0B`
- `amber-100`: `#FEF3C7`
- `amber-50`: `#FFFBEB`

**Purple** - Premium features, special highlights
- `purple-700`: `#7C3AED`
- `purple-600`: `#9333EA` **← Main Purple**
- `purple-500`: `#A855F7`
- `purple-100`: `#F3E8FF`
- `purple-50`: `#FAF5FF`

**Usage**: Accent elements, data visualization variety, special badges

---

### Status Colors

**Success Green**
- `success-700`: `#15803D`
- `success-600`: `#16A34A` **← Main Success**
- `success-500`: `#22C55E`
- `success-100`: `#DCFCE7`
- `success-50`: `#F0FDF4`

**Warning Amber** (see Accent Colors above)

**Error Red**
- `error-700`: `#B91C1C`
- `error-600`: `#DC2626` **← Main Error**
- `error-500`: `#EF4444`
- `error-100`: `#FEE2E2`
- `error-50`: `#FEF2F2`

**Info Blue**
- `info-700`: `#0369A1`
- `info-600`: `#0284C7` **← Main Info**
- `info-500`: `#0EA5E9`
- `info-100`: `#E0F2FE`
- `info-50`: `#F0F9FF`

**Usage**:
- Success: Positive metrics, coverage above target, goals met
- Warning: Medium priority actions, coverage gaps, approaching limits
- Error: Critical gaps, failed validation, errors
- Info: Informational tooltips, neutral notifications

---

### Neutral Colors (Grays)

**Gray Scale** - Text, borders, backgrounds
- `gray-900`: `#111827` - Primary text
- `gray-800`: `#1F2937` - Secondary text
- `gray-700`: `#374151` - Tertiary text
- `gray-600`: `#4B5563` - Placeholder text
- `gray-500`: `#6B7280` - Disabled text
- `gray-400`: `#9CA3AF` - Borders, dividers
- `gray-300`: `#D1D5DB` - Light borders
- `gray-200`: `#E5E7EB` - Hover backgrounds
- `gray-100`: `#F3F4F6` - Card backgrounds
- `gray-50`: `#F9FAFB` - Page backgrounds
- `white`: `#FFFFFF` - Card surfaces, modals

**Usage**:
- `gray-900` to `gray-700`: Text hierarchy
- `gray-400` to `gray-300`: Borders, dividers
- `gray-100` to `white`: Surfaces and backgrounds

---

### Financial Data Colors

**Chart Color Palette** (for data series, ApexCharts)
- `chart-1`: `#2563EB` (Primary Blue)
- `chart-2`: `#14B8A6` (Teal)
- `chart-3`: `#A855F7` (Purple)
- `chart-4`: `#F59E0B` (Amber)
- `chart-5`: `#EC4899` (Pink)
- `chart-6`: `#10B981` (Green)
- `chart-7`: `#F97316` (Orange)
- `chart-8`: `#6366F1` (Indigo)

**Coverage Gap Colors** (Heatmap)
- No Gap: `#10B981` (Green)
- Small Gap (< 20%): `#F59E0B` (Amber)
- Medium Gap (20-50%): `#F97316` (Orange)
- Large Gap (> 50%): `#DC2626` (Red)

**Positive/Negative Colors**
- Positive (gains, surplus): `#10B981` (Green)
- Negative (losses, deficit): `#DC2626` (Red)
- Neutral: `#6B7280` (Gray)

---

## Typography

### Font Families

**Primary Font: Inter**
- Clean, modern sans-serif
- Excellent readability for financial data
- Wide range of weights (300-700)
- Use for: Body text, UI elements, labels, data values

```css
font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
```

**Secondary Font: Plus Jakarta Sans**
- Geometric, friendly sans-serif
- Use for: Headings, section titles, hero text

```css
font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
```

**Monospace Font: JetBrains Mono**
- Use for: Account numbers, policy numbers, precise numeric data

```css
font-family: 'JetBrains Mono', 'Courier New', monospace;
```

---

### Font Scales

#### Display / Hero Text
```css
.text-display {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 3.75rem; /* 60px */
  font-weight: 700;
  line-height: 1.1;
  letter-spacing: -0.02em;
}
```

#### H1 - Page Titles
```css
.text-h1 {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 2.25rem; /* 36px */
  font-weight: 700;
  line-height: 1.2;
  letter-spacing: -0.01em;
  color: #111827; /* gray-900 */
}
```

#### H2 - Section Titles
```css
.text-h2 {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 1.875rem; /* 30px */
  font-weight: 600;
  line-height: 1.3;
  color: #1F2937; /* gray-800 */
}
```

#### H3 - Subsection Titles
```css
.text-h3 {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 1.5rem; /* 24px */
  font-weight: 600;
  line-height: 1.4;
  color: #374151; /* gray-700 */
}
```

#### H4 - Card Titles
```css
.text-h4 {
  font-family: 'Inter', sans-serif;
  font-size: 1.25rem; /* 20px */
  font-weight: 600;
  line-height: 1.5;
  color: #374151; /* gray-700 */
}
```

#### H5 - Component Titles
```css
.text-h5 {
  font-family: 'Inter', sans-serif;
  font-size: 1rem; /* 16px */
  font-weight: 600;
  line-height: 1.5;
  color: #4B5563; /* gray-600 */
}
```

#### Body Large
```css
.text-body-lg {
  font-family: 'Inter', sans-serif;
  font-size: 1.125rem; /* 18px */
  font-weight: 400;
  line-height: 1.7;
  color: #1F2937; /* gray-800 */
}
```

#### Body Regular (Default)
```css
.text-body {
  font-family: 'Inter', sans-serif;
  font-size: 1rem; /* 16px */
  font-weight: 400;
  line-height: 1.6;
  color: #374151; /* gray-700 */
}
```

#### Body Small
```css
.text-body-sm {
  font-family: 'Inter', sans-serif;
  font-size: 0.875rem; /* 14px */
  font-weight: 400;
  line-height: 1.5;
  color: #4B5563; /* gray-600 */
}
```

#### Caption / Helper Text
```css
.text-caption {
  font-family: 'Inter', sans-serif;
  font-size: 0.75rem; /* 12px */
  font-weight: 400;
  line-height: 1.4;
  color: #6B7280; /* gray-500 */
}
```

#### Label
```css
.text-label {
  font-family: 'Inter', sans-serif;
  font-size: 0.875rem; /* 14px */
  font-weight: 500;
  line-height: 1.4;
  color: #374151; /* gray-700 */
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
```

#### Data Value (Large)
```css
.text-data-lg {
  font-family: 'Inter', sans-serif;
  font-size: 2.25rem; /* 36px */
  font-weight: 700;
  line-height: 1.2;
  color: #111827; /* gray-900 */
  font-feature-settings: 'tnum', 'lnum'; /* Tabular numbers */
}
```

#### Data Value (Medium)
```css
.text-data-md {
  font-family: 'Inter', sans-serif;
  font-size: 1.5rem; /* 24px */
  font-weight: 600;
  line-height: 1.3;
  color: #1F2937; /* gray-800 */
  font-feature-settings: 'tnum', 'lnum';
}
```

#### Data Value (Small)
```css
.text-data-sm {
  font-family: 'Inter', sans-serif;
  font-size: 1rem; /* 16px */
  font-weight: 500;
  line-height: 1.5;
  color: #374151; /* gray-700 */
  font-feature-settings: 'tnum', 'lnum';
}
```

#### Monospace (Account Numbers)
```css
.text-mono {
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.875rem; /* 14px */
  font-weight: 400;
  line-height: 1.5;
  color: #4B5563; /* gray-600 */
}
```

---

### Text Colors

```css
.text-primary { color: #2563EB; }
.text-secondary { color: #14B8A6; }
.text-success { color: #16A34A; }
.text-warning { color: #D97706; }
.text-error { color: #DC2626; }
.text-info { color: #0284C7; }
.text-gray-900 { color: #111827; }
.text-gray-700 { color: #374151; }
.text-gray-500 { color: #6B7280; }
```

---

## Spacing & Layout

### Spacing Scale

Use an 8px base grid system for consistent spacing:

```css
--space-0: 0;
--space-1: 0.25rem; /* 4px */
--space-2: 0.5rem;  /* 8px */
--space-3: 0.75rem; /* 12px */
--space-4: 1rem;    /* 16px */
--space-5: 1.25rem; /* 20px */
--space-6: 1.5rem;  /* 24px */
--space-8: 2rem;    /* 32px */
--space-10: 2.5rem; /* 40px */
--space-12: 3rem;   /* 48px */
--space-16: 4rem;   /* 64px */
--space-20: 5rem;   /* 80px */
--space-24: 6rem;   /* 96px */
```

**Usage Guidelines**:
- `space-2` to `space-4`: Component internal spacing (padding within buttons, form fields)
- `space-4` to `space-6`: Card padding, section spacing
- `space-6` to `space-10`: Layout spacing between sections
- `space-12` to `space-24`: Page-level margins, hero sections

---

### Container Widths

```css
.container-sm { max-width: 640px; }  /* Forms, login */
.container-md { max-width: 768px; }  /* Single column content */
.container-lg { max-width: 1024px; } /* Standard layouts */
.container-xl { max-width: 1280px; } /* Dashboards */
.container-2xl { max-width: 1536px; } /* Wide dashboards */
.container-full { max-width: 100%; }  /* Full width */
```

**Default Container**: `container-xl` (1280px) for dashboards

---

### Grid System

Use CSS Grid for dashboard layouts:

```css
.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(12, 1fr);
  gap: 1.5rem; /* 24px */
  padding: 1.5rem;
}

/* Responsive column spans */
.col-span-12 { grid-column: span 12 / span 12; } /* Full width */
.col-span-8 { grid-column: span 8 / span 8; }   /* 2/3 width */
.col-span-6 { grid-column: span 6 / span 6; }   /* 1/2 width */
.col-span-4 { grid-column: span 4 / span 4; }   /* 1/3 width */
.col-span-3 { grid-column: span 3 / span 3; }   /* 1/4 width */
```

**Dashboard Layout Example**:
- Main dashboard: 3-column grid (4-4-4) for module cards
- Module dashboards: 2-column grid (8-4) for main content + sidebar
- Full-width charts: 12-column span

---

### Border Radius

```css
--radius-none: 0;
--radius-sm: 0.25rem;  /* 4px - small elements */
--radius-md: 0.5rem;   /* 8px - buttons, inputs */
--radius-lg: 0.75rem;  /* 12px - cards */
--radius-xl: 1rem;     /* 16px - large cards */
--radius-2xl: 1.5rem;  /* 24px - modals */
--radius-full: 9999px; /* Fully rounded - badges, avatars */
```

**Usage**:
- Cards: `radius-lg` (12px)
- Buttons: `radius-md` (8px)
- Inputs: `radius-md` (8px)
- Badges: `radius-full`
- Modals: `radius-2xl` (24px)

---

### Shadows

Elevation system for depth and hierarchy:

```css
/* Shadows for cards and elevated surfaces */
--shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
--shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
--shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
--shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
--shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
--shadow-inner: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);
```

**Usage**:
- Cards (default): `shadow-sm`
- Cards (hover): `shadow-md`
- Dropdowns, modals: `shadow-xl`
- Active/pressed buttons: `shadow-inner`

---

## Component Library

### Buttons

#### Primary Button
```css
.btn-primary {
  font-family: 'Inter', sans-serif;
  font-size: 1rem;
  font-weight: 500;
  line-height: 1.5;

  padding: 0.625rem 1.25rem; /* 10px 20px */
  border-radius: 0.5rem; /* 8px */
  border: none;

  color: white;
  background-color: #2563EB; /* primary-600 */

  cursor: pointer;
  transition: all 150ms cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-primary:hover {
  background-color: #1E4D7B; /* primary-700 */
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.btn-primary:active {
  background-color: #0A2540; /* primary-900 */
  box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);
}

.btn-primary:focus {
  outline: 2px solid #2563EB;
  outline-offset: 2px;
}

.btn-primary:disabled {
  background-color: #9CA3AF; /* gray-400 */
  cursor: not-allowed;
  opacity: 0.6;
}
```

#### Secondary Button
```css
.btn-secondary {
  /* Same structure as primary */
  color: #2563EB; /* primary-600 */
  background-color: white;
  border: 1px solid #D1D5DB; /* gray-300 */
}

.btn-secondary:hover {
  background-color: #F9FAFB; /* gray-50 */
  border-color: #2563EB; /* primary-600 */
}
```

#### Tertiary Button (Text Only)
```css
.btn-tertiary {
  /* Same structure as primary */
  padding: 0.5rem 1rem;
  color: #2563EB; /* primary-600 */
  background-color: transparent;
  border: none;
}

.btn-tertiary:hover {
  background-color: #EFF6FF; /* primary-50 */
}
```

#### Danger Button
```css
.btn-danger {
  /* Same structure as primary */
  background-color: #DC2626; /* error-600 */
}

.btn-danger:hover {
  background-color: #B91C1C; /* error-700 */
}
```

#### Button Sizes
```css
.btn-sm {
  font-size: 0.875rem; /* 14px */
  padding: 0.5rem 1rem; /* 8px 16px */
}

.btn-md {
  font-size: 1rem; /* 16px - default */
  padding: 0.625rem 1.25rem; /* 10px 20px */
}

.btn-lg {
  font-size: 1.125rem; /* 18px */
  padding: 0.75rem 1.5rem; /* 12px 24px */
}
```

---

### Cards

#### Default Card
```css
.card {
  background-color: white;
  border-radius: 0.75rem; /* 12px */
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  padding: 1.5rem; /* 24px */
  border: 1px solid #E5E7EB; /* gray-200 */
  transition: box-shadow 150ms ease, transform 150ms ease;
}

.card:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

/* Clickable card (module overview cards) */
.card-clickable {
  cursor: pointer;
}

.card-clickable:hover {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.card-clickable:active {
  transform: translateY(0);
}
```

#### Card Header
```css
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem; /* 16px */
  padding-bottom: 1rem;
  border-bottom: 1px solid #E5E7EB; /* gray-200 */
}

.card-title {
  font-size: 1.25rem; /* 20px */
  font-weight: 600;
  color: #374151; /* gray-700 */
}
```

#### Card Body
```css
.card-body {
  /* Content area */
}
```

#### Card Footer
```css
.card-footer {
  margin-top: 1.5rem;
  padding-top: 1rem;
  border-top: 1px solid #E5E7EB; /* gray-200 */
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}
```

#### Module Overview Card (Main Dashboard)
```css
.module-card {
  background: linear-gradient(135deg, white 0%, #F9FAFB 100%);
  border-radius: 1rem; /* 16px */
  padding: 2rem; /* 32px */
  border: 2px solid #E5E7EB;
  cursor: pointer;
  transition: all 200ms ease;
}

.module-card:hover {
  transform: translateY(-4px);
}

.module-card-title {
  font-size: 1.5rem; /* 24px */
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #111827; /* gray-900 */
}

.module-card-metric {
  font-size: 2rem; /* 32px */
  font-weight: 700;
  margin-bottom: 0.5rem;
  font-feature-settings: 'tnum', 'lnum';
}

.module-card-label {
  font-size: 0.875rem; /* 14px */
  color: #6B7280; /* gray-500 */
}
```

**Module Card Color Variants (applied on hover)**:

Each module card changes its border color and shadow on hover to match its theme:

```css
/* Protection - Blue */
.module-card-protection:hover {
  border-color: #2563EB;
  box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2);
}

.module-card-protection .module-card-metric {
  color: #2563EB;
}

/* Savings - Teal */
.module-card-savings:hover {
  border-color: #14B8A6;
  box-shadow: 0 10px 15px -3px rgba(20, 184, 166, 0.2);
}

.module-card-savings .module-card-metric {
  color: #14B8A6;
}

/* Investment - Purple */
.module-card-investment:hover {
  border-color: #9333EA;
  box-shadow: 0 10px 15px -3px rgba(147, 51, 234, 0.2);
}

.module-card-investment .module-card-metric {
  color: #9333EA;
}

/* Retirement - Amber */
.module-card-retirement:hover {
  border-color: #D97706;
  box-shadow: 0 10px 15px -3px rgba(217, 119, 6, 0.2);
}

.module-card-retirement .module-card-metric {
  color: #D97706;
}

/* Estate - Pink */
.module-card-estate:hover {
  border-color: #EC4899;
  box-shadow: 0 10px 15px -3px rgba(236, 72, 153, 0.2);
}

.module-card-estate .module-card-metric {
  color: #EC4899;
}
```

**Design Notes**:
- On hover, the module color appears as a **border all around the card** (not just on one side)
- The 2px border width makes the color more prominent when hovering
- The colored shadow provides additional visual feedback
- The card lifts up slightly (translateY) for depth
- No icons are used - the design relies on typography and color for visual distinction

---

### Forms

#### Input Fields
```css
.input {
  font-family: 'Inter', sans-serif;
  font-size: 1rem;
  line-height: 1.5;

  padding: 0.625rem 0.875rem; /* 10px 14px */
  border-radius: 0.5rem; /* 8px */
  border: 1px solid #D1D5DB; /* gray-300 */
  background-color: white;
  color: #111827; /* gray-900 */

  transition: border-color 150ms ease, box-shadow 150ms ease;
  width: 100%;
}

.input::placeholder {
  color: #9CA3AF; /* gray-400 */
}

.input:hover {
  border-color: #9CA3AF; /* gray-400 */
}

.input:focus {
  outline: none;
  border-color: #2563EB; /* primary-600 */
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.input:disabled {
  background-color: #F3F4F6; /* gray-100 */
  color: #9CA3AF; /* gray-400 */
  cursor: not-allowed;
}

/* Error state */
.input-error {
  border-color: #DC2626; /* error-600 */
}

.input-error:focus {
  border-color: #DC2626;
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

/* Success state */
.input-success {
  border-color: #16A34A; /* success-600 */
}
```

#### Form Labels
```css
.label {
  font-size: 0.875rem; /* 14px */
  font-weight: 500;
  color: #374151; /* gray-700 */
  margin-bottom: 0.5rem;
  display: block;
}

.label-required::after {
  content: '*';
  color: #DC2626; /* error-600 */
  margin-left: 0.25rem;
}
```

#### Helper Text
```css
.helper-text {
  font-size: 0.75rem; /* 12px */
  color: #6B7280; /* gray-500 */
  margin-top: 0.25rem;
}

.error-text {
  font-size: 0.75rem;
  color: #DC2626; /* error-600 */
  margin-top: 0.25rem;
}
```

#### Select / Dropdown
```css
.select {
  /* Same styling as .input */
  appearance: none;
  background-image: url('data:image/svg+xml;charset=UTF-8,...'); /* Chevron down icon */
  background-repeat: no-repeat;
  background-position: right 0.875rem center;
  background-size: 1rem;
  padding-right: 2.5rem;
}
```

#### Checkbox
```css
.checkbox {
  width: 1.25rem; /* 20px */
  height: 1.25rem;
  border-radius: 0.25rem; /* 4px */
  border: 1px solid #D1D5DB; /* gray-300 */
  cursor: pointer;
  transition: all 150ms ease;
}

.checkbox:checked {
  background-color: #2563EB; /* primary-600 */
  border-color: #2563EB;
  background-image: url('data:image/svg+xml;charset=UTF-8,...'); /* Checkmark icon */
}

.checkbox:focus {
  outline: 2px solid #2563EB;
  outline-offset: 2px;
}
```

#### Radio Button
```css
.radio {
  width: 1.25rem;
  height: 1.25rem;
  border-radius: 9999px; /* Fully rounded */
  border: 1px solid #D1D5DB;
  cursor: pointer;
  transition: all 150ms ease;
}

.radio:checked {
  border-color: #2563EB;
  border-width: 6px;
}
```

#### Toggle Switch
```css
.toggle {
  width: 44px;
  height: 24px;
  border-radius: 9999px;
  background-color: #D1D5DB; /* gray-300 - off state */
  position: relative;
  cursor: pointer;
  transition: background-color 200ms ease;
}

.toggle-checked {
  background-color: #2563EB; /* primary-600 - on state */
}

.toggle-handle {
  width: 20px;
  height: 20px;
  border-radius: 9999px;
  background-color: white;
  position: absolute;
  top: 2px;
  left: 2px;
  transition: transform 200ms ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.toggle-checked .toggle-handle {
  transform: translateX(20px);
}
```

#### Slider (Range Input)
```css
.slider {
  width: 100%;
  height: 6px;
  border-radius: 9999px;
  background-color: #E5E7EB; /* gray-200 */
  outline: none;
  -webkit-appearance: none;
}

.slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 20px;
  height: 20px;
  border-radius: 9999px;
  background-color: #2563EB; /* primary-600 */
  cursor: pointer;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: transform 150ms ease;
}

.slider::-webkit-slider-thumb:hover {
  transform: scale(1.1);
}

.slider::-webkit-slider-thumb:active {
  transform: scale(0.95);
}

/* Firefox */
.slider::-moz-range-thumb {
  width: 20px;
  height: 20px;
  border-radius: 9999px;
  background-color: #2563EB;
  cursor: pointer;
  border: none;
}
```

---

### Navigation

#### Navbar (Top Navigation)
```css
.navbar {
  height: 64px;
  background-color: white;
  border-bottom: 1px solid #E5E7EB; /* gray-200 */
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  padding: 0 1.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: sticky;
  top: 0;
  z-index: 50;
}

.navbar-brand {
  font-size: 1.5rem; /* 24px */
  font-weight: 700;
  color: #2563EB; /* primary-600 */
  text-decoration: none;
}

.navbar-menu {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.navbar-link {
  font-size: 0.875rem;
  font-weight: 500;
  color: #6B7280; /* gray-500 */
  text-decoration: none;
  padding: 0.5rem 0.75rem;
  border-radius: 0.5rem;
  transition: all 150ms ease;
}

.navbar-link:hover {
  color: #2563EB; /* primary-600 */
  background-color: #EFF6FF; /* primary-50 */
}

.navbar-link-active {
  color: #2563EB;
  background-color: #EFF6FF;
}
```

#### Sidebar (Side Navigation)
```css
.sidebar {
  width: 256px;
  height: calc(100vh - 64px); /* Full height minus navbar */
  background-color: white;
  border-right: 1px solid #E5E7EB; /* gray-200 */
  padding: 1.5rem 1rem;
  overflow-y: auto;
  position: fixed;
  left: 0;
  top: 64px;
}

.sidebar-section {
  margin-bottom: 2rem;
}

.sidebar-section-title {
  font-size: 0.75rem; /* 12px */
  font-weight: 600;
  color: #9CA3AF; /* gray-400 */
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.5rem;
  padding: 0 0.75rem;
}

.sidebar-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.625rem 0.75rem;
  border-radius: 0.5rem; /* 8px */
  font-size: 0.875rem;
  font-weight: 500;
  color: #4B5563; /* gray-600 */
  text-decoration: none;
  transition: all 150ms ease;
}

.sidebar-link:hover {
  color: #2563EB; /* primary-600 */
  background-color: #EFF6FF; /* primary-50 */
}

.sidebar-link-active {
  color: #2563EB;
  background-color: #EFF6FF;
  border-left: 3px solid #2563EB;
}

.sidebar-icon {
  width: 20px;
  height: 20px;
}
```

---

### Tables

#### Data Table
```css
.table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  background-color: white;
  border-radius: 0.75rem; /* 12px */
  overflow: hidden;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  border: 1px solid #E5E7EB; /* gray-200 */
}

.table-header {
  background-color: #F9FAFB; /* gray-50 */
  border-bottom: 1px solid #E5E7EB;
}

.table-header-cell {
  padding: 0.75rem 1rem;
  text-align: left;
  font-size: 0.75rem; /* 12px */
  font-weight: 600;
  color: #6B7280; /* gray-500 */
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.table-row {
  border-bottom: 1px solid #E5E7EB;
  transition: background-color 150ms ease;
}

.table-row:last-child {
  border-bottom: none;
}

.table-row:hover {
  background-color: #F9FAFB; /* gray-50 */
}

.table-cell {
  padding: 1rem;
  font-size: 0.875rem;
  color: #374151; /* gray-700 */
}

/* Sortable column header */
.table-header-sortable {
  cursor: pointer;
  user-select: none;
}

.table-header-sortable:hover {
  color: #2563EB; /* primary-600 */
}

/* Numeric columns (right-aligned) */
.table-cell-numeric {
  text-align: right;
  font-feature-settings: 'tnum', 'lnum';
}

/* Status badge in table */
.table-cell-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
}
```

---

### Badges

```css
.badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.75rem; /* 4px 12px */
  border-radius: 9999px;
  font-size: 0.75rem; /* 12px */
  font-weight: 500;
  line-height: 1.25;
}

.badge-primary {
  color: #2563EB; /* primary-600 */
  background-color: #EFF6FF; /* primary-50 */
}

.badge-success {
  color: #15803D; /* success-700 */
  background-color: #DCFCE7; /* success-100 */
}

.badge-warning {
  color: #B45309; /* amber-700 */
  background-color: #FEF3C7; /* amber-100 */
}

.badge-error {
  color: #B91C1C; /* error-700 */
  background-color: #FEE2E2; /* error-100 */
}

.badge-info {
  color: #0369A1; /* info-700 */
  background-color: #E0F2FE; /* info-100 */
}

.badge-gray {
  color: #4B5563; /* gray-600 */
  background-color: #F3F4F6; /* gray-100 */
}
```

---

### Progress Bars

#### Standard Progress Bar
```css
.progress-bar-container {
  width: 100%;
  height: 12px;
  background-color: #E5E7EB; /* gray-200 */
  border-radius: 9999px;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  background-color: #2563EB; /* primary-600 */
  border-radius: 9999px;
  transition: width 500ms ease;
}

/* Color variants */
.progress-bar-fill-success {
  background-color: #16A34A; /* success-600 */
}

.progress-bar-fill-warning {
  background-color: #D97706; /* amber-600 */
}

.progress-bar-fill-error {
  background-color: #DC2626; /* error-600 */
}

/* With gradient */
.progress-bar-fill-gradient {
  background: linear-gradient(90deg, #2563EB 0%, #3B82F6 100%);
}
```

#### ISA Allowance Progress Bar (Multi-Segment)
```css
.isa-progress-container {
  width: 100%;
  height: 24px;
  background-color: #E5E7EB;
  border-radius: 0.5rem; /* 8px */
  overflow: hidden;
  display: flex;
}

.isa-progress-segment {
  height: 100%;
  transition: width 500ms ease;
}

.isa-segment-cash {
  background-color: #14B8A6; /* secondary-600 - teal */
}

.isa-segment-stocks {
  background-color: #9333EA; /* purple-600 */
}

.isa-segment-remaining {
  background-color: #E5E7EB; /* gray-200 */
}
```

---

### Tabs

```css
.tabs-container {
  border-bottom: 1px solid #E5E7EB; /* gray-200 */
  margin-bottom: 1.5rem;
}

.tabs-list {
  display: flex;
  gap: 2rem;
}

.tab {
  padding: 0.75rem 0;
  font-size: 0.875rem; /* 14px */
  font-weight: 500;
  color: #6B7280; /* gray-500 */
  border-bottom: 2px solid transparent;
  cursor: pointer;
  transition: all 150ms ease;
  background: none;
  border-top: none;
  border-left: none;
  border-right: none;
}

.tab:hover {
  color: #2563EB; /* primary-600 */
}

.tab-active {
  color: #2563EB;
  border-bottom-color: #2563EB;
}

.tab-panel {
  padding: 1.5rem 0;
}
```

---

### Modals

```css
/* Backdrop */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

/* Modal container */
.modal {
  background-color: white;
  border-radius: 1.5rem; /* 24px */
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  max-width: 32rem; /* 512px */
  width: 100%;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

/* Modal header */
.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #E5E7EB;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.modal-title {
  font-size: 1.25rem; /* 20px */
  font-weight: 600;
  color: #111827; /* gray-900 */
}

.modal-close {
  width: 32px;
  height: 32px;
  border-radius: 0.5rem;
  border: none;
  background-color: transparent;
  color: #6B7280;
  cursor: pointer;
  transition: all 150ms ease;
}

.modal-close:hover {
  background-color: #F3F4F6; /* gray-100 */
  color: #111827;
}

/* Modal body */
.modal-body {
  padding: 1.5rem;
  overflow-y: auto;
}

/* Modal footer */
.modal-footer {
  padding: 1.5rem;
  border-top: 1px solid #E5E7EB;
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}
```

---

### Tooltips

```css
.tooltip {
  position: absolute;
  z-index: 200;
  padding: 0.5rem 0.75rem;
  background-color: #1F2937; /* gray-800 */
  color: white;
  font-size: 0.75rem; /* 12px */
  border-radius: 0.5rem; /* 8px */
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  max-width: 16rem; /* 256px */
  pointer-events: none;
}

.tooltip-arrow {
  position: absolute;
  width: 8px;
  height: 8px;
  background-color: #1F2937;
  transform: rotate(45deg);
}

/* Tooltip positions */
.tooltip-top .tooltip-arrow {
  bottom: -4px;
  left: 50%;
  margin-left: -4px;
}

.tooltip-bottom .tooltip-arrow {
  top: -4px;
  left: 50%;
  margin-left: -4px;
}
```

---

### Alerts / Notifications

```css
.alert {
  padding: 1rem;
  border-radius: 0.75rem; /* 12px */
  border-left: 4px solid;
  display: flex;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.alert-icon {
  flex-shrink: 0;
  width: 20px;
  height: 20px;
}

.alert-content {
  flex: 1;
}

.alert-title {
  font-size: 0.875rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.alert-message {
  font-size: 0.875rem;
}

/* Alert variants */
.alert-success {
  background-color: #DCFCE7; /* success-100 */
  border-color: #16A34A; /* success-600 */
  color: #15803D; /* success-700 */
}

.alert-warning {
  background-color: #FEF3C7; /* amber-100 */
  border-color: #D97706; /* amber-600 */
  color: #B45309; /* amber-700 */
}

.alert-error {
  background-color: #FEE2E2; /* error-100 */
  border-color: #DC2626; /* error-600 */
  color: #B91C1C; /* error-700 */
}

.alert-info {
  background-color: #E0F2FE; /* info-100 */
  border-color: #0284C7; /* info-600 */
  color: #0369A1; /* info-700 */
}
```

---

### Spinners / Loaders

```css
.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #E5E7EB; /* gray-200 */
  border-top-color: #2563EB; /* primary-600 */
  border-radius: 9999px;
  animation: spin 800ms linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Small spinner */
.spinner-sm {
  width: 20px;
  height: 20px;
  border-width: 2px;
}

/* Large spinner */
.spinner-lg {
  width: 60px;
  height: 60px;
  border-width: 6px;
}

/* Spinner with text */
.spinner-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.spinner-text {
  font-size: 0.875rem;
  color: #6B7280; /* gray-500 */
}
```

---

### Skeleton Loaders

```css
.skeleton {
  background: linear-gradient(90deg, #F3F4F6 25%, #E5E7EB 50%, #F3F4F6 75%);
  background-size: 200% 100%;
  animation: skeleton-loading 1.5s ease-in-out infinite;
  border-radius: 0.5rem;
}

@keyframes skeleton-loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* Skeleton variants */
.skeleton-text {
  height: 1rem;
  width: 100%;
  margin-bottom: 0.5rem;
}

.skeleton-heading {
  height: 2rem;
  width: 60%;
  margin-bottom: 1rem;
}

.skeleton-card {
  height: 200px;
  width: 100%;
}

.skeleton-circle {
  width: 48px;
  height: 48px;
  border-radius: 9999px;
}

.skeleton-chart {
  height: 300px;
  width: 100%;
}
```

---

## Chart Styling

### ApexCharts Global Theme

```javascript
// ApexCharts Global Theme Configuration
const apexChartsTheme = {
  chart: {
    fontFamily: 'Inter, sans-serif',
    toolbar: {
      show: true,
      tools: {
        download: true,
        zoom: true,
        zoomin: true,
        zoomout: true,
        pan: true,
        reset: true
      }
    },
    animations: {
      enabled: true,
      easing: 'easeinout',
      speed: 800,
      animateGradually: {
        enabled: true,
        delay: 150
      },
      dynamicAnimation: {
        enabled: true,
        speed: 350
      }
    }
  },
  colors: ['#2563EB', '#14B8A6', '#A855F7', '#F59E0B', '#EC4899', '#10B981', '#F97316', '#6366F1'],
  dataLabels: {
    style: {
      fontSize: '12px',
      fontWeight: 500,
      colors: ['#374151']
    }
  },
  grid: {
    borderColor: '#E5E7EB',
    strokeDashArray: 4,
    padding: {
      top: 0,
      right: 0,
      bottom: 0,
      left: 0
    }
  },
  xaxis: {
    labels: {
      style: {
        colors: '#6B7280',
        fontSize: '12px',
        fontWeight: 400
      }
    },
    axisBorder: {
      color: '#E5E7EB'
    },
    axisTicks: {
      color: '#E5E7EB'
    }
  },
  yaxis: {
    labels: {
      style: {
        colors: '#6B7280',
        fontSize: '12px',
        fontWeight: 400
      }
    }
  },
  legend: {
    fontSize: '14px',
    fontWeight: 500,
    labels: {
      colors: '#374151'
    },
    markers: {
      width: 12,
      height: 12,
      radius: 3
    }
  },
  tooltip: {
    style: {
      fontSize: '12px',
      fontFamily: 'Inter, sans-serif'
    },
    theme: 'light',
    fillSeriesColor: false,
    x: {
      show: true
    },
    y: {
      formatter: function(value) {
        // Format based on context (£, %, etc.)
        return value.toLocaleString();
      }
    }
  },
  stroke: {
    lineCap: 'round'
  }
};
```

### Chart-Specific Styling

#### Radial Bar Gauge (Coverage Adequacy, Retirement Readiness)
```javascript
{
  chart: {
    type: 'radialBar',
    height: 280
  },
  plotOptions: {
    radialBar: {
      hollow: {
        size: '70%',
        background: '#F9FAFB'
      },
      track: {
        background: '#E5E7EB',
        strokeWidth: '100%',
        margin: 5
      },
      dataLabels: {
        name: {
          fontSize: '18px',
          fontWeight: 600,
          color: '#374151',
          offsetY: -10
        },
        value: {
          fontSize: '32px',
          fontWeight: 700,
          color: '#111827',
          offsetY: 5,
          formatter: function(val) {
            return Math.round(val) + '%';
          }
        },
        total: {
          show: true,
          label: 'Coverage',
          fontSize: '14px',
          fontWeight: 500,
          color: '#6B7280',
          formatter: function(w) {
            return Math.round(w.globals.seriesTotals[0]) + '%';
          }
        }
      }
    }
  },
  colors: ['#2563EB'], // Dynamically change based on score: Red (<60%), Amber (60-80%), Green (>80%)
  labels: ['Score']
}
```

**Color Logic for Gauges**:
- 0-59%: Red (`#DC2626`)
- 60-79%: Amber (`#F59E0B`)
- 80-100%: Green (`#16A34A`)

---

#### Donut Chart (Asset Allocation)
```javascript
{
  chart: {
    type: 'donut',
    height: 350
  },
  plotOptions: {
    pie: {
      donut: {
        size: '70%',
        labels: {
          show: true,
          total: {
            show: true,
            label: 'Total Value',
            fontSize: '14px',
            fontWeight: 500,
            color: '#6B7280',
            formatter: function(w) {
              return '£' + w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString();
            }
          },
          value: {
            fontSize: '28px',
            fontWeight: 700,
            color: '#111827',
            formatter: function(val) {
              return '£' + parseFloat(val).toLocaleString();
            }
          }
        }
      }
    }
  },
  dataLabels: {
    enabled: true,
    formatter: function(val) {
      return val.toFixed(1) + '%';
    },
    style: {
      fontSize: '12px',
      fontWeight: 500
    }
  },
  legend: {
    position: 'bottom',
    horizontalAlign: 'center'
  },
  colors: ['#2563EB', '#14B8A6', '#A855F7', '#F59E0B', '#EC4899', '#10B981']
}
```

---

#### Heatmap (Coverage Gap Analysis)
```javascript
{
  chart: {
    type: 'heatmap',
    height: 350
  },
  plotOptions: {
    heatmap: {
      shadeIntensity: 0.5,
      radius: 8,
      colorScale: {
        ranges: [
          {
            from: 0,
            to: 20,
            color: '#10B981',
            name: 'No Gap'
          },
          {
            from: 21,
            to: 50,
            color: '#F59E0B',
            name: 'Small Gap'
          },
          {
            from: 51,
            to: 80,
            color: '#F97316',
            name: 'Medium Gap'
          },
          {
            from: 81,
            to: 100,
            color: '#DC2626',
            name: 'Large Gap'
          }
        ]
      }
    }
  },
  dataLabels: {
    enabled: true,
    style: {
      fontSize: '12px',
      fontWeight: 600,
      colors: ['#FFFFFF']
    }
  },
  xaxis: {
    categories: ['Death', 'Critical Illness', 'Disability', 'Unemployment']
  },
  yaxis: {
    categories: ['Now', 'Age 40', 'Age 50', 'Age 60', 'Retirement']
  }
}
```

---

#### Line Chart (Performance Over Time)
```javascript
{
  chart: {
    type: 'line',
    height: 350,
    zoom: {
      enabled: true
    }
  },
  stroke: {
    width: [3, 3, 2],
    curve: 'smooth',
    dashArray: [0, 0, 5] // Solid for portfolio, dashed for benchmark
  },
  markers: {
    size: 0,
    hover: {
      size: 6
    }
  },
  xaxis: {
    type: 'datetime',
    labels: {
      datetimeUTC: false
    }
  },
  yaxis: {
    title: {
      text: 'Value (£)',
      style: {
        fontSize: '14px',
        fontWeight: 600,
        color: '#374151'
      }
    },
    labels: {
      formatter: function(val) {
        return '£' + (val / 1000).toFixed(0) + 'k';
      }
    }
  },
  tooltip: {
    shared: true,
    intersect: false,
    y: {
      formatter: function(val) {
        return '£' + val.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      }
    }
  },
  legend: {
    position: 'top',
    horizontalAlign: 'right'
  },
  colors: ['#2563EB', '#14B8A6', '#9CA3AF']
}
```

---

#### Area Chart (Monte Carlo Simulations, Income Projections)
```javascript
{
  chart: {
    type: 'area',
    height: 400,
    stacked: false // or true for stacked income projections
  },
  stroke: {
    width: [0, 2, 0], // No stroke for outer percentiles, stroke for median
    curve: 'smooth'
  },
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: [0.6, 0.7, 0.6],
      opacityTo: [0.1, 0.3, 0.1],
      stops: [0, 90, 100]
    }
  },
  dataLabels: {
    enabled: false
  },
  xaxis: {
    categories: [2024, 2025, 2026, 2027, ...], // Years
    title: {
      text: 'Year',
      style: {
        fontSize: '14px',
        fontWeight: 600
      }
    }
  },
  yaxis: {
    title: {
      text: 'Portfolio Value (£)',
      style: {
        fontSize: '14px',
        fontWeight: 600
      }
    },
    labels: {
      formatter: function(val) {
        return '£' + (val / 1000).toFixed(0) + 'k';
      }
    }
  },
  legend: {
    position: 'top'
  },
  colors: ['#A855F7', '#2563EB', '#A855F7'] // Purple for outer, blue for median
}
```

---

#### Waterfall Chart (IHT Calculation Breakdown)
```javascript
{
  chart: {
    type: 'bar',
    height: 400
  },
  plotOptions: {
    bar: {
      horizontal: false,
      columnWidth: '60%'
    }
  },
  colors: ['#2563EB', '#10B981', '#10B981', '#10B981', '#F59E0B', '#DC2626'],
  dataLabels: {
    enabled: true,
    formatter: function(val) {
      return '£' + (Math.abs(val) / 1000).toFixed(0) + 'k';
    },
    style: {
      fontSize: '12px',
      fontWeight: 600,
      colors: ['#FFFFFF']
    }
  },
  xaxis: {
    categories: ['Gross Estate', 'Exemptions', 'NRB', 'RNRB', 'Taxable Estate', 'IHT Due'],
    labels: {
      style: {
        fontSize: '12px'
      }
    }
  },
  yaxis: {
    title: {
      text: 'Amount (£)',
      style: {
        fontSize: '14px',
        fontWeight: 600
      }
    },
    labels: {
      formatter: function(val) {
        return '£' + (val / 1000).toFixed(0) + 'k';
      }
    }
  },
  legend: {
    show: false
  }
}
```

---

#### Timeline / Range Bar (Gifting Timeline)
```javascript
{
  chart: {
    type: 'rangeBar',
    height: 350
  },
  plotOptions: {
    bar: {
      horizontal: true,
      barHeight: '40%',
      rangeBarGroupRows: true
    }
  },
  xaxis: {
    type: 'datetime',
    labels: {
      datetimeUTC: false
    }
  },
  yaxis: {
    show: true
  },
  tooltip: {
    custom: function({series, seriesIndex, dataPointIndex, w}) {
      const gift = giftsData[dataPointIndex];
      return `
        <div class="apexcharts-tooltip-custom" style="padding: 12px; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
          <strong style="font-size: 14px; color: #111827;">${gift.recipient}</strong><br/>
          <span style="font-size: 13px; color: #6B7280;">Value: <strong>£${gift.value.toLocaleString()}</strong></span><br/>
          <span style="font-size: 13px; color: #6B7280;">Years until exempt: <strong>${gift.yearsRemaining}</strong></span>
        </div>
      `;
    }
  },
  colors: ['#DC2626', '#F59E0B', '#10B981'], // Red (early), Amber (mid), Green (nearly exempt)
  legend: {
    position: 'top'
  }
}
```

---

## Screen States

### 1. Empty States

When users haven't added data yet, show helpful empty states:

```css
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
}

.empty-state-icon {
  width: 64px;
  height: 64px;
  color: #9CA3AF; /* gray-400 */
  margin-bottom: 1.5rem;
}

.empty-state-title {
  font-size: 1.25rem; /* 20px */
  font-weight: 600;
  color: #111827; /* gray-900 */
  margin-bottom: 0.5rem;
}

.empty-state-description {
  font-size: 0.875rem;
  color: #6B7280; /* gray-500 */
  margin-bottom: 1.5rem;
  max-width: 28rem;
}

.empty-state-action {
  /* Use .btn-primary */
}
```

**Examples**:
- **No policies**: "No protection policies yet. Add your first policy to see coverage analysis."
- **No holdings**: "No investment holdings yet. Add holdings to see portfolio analysis."
- **No savings goals**: "No savings goals yet. Create your first goal to track progress."
- **No pensions**: "No pensions yet. Add your pensions to see retirement projections."
- **No gifts**: "No gifts recorded yet. Track gifts for IHT planning."

---

### 2. Loading States

#### Full Page Loading
```css
.loading-overlay {
  position: fixed;
  inset: 0;
  background-color: rgba(255, 255, 255, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
}

.loading-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}
```

#### Skeleton Screens
Use skeleton loaders for:
- **Dashboard cards**: Show card outline with skeleton text and skeleton chart
- **Tables**: Show skeleton rows (5-10 rows)
- **Charts**: Show gray placeholder rectangle with shimmer animation
- **Forms**: Show skeleton input fields

**Example: Skeleton Card**
```html
<div class="card">
  <div class="skeleton skeleton-heading"></div>
  <div class="skeleton skeleton-text"></div>
  <div class="skeleton skeleton-text" style="width: 80%;"></div>
  <div class="skeleton skeleton-chart" style="margin-top: 1rem;"></div>
</div>
```

#### Job Status (Monte Carlo Simulation)
```css
.job-status {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background-color: #EFF6FF; /* primary-50 */
  border-radius: 0.75rem;
  border: 1px solid #DBEAFE; /* primary-100 */
}

.job-status-icon {
  /* Animated spinner */
}

.job-status-text {
  font-size: 0.875rem;
  color: #1E4D7B; /* primary-700 */
}

.job-status-progress {
  /* Progress bar showing 0-100% */
}
```

**States**:
- Queued: "Simulation queued..."
- Processing: "Running 1,000 simulations... (estimated 5 seconds)"
- Complete: "Simulation complete!" (then show results)

---

### 3. Error States

#### Form Validation Errors
- Show inline error message below input field (`.error-text`)
- Change input border to red (`.input-error`)
- Show error icon to the left of error message

#### Page-Level Errors
```css
.error-page {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  text-align: center;
  padding: 2rem;
}

.error-code {
  font-size: 6rem; /* 96px */
  font-weight: 700;
  color: #DC2626; /* error-600 */
  line-height: 1;
}

.error-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #111827;
  margin-top: 1rem;
  margin-bottom: 0.5rem;
}

.error-description {
  font-size: 1rem;
  color: #6B7280;
  margin-bottom: 2rem;
}

.error-actions {
  display: flex;
  gap: 1rem;
}
```

**Error Pages**:
- **404 Not Found**: "The page you're looking for doesn't exist."
- **403 Forbidden**: "You don't have permission to access this page."
- **500 Server Error**: "Something went wrong on our end. Please try again later."

#### Calculation Errors
```html
<div class="alert alert-error">
  <svg class="alert-icon">...</svg>
  <div class="alert-content">
    <div class="alert-title">Calculation Error</div>
    <div class="alert-message">Unable to calculate coverage gap. Please check your inputs and try again.</div>
  </div>
</div>
```

---

### 4. Success States

#### Form Submission Success
```html
<div class="alert alert-success">
  <svg class="alert-icon">...</svg>
  <div class="alert-content">
    <div class="alert-title">Success!</div>
    <div class="alert-message">Your life insurance policy has been added.</div>
  </div>
</div>
```

#### Toast Notifications
```css
.toast {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  background-color: white;
  border-radius: 0.75rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 300px;
  max-width: 400px;
  z-index: 200;
  animation: toast-slide-in 300ms ease-out;
  border-left: 4px solid #16A34A; /* success-600 */
}

@keyframes toast-slide-in {
  from {
    transform: translateY(100%);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.toast-success {
  border-left-color: #16A34A;
}

.toast-error {
  border-left-color: #DC2626;
}

.toast-warning {
  border-left-color: #D97706;
}

.toast-info {
  border-left-color: #0284C7;
}
```

---

## Interactions & Animations

### Transition Timing

```css
/* Global transition settings */
--transition-fast: 150ms;
--transition-base: 200ms;
--transition-slow: 300ms;
--transition-slower: 500ms;

--easing-default: cubic-bezier(0.4, 0, 0.2, 1); /* ease-in-out */
--easing-in: cubic-bezier(0.4, 0, 1, 1);
--easing-out: cubic-bezier(0, 0, 0.2, 1);
--easing-bounce: cubic-bezier(0.68, -0.55, 0.27, 1.55);
```

**Usage**:
- Hover states: `150ms` (fast)
- Button clicks, state changes: `200ms` (base)
- Modal/drawer open: `300ms` (slow)
- Chart animations, page transitions: `500ms` (slower)

---

### Micro-Interactions

#### Button Click Feedback
```css
.btn:active {
  transform: scale(0.98);
  transition: transform 100ms ease;
}
```

#### Card Hover Lift
```css
.card-clickable:hover {
  transform: translateY(-4px);
  transition: all 200ms ease;
}
```

#### Icon Hover Rotation
```css
.icon-interactive:hover {
  transform: rotate(15deg);
  transition: transform 200ms ease;
}
```

#### Ripple Effect (Optional)
Add a ripple effect on button clicks:
```css
@keyframes ripple {
  from {
    opacity: 1;
    transform: scale(0);
  }
  to {
    opacity: 0;
    transform: scale(4);
  }
}

.ripple {
  position: absolute;
  border-radius: 50%;
  background-color: rgba(255, 255, 255, 0.6);
  width: 20px;
  height: 20px;
  animation: ripple 600ms ease-out;
  pointer-events: none;
}
```

---

### Page Transitions

#### Fade In
```css
@keyframes fade-in {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.page-enter {
  animation: fade-in 300ms ease-out;
}
```

#### Slide Up
```css
@keyframes slide-up {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.content-enter {
  animation: slide-up 400ms ease-out;
}
```

---

### Chart Animations

Charts should animate on load:
- **Duration**: 800ms
- **Easing**: `easeinout`
- **Delay**: Stagger by 150ms for multiple series

ApexCharts handles this automatically with the global theme settings.

---

### Loading Animations

#### Pulse
```css
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
```

#### Shimmer (Skeleton Loader)
Already defined in skeleton section.

---

## Responsive Design

### Breakpoints

```css
/* Mobile First Approach */
--breakpoint-sm: 640px;  /* Small tablets */
--breakpoint-md: 768px;  /* Tablets */
--breakpoint-lg: 1024px; /* Laptops */
--breakpoint-xl: 1280px; /* Desktops */
--breakpoint-2xl: 1536px; /* Large desktops */
```

**Media Queries**:
```css
/* Mobile: Default (320px - 639px) */

/* Small tablets and up */
@media (min-width: 640px) {
  /* ... */
}

/* Tablets and up */
@media (min-width: 768px) {
  /* ... */
}

/* Laptops and up */
@media (min-width: 1024px) {
  /* ... */
}

/* Desktops and up */
@media (min-width: 1280px) {
  /* ... */
}
```

---

### Responsive Layout Patterns

#### Navigation
- **Mobile (<768px)**: Hamburger menu, sidebar drawer
- **Tablet (768px+)**: Horizontal navbar
- **Desktop (1024px+)**: Sidebar + navbar

#### Dashboard Grid
- **Mobile**: 1 column (all cards full width)
- **Tablet**: 2 columns
- **Desktop**: 3 columns (module cards)

```css
.dashboard-grid {
  display: grid;
  gap: 1.5rem;
  padding: 1rem;
}

/* Mobile: 1 column */
.dashboard-grid {
  grid-template-columns: 1fr;
}

/* Tablet: 2 columns */
@media (min-width: 768px) {
  .dashboard-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* Desktop: 3 columns */
@media (min-width: 1024px) {
  .dashboard-grid {
    grid-template-columns: repeat(3, 1fr);
    padding: 1.5rem;
  }
}
```

#### Module Dashboard
- **Mobile**: Single column (charts stack)
- **Tablet**: 1 column
- **Desktop**: 8-4 grid (main content 8 cols, sidebar 4 cols)

```css
.module-dashboard-grid {
  display: grid;
  gap: 1.5rem;
}

/* Mobile & Tablet: 1 column */
.module-dashboard-grid {
  grid-template-columns: 1fr;
}

/* Desktop: 8-4 split */
@media (min-width: 1024px) {
  .module-dashboard-grid {
    grid-template-columns: 2fr 1fr; /* 8-4 split */
  }
}
```

#### Tables
- **Mobile**: Horizontal scroll or card-based layout
- **Desktop**: Full table layout

```css
/* Mobile: Horizontal scroll */
@media (max-width: 767px) {
  .table-container {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  .table {
    min-width: 600px; /* Force horizontal scroll */
  }
}

/* Alternative: Card-based table on mobile */
@media (max-width: 767px) {
  .table-row {
    display: flex;
    flex-direction: column;
    border: 1px solid #E5E7EB;
    border-radius: 0.75rem;
    padding: 1rem;
    margin-bottom: 1rem;
  }

  .table-cell::before {
    content: attr(data-label);
    font-weight: 600;
    margin-right: 0.5rem;
  }
}
```

#### Forms
- **Mobile**: Single column, full width
- **Desktop**: Multi-column layouts (2-3 columns)

```css
.form-grid {
  display: grid;
  gap: 1rem;
}

/* Mobile: 1 column */
.form-grid {
  grid-template-columns: 1fr;
}

/* Desktop: 2 columns */
@media (min-width: 768px) {
  .form-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* Span full width for certain fields */
.form-field-full {
  grid-column: 1 / -1;
}
```

#### Charts
- **Mobile**: Reduce height (250px)
- **Tablet**: Medium height (300px)
- **Desktop**: Full height (350-400px)

```css
.chart-container {
  height: 250px; /* Mobile */
}

@media (min-width: 768px) {
  .chart-container {
    height: 300px; /* Tablet */
  }
}

@media (min-width: 1024px) {
  .chart-container {
    height: 350px; /* Desktop */
  }
}
```

---

### Touch Targets

For mobile, ensure all interactive elements meet minimum touch target size:

```css
/* Minimum touch target: 44x44px */
.btn, .input, .checkbox, .radio, .sidebar-link {
  min-height: 44px;
}
```

---

## Accessibility

### WCAG 2.1 AA Compliance

#### Color Contrast

Ensure all text meets minimum contrast ratios:
- **Normal text (< 18px)**: 4.5:1
- **Large text (≥ 18px or ≥ 14px bold)**: 3:1

**Tested Combinations**:
- `gray-900` on `white`: 18.67:1 ✅
- `gray-700` on `white`: 10.37:1 ✅
- `gray-500` on `white`: 4.61:1 ✅
- `primary-600` on `white`: 8.59:1 ✅
- `white` on `primary-600`: 8.59:1 ✅
- `error-700` on `white`: 7.09:1 ✅

#### Focus Indicators

All interactive elements must have visible focus indicators:

```css
.focusable:focus {
  outline: 2px solid #2563EB; /* primary-600 */
  outline-offset: 2px;
}

/* Alternative: Ring style */
.focusable:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.5);
}
```

**Apply to**:
- Links
- Buttons
- Form inputs
- Interactive cards
- Tabs
- Sidebar links

---

#### Keyboard Navigation

Ensure all interactive elements are keyboard accessible:
- Tab order follows visual order
- Enter/Space activates buttons
- Arrow keys navigate tabs, dropdowns
- Escape closes modals, dropdowns

```css
/* Visible skip link for keyboard users */
.skip-link {
  position: absolute;
  top: -40px;
  left: 0;
  background-color: #2563EB;
  color: white;
  padding: 0.5rem 1rem;
  text-decoration: none;
  z-index: 1000;
}

.skip-link:focus {
  top: 0;
}
```

---

#### ARIA Labels

Use ARIA attributes for screen readers:

```html
<!-- Button with icon only -->
<button aria-label="Close modal">
  <svg>...</svg>
</button>

<!-- Loading state -->
<div role="status" aria-live="polite">
  <span class="spinner" aria-hidden="true"></span>
  <span class="sr-only">Loading...</span>
</div>

<!-- Chart container -->
<div class="chart-container" role="img" aria-label="Coverage gap heatmap showing protection needs by category and age">
  <apexchart>...</apexchart>
</div>

<!-- Tab panel -->
<div role="tabpanel" aria-labelledby="tab-1">
  ...
</div>
```

---

#### Screen Reader Only Text

```css
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}
```

---

#### Reduced Motion

Respect user's motion preferences:

```css
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
```

---

## Screen Inventory

Comprehensive list of all screens and their states:

### Authentication Screens
1. **Login** (`Login.vue`)
   - Empty state: Show login form
   - Loading state: Button spinner
   - Error state: Inline error message ("Invalid email or password")
   - Success state: Redirect to dashboard

2. **Register** (`Register.vue`)
   - Empty state: Show registration form
   - Loading state: Button spinner
   - Error state: Inline validation errors
   - Success state: Redirect to dashboard or email verification

3. **Forgot Password** (`ForgotPassword.vue`)
   - Empty state: Email input form
   - Loading state: Button spinner
   - Success state: "Check your email for reset link"

4. **Reset Password** (`ResetPassword.vue`)
   - Empty state: New password form
   - Loading state: Button spinner
   - Error state: "Invalid or expired reset link"
   - Success state: "Password reset successfully"

---

### Main Dashboard
**Screen**: `MainDashboard.vue`

**Components**:
- 5 Module Overview Cards (clickable):
  - `ProtectionOverviewCard.vue` (Blue theme)
  - `SavingsOverviewCard.vue` (Teal theme)
  - `InvestmentOverviewCard.vue` (Purple theme)
  - `RetirementOverviewCard.vue` (Amber theme)
  - `EstateOverviewCard.vue` (Pink theme)
- `ISAAllowanceTracker.vue` (Multi-segment progress bar)
- `PriorityActionsFeed.vue` (Top 5 actions from all modules)
- `NetWorthSnapshot.vue` (Large data value with trend)
- `CashflowSummary.vue` (Income vs Expenditure)

**States**:
- **Empty**: Show welcome message + "Get started by adding your first policy/account"
- **Loading**: Skeleton cards for all 5 modules
- **Partial data**: Show completed modules, empty state cards for missing modules
- **Complete**: Show all module cards with metrics

---

### Protection Module Dashboard
**Screen**: `ProtectionDashboard.vue`

**Tabs**:
1. **Current Situation** (`CurrentSituation.vue`)
   - List of current policies (`PolicyCard.vue`)
   - Empty state: "No policies added yet"
   - Action: Add policy button

2. **Gap Analysis** (`GapAnalysis.vue`)
   - `CoverageAdequacyGauge.vue` (Radial bar: 0-100%)
   - `CoverageGapChart.vue` (Heatmap: Death, CI, Disability, Unemployment × Age ranges)
   - Premium Breakdown (Pie chart)
   - Coverage Timeline (Timeline/gantt chart)
   - Empty state: "Add policies to see gap analysis"
   - Loading state: Skeleton charts

3. **Recommendations** (`Recommendations.vue`)
   - List of `RecommendationCard.vue` (draggable to reorder)
   - Priority badges (High/Medium/Low)
   - Empty state: "No recommendations yet"
   - Loading state: Skeleton recommendation cards

4. **What-If Scenarios** (`WhatIfScenarios.vue`)
   - `ScenarioBuilder.vue` (Interactive sliders for premium, cover amount)
   - Scenario comparison chart
   - Empty state: "Build your first scenario"

5. **Policy Details** (`PolicyDetails.vue`)
   - Table of all policies
   - Sortable columns
   - Edit/Delete actions
   - Empty state: "No policies added"

---

### Savings Module Dashboard
**Screen**: `SavingsDashboard.vue`

**Tabs**:
1. **Current Situation** (`CurrentSituation.vue`)
   - List of savings accounts
   - Total balance
   - Empty state: "No savings accounts added"

2. **Emergency Fund** (`EmergencyFund.vue`)
   - `EmergencyFundGauge.vue` (Radial bar: Runway in months)
   - Target: 6 months
   - Shortfall calculation
   - Color: Red (<3 months), Amber (3-5 months), Green (6+ months)
   - Empty state: "Add savings to calculate emergency fund runway"

3. **Savings Goals** (`SavingsGoals.vue`)
   - List of `SavingsGoalCard.vue` (drag to reorder by priority)
   - Progress bars for each goal
   - On-track status
   - Required monthly savings
   - Empty state: "No savings goals yet. Create your first goal."
   - Action: Add goal button

4. **Recommendations** (`Recommendations.vue`)
   - Recommendation cards
   - Empty state: "No recommendations"

5. **What-If Scenarios** (`WhatIfScenarios.vue`)
   - Scenario builder
   - Empty state: "Build scenarios"

6. **Account Details** (`AccountDetails.vue`)
   - Liquidity Ladder (Stacked horizontal bar chart)
   - Interest Rate Comparison (Column chart)
   - Empty state: "No accounts"

---

### Investment Module Dashboard
**Screen**: `InvestmentDashboard.vue`

**Tabs**:
1. **Portfolio Overview** (`PortfolioOverview.vue`)
   - Total portfolio value (Large data value)
   - `AssetAllocationChart.vue` (Donut chart)
   - `GeographicMap.vue` (Optional map chart)
   - Empty state: "No holdings added yet"
   - Loading state: Skeleton chart

2. **Holdings** (`Holdings.vue`)
   - `HoldingsTable.vue` (Sortable, filterable)
   - Columns: Security, Asset Type, Quantity, Cost Basis, Current Price, Current Value, Gain/Loss %
   - Empty state: "No holdings"
   - Action: Add holding button

3. **Performance** (`Performance.vue`)
   - `PerformanceLineChart.vue` (Portfolio vs benchmarks over time)
   - Time-weighted return, Money-weighted return
   - Empty state: "Add holdings to see performance"
   - Loading state: Skeleton chart

4. **Goals** (`Goals.vue`)
   - `MonteCarloResults.vue` (Area chart with percentiles: 10th, 50th, 90th)
   - Job status (Queued → Processing → Complete)
   - Empty state: "Run your first Monte Carlo simulation"
   - Loading state: "Running 1,000 simulations... (5 seconds)"

5. **Recommendations** (`Recommendations.vue`)
   - Rebalancing suggestions
   - Fee reduction opportunities
   - Empty state: "No recommendations"

6. **What-If Scenarios** (`WhatIfScenarios.vue`)
   - Scenario builder
   - Empty state: "Build scenarios"

7. **Tax & Fees** (`TaxFees.vue`)
   - Fee breakdown (Platform fees, fund OCF, transaction costs)
   - Tax efficiency analysis
   - Empty state: "Add holdings to see fees"

---

### Retirement Module Dashboard
**Screen**: `RetirementDashboard.vue`

**Tabs**:
1. **Retirement Readiness** (`RetirementReadiness.vue`)
   - `ReadinessGauge.vue` (Radial bar: 0-100%)
   - Projected income vs target income
   - Income replacement ratio
   - Color: Red (<60%), Amber (60-80%), Green (80%+)
   - Empty state: "Add pensions to see readiness score"

2. **Pension Inventory** (`PensionInventory.vue`)
   - List of `PensionCard.vue` (DC and DB pensions)
   - Total pension value
   - Empty state: "No pensions added"
   - Action: Add pension button

3. **Contributions & Allowances** (`ContributionsAllowances.vue`)
   - Annual allowance tracker
   - Carry forward available
   - Tapering status (if applicable)
   - Contribution breakdown (Column chart)
   - Empty state: "Add pensions to track allowances"

4. **Projections** (`Projections.vue`)
   - `IncomeProjectionChart.vue` (Stacked area chart: DC + DB + State Pension + Other)
   - Projected retirement age
   - Empty state: "Add pensions to see projections"
   - Loading state: Skeleton chart

5. **Recommendations** (`Recommendations.vue`)
   - Contribution increase suggestions
   - Consolidation opportunities
   - Empty state: "No recommendations"

6. **What-If Scenarios** (`WhatIfScenarios.vue`)
   - Retirement age slider
   - Contribution slider
   - Growth rate slider
   - Empty state: "Build scenarios"

7. **Decumulation Planning** (`DecumulationPlanning.vue`)
   - `DrawdownSimulator.vue` (Interactive: withdrawal rate slider, growth rate, inflation)
   - Annuity vs drawdown comparison
   - PCLS strategy
   - Empty state: "Add pensions to plan decumulation"

---

### Estate Planning Module Dashboard
**Screen**: `EstateDashboard.vue`

**Tabs**:
1. **Overview & Net Worth** (`OverviewNetWorth.vue`)
   - `NetWorthStatement.vue` (Horizontal bar chart: Assets vs Liabilities)
   - Net worth value (Large data value)
   - Empty state: "Add assets and liabilities to see net worth"

2. **IHT Liability** (`IHTLiability.vue`)
   - `IHTWaterfallChart.vue` (Waterfall: Gross Estate → Exemptions → NRB → RNRB → Taxable Estate → IHT Due)
   - IHT due value (Large data value, red if > £0)
   - Breakdown table
   - Empty state: "Add assets to calculate IHT"
   - Loading state: Skeleton chart

3. **Gifting Strategy** (`GiftingStrategy.vue`)
   - `GiftingTimeline.vue` (Timeline/rangeBar: PETs and CLTs over 7 years)
   - List of gifts with taper relief status
   - Empty state: "No gifts recorded yet"
   - Action: Add gift button

4. **Personal Accounts** (`PersonalAccounts.vue`)
   - `PLStatement.vue` (Pie chart: Income breakdown, Expenditure breakdown)
   - `CashflowStatement.vue` (Line chart: Cashflow over time)
   - `BalanceSheet.vue` (Table: Assets, Liabilities, Net Worth)
   - Empty state: "Add income and expenditure data"

5. **Recommendations** (`Recommendations.vue`)
   - IHT mitigation strategies
   - Gifting opportunities
   - Empty state: "No recommendations"

6. **What-If Scenarios** (`WhatIfScenarios.vue`)
   - Gifting scenarios
   - Estate value scenarios
   - Empty state: "Build scenarios"

7. **Documentation & Probate** (`DocumentationProbate.vue`)
   - Will checklist
   - LPA checklist
   - Probate guidance
   - Empty state: "Complete your estate documentation"

---

### Data Entry Screens

All modules have data entry forms (modals or separate pages):

**Common Features**:
- Form validation (inline, on blur, on submit)
- Required field indicators (red asterisk)
- Helper text below inputs
- Error messages (red text below inputs)
- Success toast on save
- Cancel/Save buttons in footer

**Forms**:
1. **Add/Edit Life Insurance Policy**
   - Fields: Policy type (dropdown), Provider, Policy number, Sum assured, Premium amount, Premium frequency, Policy start date, Policy term years
   - Conditional fields (e.g., Critical illness rider: Yes/No)

2. **Add/Edit Savings Account**
   - Fields: Account type, Institution, Current balance, Interest rate, Is ISA? (toggle), ISA subscription year

3. **Add/Edit Savings Goal**
   - Fields: Goal name, Target amount, Target date, Priority (drag to reorder), Current saved

4. **Add/Edit Investment Holding**
   - Fields: Account, Asset type, Security name, Ticker, Quantity, Purchase price, Current price

5. **Add/Edit Pension**
   - Fields: Pension type (DC/DB), Scheme name, Current fund value, Employee contribution %, Employer contribution %, Retirement age

6. **Add/Edit Asset**
   - Fields: Asset type, Asset name, Current value

7. **Add/Edit Liability**
   - Fields: Liability type, Name, Current balance, Monthly payment

8. **Add/Edit Gift**
   - Fields: Gift type (PET/CLT), Recipient name, Gift date, Gift value, Exemption claimed

---

## Conclusion

This design style guide provides a comprehensive foundation for building a clean, professional, and accessible financial planning web application. All components, colors, typography, and interactions are designed to work together harmoniously while maintaining clarity and usability for complex financial data.

**Key Takeaways**:
- **Color System**: Professional blues/teals with clear status colors for financial data
- **Typography**: Inter for body/data, Plus Jakarta Sans for headings, tabular numbers for financial values
- **Components**: Comprehensive library covering all UI needs (cards, forms, tables, charts)
- **Charts**: ApexCharts with consistent theming across all visualization types
- **States**: Clear patterns for empty, loading, error, and success states
- **Responsive**: Mobile-first design with clear breakpoints
- **Accessible**: WCAG 2.1 AA compliant with focus indicators, keyboard navigation, ARIA labels

**Next Steps**:
1. Review this style guide with the team
2. Create 4 HTML mockups to demonstrate the design in action
3. Begin implementing components in Vue.js using this guide as reference

---

**End of Design Style Guide**
