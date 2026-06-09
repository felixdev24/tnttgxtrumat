---
name: Vietnamese Catholic Youth Design System
colors:
  surface: '#f8f9fa'
  surface-dim: '#d9dadb'
  surface-bright: '#f8f9fa'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f3f4f5'
  surface-container: '#edeeef'
  surface-container-high: '#e7e8e9'
  surface-container-highest: '#e1e3e4'
  on-surface: '#191c1d'
  on-surface-variant: '#42474d'
  inverse-surface: '#2e3132'
  inverse-on-surface: '#f0f1f2'
  outline: '#73777e'
  outline-variant: '#c2c7ce'
  surface-tint: '#42617d'
  primary: '#42617d'
  on-primary: '#ffffff'
  primary-container: '#a7c7e7'
  on-primary-container: '#34536f'
  inverse-primary: '#aacaea'
  secondary: '#636037'
  on-secondary: '#ffffff'
  secondary-container: '#e7e1ae'
  on-secondary-container: '#67643b'
  tertiary: '#c00008'
  on-tertiary: '#ffffff'
  tertiary-container: '#ffb0a5'
  on-tertiary-container: '#a60006'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#cde5ff'
  primary-fixed-dim: '#aacaea'
  on-primary-fixed: '#001d32'
  on-primary-fixed-variant: '#294964'
  secondary-fixed: '#eae4b1'
  secondary-fixed-dim: '#cdc897'
  on-secondary-fixed: '#1e1c00'
  on-secondary-fixed-variant: '#4b4822'
  tertiary-fixed: '#ffdad5'
  tertiary-fixed-dim: '#ffb4a9'
  on-tertiary-fixed: '#410001'
  on-tertiary-fixed-variant: '#930005'
  background: '#f8f9fa'
  on-background: '#191c1d'
  surface-variant: '#e1e3e4'
typography:
  display-lg:
    fontFamily: Quicksand
    fontSize: 48px
    fontWeight: '700'
    lineHeight: '1.2'
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Quicksand
    fontSize: 32px
    fontWeight: '700'
    lineHeight: '1.3'
  headline-lg-mobile:
    fontFamily: Quicksand
    fontSize: 28px
    fontWeight: '700'
    lineHeight: '1.3'
  title-md:
    fontFamily: Quicksand
    fontSize: 24px
    fontWeight: '600'
    lineHeight: '1.4'
  body-lg:
    fontFamily: Nunito
    fontSize: 18px
    fontWeight: '400'
    lineHeight: '1.6'
  body-md:
    fontFamily: Nunito
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.6'
  label-bold:
    fontFamily: Quicksand
    fontSize: 14px
    fontWeight: '700'
    lineHeight: '1.2'
rounded:
  sm: 0.5rem
  DEFAULT: 1rem
  md: 1.5rem
  lg: 2rem
  xl: 3rem
  full: 9999px
spacing:
  unit: 8px
  container-max: 1200px
  gutter: 24px
  margin-mobile: 16px
  margin-desktop: 40px
  stack-sm: 8px
  stack-md: 16px
  stack-lg: 32px
  section-gap: 80px
---

## Brand & Style

This design system captures the spirit of the Thiếu Nhi Thánh Thể (TNTT) movement: a harmonious blend of youthful joy, spiritual discipline, and Vietnamese heritage. The aesthetic is "Apple-meets-Duolingo"—combining the premium, clean finish of high-end hardware with the playful, gamified approachability of modern education apps.

The visual narrative focuses on "Disciplined Playfulness." While the interface uses soft shapes and bright colors to welcome children and teenagers, the underlying structure is rigid and organized to reflect the organizational discipline of the movement. The goal is to evoke a sense of belonging, trust, and excitement for faith-based learning.

**Design Movements:**
- **Glassmorphism:** Used for cards and overlays to provide a sense of depth and modernity.
- **Minimalism:** Clean white space to ensure the spiritual message remains the focus.
- **Tactile / Playful:** Large, "squishy" buttons and high-contrast elevations that feel interactive and friendly.

## Colors

The palette is rooted in Catholic symbolism filtered through a modern, pastel lens. 

- **Pastel Blue (Primary):** Represents the Virgin Mary and the calm, trustworthy nature of the organization. Used for primary branding, headers, and navigation.
- **Soft Yellow (Secondary):** Represents the light of the Eucharist and joyful service. Used for highlights, achievement badges, and secondary backgrounds.
- **TNTT Red (Accent):** The traditional color of the TNTT scarf (Khăn Quàng), symbolizing the sacrifice of Christ and the fire of the Holy Spirit. Reserved for high-priority Call-to-Actions (CTAs), critical alerts, and significant religious milestones.
- **Neutral/Base:** We utilize a "Pure White" strategy for content areas to maintain a professional look, supported by "Very Light Gray" for structural depth and section separation.

## Typography

This design system uses rounded, humanist typefaces to maintain the "cute and professional" balance. 

**Quicksand** is our primary display face. Its geometric yet rounded terminals feel contemporary and approachable, perfect for Vietnamese diacritics which can often look cluttered in sharper fonts. Use this for all headings and interactive labels.

**Nunito** is our workhorse for body text. It mirrors the rounded nature of Quicksand but offers better legibility at smaller sizes and longer paragraphs, ensuring that religious teachings and organizational news are easy to read for all age groups.

All typography should prioritize generous line-heights to avoid a "cramped" feeling, maintaining the airy Apple-inspired aesthetic.

## Layout & Spacing

The layout follows a **Fluid Grid** model with a strictly defined max-width for desktop to maintain a professional, "contained" feel. 

- **Grid:** A 12-column system is used for desktop, collapsing to 4 columns for mobile. 
- **Rhythm:** We use an 8px base unit. Component padding is consistently generous (usually 24px or 32px) to support the "soft" shape language.
- **Safe Areas:** Large margins are employed to give elements room to "breathe," emulating the premium whitespace found in Apple interfaces.
- **Reflow:** On mobile, glassmorphic cards should transition to full-width or slightly inset with rounded corners, ensuring the "Floating Action Button" (FAB) remains easily accessible in the bottom right corner for primary actions like "Tham Gia" (Join) or "Khai Báo" (Check-in).

## Elevation & Depth

Visual hierarchy is established using three distinct techniques to create a layered, tactile environment:

1.  **Glassmorphism (Medium Elevation):** Main content cards use a semi-transparent white background (opacity 70-80%) with a `backdrop-filter: blur(20px)`. This is paired with a thin, 1px white border at low opacity to define the edge.
2.  **Diffuse Shadows (High Elevation):** Floating Action Buttons and primary modals use "Apple-style" shadows. These are very soft, with a high blur radius (30px+) and low opacity (10-15%), often tinted with a hint of the Primary Blue to avoid a "dirty" look.
3.  **Tonal Backgrounds (Base Elevation):** Sections are divided by alternating between pure white and the very light gray/blue base colors, rather than using heavy lines or borders.

## Shapes

The shape language is the core of this design system's "friendliness." Sharp corners are strictly prohibited.

- **Extreme Roundedness:** A base `border-radius` of 24px is applied to all standard cards and containers.
- **Pill Shapes:** Small buttons, chips, and tags must use a full pill-shape (radius 999px).
- **Icons:** Icons should be "chunky" with rounded caps and joins. Avoid thin, clinical line icons; instead, use filled or heavy-stroke styles that look like they belong in a friendly illustration.

## Components

**Buttons (Nút bấm):**
- **Primary:** TNTT Red with a 4px bottom "shadow-border" (Duolingo style) that gives a tactile, pressable look.
- **Secondary:** Pastel Blue, flat or with glassmorphism, for less urgent actions.
- **Shape:** Rounded-xl or Pill-shaped.

**Cards (Thẻ thông tin):**
- Must utilize glassmorphism effects on light-colored backgrounds.
- 24px+ border radius.
- Internal padding should be at least 24px to keep content from feeling cramped.

**Lists & Inputs:**
- Input fields should have a soft gray background and a 16px radius.
- On focus, the border should animate to the Primary Pastel Blue with a soft outer glow.

**Floating Action Button (FAB):**
- Circular, vibrant TNTT Red, floating in the bottom-right corner.
- Used for the most common action (e.g., "Khai báo sinh hoạt" or "Cầu nguyện").

**Illustrations & Icons:**
- Incorporate "Chibi" or cartoon-style versions of TNTT characters (youth in uniform) and religious symbols. 
- All illustrations should follow the pastel color palette to ensure they integrate seamlessly with the UI.