# Design System

## Visual Theme & Color Strategy
- **Mode**: Light (Parchment & Herbarium physical scene: daylight botanical explorer journal).
- **Strategy**: Restrained to Committed (Parchment background `#F5F4DA`, Deep Forest Green `#1F3D20` for structure, Sand `#EDE6D3` for card surfaces, Gold `#FFD700` for achievements/NC coins, Terracotta `#8B6A4C` for Ranger roles).
- **Body Background**: `#F5F4DA` (Parchment tone, high contrast against dark green text `#2A2A22` / `#1F3D20`).
- **Primary Ink**: `#1F3D20` (Deep Forest) and `#2A2A22` (Charcoal).
- **Muted Ink**: `#5C574C` / `#6B6B55` (Ensuring >= 4.5:1 WCAG contrast against parchment).

## Typography
- **Headings**: `Baloo 2` (Playful, bold, highly legible for gamified headings & UI titles).
- **Body & Controls**: `Nunito` (Friendly, rounded sans-serif for UI labels, badges, and prose).
- **Headline Accents**: `Fraunces` (Editorial serif for field journal volume headers and specimen titles).
- **Code & Metadata**: `IBM Plex Mono` (Monospace badges, specimen codes, role IDs).

## Key Components & Interaction Rules
- **Header & Navigation**: Fixed top header with clean unobstructed logo (`logo-plantGuardian.jpeg`), user level badge, Nature Coins (NC), and EXP pills.
- **Specimen Cards & Panels**: Dashed/solid border framing, `#EDE6D3` surface, clear 1-2px borders instead of 32px soft drop shadows.
- **Buttons**:
  - Primary: Deep Forest `#1F3D20` with `#F5F4DA` text, 150ms state transition, hover/active scale feedback.
  - Secondary: `#E2E1C4` / `#E3DABF` with `#1F3D20` text.
- **Banned Patterns**:
  - No side-stripe borders (`border-left` > 1px as accent).
  - No hover scale animations on static `<img>` elements.
  - No washed-out low-contrast body text.
  - No generic AI slop templates.
