# ClinicSync

ClinicSync is an outpatient clinic management web application built with **Laravel 13**, **Blade**, **Tailwind CSS v4**, and **Spatie Laravel Permission**. It coordinates appointment scheduling, live clinic queuing, electronic medical records (EMR), prescriptions, and role-based clinic operations.

## Features

### Patient Portal (`patient`)
- Dashboard with upcoming appointments and live queue status
- Appointment booking with real-time physician slot availability and availability calendar
- Appointment rescheduling and cancellation
- Personal medical records view (encounters, vitals, prescriptions, allergies, and medical history)
- In-app notification center and profile/password settings

### Physician Workspace (`physician`)
- Daily consultation dashboard and live patient queue
- Availability schedule management and daily patient limit configuration
- Clinical encounter recording (chief complaint, diagnosis, assessment, treatment plan, clinical notes, vitals)
- E-prescription issuance per encounter
- Full patient chart review (medical history, allergies, and past encounters)

### Reception & Clinic Operations (`receptionist`, `administrator`)
- Live clinic queue management (walk-in registration, patient search, calling patients, status transitions)
- Physician schedule management and availability calendar
- Clinic operational reports

### System Administration (`administrator`)
- Includes all Reception & Clinic Operations capabilities
- User management (create staff/user accounts and toggle active status)
- System audit log viewer

## Tech Stack

- **Backend:** PHP 8.3+, Laravel 13, Spatie Laravel Permission
- **Frontend:** Blade templates, Tailwind CSS v4, Vite
- **Database:** SQLite (default in `.env.example`) or any Laravel-supported RDBMS
- **Testing & Quality:** PHPUnit 12, Laravel Pint

## Getting Started

### Prerequisites

- PHP `^8.3` with Composer
- Node.js and npm

### Installation

1. Clone the repository and enter the project directory:
   ```bash
   git clone https://github.com/ryansalon/clinicsync.git
   cd clinicsync
   ```

2. Run the automated setup script (installs Composer & npm dependencies, creates `.env`, generates the app key, runs migrations, and builds assets):
   ```bash
   composer run setup
   ```

   Or run the steps manually:
   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   npm install
   npm run build
   ```

### Seeding Data

#### Local Development (Demo Data)

`DatabaseSeeder` runs `RoleSeeder`, `AdminSeeder`, and `DemoDataSeeder` (automatically skipped in `production`):

```bash
php artisan db:seed
```

Seeded demo accounts (all use password `password`):

| Role | Email |
| --- | --- |
| System Administrator | `admin@clinicsync.test` |
| Receptionist | `receptionist@clinicsync.test` |
| Physician (General Medicine) | `physician1@clinicsync.test` |
| Physician (Pediatrics) | `physician2@clinicsync.test` |
| Patients | `patient1@clinicsync.test` – `patient5@clinicsync.test` |

#### Production Administrator

Set `ADMIN_EMAIL`, `ADMIN_PASSWORD`, and optionally `ADMIN_NAME` in `.env`, then seed roles and the administrator account:

```bash
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=AdminSeeder
```

## Running Locally

Start the development environment:

```bash
composer run dev
```

Or run the Laravel server and Vite dev server separately:

```bash
php artisan serve
npm run dev
```

### Queues & Scheduled Reminders

Appointment confirmations, reschedules, and reminders are queued (`QUEUE_CONNECTION=database` by default) and scheduled in `routes/console.php`:
- `appointments:send-reminders` runs daily at `18:00` (can also be run manually with `php artisan appointments:send-reminders --date=YYYY-MM-DD`).
- `queue:work --stop-when-empty --max-time=55` runs every minute via the Laravel scheduler.

To run the scheduler locally:

```bash
php artisan schedule:work
```

## Testing & Code Style

Run the test suite:

```bash
composer run test
```

Format PHP files with Laravel Pint:

```bash
vendor/bin/pint
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
