# SponsorFlow — Viva Examination Preparation Guide

This guide contains clear, concise answers to viva questions commonly asked by examiners for Web Technology Lab projects.

---

### 1. What is SponsorFlow?
**Answer:**
SponsorFlow is a full-stack Event Sponsorship & Partnership Management CRM web application. It provides event organizing committees with a centralized database to manage sponsor companies, contacts, outreach logs, follow-ups, proposals, confirmed sponsorships, contractual deliverables, and financial reporting.

---

### 2. Why did you choose this specific problem?
**Answer:**
During college symposia, hackathons, and conferences, sponsorship coordination is usually disorganized. Teams track corporate contacts in disparate spreadsheets, communicate over WhatsApp where reminders get forgotten, and lose track of promised on-ground branding deliverables like stage banners and stalls. SponsorFlow solves this real-world problem by providing an end-to-end relational CRM specifically designed for event sponsorships.

---

### 3. Why did you use PHP for the backend?
**Answer:**
1. **Simplicity and Core Fundamentals:** PHP is native to server-side web development and part of the foundational Web Technology curriculum.
2. **Built-in Session & Security Utilities:** PHP has built-in session management (`$_SESSION`), secure hashing (`password_hash`, `password_verify`), and native PDO database abstraction.
3. **No Heavy Framework Overhead:** By writing clean, modular PHP without frameworks like Laravel, the entire request lifecycle remains transparent, easy to explain, and easy to maintain.

---

### 4. Why did you choose MySQL?
**Answer:**
Event sponsorship data is inherently relational: companies have multiple contacts, each contact participates in communications, proposals lead to commercial sponsorships, and sponsorships generate deliverables. MySQL is an ACID-compliant Relational Database Management System (RDBMS) that enforces referential integrity through foreign keys and cascading constraints.

---

### 5. Why MySQL for SponsorFlow?
**Answer:**
MySQL 8.0+ provides an ACID-compliant, highly reliable relational database engine with native foreign key support, cascading constraints, and robust indexing. For an event sponsorship system tracking corporate relationships, financial totals, and multi-tier deliverables, relational integrity is critical to avoid orphaned records and financial discrepancies.

---

### 6. What is CRUD?
**Answer:**
CRUD stands for **Create, Read, Update, and Delete**—the four foundational operations performed on persistent database storage:
- **Create:** `INSERT INTO companies ...`
- **Read:** `SELECT * FROM companies ...`
- **Update:** `UPDATE companies SET ... WHERE id = ...`
- **Delete:** `DELETE FROM companies WHERE id = ...`

In SponsorFlow, every core module (Companies, Contacts, Outreach, Follow-ups, Proposals, Sponsorships, Deliverables) implements full CRUD functionality.

---

### 7. What is PDO and why did you use it instead of `mysqli`?
**Answer:**
**PDO (PHP Data Objects)** is a database abstraction layer in PHP that provides a consistent interface for working with various databases. We chose PDO over `mysqli` because:
1. It supports named parameters (`:email`, `:company_id`), making prepared statements much more readable.
2. It throws standard `PDOException` objects for robust error handling.
3. It enforces strict type safety and secure emulation modes (`PDO::ATTR_EMULATE_PREPARES => false`).

---

### 8. Why are prepared statements essential?
**Answer:**
Prepared statements completely eliminate **SQL Injection (SQLi)** vulnerabilities. Instead of concatenating untrusted user input directly into SQL strings, the database engine compiles the query template first. User parameters are sent separately and treated strictly as literal data values, never as executable SQL commands.

---

### 9. What is a Foreign Key and what are its cascading options?
**Answer:**
A Foreign Key is a column (or set of columns) that references the Primary Key of another table, enforcing referential integrity.
- `ON DELETE CASCADE`: When the parent record is deleted, all dependent child records are automatically deleted (e.g., deleting a company deletes its contacts and deliverables).
- `ON DELETE SET NULL`: When the parent record is deleted, the foreign key column in the child record is set to `NULL` (e.g., if a contact person is removed, historical outreach logs remain intact with `contact_id = NULL`).

---

### 10. What is Database Normalization and what normal form does SponsorFlow follow?
**Answer:**
Database Normalization is the systematic process of organizing database tables to minimize data redundancy and prevent insertion, update, and deletion anomalies.
SponsorFlow adheres to **Third Normal Form (3NF)**:
- **1NF:** All attributes are atomic (no repeating groups; phone numbers and emails are individual scalar values).
- **2NF:** It is in 1NF and all non-key attributes are fully functionally dependent on the entire primary key.
- **3NF:** It is in 2NF and there are no transitive functional dependencies (e.g., company attributes like industry and location are stored solely in `companies`, not duplicated across `contacts` or `outreach`).

---

### 11. How does Session-based Authentication work in this project?
**Answer:**
When a user submits their email and password:
1. PHP validates credentials against the database.
2. Upon verification, PHP calls `session_regenerate_id(true)` (to mitigate session fixation) and stores user details (`$_SESSION['user_id']`, `$_SESSION['user_role']`).
3. A unique session cookie (`PHPSESSID`) is sent to the client browser.
4. On subsequent requests, the server reads this cookie, identifies the active session, and guards protected pages via `requireAuth()`. Unauthenticated users are redirected to `login.php`.

---

### 12. What is Password Hashing and how is it implemented?
**Answer:**
Password hashing converts a plaintext password into a one-way mathematical cryptographic string that cannot be reversed. Plaintext passwords are never stored in SponsorFlow.
- We use PHP's `password_hash($password, PASSWORD_DEFAULT)` which applies the secure **bcrypt** algorithm with automatic cryptographic salting.
- During authentication, we verify credentials using `password_verify($password, $hash)`, which is protected against timing attacks.

---

### 13. What is CSRF and how do you protect against it?
**Answer:**
**Cross-Site Request Forgery (CSRF)** occurs when an unauthorized external website tricks an authenticated user's browser into submitting malicious state-changing requests (like deleting a company or creating an admin user).
**Protection Mechanism:**
1. A cryptographically strong pseudo-random token (`bin2hex(random_bytes(32))`) is generated and stored in `$_SESSION['csrf_token']`.
2. Every HTML form embeds this token via a hidden input field (`csrf_field()`).
3. On every `POST` request, the server executes `csrf_protect()`, comparing the submitted token with the session token using `hash_equals()`. If they mismatch or the token is missing, the request is halted with HTTP `403 Forbidden`.

---

### 14. What is the difference between client-side and server-side validation?
**Answer:**
- **Client-Side Validation (JavaScript):** Runs directly in the user's browser before form submission (e.g. checking phone number length or empty fields). It enhances user experience by providing immediate visual feedback without reloading the page.
- **Server-Side Validation (PHP):** Runs on the web server after submission. Client-side checks can be bypassed (e.g. via cURL or Postman), so server-side validation is strictly required for data integrity and security.

---

### 15. How are the Dashboard KPI figures calculated?
**Answer:**
Dashboard statistics are calculated live from MySQL queries every time `dashboard.php` loads.
- Total Companies: `SELECT COUNT(*) FROM companies`
- Pipeline Value: `SELECT COALESCE(SUM(amount), 0) FROM proposals WHERE status IN ('Draft', 'Sent', 'Viewed', 'Negotiation')`
- Confirmed Sponsorship Value: `SELECT COALESCE(SUM(amount), 0) FROM sponsorships WHERE overall_status != 'Cancelled'`
- Deliverables percentage: `(Completed Deliverables / Total Deliverables) * 100`
No statistics are hardcoded or read from local storage.

---

### 16. How does a Proposal become a Sponsorship in the workflow?
**Answer:**
When a corporate sponsor accepts a proposal:
1. The user clicks the **Convert** button on the Proposals page.
2. The server initiates a database transaction (`beginTransaction()`).
3. A new record is created in the `sponsorships` table linked to the proposal ID and company ID.
4. The proposal status is updated to `Accepted`.
5. The company's pipeline stage is automatically set to `Confirmed` and its `estimated_value` is updated to match the proposal amount.
6. The transaction commits, ensuring all operations succeed together.

---

### 17. How are Deliverables tracked and classified?
**Answer:**
Each deliverable (e.g., stage banner, booth space, student vouchers) is linked to a confirmed sponsorship and company. It records an event name, due date, owner, priority, and status (`Not Started`, `In Progress`, `Completed`, `Delayed`). When marked complete, the current date is stored in `completion_date` and the overall event fulfillment percentage automatically updates.

---

### 18. What are the limitations of the current implementation?
**Answer:**
1. Communication logs (outreach) are recorded manually by team members rather than automatically tracked via third-party email APIs (e.g., SendGrid/SES).
2. Large file assets (such as high-resolution sponsor vector logos) are referenced via URLs rather than uploaded to cloud object storage (like AWS S3).

---

### 19. What future enhancements would you suggest?
**Answer:**
1. Automated PDF invoice and formal payment receipt generation directly in PHP.
2. Webhook triggers for instant Slack / WhatsApp notifications when a follow-up becomes overdue.
3. Role-based field access control (e.g., restricting student volunteers from viewing contract financial amounts while allowing them to view and check off deliverables).
