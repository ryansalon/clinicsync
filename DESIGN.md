---
name: ClinicSync
description: Light-only outpatient clinic management interface built on sunlit archival ivory surfaces, a warm travertine navigation rail, and Specimen Indigo actions ("Camiguin Herbarium Dossier").
colors:
  specimen-indigo: "#1E4976"
  specimen-indigo-deep: "#163759"
  indigo-wash: "#EAF1F7"
  indigo-border: "#C5D7E8"
  travertine-rail: "#ECE8DF"
  travertine-hover: "#E2DDD1"
  archival-ivory: "#FAF8F4"
  subtle-paper: "#F4F1EA"
  folio-paper: "#FFFFFF"
  stone-hairline: "#DFDBD0"
  stone-hairline-strong: "#CCC6B8"
  ink-primary: "#18201D"
  ink-read: "#2D3631"
  ink-muted: "#565E58"
  herbarium-moss: "#1F5438"
  herbarium-moss-solid: "#245B3E"
  moss-wash: "#EAF4EE"
  moss-border: "#B5D7C2"
  ochre-caution: "#8A4F0F"
  ochre-wash: "#FDF4E7"
  ochre-border: "#DFC08B"
  vermilion-alert: "#96281B"
  vermilion-solid: "#B83B2A"
  vermilion-wash: "#FDF1EF"
  vermilion-border: "#EABCB4"
typography:
  display:
    fontFamily: "Public Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "2.5rem"
    fontWeight: 700
    lineHeight: 1.08
    letterSpacing: "-0.03em"
  display-lg:
    fontFamily: "Public Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "3.1rem"
    fontWeight: 700
    lineHeight: 1.08
    letterSpacing: "-0.03em"
  headline:
    fontFamily: "Public Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.5rem"
    fontWeight: 700
    lineHeight: 1.25
    letterSpacing: "-0.025em"
  lead:
    fontFamily: "Public Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "15px"
    fontWeight: 400
    lineHeight: 1.55
    letterSpacing: "0em"
  title:
    fontFamily: "Public Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 600
    lineHeight: 1.4
    letterSpacing: "-0.01em"
  body:
    fontFamily: "Public Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 400
    lineHeight: 1.5
    letterSpacing: "0em"
  label:
    fontFamily: "JetBrains Mono, ui-monospace, SFMono-Regular, monospace"
    fontSize: "0.6875rem"
    fontWeight: 600
    lineHeight: 1.3
    letterSpacing: "0.06em"
  micro:
    fontFamily: "JetBrains Mono, ui-monospace, SFMono-Regular, monospace"
    fontSize: "10px"
    fontWeight: 600
    lineHeight: 1.25
    letterSpacing: "0.08em"
  mono:
    fontFamily: "JetBrains Mono, ui-monospace, SFMono-Regular, monospace"
    fontSize: "0.8125rem"
    fontWeight: 500
    lineHeight: 1.4
    letterSpacing: "0em"
rounded:
  sm: "6px"
  md: "8px"
  full: "9999px"
spacing:
  xs: "4px"
  sm: "8px"
  md: "16px"
  lg: "20px"
  xl: "24px"
components:
  button-primary:
    backgroundColor: "{colors.specimen-indigo}"
    textColor: "{colors.folio-paper}"
    rounded: "{rounded.sm}"
    padding: "10px 16px"
  button-primary-hover:
    backgroundColor: "{colors.specimen-indigo-deep}"
  button-secondary:
    backgroundColor: "{colors.folio-paper}"
    textColor: "{colors.ink-primary}"
    rounded: "{rounded.sm}"
    padding: "10px 16px"
  button-outline-danger:
    backgroundColor: "{colors.folio-paper}"
    textColor: "{colors.vermilion-alert}"
    rounded: "{rounded.sm}"
    padding: "8px 12px"
  card-ledger:
    backgroundColor: "{colors.folio-paper}"
    textColor: "{colors.ink-primary}"
    rounded: "{rounded.md}"
    padding: "20px"
---

# Design System: ClinicSync

## Overview

**Creative North Star: "Camiguin Herbarium Dossier"**

ClinicSync treats campus outpatient clinic operations as a sunlit archival dossier built for speed, legibility, and clinical safety. Triage receptionists, examining physicians, outpatients, and system administrators share one strictly light-only daylight interface anchored by a Warm Travertine navigation rail (`#ECE8DF`), an Archival Ivory ambient canvas (`#FAF8F4`), crisp Folio Paper work sheets (`#FFFFFF`), 1px Warm Stone hairlines (`#DFDBD0`), and Specimen Indigo (`#1E4976`) primary actions.

Visual hierarchy comes from typographic weight, flat 1px hairline separation, proportional physician capacity meters, unclipped right-hand slide-over row inspectors, and non-color state redundancy rather than stacked SaaS card chrome or decorative drop shadows.

**Key Characteristics:**
- **Strictly light-only daylight polarity** (`color-scheme: light`) where full-color transparent CPSC and ClinicSync marks sit cleanly on Warm Travertine (`#ECE8DF`) without glow or drop-shadow halos.
- **Flat-Hairline Layering:** Resting cards, `.ledger-strip` metric bands, and rails use 1px `#DFDBD0` borders without box shadows; soft shadows appear exclusively on transient overlays (dropdowns, slide-over drawers, confirmation dialogs).
- **Tabular Catalog Discipline:** `Public Sans` paired with `JetBrains Mono` (`font-variant-numeric: tabular-nums` via `.cs-num`) across queue tickets (`#04`), patient numbers (`PT-0001`), encounter IDs (`ENC-*`), timestamps, and vitals.
- **Four Clinical Safety & Operations Disciplines:**
  1. **Isolated destructive actions** (`.btn-outline-danger`) separated into drawer footers or spaced away from primary dispatch actions.
  2. **Proportional capacity meters** (`.capacity-bar` / `.capacity-fill`) visualizing physician daily slot utilization at a glance.
  3. **Non-color state redundancy** (ringed active/called badges, dashed pending borders, struck-through cancelled badges, solid completed standing).
  4. **Sticky tabbed clinical reference rails** (`[data-tabs]`) so physicians cross-check EMR history while charting SOAP notes.

## Colors

Over 90% of every screen remains Archival Ivory (`#FAF8F4`), Folio Paper (`#FFFFFF`), Warm Travertine (`#ECE8DF`), and Deep Charcoal-Forest ink (`#18201D`). All tokens are exposed via Tailwind v4 `@theme` utilities in `resources/css/app.css` (`bg-canvas`, `bg-rail`, `bg-surface`, `bg-surface-subtle`, `border-hairline`, `text-bright`, `text-read`, `text-meta`, `text-brand`, `bg-brand`, `bg-brand-tint`, `text-danger`, `text-warn`, `text-ok`).

### Primary
- **Specimen Indigo** (`#1E4976`, hover `#163759`): Primary commit actions, active navigation highlight, queue ticket numerals, selected calendar dates, and appointment progress bars.
- **Indigo Wash** (`#EAF1F7` with `#C5D7E8` border): Soft foreground tint for active calling bands, next-patient physician spotlights, and selected state washes.

### Neutral
- **Archival Ivory** (`#FAF8F4`): Primary viewport canvas and guest form stage.
- **Warm Travertine Rail** (`#ECE8DF`, hover `#E2DDD1`): Persistent desktop sidebar rail and guest institutional anchor panel.
- **Folio Paper** (`#FFFFFF`) & **Subtle Paper** (`#F4F1EA`): Work sheets, tables, forms, table headers, and overlay panels.
- **Warm Stone Hairline** (`#DFDBD0` / strong `#CCC6B8`): Structural 1px borders, table rules, and `.ledger-strip` dividers.
- **Ink Primary** (`#18201D`), **Ink Read** (`#2D3631`), **Ink Muted** (`#565E58`): >= 4.5:1 contrast across white, ivory, and travertine grounds.

### Semantic Status
- **Herbarium Moss** (`#1F5438` / `#245B3E` on `#EAF4EE` with `#B5D7C2` border): Completed consultations and active schedules/accounts.
- **Ochre Caution** (`#8A4F0F` on `#FDF4E7` with `#DFC08B` border): Pending triage states and incomplete profile reminders.
- **Pressed Vermilion** (`#96281B` / `#B83B2A` on `#FDF1EF` with `#EABCB4` border): Active allergies, severe warnings, full-capacity indicators, no-shows, and destructive confirmations.

## Typography

- **UI & Headings:** `Public Sans` (`400, 500, 600, 700`), self-hosted via Laravel Vite Bunny Fonts.
- **Mono & Data:** `JetBrains Mono` (`400, 500, 600`), self-hosted via Laravel Vite Bunny Fonts.
- **Hierarchy:** Single `<h1>` (`1.5rem` bold, `-0.025em`) inside `<x-page-header>` with zero eyebrow kickers above it; card headings use `<h2>` (`0.875rem` semibold); table heads and metadata labels use `10px–11px` uppercase `JetBrains Mono`.

## Layout

- **Application Shell:** Persistent `248px` Warm Travertine rail on desktop (`md+`) with crisp CPSC + ClinicSync lockup and compact utility topbar (`56px`). Mobile uses a focus-trapped off-canvas drawer.
- **Reception Daily Queue:** Consolidated single-bar command toolbar (Date, Physician, `.tab-strip` status filter, Quick Search) above a 5-cell `.ledger-strip`, unified Now Calling + proportional Daily Capacity bar band, hairline table, and unclipped right-hand slide-over inspector drawers (`[data-drawer]`).
- **Physician Consultation Workspace:** `7/12` SOAP + hairline-divided Vitals form beside a `5/12` sticky tabbed EMR reference rail (`Profile`, `History`, `Visits`).
- **Responsive Tables:** Desktop tables (`md:block`) carry `<caption class="sr-only">` and `scope="col"` headers; mobile viewports (`md:hidden`) switch to stacked `<article>` items with >= `44px` (`min-h-11`) touch targets.

## Components & Interactions

- **Slide-Over Inspector Drawers (`[data-drawer]`):** Document-delegated so triggers survive live queue polling (`clinicsync:polled`). Live polling pauses automatically while any drawer is open; Tab focus traps inside the drawer and restores to the opener on Escape or close.
- **Destructive Action Isolation:** `.btn-outline-danger` keeps high-impact actions (`No show`, `Cancel visit`, `Remove appointment`, `Suspend`, `Delete`) quiet until focused/hovered and places them in dedicated drawer footers or spaced apart from primary controls.
- **Live Queue Polling (`[data-poll]`):** Persistent `aria-live="polite"` containers update `[data-poll-status]` (`Synced HH:MM` / `Offline — retrying`) cleanly without yanking focus.

## Do's and Don'ts

### Do:
- **Do** use semantic Tailwind v4 tokens (`bg-canvas`, `border-hairline`, `text-bright`, `text-brand`, etc.) instead of hardcoded hex values.
- **Do** use `cs-num` (`tabular-nums`) and `font-mono` on queue numbers, patient/encounter IDs, timestamps, and vitals.
- **Do** keep Blade `@php` directives in block `@php ... @endphp` syntax (never mix inline `@php(...)` and `@endphp` in the same file).

### Don't:
- **Don't** add dark mode toggles, colored `border-left` / `border-right` stripes `>1px` on cards/alerts, nested bordered cards inside cards, or eyebrow kickers above `<h1>`.
- **Don't** place multi-form dropdowns inside `overflow-x-auto` table containers; use `[data-drawer]` or `[data-dialog]` overlays instead.
