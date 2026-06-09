---
name: Vietnamese Catholic Youth Dark Mode
colors:
  surface: '#131313'
  surface-dim: '#131313'
  surface-bright: '#393939'
  surface-container-lowest: '#0e0e0e'
  surface-container-low: '#1c1b1b'
  surface-container: '#201f1f'
  surface-container-high: '#2a2a2a'
  surface-container-highest: '#353534'
  on-surface: '#e5e2e1'
  on-surface-variant: '#e4beba'
  inverse-surface: '#e5e2e1'
  inverse-on-surface: '#313030'
  outline: '#ab8986'
  outline-variant: '#5b403e'
  surface-tint: '#ffb3ad'
  primary: '#ffb3ad'
  on-primary: '#68000a'
  primary-container: '#ff5451'
  on-primary-container: '#5c0008'
  inverse-primary: '#b91a24'
  secondary: '#a4c9ff'
  on-secondary: '#00315d'
  secondary-container: '#0267b8'
  on-secondary-container: '#d6e5ff'
  tertiary: '#e2c62d'
  on-tertiary: '#393000'
  tertiary-container: '#c5ab02'
  on-tertiary-container: '#4a3f00'
  error: '#ffb4ab'
  on-error: '#690005'
  error-container: '#93000a'
  on-error-container: '#ffdad6'
  primary-fixed: '#ffdad7'
  primary-fixed-dim: '#ffb3ad'
  on-primary-fixed: '#410004'
  on-primary-fixed-variant: '#930013'
  secondary-fixed: '#d4e3ff'
  secondary-fixed-dim: '#a4c9ff'
  on-secondary-fixed: '#001c39'
  on-secondary-fixed-variant: '#004883'
  tertiary-fixed: '#ffe24c'
  tertiary-fixed-dim: '#e2c62d'
  on-tertiary-fixed: '#211b00'
  on-tertiary-fixed-variant: '#524600'
  background: '#131313'
  on-background: '#e5e2e1'
  surface-variant: '#353534'
typography:
  headline-lg:
    fontFamily: Quicksand
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
  headline-lg-mobile:
    fontFamily: Quicksand
    fontSize: 26px
    fontWeight: '700'
    lineHeight: 32px
  headline-md:
    fontFamily: Quicksand
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 30px
  body-lg:
    fontFamily: Quicksand
    fontSize: 18px
    fontWeight: '500'
    lineHeight: 28px
  body-md:
    fontFamily: Quicksand
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  label-md:
    fontFamily: Quicksand
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
    letterSpacing: 0.02em
  label-sm:
    fontFamily: Quicksand
    fontSize: 12px
    fontWeight: '700'
    lineHeight: 16px
    letterSpacing: 0.05em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 4px
  xs: 8px
  sm: 16px
  md: 24px
  lg: 32px
  xl: 48px
  gutter: 16px
  margin-mobile: 20px
  margin-desktop: 64px
---

## Brand & Style

This design system translates the joyful, faith-filled spirit of the Vietnamese Eucharistic Youth Movement (TNTT) into a sophisticated dark interface. The brand personality is **warm, communal, and spiritually vibrant**. By moving from light pastels to a deep-sea and charcoal foundation, the system evokes a sense of evening prayer and vigil while maintaining a "cute" and approachable energy through rounded forms and glowing accents.

The aesthetic follows a **Glassmorphic-Modern** hybrid style. It uses semi-transparent layers to suggest depth and light, symbolizing the "Light of Christ" breaking through the darkness. The professional aspect is maintained through precise typography and clean, structured layouts, ensuring the interface feels like a reliable tool for youth leaders and members alike.

## Colors

The palette transitions from daylight pastels to "Vibrant Midnight" tones. 

- **Surfaces:** The primary foundation is a deep Charcoal (#121212). Elevated surfaces (cards, modals) use a slightly lighter Navy-Charcoal (#1E1E1E) to create a clear visual hierarchy without relying on harsh borders.
- **TNTT Accents:** 
    - **TNTT Red (#EF4444):** A passionate, vibrant red used for primary actions and core branding, reminiscent of the youth scarf.
    - **Vibrant Blue (#60A5FA):** A bright, pastel-leaning blue for secondary information and calm interactions.
    - **Golden Yellow (#FDE047):** A luminous yellow for highlights, icons, and special status indicators.
- **Typography:** Text uses off-white (#F8FAFC) for maximum readability and light gray (#CBD5E1) for secondary content, ensuring AAA accessibility against the dark backgrounds.

## Typography

The design system exclusively utilizes **Quicksand** for its rounded terminals and friendly disposition. 

- **Headlines:** Use Bold (700) or SemiBold (600) weights. The rounded nature of Quicksand prevents large headlines from feeling aggressive, even in high-contrast dark mode.
- **Body Text:** Standardized at Medium (500) for better legibility against dark backgrounds, as thinner weights can often "shimmer" or lose clarity on low-brightness screens.
- **Labels:** Uppercase tracking is slightly increased for the `label-sm` role to ensure quick scanning in navigation and tag components.

## Layout & Spacing

The layout utilizes a **12-column fluid grid** for desktop and a **4-column grid** for mobile. 

- **Rhythm:** An 8px linear scale (with a 4px half-step for tight UI elements) governs all padding and margins.
- **Breathability:** Given the dark theme, generous whitespace (using `lg` and `xl` spacing tokens) is essential to prevent the UI from feeling claustrophobic.
- **Containment:** Content is primarily housed in "glass" containers with inner padding of `md` (24px) to maintain a friendly, spacious aesthetic.

## Elevation & Depth

This design system avoids traditional drop shadows in favor of **Tonal Elevation** and **Glassmorphism**.

1.  **Surfaces:** The higher the elevation, the lighter the surface color. Level 0 is #121212. Level 1 (Cards) is #1E1E1E.
2.  **Glassmorphism:** For top navigation bars and floating action buttons, use a background blur (12px to 20px) with a semi-transparent fill (e.g., `#FFFFFF` at 5% opacity). Add a 1px "inner glow" border (white at 10% opacity) on the top and left edges to simulate light catching the edge of the glass.
3.  **Soft Glows:** Instead of black shadows, use tinted "ambient glows." For example, a Red TNTT button should have a very soft, diffused red shadow (`rgba(239, 68, 68, 0.3)`) with a high blur radius to make it appear as if it's emitting light.

## Shapes

The shape language is **distinctly rounded**, reinforcing the "cute" and friendly brand personality. 

- **Small Components (Buttons, Inputs):** Use `rounded` (0.5rem / 8px).
- **Medium Components (Cards, Modals):** Use `rounded-lg` (1rem / 16px).
- **Large Sections / Aesthetic Elements:** Use `rounded-xl` (1.5rem / 24px) for a soft, pillowy feel.
- **Interactive States:** On hover, shapes may slightly increase their roundedness or scale by 2% to provide tactile, "squishy" feedback.

## Components

- **Buttons:** Primary buttons use a solid TNTT Red fill with off-white text. Secondary buttons use a Glassmorphic style: a 1px border of Blue or Yellow with a subtle 5% background tint.
- **Chips & Tags:** Small, fully rounded (pill-shaped) containers with low-opacity background tints (15% opacity of the accent color) and high-contrast text.
- **Inputs:** Dark charcoal fills (#1E1E1E) with a subtle 1px border (#333333). On focus, the border glows with the Primary Red or Secondary Blue.
- **Cards:** Use the #1E1E1E surface color. Ensure a subtle "inner stroke" (white at 5%) to define the shape against the #121212 background.
- **Lists:** Items are separated by soft, low-opacity dividers. High-priority items can use a vertical accent bar (2px wide) on the left side in the TNTT Red.
- **Progress Bars:** Use a thick, rounded track in dark charcoal with a vibrant gradient fill (e.g., Blue to Yellow) to show activity and life.