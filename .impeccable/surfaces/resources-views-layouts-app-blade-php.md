---
version: 1
slug: "resources-views-layouts-app-blade-php"
primary_target: "resources/views/layouts/app.blade.php"
related_targets: ["resources/css/app.css","resources/views/layouts/guest.blade.php","resources/views/admin/queue/index.blade.php","resources/views/physician/dashboard.blade.php","resources/views/patient/dashboard.blade.php"]
---

# ClinicSync Full Web UI/UX Redesign

- **Scope:** Full web application — Guest/Auth shell (`layouts/guest.blade.php`, `auth/*`), shared authenticated app shell (`layouts/app.blade.php`), reusable Blade components (`components/*`), and all 4 role portals (`admin/*`, `physician/*`, `patient/*`, `settings/profile.blade.php`).
- **Visitor Mode:** Operate (fast daily triage, consultation charting, patient queue tracking, and clinic governance under bright campus daylight).
- **Constraints:** Light-only clinical daylight palette, CPSC + ClinicSync co-branding (`cpsc-logo2.png` + `logocsasd.png`), Laravel 13 + Blade + Tailwind CSS v4 + Vite Bunny Fonts, no SPA framework, never mix inline `@php(...)` and block `@php ... @endphp`, preserve all 83 PHPUnit tests and routes.

## Direction contract

- **THESIS:** Camiguin Herbarium Dossier — a sunlit archival clinical ledger that replaces stacked SaaS chrome and clinical-teal cliché with warm daylight paper surfaces, a single consolidated operational toolbar, and zero-clipping slide-over row inspection.
- **OWN-WORLD:** Archival Ivory canvas (`#FAF8F4`), Warm Travertine rail (`#ECE8DF`), crisp Folio Paper (`#FFFFFF`), 1px Warm Stone hairlines (`#DFDBD0`), Deep Charcoal-Forest ink (`#18201D` / `#565E58`), Specimen Indigo (`#1E4976`) primary actions, Pressed Vermilion (`#B83B2A` on `#FDF1EF`) allergies/alerts, Ochre (`#9A5B13`) waiting states, Herbarium Moss (`#245B3E`) completed standing; `Public Sans` + `JetBrains Mono` (`tabular-nums`).
- **STORY:** Receptionists see live queue rows above the fold and manage appointments cleanly in an unclipped slide-over drawer with isolated destructive controls; physicians chart SOAP notes alongside a sticky tabbed EMR rail; patients track their live queue pass and records with immediate clarity.
- **FIRST VIEWPORT:** Persistent 248px Warm Travertine sidebar rail with CPSC + ClinicSync lockup; compact top utility bar with live sync status; Reception Daily Queue opens directly onto a 5-cell hairline KPI strip, unified Now Calling + proportional Physician Capacity bar strip, single consolidated filter/tab/search toolbar, and the live queue table above the fold.
- **FORM:** Camiguin Herbarium Dossier (candidate 5 of 7 grounded directions, raised with isolated destructive drawer footers, proportional capacity bars, tabular catalog indexing, and non-color state redundancy); `seed:17499f44af0e4b6d`.
- **FINISH:** unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance
