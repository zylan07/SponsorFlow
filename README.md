# SponsorFlow · Event Sponsorship & Partnership Management System
> Full-Stack Web Application for Web Technology Lab Project Submission

[![PHP 8+](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://www.php.net/)
[![MySQL 8+](https://img.shields.io/badge/MySQL-8.0%2B-orange.svg)](https://www.mysql.com/)
[![Database](https://img.shields.io/badge/Database-Local%20MySQL-success.svg)]()
[![Security](https://img.shields.io/badge/Security-Prepared%20Statements%20%7C%20CSRF%20%7C%20Bcrypt-green.svg)]()

---

## 1. Project Overview
**SponsorFlow** is an industrial-style, full-stack Event Sponsorship & Partnership Management System designed to centralize and automate sponsorship operations for college symposia, hackathons, academic conferences, and cultural fests.

It replaces disorganized spreadsheets, lost emails, and chaotic WhatsApp messages with a unified, database-driven B2B CRM covering the complete sponsorship lifecycle:
```
Prospect → Outreach → Follow-up → Proposal → Negotiation → Confirmed → Payment → Deliverables → Reporting
```

---

## 2. Key Features

- **Session-Based Authentication:** Secure email/password login using PHP sessions, bcrypt password hashing (`password_hash`, `password_verify`), and session regeneration to prevent session fixation.
- **Real-Time KPI Dashboard:** Real-time metrics calculated directly from MySQL queries (Total Companies, Active Prospects, Proposals Sent, Confirmed Sponsors, Pipeline Value, Confirmed Value, Overdue Follow-ups, and Deliverable Progress).
- **Companies Management (CRUD):** Complete company directory with status badges, industry filters, full-text search, modal forms, and cascading deletion.
- **Company 360° Profile:** Comprehensive relationship view aggregating company profile, key contacts, communication history, follow-ups, proposals, sponsorships, deliverables, and chronological activity timeline.
- **Contacts Directory:** Relationship management tracking multiple corporate contacts per sponsor with role designations, contact methods, and relationship heat levels (New, Warm, Engaged, Strategic, Inactive).
- **Outreach Interaction Logging:** Logs emails, phone calls, WhatsApp messages, LinkedIn conversations, and in-person meetings with assigned team members and next-action triggers.
- **Automated Follow-up Engine:** Automatically calculates and classifies tasks as **Overdue**, **Due Today**, or **Upcoming** based on real-time database timestamps with 1-click completion.
- **Proposals & 1-Click Conversion:** Create and track formal proposals across tiers. Accepted proposals can be converted directly into commercial sponsorship commitments with one click.
- **Commercial Sponsorships Governance:** Tracks contract status (Draft, Sent, Signed), payment statuses (Pending, Partial, Paid, Overdue), and cash flow totals.
- **Deliverables Fulfillment Matrix:** Deadlines, task owners, progress bars, and completion logging for all promised entitlements.
- **Executive Analytics & Reporting:** Real-time financial breakdowns, outreach response rates, lead conversion rates, and industry/tier distributions.
- **REST JSON API:** Clean JSON endpoints under `api/` for programmatic data access.

---

## 3. Technology Stack

- **Frontend:** HTML5, CSS3 (Modern SaaS design system, CSS Grid, Flexbox, Dark Mode), Vanilla JavaScript (no heavy frontend frameworks).
- **Backend:** PHP 8.2+ (Modular procedural MVC-style controllers, PDO extension, native sessions).
- **Database:** Local MySQL 8.0+ Server (InnoDB engine, utf8mb4, foreign key constraints, cascading updates/deletions).
- **Security:** PDO prepared statements (zero SQL concatenation), CSRF token verification on all POST actions, HTML entity escaping (`htmlspecialchars`), bcrypt hashing.

---

## 4. Project Structure

```
SponsorFlow/
├── index.php                 # Public landing page with problem/solution workflow
├── login.php                 # Secure login authentication portal
├── logout.php                # Session termination handler
├── dashboard.php             # Database-driven executive dashboard
├── companies.php             # Companies management (CRUD, search, filters)
├── company-details.php       # Company 360° relationship view
├── contacts.php              # Stakeholder contacts management
├── outreach.php              # Multi-channel communications tracker
├── followups.php             # Follow-up scheduling with date classification
├── proposals.php             # Commercial proposals & 1-click conversion
├── sponsorships.php          # Commercial contracts & payment governance
├── deliverables.php          # Deliverables fulfillment & progress tracking
├── reports.php               # Analytics, conversion rates & distributions
│
├── config/
│   ├── database.php          # Active database credentials (ignored by Git)
│   └── database.example.php  # Template for local MySQL setup
│
├── includes/
│   ├── auth.php              # Session guards, RBAC, and auth helpers
│   ├── header.php            # App shell header, navigation bar, flash messages
│   ├── sidebar.php           # Dynamic sidebar with active page indicator
│   ├── footer.php            # App shell footer, modals, and toast triggers
│   ├── functions.php         # XSS sanitization, formatters, activity logger
│   └── csrf.php              # Cryptographic CSRF token generation & validation
│
├── api/                      # JSON API endpoints
│   ├── companies.php
│   ├── contacts.php
│   ├── outreach.php
│   ├── followups.php
│   ├── proposals.php
│   ├── sponsorships.php
│   └── deliverables.php
│
├── assets/
│   ├── css/
│   │   └── style.css         # Responsive SaaS CRM stylesheet with dark mode
│   └── js/
│       ├── app.js            # Core Vanilla JS utilities (modals, toasts, validation)
│       ├── dashboard.js      # Dashboard progress bar animations
│       ├── companies.js      # Client-side instant table filtering
│       ├── contacts.js
│       ├── outreach.js
│       ├── followups.js
│       ├── proposals.js
│       ├── sponsorships.js
│       ├── deliverables.js
│       └── reports.js
│
├── database/
│   ├── schema.sql            # DDL: 9 relational tables with foreign keys
│   ├── seed.sql              # Realistic seed data (24 companies, 30 contacts...)
│   └── init_db.php           # Automated database setup & migration script
│
├── tests/
│   └── test_suite.php        # Automated test suite (18 integration checks)
│
├── docs/
│   ├── PROJECT_DOCUMENTATION.md   # Complete academic project report
│   ├── DATABASE_DOCUMENTATION.md  # Detailed table definitions & dictionaries
│   ├── ER_DIAGRAM.md              # Entity-Relationship diagram (Mermaid)
│   ├── DFD.md                     # Data Flow Diagrams (Level 0 and Level 1)
│   └── TESTING.md                 # Test cases matrix & verification results
│
├── .gitignore                # Protects credentials, logs, and temporary files
├── README.md                 # Main project documentation
└── GIT_HISTORY.md            # Git development history log
```

---

## 5. Local Database Configuration

SponsorFlow connects to a local MySQL 8.0+ instance via PDO:

1. Ensure MySQL Server is running locally (default port `3306`).
2. Copy `config/database.example.php` to `config/database.php` if setting up on a new machine:
   ```bash
   cp config/database.example.php config/database.php
   ```
3. Configuration in `config/database.php`:
   ```php
   define('DB_HOST', '127.0.0.1');
   define('DB_PORT', '3306');
   define('DB_NAME', 'sponsorflow');
   define('DB_USER', 'root');
   define('DB_PASSWORD', ''); // Set your local root password if configured
   ```
   > **SECURITY NOTICE:** `config/database.php` is listed in `.gitignore`. Never commit or expose passwords to source control.

---

## 6. One-Click Database Initialization & Seeding

Run the migration script to automatically create the database, all 9 relational tables, and load realistic seed data:

```bash
php database/init_db.php
```

You will see:
```
============================================
 SponsorFlow Database Setup & Migration 
============================================
Connecting to MySQL server (127.0.0.1:3306)...
 Database 'sponsorflow' verified/created.
 Connected to database successfully!
Reading schema.sql...
 Schema created successfully (all 9 tables)!
Reading seed.sql...
 Realistic seed data loaded successfully!
--------------------------------------------
Verification of Data Records:
 - users: 3 rows
 - companies: 24 rows
 - contacts: 30 rows
 - outreach: 33 rows
 - followups: 22 rows
 - proposals: 18 rows
 - sponsorships: 11 rows
 - deliverables: 28 rows
 - activities: 35 rows
============================================
 Database setup completed successfully!
```

---

## 7. How to Run Locally

You can run SponsorFlow using PHP's built-in development server:

```bash
# Navigate to the project root
cd SponsorFlow

# Start the PHP server
php -S 127.0.0.1:8000
```

Open your browser and navigate to:
**[http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

## 8. Demo Credentials

The database is seeded with 3 demo accounts (all passwords hashed with bcrypt):

| Role | Email | Password | Access Level |
|---|---|---|---|
| **Administrator** | `admin@sponsorflow.local` | `Admin@123` | Full system access |
| **Manager** | `manager@sponsorflow.local` | `Manager@123` | Sponsorships, proposals, operations |
| **Staff** | `staff@sponsorflow.local` | `Staff@123` | Outreach and follow-up logging |

---

## 9. Running the Automated Test Suite

An automated test suite validates all modules, authentication guards, CSRF protections, and live database CRUD operations:

```bash
php tests/test_suite.php
```

Expected result:
```
Running: Landing Page (GET /index.php)... [PASS]
Running: Auth Guard on Protected Route (GET /dashboard.php)... [PASS]
Running: Login Page (GET /login.php)... [PASS]
Running: Invalid Credentials Login (POST /login.php)... [PASS]
Running: Valid Authentication (POST /login.php)... [PASS]
Running: Authenticated Dashboard (GET /dashboard.php)... [PASS]
Running: Companies Management (GET /companies.php)... [PASS]
Running: Company 360 Details (GET /company-details.php?id=1)... [PASS]
Running: Contacts Module (GET /contacts.php)... [PASS]
Running: Outreach Module (GET /outreach.php)... [PASS]
Running: Follow-ups Module (GET /followups.php)... [PASS]
Running: Proposals Module (GET /proposals.php)... [PASS]
Running: Sponsorships Module (GET /sponsorships.php)... [PASS]
Running: Deliverables Module (GET /deliverables.php)... [PASS]
Running: Executive Reports (GET /reports.php)... [PASS]
Running: JSON API Endpoint (GET /api/companies.php)... [PASS]
Running: Live Database CRUD Cycle (Companies)... [PASS]
Running: User Logout (GET /logout.php)... [PASS]
============================================
Tests Passed: 18 | Tests Failed: 0
============================================
```

---

## 10. Security Notes

- **Zero Plaintext Passwords:** Authenticated via `password_hash()` and `password_verify()`.
- **Prepared Statements:** 100% of queries use PDO prepared statements with bound parameters (`:param`).
- **CSRF Protection:** State-changing requests validate `csrf_token` with `hash_equals()`.
- **XSS Prevention:** All user-supplied output is escaped using `htmlspecialchars()`.
- **Credential Protection:** `config/database.php` is strictly ignored by Git.

---

## 11. Screenshots Section (Placeholders)

| Screen | Description |
|---|---|
| `screenshots/landing_page.png` | Public landing page with problem/solution workflow |
| `screenshots/login.png` | Secure authentication portal |
| `screenshots/dashboard.png` | Real-time database KPI metrics and pipeline view |
| `screenshots/companies.png` | Companies management with modal CRUD |
| `screenshots/company_details.png` | 360° company profile with relationship timeline |
| `screenshots/proposals.png` | Proposal pipeline and 1-click sponsorship conversion |
| `screenshots/deliverables.png` | Deliverables fulfillment matrix and progress indicators |
| `screenshots/reports.png` | Executive analytics, conversion rates, and distributions |

---

## 12. Future Enhancements

1. Automated PDF invoice and formal payment receipt generator in PHP.
2. Direct email dispatch integration via SMTP / transactional APIs.
3. Webhook alerts for Slack and WhatsApp when follow-ups are overdue.
4. Role-based field restrictions for student volunteers.
