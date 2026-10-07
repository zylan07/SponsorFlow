# SponsorFlow — Git Development & Commit History

This repository represents the consolidated, full-stack **SponsorFlow** web application for Web Technology Lab. The project transitioned from an initial static HTML/CSS/JS prototype into a fully integrated, database-driven PHP 8+ and MySQL web application.

---

## Commit Milestone History

### Milestone 1: Initial SponsorFlow Prototype
**Commit Hash / Reference:** `milestone/v1.0-static-prototype`
- Created core UI layout, branding, and color palette (`--pri: #0b6e6e`).
- Established landing page concept, login view, and dashboard navigation.
- Created preliminary responsive layout and modal forms.
- Structured initial mock dataset of 24 fictional sponsor companies.

### Milestone 2: Complete PHP MySQL Integration
**Commit Hash / Reference:** `milestone/v2.0-fullstack-integration`
- Replaced client-side mock storage with normalized MySQL 8+ database schema (9 tables).
- Created `config/database.php` and `config/database.example.php` supporting Aiven Cloud MySQL with SSL encryption via PDO.
- Implemented real session-based authentication in `login.php` using bcrypt `password_hash()` and `password_verify()`.
- Implemented CSRF protection (`includes/csrf.php`) and XSS sanitization (`includes/functions.php`).
- Converted dashboard into a real database-driven analytics hub (`dashboard.php`) with 8 live KPI queries.
- Created full CRUD modules with prepared statements for:
  - Companies (`companies.php`)
  - Company 360° View (`company-details.php`)
  - Contacts (`contacts.php`)
  - Outreach Communications (`outreach.php`)
  - Follow-up Scheduler (`followups.php`) with dynamic date classification
  - Proposals (`proposals.php`) with 1-click conversion to sponsorships
  - Sponsorships & Contracts (`sponsorships.php`)
  - Deliverables Fulfillment (`deliverables.php`) with progress bars
  - Executive Reports (`reports.php`) with real-time conversion ratios
- Built JSON API endpoints under `api/`.
- Seeded relational demo database with realistic records (`database/seed.sql`).

### Milestone 3: Final SponsorFlow Application
**Commit Hash / Reference:** `milestone/v3.0-final-submission`
- Verified end-to-end integration with live MySQL server.
- Built automated test runner (`tests/test_suite.php`) covering 18 integration tests with a 100% pass rate.
- Authored academic and industrial documentation suite in `docs/`:
  - `PROJECT_DOCUMENTATION.md`
  - `DATABASE_DOCUMENTATION.md`
  - `ER_DIAGRAM.md`
  - `DFD.md`
  - `TESTING.md`
  - `VIVA_PREPARATION.md`
- Configured `.gitignore` to safeguard database credentials and environment files.
- Completed project cleanup and final submission readiness.

### Milestone 4: Local MySQL Migration
**Commit Hash / Reference:** `milestone/v3.1-local-mysql-migration`
- Migrated primary database infrastructure from external cloud hosting to local native MySQL 8.0 server (`127.0.0.1:3306`).
- Updated `config/database.php` and `config/database.example.php` for local PDO connections without SSL dependencies.
- Updated `database/init_db.php` to automatically check and create the `sponsorflow` database if not present, then execute `schema.sql` and `seed.sql`.
- Removed obsolete cloud CA certificate files and updated `.gitignore`.
- Re-executed automated test suite against local MySQL: 18/18 tests passed (100%).
- Updated all academic documentation to reflect the local MySQL runtime environment.

