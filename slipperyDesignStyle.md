# **FPS Financial Planning System – Design Style Guide (v2.0)**

**Status:** Final – Ready for implementation
**Revision Date:** 2025-11-20
**Purpose:** Create a clean, trustworthy, high-contrast UI framework for a financial planning system with strong data clarity.

---

# **1. Design Principles**

### **1. Clarity First (Data Visibility Above All)**

Layout, spacing, and colour must focus attention on **figures, charts, and key metrics**.

### **2. Trust & Professionalism**

Colours are **solid, saturated, non-pastel**. Typography creates confidence. No playful or soft visuals.

### **3. Consistency Across All Components**

Strict use of tokens for spacing, colour, typography, shadows, charts, and interactive elements.

### **4. Low Cognitive Load**

Financial data is complex—use clear hierarchy, spacing, and progressive disclosure.

### **5. Accessibility**

* Minimum contrast WCAG AA
* Tabular numbers for financial values
* Clear focus states

---

# **2. Colour System (NO Pastels — All Solid Tones)**

This palette replaces the original pastel-heavy palette.
All colours are deep, solid, and suitable for financial dashboards.

---

## **2.1 Primary Palette — “Trust Blue”**

Professional blue shades used by major financial institutions.

| Token         | Hex         | Usage                        |
| ------------- | ----------- | ---------------------------- |
| `primary-900` | **#071A2C** | Headings, navbar text        |
| `primary-700` | **#0E3A66** | Hover states, emphasis       |
| `primary-600` | **#1257A0** | Primary CTAs                 |
| `primary-500` | **#1A75CE** | Icons, secondary interactive |
| `primary-400` | **#2D8FEF** | Chart series, accents        |

---

## **2.2 Secondary Palette — “Institutional Teal”**

Used for alternative actions and data series.

| Token           | Hex         | Usage             |
| --------------- | ----------- | ----------------- |
| `secondary-900` | **#043B38** | Deep accents      |
| `secondary-700` | **#0A5F58** | Hover, borders    |
| `secondary-600` | **#0F7F76** | Secondary actions |
| `secondary-500` | **#13A89C** | Chart series      |
| `secondary-400` | **#17C2B4** | Highlights        |

---

## **2.3 Accent Colours**

### **Amber (Warnings)**

| Token       | Hex         |
| ----------- | ----------- |
| `amber-800` | **#8C5307** |
| `amber-700` | **#B86D09** |
| `amber-600` | **#D98508** |

### **Error Red**

| Token       | Hex         |
| ----------- | ----------- |
| `error-800` | **#7A0F0F** |
| `error-700` | **#A31616** |
| `error-600` | **#C92222** |

### **Success Green**

| Token         | Hex         |
| ------------- | ----------- |
| `success-800` | **#0B5328** |
| `success-700` | **#0F6F36** |
| `success-600` | **#149447** |

---

## **2.4 Gray / Neutral System**

All neutrals are solid, non-pastel.

| Token      | Hex         | Usage               |
| ---------- | ----------- | ------------------- |
| `gray-900` | **#111111** | Headings            |
| `gray-800` | **#1F1F1F** | Body text           |
| `gray-700` | **#353535** | Secondary text      |
| `gray-600` | **#4A4A4A** | Labels              |
| `gray-500` | **#6A6A6A** | Placeholder         |
| `gray-400` | **#8C8C8C** | Borders             |
| `gray-300` | **#B5B5B5** | Lines               |
| `gray-200` | **#D6D6D6** | Background surfaces |
| `white`    | **#FFFFFF** | Cards, modals       |

---

## **2.5 Chart Palette (High-Contrast, Professional)**

Replace previous palette with solid, bank-grade colours:

```
chart-1:  #1257A0  
chart-2:  #0F7F76  
chart-3:  #7A2AA8  
chart-4:  #D98508  
chart-5:  #C92222  
chart-6:  #149447  
chart-7:  #F05A28  
chart-8:  #4C52F5  
```

---

# **3. Typography**

### **Font Strategy**

| Use              | Font                                                                  |
| ---------------- | --------------------------------------------------------------------- |
| **Headings**     | **IBM Plex Sans** (professional, modern, enterprise-grade)            |
| **Body text**    | **Inter** (neutral, readable, clean)                                  |
| **Numeric data** | **JetBrains Mono** (monospaced, perfect alignment for £, %, decimals) |

---

## **3.1 Typeface Imports**

```css
font-family: 'IBM Plex Sans', sans-serif;
font-family: 'Inter', sans-serif;
font-family: 'JetBrains Mono', monospace;
```

---

## **3.2 Typographic Scale**

### **H1 — Page Title**

```
font-family: 'IBM Plex Sans';
font-size: 32px;
font-weight: 600;
color: #111111;
```

### **H2 — Section Title**

```
font-size: 26px;
font-weight: 600;
color: #1F1F1F;
```

### **H3 — Subsection**

```
font-size: 20px;
font-weight: 600;
color: #353535;
```

### **Body Text**

```
font-family: 'Inter';
font-size: 16px;
line-height: 1.55;
color: #353535;
```

### **Labels**

```
font-size: 14px;
font-weight: 500;
color: #4A4A4A;
```

### **Numeric Values**

```
font-family: 'JetBrains Mono';
font-feature-settings: "tnum", "lnum";
letter-spacing: -0.01em;
```

---

# **4. Spacing & Layout Tokens**

Using an 8px baseline grid.

| Token      | Value |
| ---------- | ----- |
| `space-1`  | 4px   |
| `space-2`  | 8px   |
| `space-3`  | 12px  |
| `space-4`  | 16px  |
| `space-5`  | 20px  |
| `space-6`  | 24px  |
| `space-8`  | 32px  |
| `space-12` | 48px  |
| `space-16` | 64px  |

### Rules

* Cards: 24–32px internal padding
* Sections: 48px top/bottom
* Dashboard grids: 24px gaps

---

# **5. Component System**

## **5.1 Buttons**

### Primary Button

```
background: #1257A0;
color: white;
border-radius: 8px;
padding: 10px 20px;
font-weight: 500;
```

Hover:

```
background: #0E3A66;
```

### Secondary Button

```
background: white;
border: 1px solid #8C8C8C;
color: #1257A0;
```

### Danger Button

```
background: #C92222;
color: white;
```

---

# **6. Cards**

```
background: white;
border-radius: 12px;
padding: 24px;
border: 1px solid #D6D6D6;
box-shadow: 0 1px 3px rgba(0,0,0,0.08);
```

Hover:

```
box-shadow: 0 4px 12px rgba(0,0,0,0.12);
```

---

# **7. Form Inputs**

```
border: 1px solid #8C8C8C;
border-radius: 8px;
padding: 10px 14px;
font-size: 16px;
color: #1F1F1F;
```

Focus:

```
border-color: #1257A0;
box-shadow: 0 0 0 3px rgba(18,87,160,0.25);
```

Error state:

```
border-color: #C92222;
```

---

# **8. Tables**

```
border: 1px solid #D6D6D6;
border-radius: 12px;
```

Header:

```
background: #F5F5F5;
font-weight: 600;
font-size: 12px;
letter-spacing: 0.05em;
```

Cells:

```
font-size: 14px;
color: #353535;
```

Numeric:

```
font-family: 'JetBrains Mono';
text-align: right;
```

---

# **9. Chart Styling**

Global ApexCharts theme overrides:

```
font-family: 'Inter';
colors: [chart palette tokens above];
grid border: #D6D6D6;
axis labels: #4A4A4A;
tooltip background: white;
tooltip text: #111111;
```

### Gauge Colour Logic

* <60% → **#C92222**
* 60–80% → **#D98508**
* > 80% → **#149447**

---

# **10. Navigation**

### Top Navbar

```
height: 64px;
background: white;
border-bottom: 1px solid #D6D6D6;
color: #111111;
```

### Sidebar

```
background: #FFFFFF;
border-right: 1px solid #D6D6D6;
width: 256px;
```

Active link:

```
color: #1257A0;
background: rgba(18,87,160,0.10);
border-left: 3px solid #1257A0;
```

---

# **11. Screen States**

## Empty States

* Neutral icon
* Title (IBM Plex Sans 20px)
* Description (Inter 14px)
* CTA button

## Loading States

* Skeletons (gray-300 → gray-200 shimmer)
* Optional center spinner

## Error States

* Red border-left
* Strong heading
* Clear resolution advice

---

# **12. Interactions**

* Hover transitions: **150ms**
* Modal transitions: **300ms**
* Chart animations: **800ms**
* Button press scale: **0.98**

---

# **13. Responsive Rules**

### Breakpoints

* `sm: 640px`
* `md: 768px`
* `lg: 1024px`
* `xl: 1280px`
* `xl2: 1536px`

Dashboard rules:

* 1 column mobile
* 2 columns tablet
* 3–4 columns desktop

---