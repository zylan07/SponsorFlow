# SponsorFlow — Event Sponsorship & Partnership Management System
## Web Technology Lab Individual Project Documentation

---

### 1. Project Title
**SponsorFlow: Event Sponsorship & Partnership Management System**

---

### 2. Abstract
Event organization for college symposia, hackathons, academic conferences, and cultural fests requires sustained coordination with corporate sponsors and industry partners. Historically, organizing committees rely on ad-hoc combinations of personal spreadsheets, unstructured email chains, and messaging apps. This fragmented approach leads to missed follow-ups, lost proposal versions, delayed invoice tracking, and unfulfilled sponsor deliverables (such as missing logo placements or unassigned booth spaces).

**SponsorFlow** is a full-stack, industrial-grade web application engineered using **HTML5, CSS3, Vanilla JavaScript, PHP 8+**, and **MySQL 8+**. It centralizes the complete lifecycle of event sponsorships into an integrated CRM: from cold lead prospecting, communications logging, follow-up scheduling, and proposal generation, to contract signing, payment milestone monitoring, on-ground deliverable fulfillment, and real-time executive analytics.

---

### 3. Problem Statement
Sponsorship coordination in institutions and event organizations suffers from several structural inefficiencies:
1. **Scattered Contact Records:** Sponsor contacts are stored across individual students' phones and disjointed Google Sheets, resulting in orphaned records when student organizers graduate.
2. **Untracked Communication & Broken Follow-ups:** Organizing teams lose track of whether a corporate marketing team requested a deck or requested a callback, causing deals to go cold.
3. **Disjointed Proposal & Commercial Governance:** Multiple versions of sponsorship brochures and package costs float around, creating confusion regarding committed entitlements.
4. **Deliverable Slippage:** On the day of the event, promised entitlements—such as keynote speaking slots, stage banner dimensions, and interview booths—fail to materialize, damaging institutional credibility.
5. **Lack of Real-Time Financial Visibility:** Faculty coordinators and treasurers lack a single source of truth for total committed funding versus actual cash collected.

---

### 4. Existing System
In existing manual and spreadsheet-based systems:
- Data is stored in static spreadsheets without referential constraints.
- Follow-ups require manual calendar entries that lack alerts and contextual meeting history.
- Proposals exist as static PDFs without linkage to actual payment or deliverable records.
- No role-based access control or audit trail exists for tracking modifications.
- Calculating metrics (such as conversion rates or outstanding receivables) requires manual formula recalculations prone to human error.

---

### 5. Proposed System
The proposed SponsorFlow platform introduces an integrated relational database architecture:
- **Centralized Sponsor Roster:** Company profiles linked relationally to multiple corporate contacts.
- **Unified Outreach Log:** Tracks emails, phone calls, WhatsApp messages, and in-person meetings with assigned committee owners.
- **Automated Follow-up Engine:** Automatically flags overdue, due-today, and upcoming tasks based on database timestamps.
- **End-to-End Proposal Pipeline:** Allows drafting, issuing, and converting accepted proposals directly into confirmed commercial commitments.
- **Contract & Payment Governance:** Tracks contract drafting, execution status, and payment milestones (Pending, Partial, Paid, Overdue).
- **Deliverables Fulfillment Matrix:** Assigns specific event deliverables (e.g. stage banners, stall demarcations, passes) with deadlines and completion statuses.
- **Database-Driven Dashboard & Analytics:** Real-time KPI aggregation using SQL queries with zero hardcoded values.

---

### 6. Objectives
1. Build a responsive, clean B2B SaaS web interface using HTML5, CSS3, and Vanilla JavaScript.
2. Implement server-side business logic and session-based authentication in PHP 8+.
3. Architect a normalized MySQL database schema supporting Local MySQL Server.
4. Eliminate SQL injection vulnerabilities via PDO prepared statements.
5. Protect state-changing HTTP requests with Cross-Site Request Forgery (CSRF) tokens.
6. Provide an intuitive user experience with modal-based CRUD operations and client/server validation.

---

### 7. Industrial Use Case
In industrial and corporate conference management (e.g., tech summits, developer conventions, university placement fests), sponsorship sales follow a multi-stage B2B funnel. SponsorFlow models this industrial sales cycle:
```
Lead Generation (Prospect)
   ↓
Initial Engagement (Contacted / Outreach)
   ↓
Opportunity Qualification (Interested / Meeting)
   ↓
Commercial Proposal (Proposal Sent / Negotiation)
   ↓
Contract Finalization (Accepted Proposal → Confirmed Sponsorship)
   ↓
Financial Disbursement (Invoicing → Payment Received)
   ↓
On-Ground Delivery (Deliverables Fulfillment)
   ↓
Post-Event Reporting (Analytics & Conversion Metrics)
```

---

### 8. Features
- **Session-Based Authentication:** Secure email/password login with bcrypt password hashing (`password_hash` / `password_verify`).
- **Real-Time KPI Dashboard:** Cards displaying Total Companies, Active Prospects, Proposals Sent, Confirmed Sponsors, Pipeline Value, Confirmed Value, and Deliverable completion percentage.
- **Companies CRUD:** Filter by status and industry, full-text search, create with primary contact, update, and cascade delete.
- **Company 360° View:** A consolidated relationship view aggregating contacts, outreach, follow-ups, proposals, sponsorships, deliverables, and activity history.
- **Contacts Directory:** Support for multiple contacts per company with role designations and relationship heat levels (New, Warm, Engaged, Strategic, Inactive).
- **Outreach Tracker:** Multi-channel communication tracking (Email, Phone, WhatsApp, LinkedIn, Meeting) with response logging and next-action triggers.
- **Follow-up Engine:** Auto-categorization of follow-ups into Overdue, Due Today, and Upcoming with 1-click completion.
- **Proposal Lifecycle Management:** Package selection, currency amounts, validity tracking, and 1-click conversion into confirmed sponsorships.
- **Commercial Contract & Payment Tracking:** Governance of contract statuses (Draft, Sent, Signed) and payment terms.
- **Deliverable Fulfillment Tracking:** Deadlines, task owners, progress bars, and completion logging.
- **Executive Analytics & Reporting:** Real-time financial breakdowns, conversion rates, response rates, and sponsor distributions.

---

### 9. Modules
1. **Authentication Module:** `login.php`, `logout.php`, `includes/auth.php`
2. **Executive Dashboard:** `dashboard.php`, `assets/js/dashboard.js`
3. **Companies Module:** `companies.php`, `company-details.php`, `api/companies.php`
4. **Contacts Module:** `contacts.php`, `api/contacts.php`
5. **Outreach Module:** `outreach.php`, `api/outreach.php`
6. **Follow-ups Module:** `followups.php`, `api/followups.php`
7. **Proposals Module:** `proposals.php`, `api/proposals.php`
8. **Sponsorships Module:** `sponsorships.php`, `api/sponsorships.php`
9. **Deliverables Module:** `deliverables.php`, `api/deliverables.php`
10. **Reports & Analytics Module:** `reports.php`, `assets/js/reports.js`

---

### 10. Technology Stack
- **Frontend Layer:** HTML5 (semantic elements, `<dialog>`, form inputs), CSS3 (CSS custom properties, CSS Grid, Flexbox, dark mode media queries), Vanilla JavaScript (DOM manipulation, Fetch API, RegEx validation).
- **Backend Layer:** PHP 8.2+ (Procedural and modular architecture, PDO extension, native session handling).
- **Database Layer:** MySQL 8.0+ (InnoDB engine, utf8mb4 charset, foreign keys, cascading constraints) running locally on port 3306.
- **Security:** Bcrypt hashing, CSRF tokens (`hash_equals`), XSS sanitization (`htmlspecialchars`), Prepared Statements.

---

### 11. System Architecture
```
+-------------------------------------------------------------+
|                        Browser Client                       |
|       (HTML5 Forms, CSS3 Responsive Grid, Vanilla JS)       |
+-------------------------------------------------------------+
                              |
                         HTTP Requests
                              |
                              v
+-------------------------------------------------------------+
|                       PHP 8+ Backend                        |
|  - includes/auth.php      (Session Guards & RBAC)           |
|  - includes/csrf.php      (CSRF Protection Engine)          |
|  - includes/functions.php (Formatters, XSS Escaping)        |
|  - Page Controllers       (CRUD Operations, Business Logic) |
|  - API Endpoints          (api/*.php REST JSON endpoints)   |
+-------------------------------------------------------------+
                              |
                        PDO (Prepared SQL)
                              |
                              v
+-------------------------------------------------------------+
|                 Local MySQL 8+ Database                     |
|  - users            - companies          - contacts         |
|  - outreach         - followups          - proposals        |
|  - sponsorships     - deliverables       - activities       |
+-------------------------------------------------------------+
```

---

### 12. Database Design & Tables
The database consists of 9 relational tables:
1. `users`: Stores system users, email credentials, bcrypt password hashes, and access roles (`admin`, `manager`, `staff`).
2. `companies`: Primary entity tracking company name, industry, location, pipeline stage, and valuation.
3. `contacts`: Stakeholders linked to companies with designation, email, phone, and relationship temperature.
4. `outreach`: Interaction logs linked to companies and contacts.
5. `followups`: Actionable tasks with date deadlines, priorities, and completion flags.
6. `proposals`: Commercial proposals with quotation numbers, packages, values, and status.
7. `sponsorships`: Executed sponsorships converted from proposals or logged directly.
8. `deliverables`: Physical and digital entitlements tied to sponsorships and companies.
9. `activities`: Audit log recording chronological events per company.

---

### 13. Relationships & Foreign Key Integrity
- `companies (1) ──< contacts (N)`: `ON DELETE CASCADE` ensures deleting a company cleans up its contacts.
- `companies (1) ──< outreach (N)`: `ON DELETE CASCADE` removes interactions if company is removed.
- `contacts (1) ──< outreach (N)`: `ON DELETE SET NULL` preserves communication logs even if a specific contact leaves the sponsor.
- `companies (1) ──< followups (N)`: `ON DELETE CASCADE`.
- `companies (1) ──< proposals (N)`: `ON DELETE CASCADE`.
- `proposals (1) ──< sponsorships (N)`: `ON DELETE SET NULL` preserves commercial contract records while maintaining proposal linkage.
- `sponsorships (1) ──< deliverables (N)`: `ON DELETE CASCADE` removes associated deliverables if sponsorship is cancelled.
- `companies (1) ──< activities (N)`: `ON DELETE CASCADE`.

---

### 14. Authentication & Security
- **Authentication:** Sessions are managed securely via PHP's native session engine. Upon successful login, `session_regenerate_id(true)` is executed to mitigate session fixation attacks.
- **Password Security:** Passwords are never stored in plaintext. Passwords are encrypted using PHP's `password_hash($pwd, PASSWORD_DEFAULT)` and authenticated via timing-attack safe `password_verify($pwd, $hash)`.
- **SQL Injection Prevention:** 100% of SQL queries interacting with user input utilize PDO prepared statements with bound parameters (`:param`).
- **Cross-Site Scripting (XSS) Prevention:** Output rendering uses the helper function `e($val)` wrapping `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')`.
- **Cross-Site Request Forgery (CSRF):** Every state-changing form includes a cryptographically random token (`bin2hex(random_bytes(32))`) validated on POST via `hash_equals()`.

---

### 15. Workflow Demonstration
1. **Prospecting:** User registers a new company (e.g., NovaGrid Technologies) as a *Prospect* along with its primary contact person (Anita Rao).
2. **Outreach:** Staff logs an email pitch outlining the event prospectus. Status shifts to *Contacted*.
3. **Follow-up:** Staff creates a follow-up task to call Anita Rao in 3 days. System flags it as *Upcoming*, then *Today*, then *Overdue* if unattended.
4. **Proposal:** Manager creates Proposal P-101 for the *Gold Sponsor Package* valued at ₹50,000.
5. **Negotiation & Acceptance:** Terms are agreed upon; proposal status changes to *Accepted*.
6. **Conversion:** Clicking **Convert** converts P-101 into Sponsorship #1, updates NovaGrid's status to *Confirmed*, and sets payment status to *Pending*.
7. **Deliverables:** Team logs promised entitlements: "Main Stage Backdrop Banner" and "10x10 ft Exhibition Stall".
8. **Fulfillment & Reporting:** As payments clear and banners are printed, items are marked *Completed*. Real-time reports reflect updated conversion rates and cash flows.

---

### 16. Testing Summary
An automated test suite (`tests/test_suite.php`) validates the platform across 18 automated checkpoints:
- Auth guards, login verification, and bad password rejection
- Data loading for all 9 core modules
- Live database CRUD cycles on companies and contacts
- JSON API responses
- 100% passing rate achieved across all test specifications.

---

### 17. Limitations
- Email and WhatsApp messages are logged manually by committee members rather than sent directly via external third-party transactional APIs (e.g. SendGrid or Twilio).
- File uploads for large PDF contracts and high-resolution sponsor vector logos are stored via URL reference rather than multi-part S3 cloud bucket uploads.

---

### 18. Future Enhancements
- Integration of automated email dispatch using PHPMailer or webhook triggers.
- In-app PDF generation of sponsorship invoices and formal receipts.
- Role-based granular permissions restricting staff from viewing overall financial balances.
- Real-time notification banners for team members when new follow-up tasks are assigned.

---

### 19. Conclusion
SponsorFlow demonstrates how a clean, traditional full-stack architecture—combining HTML5, CSS3, Vanilla JavaScript, PHP 8+, and relational MySQL—can solve complex operational coordination challenges without unnecessary frontend frameworks. It provides educational clarity for a Web Technology Lab curriculum while adhering to industrial security and database standards.
