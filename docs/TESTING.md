# SponsorFlow — Testing Documentation & Test Cases

This document details the functional, security, validation, and integration test cases executed on the **SponsorFlow** platform.

---

## 1. Test Summary
- **Test Strategy:** Automated HTTP Integration Testing (`tests/test_suite.php`) + Manual Functional Testing.
- **Environment:** PHP 8.2 (Built-in Server), MySQL 8.0 / Aiven Cloud MySQL with SSL.
- **Pass Rate:** 100% (All test cases passed).

---

## 2. Test Cases Table

| Test Case ID | Feature | Input / Precondition | Expected Result | Actual Result | Status |
|---|---|---|---|---|---|
| **TC-AUTH-01** | Public Landing Page | Unauthenticated GET `/index.php` | 200 OK; renders hero section, problem overview, and CTA buttons | Rendered landing page properly | **PASS** |
| **TC-AUTH-02** | Auth Guard (Unauthorized Access) | Unauthenticated GET `/dashboard.php` | 302 Redirect to `/login.php`; denies access to private data | Redirected to `login.php` | **PASS** |
| **TC-AUTH-03** | Login Page Render | GET `/login.php` | 200 OK; renders email/password form with CSRF token | Form rendered with hidden token | **PASS** |
| **TC-AUTH-04** | Invalid Credentials Login | POST `/login.php` with `admin@sponsorflow.local` and `WrongPassword!` | 200 OK; shows "Invalid email address or password" error banner | Displayed validation error banner | **PASS** |
| **TC-AUTH-05** | Empty Input Validation | POST `/login.php` with empty email and password | 200 OK; shows "Please provide both your email address and password" | Required fields alert displayed | **PASS** |
| **TC-AUTH-06** | Successful Admin Login | POST `/login.php` with `admin@sponsorflow.local` and `Admin@123` | 302 Redirect to `dashboard.php`; establishes secure session; regenerates session ID | Logged in and redirected to dashboard | **PASS** |
| **TC-AUTH-07** | User Logout | GET `/logout.php` with active session | Session destroyed, cookies cleared, 302 redirect to `login.php` | Successfully logged out | **PASS** |
| **TC-DASH-01** | Database KPI Metrics | Authenticated GET `/dashboard.php` | Displays 8 KPI cards calculated via SQL; values match MySQL counts | Displayed all 8 KPI cards accurately | **PASS** |
| **TC-DASH-02** | Pipeline Bar Chart | Authenticated GET `/dashboard.php` | Renders 7 pipeline stages with proportioned CSS width bars | Proportional bars rendered | **PASS** |
| **TC-DASH-03** | Upcoming Follow-ups Feed | Authenticated GET `/dashboard.php` | Displays nearest 5 non-completed follow-ups ordered by date | Showed top 5 pending tasks | **PASS** |
| **TC-COMP-01** | Companies Listing | Authenticated GET `/companies.php` | 200 OK; loads all seeded companies from MySQL with primary contacts | Loaded 24 seeded companies | **PASS** |
| **TC-COMP-02** | Company Search Query | GET `/companies.php?q=NovaGrid` | Filters list to display only matching company | Displayed NovaGrid Technologies | **PASS** |
| **TC-COMP-03** | Company Status Filter | GET `/companies.php?status=Confirmed` | Displays only companies with status Confirmed | Only confirmed sponsors shown | **PASS** |
| **TC-COMP-04** | Company Creation | POST `/companies.php` with valid company name, industry, location, status | Inserts record into MySQL, logs activity, redirects with success flash message | Record saved to database | **PASS** |
| **TC-COMP-05** | Company Update | POST `/companies.php` with updated valuation and status | Updates MySQL record and logs activity | Record updated in database | **PASS** |
| **TC-COMP-06** | Company Deletion | POST `/companies.php` with action `delete` and valid ID | Removes company and cascade deletes associated contacts/proposals | Deleted company from MySQL | **PASS** |
| **TC-VIEW-01** | Company 360° Profile | GET `/company-details.php?id=1` | Displays profile, key contacts, outreach history, proposals, sponsorships, deliverables, and timeline | Comprehensive profile rendered | **PASS** |
| **TC-VIEW-02** | Non-Existent Company ID | GET `/company-details.php?id=99999` | Displays friendly "Company not found" message with back link | Friendly message displayed | **PASS** |
| **TC-CONT-01** | Contacts List | GET `/contacts.php` | Displays contacts table with company names and relationship badges | All 30 seeded contacts displayed | **PASS** |
| **TC-CONT-02** | Contact Validation (Invalid Email) | POST `/contacts.php` with `not-an-email` | Rejects submission; displays "Please provide a valid email address" | Blocked invalid email | **PASS** |
| **TC-CONT-03** | Contact Validation (Invalid Phone) | POST `/contacts.php` with `12345` | Rejects submission; displays "Please provide a valid 10-digit Indian mobile number" | Blocked non-10-digit phone | **PASS** |
| **TC-OUT-01** | Outreach Logging | POST `/outreach.php` with valid touchpoint data | Inserts into `outreach`; updates `contacts.last_contacted` timestamp | Saved touchpoint & updated contact | **PASS** |
| **TC-FOL-01** | Follow-up Classification | GET `/followups.php` | Dynamically labels tasks as Overdue, Today, or Upcoming based on database date | Auto-classified according to current date | **PASS** |
| **TC-FOL-02** | Quick Mark Complete | POST `/followups.php` with action `complete` | Sets status to `Completed`; removes from pending queue | Marked completed immediately | **PASS** |
| **TC-PROP-01** | Proposal Creation | POST `/proposals.php` with package and amount | Inserts proposal into MySQL with auto-generated quotation # | Proposal P-119 generated | **PASS** |
| **TC-PROP-02** | Proposal Date Validation | POST `/proposals.php` with valid date earlier than sent date | Rejects submission; displays "Valid Until date must be on or after Sent date" | Error message displayed | **PASS** |
| **TC-PROP-03** | Proposal Conversion | POST `/proposals.php` with action `convert` on Accepted proposal | Creates new record in `sponsorships`, updates company status to `Confirmed`, logs activity | Sponsorship created; company Confirmed | **PASS** |
| **TC-SPON-01** | Sponsorships Table & Totals | GET `/sponsorships.php` | Displays contracts list; calculates Total Committed, Paid, and Outstanding balances | Balances calculated from MySQL | **PASS** |
| **TC-DEL-01** | Deliverables Progress Bar | GET `/deliverables.php` | Calculates percentage of completed items and renders progress bar | Rendered percentage and progress bar | **PASS** |
| **TC-DEL-02** | Deliverable Mark Complete | POST `/deliverables.php` with action `complete` | Updates status to `Completed`, records completion date as current timestamp | Updated status and date | **PASS** |
| **TC-REP-01** | Analytics Aggregations | GET `/reports.php` | Computes conversion rate, response rate, open pipeline, and category distributions | Real-time analytics rendered | **PASS** |
| **TC-API-01** | JSON API Companies | GET `/api/companies.php` with auth session | 200 OK; returns valid JSON with `{"success": true, "data": [...]}` | Returned valid JSON payload | **PASS** |
| **TC-SEC-01** | CSRF Attack Simulation | POST `/companies.php` with missing or invalid `csrf_token` | 403 Forbidden; halts execution without database write | Blocked request with 403 Forbidden | **PASS** |
| **TC-SEC-02** | SQL Injection Resistance | Inputting `' OR 1=1 --` into search query `q` | Interprets string literally through PDO prepared parameter binding | Safely treated as literal string; 0 injections | **PASS** |
| **TC-SEC-03** | XSS Escaping | Creating company with name `<script>alert(1)</script>` | HTML entities escaped via `htmlspecialchars`; no script execution | Rendered safely as `&lt;script&gt;` | **PASS** |

---

## 3. Automated Test Suite Execution Log

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
