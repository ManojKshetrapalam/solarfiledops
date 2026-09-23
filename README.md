# Solar Field Operations & Service Management System

A mobile-first field reporting and service management system designed for solar installation and O&M groups operating across multiple corporate entities (such as *Sun on Earth* and *Sabha*).

The system digitizes paper-based solar maintenance and inspection forms into a structured, mobile-first digital workflow: from job creation and dispatch, to on-site 10-step reporting, photo capture, draft persistence, admin review, correction cycles, and approval locking.

---

## Current Status
- **Core Operations**: Fully operational and verified with end-to-end automated feature tests.
- **Workflow Phase**: First release covering Admin Panel, Company Entity Master, Employee Master, Customer & Site Masters, Service Dispatch, Mobile-First 10-Step Service Report, Draft Persistence, Photo Upload, Review & Approval/Correction cycles, and Company-wise Reporting.
- **Form Extensibility**: Built with decoupled section payloads so additional report types (*Site Inspection*, *Installation Report*, *Complaint Attending*, *Daily Work Report*) can be plugged in without database schema changes.

---

## Technology Stack
- **Backend**: Laravel 11/12 (PHP 8.4)
- **Frontend**: Blade Components, Tailwind CSS v4, Alpine.js
- **Database**: SQLite (Local Development) / MySQL (Production)
- **Asset Pipeline**: Vite
- **Storage**: Local filesystem via `storage/app/public` with public symlink (`public/storage`)
- **Testing**: PHPUnit / Laravel Feature Testing (66 assertions, 100% passing)

---

## Architecture
```
COMPANY (e.g. Sun on Earth, Sabha)
   ↓
CUSTOMER (e.g. ABC Industries)
   ↓
SITE (e.g. Bangalore Plant)
   ↓
SERVICE JOB (e.g. SOE-SRV-0001)
   ↓
REPORT (10-Step Service Report)
   ↓
ENGINEER (e.g. Raj Kumar)
```

### Architecture Invariants:
1. **Multi-Entity Isolation**: Every customer, site, service, and report strictly belongs to a Company/Entity.
2. **Dual Status Tracking**: `service.status` (`unassigned`, `assigned`, `in_progress`, `report_submitted`, `correction_required`, `completed`, `cancelled`) and `report.status` (`draft`, `submitted`, `correction_required`, `resubmitted`, `approved`, `rejected`) remain separate concepts.
3. **Decoupled Section Data**: Form fields are stored in `report_data` grouped by section (`customer_details`, `system_details`, `module_inspection`, `structure_inspection`, `pcu_inspection`, `battery_inspection`, `complaint_details`, `remarks`), ensuring zero schema bloat.
4. **Photo & Document Traceability**: All attachments in `report_photos` and `report_documents` are explicitly indexed by Company, Service, Report, Section, Uploader, and Timestamp.
5. **Approval Immutability**: Once an Admin approves a report, it is permanently locked against further engineer edits and marked as approved in audit logs.

---

## AI Quick Start
- **Entry Points**:
  - `routes/web.php`: Complete application routing.
  - `bootstrap/app.php`: Middleware configuration and routing bootstrap.
- **Controllers**:
  - Admin: `app/Http/Controllers/Admin/` (`DashboardController`, `CompanyController`, `EmployeeController`, `CustomerController`, `SiteController`, `ServiceController`, `ReportController`, `AnalyticsController`, `NotificationController`, `AuditLogController`).
  - Engineer (Mobile-First): `app/Http/Controllers/Engineer/` (`DashboardController`, `ServiceController`, `ReportController`, `NotificationController`).
  - Auth: `app/Http/Controllers/AuthController.php`.
- **Views**:
  - Base Layouts: `resources/views/layouts/app.blade.php`, `resources/views/layouts/admin.blade.php`, `resources/views/layouts/engineer.blade.php`.
  - Mobile Wizard: `resources/views/engineer/reports/edit.blade.php` (Alpine.js powered 10-step wizard).
  - Admin Review: `resources/views/admin/reports/show.blade.php`.
  - Printable Report: `resources/views/admin/reports/print.blade.php`.

---

## Important Project Structure
```
e:\Projects\Solar/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/              # Admin CRUD, review, assignment, analytics
│   │   │   ├── Engineer/           # Mobile engineer reporting, drafts, photo uploads
│   │   │   └── AuthController.php  # Session login/logout with role redirection
│   │   └── Middleware/             # AdminMiddleware, EngineerMiddleware
│   └── Models/                     # Eloquent models (Company, Customer, Service, Report, etc.)
├── database/
│   ├── migrations/                 # Strict relational schema
│   └── seeders/DatabaseSeeder.php  # Default admin, companies, engineers, sample customers/sites
├── resources/
│   ├── css/app.css                 # Tailwind CSS v4 solar theme
│   ├── js/app.js                   # Alpine.js initialization
│   └── views/
│       ├── admin/                  # Desktop-optimized responsive admin panels
│       ├── engineer/               # Mobile-first field engineer touch screens
│       ├── auth/                   # Branded login with demo account selectors
│       └── layouts/                # Admin & Mobile layouts with notifications & connectivity badges
├── routes/web.php                  # Web application routes
├── tests/Feature/                  # Full 25-point acceptance workflow tests
└── public/storage/                 # Symlinked uploads (photos, documents, logos)
```

---

## Where To Change What

| Feature / Concern | Primary Location | Related Locations |
|---|---|---|
| **Authentication & Role Redirection** | `app/Http/Controllers/AuthController.php` | `app/Http/Middleware/`, `resources/views/auth/login.blade.php` |
| **Companies / Entities** | `app/Http/Controllers/Admin/CompanyController.php` | `app/Models/Company.php`, `resources/views/admin/companies/` |
| **Field Engineers / Employees** | `app/Http/Controllers/Admin/EmployeeController.php` | `app/Models/User.php`, `resources/views/admin/employees/` |
| **Customers & Plant Sites** | `app/Http/Controllers/Admin/CustomerController.php`, `SiteController.php` | `app/Models/Customer.php`, `Site.php`, `resources/views/admin/customers/`, `sites/` |
| **Service Job Dispatch** | `app/Http/Controllers/Admin/ServiceController.php` | `app/Models/Service.php`, `ServiceAssignment.php`, `resources/views/admin/services/` |
| **Mobile 10-Step Wizard** | `resources/views/engineer/reports/edit.blade.php` | `app/Http/Controllers/Engineer/ReportController.php`, `app/Models/ReportData.php` |
| **Photo / Camera Uploads** | `app/Http/Controllers/Engineer/ReportController.php@uploadPhoto` | `app/Models/ReportPhoto.php`, `resources/views/engineer/reports/edit.blade.php` |
| **Admin Report Review & Approval** | `app/Http/Controllers/Admin/ReportController.php` | `resources/views/admin/reports/show.blade.php`, `print.blade.php` |
| **Correction Cycle** | `app/Http/Controllers/Admin/ReportController.php@requestCorrection` | `resources/views/admin/reports/show.blade.php`, `engineer/reports/edit.blade.php` |
| **Company-Wise Analytics** | `app/Http/Controllers/Admin/AnalyticsController.php` | `resources/views/admin/analytics/index.blade.php`, `admin/dashboard.blade.php` |
| **Audit Logging** | `app/Models/AuditLog.php` | `app/Http/Controllers/Admin/AuditLogController.php`, `resources/views/admin/audit_logs/` |

---

## Database Models & Relationships
- `Company` &rarr; hasMany `Customer`, `User` (employees), `Service`, `Report`.
- `Customer` &rarr; belongsTo `Company`, hasMany `Site`, `Service`, `Report`.
- `Site` &rarr; belongsTo `Customer`, hasMany `Service`, `Report`.
- `Service` &rarr; belongsTo `Company`, `Customer`, `Site`, `ServiceType`, `User` (assigned), hasMany `ServiceAssignment`, hasOne `Report`.
- `Report` &rarr; belongsTo `Service`, `Company`, `Customer`, `Site`, `User` (engineer), `User` (reviewer), hasMany `ReportData`, `ReportPhoto`, `ReportDocument`.
- `Notification` &rarr; belongsTo `User`.
- `AuditLog` &rarr; polymorphic `auditable`, belongsTo `User`.

---

## Business Rules & Workflows

### 1. Service Dispatch:
- Admin creates service with Company, Customer, Site, Type, Priority, Date, and Instructions.
- Status starts as `unassigned` or `assigned`.
- When assigned, the engineer receives an instant in-app notification.

### 2. Field Execution:
- Engineer sees assigned job on mobile dashboard.
- Tapping **Start Service** updates status to `in_progress` and initializes a `draft` Report.
- Engineer navigates the 10-step wizard.
- **Draft Persistence**: Auto-saves locally in `localStorage` on mobile device and persists to database via `saveDraft` endpoint.
- Direct camera integration allows snapping cleaning and battery photos on site.

### 3. Submission & Locking:
- Step 10 presents a full summary preview and confirmation dialog before submission.
- On submit, report status becomes `submitted` (or `resubmitted`), and service status becomes `report_submitted`.
- The report becomes read-only for the engineer while under review.

### 4. Verification & Approval:
- Admin reviews all 10 steps, photos, readings, and timestamps.
- **Approve**: Report becomes `approved` (permanently locked), service marked `completed`, engineer notified.
- **Request Correction**: Admin must input mandatory notes. Report status becomes `correction_required`. Engineer receives notification with admin comments, form re-opens for editing, and engineer resubmits (`resubmitted`).
- **Reject**: Admin inputs rejection reason.

### 5. Multi-Company Reporting:
- All services, reports, completions, and corrections are aggregated per company dynamically (no hardcoded figures).
- Filterable by Today, This Week, This Month, Custom Date Range, Company, Employee, Customer, Site, Service Type, and Status.

---

## Local Development & Setup

### Prerequisites
- PHP 8.4+ with `pdo_sqlite`, `pdo_mysql`, `gd`, `fileinfo`, `mbstring`, `curl`
- Composer 2.x
- Node.js 20+ & npm

### Initial Run
```bash
# 1. Install dependencies
composer install
npm install

# 2. Setup environment & database
php -r "file_exists('.env') || copy('.env.example', '.env');"
php artisan key:generate
php artisan migrate --seed
php artisan storage:link

# 3. Compile assets
npm run build

# 4. Start local development server
php artisan serve
```

### Default Demo Credentials
- **Admin Portal**: `admin@solar.local` / `password123`
- **Field Engineer (Sun on Earth)**: `raj@solar.local` / `password123`
- **Field Engineer (Sabha)**: `kiran@solar.local` / `password123`

---

## Testing
Run the automated test suite covering authentication barriers and the complete 25-point acceptance workflow:
```bash
php artisan test
```

---

## Deployment Standards (Hostinger VPS / Production)
- **Repository**: `https://github.com/ManojKshetrapalam/solarfiledops.git`
- **Target Host**: Hostinger Server (`myworks.sbs` / `31.97.225.172`)
- **Web Root**: `public/`
- Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`.
- Run `php artisan config:cache`, `route:cache`, `view:cache`.
- Symlink storage: `php artisan storage:link`.
