---
name: Administrative Grace
colors:
  surface: '#f7f9fb'
  surface-dim: '#d8dadc'
  surface-bright: '#f7f9fb'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f4f6'
  surface-container: '#eceef0'
  surface-container-high: '#e6e8ea'
  surface-container-highest: '#e0e3e5'
  on-surface: '#191c1e'
  on-surface-variant: '#424751'
  inverse-surface: '#2d3133'
  inverse-on-surface: '#eff1f3'
  outline: '#737783'
  outline-variant: '#c2c6d3'
  surface-tint: '#255dad'
  primary: '#00346f'
  on-primary: '#ffffff'
  primary-container: '#004a99'
  on-primary-container: '#9bbdff'
  inverse-primary: '#abc7ff'
  secondary: '#bc0000'
  on-secondary: '#ffffff'
  secondary-container: '#e22618'
  on-secondary-container: '#fffbff'
  tertiary: '#283649'
  on-tertiary: '#ffffff'
  tertiary-container: '#3f4d61'
  on-tertiary-container: '#afbed5'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#d7e2ff'
  primary-fixed-dim: '#abc7ff'
  on-primary-fixed: '#001b3f'
  on-primary-fixed-variant: '#00458f'
  secondary-fixed: '#ffdad4'
  secondary-fixed-dim: '#ffb4a8'
  on-secondary-fixed: '#410000'
  on-secondary-fixed-variant: '#930000'
  tertiary-fixed: '#d5e3fc'
  tertiary-fixed-dim: '#b9c7df'
  on-tertiary-fixed: '#0d1c2e'
  on-tertiary-fixed-variant: '#3a485b'
  background: '#f7f9fb'
  on-background: '#191c1e'
  surface-variant: '#e0e3e5'
typography:
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '700'
    lineHeight: '1.2'
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '600'
    lineHeight: '1.3'
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: '1.4'
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.6'
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: '1.5'
  label-md:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: '1'
    letterSpacing: 0.05em
  headline-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 26px
    fontWeight: '700'
    lineHeight: '1.2'
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 8px
  container-padding: 24px
  gutter: 20px
  sidebar-width: 260px
  card-gap: 24px
---

## Brand & Style

This design system is engineered for a religious youth organization, balancing administrative precision with a welcoming, communal spirit. The visual direction is **Corporate / Modern**, emphasizing clarity, order, and trust. 

The system utilizes a structured card-based layout and high-contrast typography to ensure complex data (such as attendance, schedules, and curriculum) remains accessible to both adult administrators and youth leaders. The aesthetic response is one of calm authority and functional elegance, utilizing generous whitespace to prevent cognitive overload in data-heavy environments.

## Colors

The palette is anchored by a deep **Ecclesiastical Blue** (#004a99), signifying stability and leadership, and a vibrant **Ceremonial Red** (#c00000) used strictly for primary actions and critical alerts. 

- **Primary (Blue):** Navigation, active states, and structural accents.
- **Secondary (Red):** High-priority calls to action and destructive indicators.
- **Surface:** Uses a layered neutral approach. The base background is a cool light grey (#f8fafc), while cards and containers utilize pure white (#ffffff) to create distinction.
- **Status:** Standard semantic colors apply (Green for success, Amber for warning) but are muted to maintain the professional tone.

## Typography

The system uses a dual-font strategy to balance character with utility. 

**Plus Jakarta Sans** is used for headlines and titles. Its modern, slightly rounded geometric forms provide a friendly and optimistic tone appropriate for a youth-centric organization.

**Inter** is the workhorse for all body copy, data tables, and form inputs. It was selected for its exceptional legibility at small sizes and its neutral, systematic feel that aids in processing administrative information. Uppercase labels with slight letter spacing are used for table headers and section overviews to create clear visual hierarchy.

## Layout & Spacing

The layout follows a **Fixed Sidebar + Fluid Content** model. The interface is grounded on an 8px grid system, ensuring consistent alignment across all components.

- **Desktop:** A 12-column grid is used within the main content area. Content is housed in distinct cards to separate different functional modules (e.g., "Media Management" vs "Quick Stats").
- **Sidebar:** A persistent left-hand navigation bar (260px) provides high-level access to administrative sectors.
- **Margins:** Standard page margins are 32px on desktop, scaling down to 16px on mobile. 
- **Responsive Behavior:** On tablet, the sidebar collapses into a drawer. On mobile, cards stack vertically, and the primary navigation moves to a bottom bar or top-level hamburger menu.

## Elevation & Depth

This design system uses a **Tonal Layering** approach combined with **Ambient Shadows** to define hierarchy.

1.  **Level 0 (Background):** The canvas layer (#f8fafc).
2.  **Level 1 (Cards/Containers):** Pure white surfaces with a soft, diffused shadow (0px 4px 12px rgba(0, 0, 0, 0.05)). This creates a subtle lift that separates content from the background without feeling heavy.
3.  **Level 2 (Dropdowns/Modals):** Higher elevation with a more pronounced shadow (0px 10px 25px rgba(0, 0, 0, 0.1)) to indicate temporary interaction layers.

Inverted surfaces (using the Primary Blue) are used for "Summary Cards" to provide immediate visual focus on key metrics.

## Shapes

The shape language is **Rounded**, favoring approachable geometry over clinical sharp corners. 

- **Cards & Primary Modules:** Use 1rem (16px) corner radius to create a soft, modern framing.
- **Inputs & Small Buttons:** Use 0.5rem (8px) corner radius for a precise, clickable feel.
- **Status Pills & Chips:** Use a full pill shape (999px) to distinguish them from actionable buttons.

This consistent use of roundedness reinforces the organization's mission of being a welcoming community while maintaining professional boundaries.

## Components

### Buttons
- **Primary:** Solid Red (#c00000) with white text for main actions (e.g., "Create New Post").
- **Secondary:** Outlined Blue (#004a99) or soft grey ghost buttons for secondary navigation.
- **States:** Hover states should involve a slight darkening (10%) of the fill color.

### Input Fields
- **Search & Text:** Subtle grey border (#e2e8f0) with soft rounded corners. On focus, the border transitions to Primary Blue with a 2px outer glow.
- **Icons:** Use linear, medium-weight icons (24px) for navigation and 18px for inline actions.

### Cards & Tables
- **Data Tables:** Clean, no-border rows with a light hover highlight. Headers are styled using the `label-md` typography.
- **Stat Cards:** Use the Primary Blue background with white text for "at-a-glance" metrics to differentiate them from standard content cards.

### Navigation
- **Active State:** Sidebar items use a "light blue" background tint and a 4px vertical primary blue bar on the left edge to clearly denote the current location.