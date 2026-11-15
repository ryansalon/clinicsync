---
name: ClinicSync
description: Light-only outpatient clinic management interface built on coastal porcelain surfaces, a bright sage-mineral rail, and Clinical Teal accents.
colors:
  clinical-teal: "#0F766E"
  clinical-teal-deep: "#0D5D57"
  mineral-wash: "#E6F4F1"
  mineral-wash-strong: "#D5ECE7"
  mineral-wash-border: "#B5DCD4"
  sage-rail: "#EAEFED"
  sage-rail-hover: "#DFE7E4"
  porcelain-canvas: "#F4F7F6"
  subtle-surface: "#EFF4F2"
  exam-paper: "#FFFFFF"
  stone-hairline: "#DCE3E1"
  stone-hairline-strong: "#C5D0CD"
  ink-primary: "#14201E"
  ink-read: "#243330"
  ink-muted: "#465854"
  clinical-green: "#15803D"
  emerald-completed: "#047857"
  amber-caution: "#B45309"
  allergy-crimson: "#9F1239"
  danger-red: "#B91C1C"
  allergy-wash: "#FCE7EA"
typography:
  display:
    fontFamily: "Geist, ui-sans-serif, system-ui, sans-serif"
    fontSize: "2.75rem"
    fontWeight: 500
    lineHeight: 1.08
    letterSpacing: "-0.025em"
  display-lg:
    fontFamily: "Geist, ui-sans-serif, system-ui, sans-serif"
    fontSize: "3.35rem"
    fontWeight: 500
    lineHeight: 1.05
    letterSpacing: "-0.025em"
  headline:
    fontFamily: "Geist, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.5rem"
    fontWeight: 600
    lineHeight: 1.25
    letterSpacing: "-0.02em"
  lead:
    fontFamily: "Geist, ui-sans-serif, system-ui, sans-serif"
    fontSize: "15px"
    fontWeight: 400
    lineHeight: 1.55
    letterSpacing: "0em"
  title:
    fontFamily: "Geist, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 600
    lineHeight: 1.4
    letterSpacing: "-0.01em"
  body:
    fontFamily: "Geist, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 400
    lineHeight: 1.5
    letterSpacing: "0em"
  label:
    fontFamily: "Geist, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.6875rem"
    fontWeight: 600
    lineHeight: 1.3
    letterSpacing: "0.06em"
  micro:
    fontFamily: "Geist, ui-sans-serif, system-ui, sans-serif"
    fontSize: "10px"
    fontWeight: 600
    lineHeight: 1.25
    letterSpacing: "0.08em"
  mono:
    fontFamily: "Geist Mono, ui-monospace, SFMono-Regular, monospace"
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
    backgroundColor: "{colors.clinical-teal}"
    textColor: "{colors.exam-paper}"
    rounded: "{rounded.sm}"
    padding: "10px 16px"
  button-primary-hover:
    backgroundColor: "{colors.clinical-teal-deep}"
  button-secondary:
    backgroundColor: "{colors.exam-paper}"
    textColor: "{colors.ink-primary}"
    rounded: "{rounded.sm}"
    padding: "10px 16px"
  card-ledger:
    backgroundColor: "{colors.exam-paper}"
    textColor: "{colors.ink-primary}"
    rounded: "{rounded.md}"
    padding: "20px"
---

# Design System: ClinicSync

## Overview

**Creative North Star: "Civic Outpatient Chart"**

ClinicSync treats clinic operations software as a crisp paper medical chart digitized without ornament. High-volume triage receptionists, examining physicians, and outpatients share one calm, strictly light-only interface anchored by a bright sage-mineral navigation rail (`#EAEFED`), a coastal porcelain ambient canvas (`#F4F7F6`), pure exam-paper work surfaces (`#FFFFFF`), 1px cool-stone hairlines (`#DCE3E1`), and Clinical Teal (`#0F766E`) action controls.

Instead of SaaS marketing flourishes, glow effects, or multi-hue stat card rainbows, visual hierarchy comes from contrast, typographic weight, hairline-divided `.ledger-strip` metric bands, and strict color restraint. Color carries clinical signal rather than decoration: crimson is reserved for active allergies and destructive actions, amber for pending/caution states, and clinical teal for primary calls to action, live queue flow, and active calls.

**Key Characteristics:**
- Strictly light-only polarity (`color-scheme: light`) matched to bright reception desks and exam rooms, keeping full-color transparent clinic and school marks legible against light `#EAEFED` / `#F4F7F6` panels.
- One-piece structural separation: 1px `#DCE3E1` hairlines and quiet tonal shifts instead of heavy drop shadows or nested cards.
- Numeric discipline via `Geist` + `Geist Mono` with `font-variant-numeric: tabular-nums` (`.cs-num`) across queue tickets, timestamps, vitals, and chart identifiers.
- Minimum `44px` (`min-h-11`) interactive hit targets across mobile triage controls, slot pickers, pagination, and dialog dismissals.

## Colors

ClinicSync uses a bright clinical neutral palette where over 90% of every screen remains coastal porcelain canvas, white exam paper, sage-mineral navigation panels, and deep slate-forest ink.

### Primary
- **Clinical Teal** (`#0F766E`): Primary commit buttons, active calling badges, active navigation highlights, chart bars, and selected calendar dates. Hover deepens to `#0D5D57`.
- **Mineral Wash** (`#E6F4F1` / `#D5ECE7` with `#B5DCD4` border): Soft foreground tint for active physician next-patient bands, icon pills, and selected state washes.

### Neutral
- **Coastal Porcelain** (`#F4F7F6`): Primary viewport background and subtle alternating canvas tone.
- **Sage Mineral Rail** (`#EAEFED`, hover `#DFE7E4`): Sidebar rail and guest institution anchor panel ground, providing calm zoning and clean legibility for transparent clinic & college logos.
- **Exam Paper** (`#FFFFFF`) & **Subtle Table Tint** (`#EFF4F2`): Elevated clinical surfaces, ledger tables, forms, table headers, and modal sheets.
- **Stone Hairline** (`#DCE3E1` / strong `#C5D0CD`): Structural 1px borders, table dividers, and `.ledger-strip` cell rules.
- **Ink Primary** (`#14201E`) & **Ink Read** (`#243330`): Headings, patient names, clinical diagnoses, and vital values.
- **Ink Muted** (`#465854`): Field labels, timestamps, clinical units, and secondary metadata (strictly >= 4.5:1 contrast on `#FFFFFF`, `#EAEFED`, and `#F4F7F6`).

### Semantic Status
- **Clinical Green** (`#15803D`): Completed visits and active schedule badges.
- **Amber Caution** (`#B45309`): Pending approvals and incomplete profile warnings.
- **Allergy Crimson** (`#9F1239` on `#FCE7EA`): Active allergen banners, severe warnings, no-show flags, and cancellation actions.

**The Single-Signal Rule.** Saturated semantic hues appear only in status badges, active allergy alerts, and live calling actions—never as decorative card top stripes or rainbow avatar grids.

## Typography

**Display & Body Font:** `Geist`, fallback `ui-sans-serif, system-ui, sans-serif`
**Mono Font:** `Geist Mono`, fallback `ui-monospace, SFMono-Regular, monospace`

**Character:** Compact geometric clarity designed for high-density patient ledgers, 12-hour clinic schedules, and multi-parameter vital sign grids without horizontal drift.

### Hierarchy
- **Headline (`<h1>`)** (`600`, `1.5rem`, `line-height: 1.25`, `letter-spacing: -0.02em`): View title inside `<x-page-header>`. Never preceded by eyebrow kickers.
- **Section Title (`<h2>`)** (`600`, `0.875rem`, `line-height: 1.4`, `letter-spacing: -0.01em`): Card headers (`<x-card-title>`) and empty state headings.
- **Body** (`400`, `0.875rem`, `line-height: 1.5`): Form inputs, clinical notes, table cells, and patient instructions.
- **Label** (`600`, `0.6875rem`, `line-height: 1.3`, `letter-spacing: 0.06em`, `uppercase`): Table column heads (`<th scope="col">`), ledger strip labels, and field labels.
- **Clinical Mono** (`500`, `0.8125rem`, `font-variant-numeric: tabular-nums`): Encounter IDs (`ENC-*`), record numbers, vitals, and queue tickets (`#1`..`#N`).

**The Strict Heading Ladder Rule.** Pages carry a single `<h1>` in `<x-page-header>`; card sections use `<h2>`; sub-items within an already-headed card use `<h3>`. Never jump directly from `<h1>` to `<h3>`.

## Layout

- **Application Shell:** Persistent `240px` `#EAEFED` light sage-mineral left sidebar rail on desktop (`lg+`) separated by a 1px `#DCE3E1` hairline with compact top utility bar; drawer navigation on mobile. Main workspace container sits on `#F4F7F6` with generous `24px` (`gap-6`) section rhythm.
- **Summary Ledgers:** Replaced boxed KPI card grids with single-row horizontal `.ledger-strip` `<dl>` bars divided by 1px `#DCE3E1` hairlines (`sm:grid-cols-2 lg:grid-cols-4` / `5` / `7`).
- **Clinical Workspaces:** Physician encounter creation allocates `7/12` columns to the active clinical documentation and vitals form (`sm:grid-cols-3`) and `5/12` columns to the read-only EMR history rail.
- **Responsive Tables:** Desktop tables (`md:block`) include `<caption class="sr-only">` and `scope="col"`; mobile viewports (`md:hidden`) switch to structured stacked `<article>` items with explicit `<label>` bindings on inline actions.

## Elevation & Depth

ClinicSync operates on a **Flat-Hairline Layering** model. Resting cards, ledger strips, and navigation rails sit flush on the canvas and separate strictly through `1px solid #DCE3E1` borders and canvas-to-paper contrast (`#F4F7F6` vs. `#FFFFFF`). Soft ambient shadows appear exclusively on transient overlays (modals, popovers, dropdown menus).

**The No-Nested-Cards Rule.** Never nest bordered rounded cards inside another bordered card; represent inner groupings (vital grids, allergy lists, prescriptions, consultation timelines) using `divide-y divide-[#DCE3E1]` or `divide-x divide-[#DCE3E1]` hairlines.

## Shapes

- **Cards & Dialogs:** `8px` (`rounded-lg`) sharp-institutional corners with `1px solid #DCE3E1` stroke.
- **Controls & Badges:** `6px` (`rounded-md`) on buttons, inputs, time-slot buttons, and date cells.
- **Filter Pills & Status Tags:** Full pill (`rounded-full`) reserved exclusively for clickable filter chips and semantic badges so status tags remain distinct from source metadata tags (`.badge-online`, `.badge-walk_in`).

## Components

### Buttons
- **Shape:** `6px` (`rounded-md`), `min-h-11` (`44px`) on mobile / compact `36px+` inline actions on desktop (`min-h-11` preserved for standalone controls).
- **Primary:** `#0F766E` fill, `#FFFFFF` text, `font-semibold`. Hover `#0D5D57`; focus ring `2px solid #0F766E` with `2px` offset.
- **Secondary / Ghost:** `#FFFFFF` surface with `1px solid #DCE3E1` border and `#14201E` text. Hover shifts canvas to `#F4F7F6`.

### Metric Ledger Strip (`.ledger-strip`)
- **Style:** Full-width `#FFFFFF` container with `1px solid #DCE3E1` perimeter and `divide-y` / `divide-x` internal `.ledger-cell` compartments. Zero multi-color top bars.

### Inputs & Modals
- **Style:** `1px solid #DCE3E1` border, `#FFFFFF` fill, `#14201E` text, `6px` radius, `caret-color: #0F766E`.
- **Modals (`[data-dialog]`):** Trap keyboard `Tab` focus inside the open dialog, apply `inert` to `#app-shell`, restore trigger focus on close, and keep dismissal targets >= `44px`.

### Availability & Queue Pollers
- **Polling Feedback:** Persistent `aria-live="polite"` containers carry `[data-poll-status]` (`Synced HH:MM` / `Offline — retrying`) rather than disappearing silently on transient network drops.

## Do's and Don'ts

### Do:
- **Do** use `cs-num` (`tabular-nums`) on every queue number, appointment time, vital sign reading, and encounter code.
- **Do** keep text contrast at `#14201E` or `#465854` on light surfaces—never use washed-out `#94A3B8` (`slate-400`) on text.
- **Do** pair every desktop `<table>` with a `<caption class="sr-only">` and `scope="col"` column headers, plus `md:hidden` stacked cards on mobile.
- **Do** standardize Blade directives on `@php ... @endphp` multi-line blocks rather than mixing inline `@php(...)` inside the same template.

### Don't:
- **Don't** introduce dark mode toggles, glassmorphism blurs over workspace cards, WebGL shaders, or decorative accent top/left borders (`>1px`) on rounded cards.
- **Don't** place eyebrow kickers or small uppercase subtitles directly above `<h1>` page titles.
- **Don't** show pre-visit arrival instructions (`"arrive 15 minutes early"`) on completed, cancelled, or no-show visits.
