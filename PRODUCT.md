# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

ClinicSync serves four campus outpatient clinic roles at CPSC (Camiguin Polytechnic State College) with a balanced priority that emphasizes fast daily intake and consultation execution:

- **Receptionists & Clinic Staff (`receptionist`)**: Manage walk-in registration, patient search, live waiting queue transitions, physician schedule coordination, and daily operational reports.
- **Physicians (`physician`)**: Review daily consultation queues, inspect patient charts (medical history, allergies, vitals, past encounters), record clinical encounters, issue e-prescriptions, and configure daily patient limits/availability.
- **Patients (`patient`)**: Book, reschedule, and cancel appointments against real-time physician slots, track live queue status, review personal medical records/prescriptions, and manage notifications and profile settings.
- **System Administrators (`administrator`)**: Perform all reception/clinic operations plus oversee overall system metrics, staff/user account management, active status control, and audit logs.

## Product Purpose

ClinicSync replaces fragmented paper logs and manual waiting-room tracking with an integrated outpatient clinic operations platform unifying appointment scheduling, live queue coordination, electronic medical records (EMR), and e-prescriptions. Success means patients see accurate wait/queue status, receptionists move intake cleanly without confusion, physicians document consultations quickly with zero charting friction, and administrators have immediate visibility into clinic readiness and utilization.

## Positioning

Purpose-built around real-time outpatient queue state and structured clinical workflows inside a campus health clinic (CPSC), pairing role-tailored operational speed with calm clinical legibility rather than generic multi-department hospital bloat.

## Operating Context

- Campus outpatient clinic setting operating Monday–Saturday (08:00–17:00), handling both scheduled appointments and walk-in patients under daily per-physician caps.
- Live polling updates queue boards (`admin.queue.index` / board partial), physician queues, and patient waiting statuses throughout the clinic day.
- Desktop terminals at reception desks and consultation rooms alongside mobile viewports used by patients checking appointments and queue positions.

## Capabilities and Constraints

- **Stack**: PHP ^8.3, Laravel 13, Blade templates, Tailwind CSS v4, Vite, Spatie Laravel Permission, SQLite/RDBMS, PHPUnit 12, Laravel Pint. (No dependency changes without approval; no SPA framework rewrite.)
- **Functional & Test Contracts**: All 4 role portals (`patient`, `physician`, `receptionist`, `administrator`), existing named routes (including `administrator` home route `admin.queue.index`, `admin.dashboard`, `physician.dashboard`, `patient.dashboard`), and 83 existing PHPUnit feature/unit tests must continue passing.
- **Blade Constraint**: Do not mix inline `@php(...)` and block `@php ... @endphp` in the same Blade template.
- **Redesign Scope (Confirmed)**: Full application — Login/Guest screens, shared authenticated app shell (`layouts/app.blade.php`), and all 4 role dashboards and inner clinical/operations workflows.

## Brand Commitments

- **Co-branding**: Co-branded for CPSC & ClinicSync on authentication/guest surfaces (`public/images/cpsc-logo2.png` CPSC seal alongside transparent `public/images/logocsasd.png` ClinicSync mark at equal height above `Outpatient Clinic Management`).
- **Theme Policy**: Strictly light-only clinical palette (no dark mode or theme toggle), prioritizing calm, eye-friendly daylight legibility for prolonged clinic desk use.

## Evidence on Hand

- Live Laravel 13 Blade application views under `resources/views/` (`auth/`, `layouts/`, `patient/`, `physician/`, `admin/`, `settings/`, `notifications/`, `components/`).
- Shared styling and scripts in `resources/css/app.css`, `resources/js/app.js`, and `resources/js/ribbon-field.js`.
- Verified visual assets: `public/images/logocsasd.png` (`597x497` transparent ClinicSync logo), `public/images/cpsc-logo2.png` (`474x474` transparent CPSC seal), `public/favicon.svg`.
- Demo seeder (`Database\Seeders\DemoDataSeeder`) providing realistic outpatient appointments, queues, encounters, prescriptions, and multi-role accounts.

## Product Principles

1. **Speed at the point of care**: Optimize primary actions — queue progression at reception, patient chart lookup, and encounter entry for physicians — so staff spend attention on patients rather than UI hunting.
2. **Calm, unambiguous clinical state**: Live queue states, allergies, clinical vitals, and confirmation actions must be scannable at a glance without visual noise or low-contrast ambiguity.
3. **Role-fitted density**: Give high-frequency operators (receptionists, physicians, admins) compact, purposeful information density while keeping patient workflows straightforward and mobile-friendly.
4. **Trustworthy campus clinical identity**: Ground every surface in clean, light-first clinical clarity and cohesive CPSC + ClinicSync co-branding.

## Accessibility & Inclusion

- High text contrast and eye-friendly light clinical surfaces suitable for bright clinic monitors and extended desk shifts.
- Full responsive parity across reception desktops/tablets and patient mobile viewports, with clear keyboard focus visibility.
