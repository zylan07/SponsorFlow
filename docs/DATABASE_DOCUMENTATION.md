# SponsorFlow — Database Documentation

This document details the relational database design for **SponsorFlow**, built for **MySQL 8+** and compatible with **Aiven Cloud MySQL**.

---

## Database Summary
- **Engine:** InnoDB
- **Character Set:** `utf8mb4`
- **Collation:** `utf8mb4_unicode_ci`
- **Total Tables:** 9
- **Referential Integrity:** Enabled via Foreign Keys with `ON DELETE CASCADE` and `ON DELETE SET NULL` constraints.

---

## 1. `users` Table
### Purpose
Stores authorized users of the system (administrators, managers, staff) who have access to the sponsorship CRM portal.
### Columns
| Column Name | Data Type | Constraints | Description |
|---|---|---|---|
| `id` | `INT` | `AUTO_INCREMENT`, `PRIMARY KEY` | Unique identifier for each user |
| `name` | `VARCHAR(100)` | `NOT NULL` | Full legal or display name |
| `email` | `VARCHAR(150)` | `NOT NULL`, `UNIQUE` | Login email address |
| `password_hash` | `VARCHAR(255)` | `NOT NULL` | Bcrypt encrypted hash from `password_hash()` |
| `role` | `ENUM('admin', 'manager', 'staff')` | `NOT NULL`, Default: `'staff'` | Role-based authorization tier |
| `created_at` | `TIMESTAMP` | Default: `CURRENT_TIMESTAMP` | Account registration timestamp |

### Indexes
- `idx_user_email` on `email`

---

## 2. `companies` Table
### Purpose
Represents the prospective and confirmed sponsor companies, serving as the root entity of the CRM.
### Columns
| Column Name | Data Type | Constraints | Description |
|---|---|---|---|
| `id` | `INT` | `AUTO_INCREMENT`, `PRIMARY KEY` | Unique corporate identifier |
| `company_name` | `VARCHAR(150)` | `NOT NULL` | Registered or trading company name |
| `industry` | `VARCHAR(100)` | `NOT NULL` | Industry vertical (e.g. IT Services, Cloud, Fintech) |
| `location` | `VARCHAR(100)` | `NOT NULL` | City or headquarters location |
| `website` | `VARCHAR(200)` | `NULLABLE` | Official website URL |
| `phone` | `VARCHAR(30)` | `NULLABLE` | General phone number |
| `email` | `VARCHAR(150)` | `NULLABLE` | General inquiry or corporate contact email |
| `status` | `ENUM` | `NOT NULL`, Default: `'Prospect'` | Stage: `Prospect`, `Contacted`, `Proposal Sent`, `Interested`, `Negotiation`, `Confirmed`, `Rejected` |
| `estimated_value`| `DECIMAL(12,2)`| `NOT NULL`, Default: `0.00` | Estimated or confirmed monetary sponsorship value in INR |
| `notes` | `TEXT` | `NULLABLE` | Background notes, referral history |
| `created_at` | `TIMESTAMP` | Default: `CURRENT_TIMESTAMP` | Record creation timestamp |
| `updated_at` | `TIMESTAMP` | `ON UPDATE CURRENT_TIMESTAMP` | Last updated timestamp |

### Indexes
- `idx_company_status` on `status`
- `idx_company_industry` on `industry`
- `idx_company_name` on `company_name`

---

## 3. `contacts` Table
### Purpose
Stores individual points of contact, HR leaders, and marketing directors associated with each sponsor company.
### Columns
| Column Name | Data Type | Constraints | Description |
|---|---|---|---|
| `id` | `INT` | `AUTO_INCREMENT`, `PRIMARY KEY` | Unique contact identifier |
| `company_id` | `INT` | `NOT NULL` | Foreign key referencing `companies(id)` |
| `name` | `VARCHAR(100)` | `NOT NULL` | Full name of the contact person |
| `designation` | `VARCHAR(100)` | `NOT NULL` | Corporate job title (e.g. VP Marketing) |
| `email` | `VARCHAR(150)` | `NOT NULL` | Direct professional email address |
| `phone` | `VARCHAR(30)` | `NOT NULL` | Mobile or direct contact number |
| `preferred_contact`| `ENUM` | Default: `'Email'` | Channel: `Email`, `Phone Call`, `WhatsApp`, `LinkedIn`, `Meeting` |
| `relationship_status`| `ENUM`| Default: `'New'` | Temperature: `New`, `Warm`, `Engaged`, `Strategic`, `Inactive` |
| `last_contacted` | `DATE` | `NULLABLE` | Date of last touchpoint |
| `notes` | `TEXT` | `NULLABLE` | Direct contact preferences and notes |
| `created_at` | `TIMESTAMP` | Default: `CURRENT_TIMESTAMP` | Timestamp |
| `updated_at` | `TIMESTAMP` | `ON UPDATE CURRENT_TIMESTAMP` | Last updated timestamp |

### Foreign Keys & Relationships
- `fk_contacts_company`: `company_id` references `companies(id)` `ON DELETE CASCADE ON UPDATE CASCADE`.

---

## 4. `outreach` Table
### Purpose
Logs individual communication interactions (pitches, calls, video conferences, messages) with sponsors.
### Columns
| Column Name | Data Type | Constraints | Description |
|---|---|---|---|
| `id` | `INT` | `AUTO_INCREMENT`, `PRIMARY KEY` | Unique interaction log ID |
| `company_id` | `INT` | `NOT NULL` | Foreign key referencing `companies(id)` |
| `contact_id` | `INT` | `NULLABLE` | Foreign key referencing `contacts(id)` |
| `outreach_type` | `ENUM` | `NOT NULL` | Channel: `Email`, `Phone Call`, `WhatsApp`, `LinkedIn`, `Meeting` |
| `subject` | `VARCHAR(200)` | `NOT NULL` | Purpose or subject line of communication |
| `message` | `TEXT` | `NULLABLE` | Content summary of the touchpoint |
| `outreach_date` | `DATE` | `NOT NULL` | Date when interaction took place |
| `status` | `ENUM` | `NOT NULL`, Default: `'Sent'` | Status: `Sent`, `Delivered`, `Responded`, `Interested`, `No Response`, `Not Interested` |
| `response` | `TEXT` | `NULLABLE` | Feedback received from the sponsor |
| `assigned_to` | `VARCHAR(100)` | `NOT NULL` | Organizing committee team member responsible |
| `next_action` | `VARCHAR(200)` | `NULLABLE` | Subsequent action item |
| `created_at` | `TIMESTAMP` | Default: `CURRENT_TIMESTAMP` | Timestamp |

### Foreign Keys
- `fk_outreach_company`: `company_id` references `companies(id)` `ON DELETE CASCADE`.
- `fk_outreach_contact`: `contact_id` references `contacts(id)` `ON DELETE SET NULL`.

---

## 5. `followups` Table
### Purpose
Maintains actionable follow-up tasks with due dates, priorities, and assignments to prevent missed sponsor commitments.
### Columns
| Column Name | Data Type | Constraints | Description |
|---|---|---|---|
| `id` | `INT` | `AUTO_INCREMENT`, `PRIMARY KEY` | Unique follow-up task ID |
| `company_id` | `INT` | `NOT NULL` | Foreign key referencing `companies(id)` |
| `contact_id` | `INT` | `NULLABLE` | Foreign key referencing `contacts(id)` |
| `followup_date` | `DATE` | `NOT NULL` | Target date when follow-up is scheduled |
| `priority` | `ENUM('High', 'Medium', 'Low')` | `NOT NULL`, Default: `'Medium'` | Urgency level |
| `reason` | `VARCHAR(255)` | `NOT NULL` | Objective of the follow-up task |
| `status` | `ENUM` | `NOT NULL`, Default: `'Pending'` | Status: `Pending`, `Completed`, `Overdue`, `Rescheduled` |
| `assigned_to` | `VARCHAR(100)` | `NOT NULL` | Assigned team member |
| `notes` | `TEXT` | `NULLABLE` | Preparatory notes |
| `created_at` | `TIMESTAMP` | Default: `CURRENT_TIMESTAMP` | Timestamp |
| `updated_at` | `TIMESTAMP` | `ON UPDATE CURRENT_TIMESTAMP` | Last updated timestamp |

### Foreign Keys
- `fk_followups_company`: `company_id` references `companies(id)` `ON DELETE CASCADE`.
- `fk_followups_contact`: `contact_id` references `contacts(id)` `ON DELETE SET NULL`.

---

## 6. `proposals` Table
### Purpose
Tracks formal sponsorship proposals, package tiers, quoted fees, validity periods, and client acceptance.
### Columns
| Column Name | Data Type | Constraints | Description |
|---|---|---|---|
| `id` | `INT` | `AUTO_INCREMENT`, `PRIMARY KEY` | Internal ID |
| `proposal_number`| `VARCHAR(50)` | `NOT NULL`, `UNIQUE` | Human-readable identifier (e.g. `P-101`) |
| `company_id` | `INT` | `NOT NULL` | Foreign key referencing `companies(id)` |
| `contact_id` | `INT` | `NULLABLE` | Foreign key referencing `contacts(id)` |
| `package_name` | `VARCHAR(100)` | `NOT NULL` | Package name (e.g. Gold Sponsor Package) |
| `amount` | `DECIMAL(12,2)`| `NOT NULL`, Default: `0.00` | Proposed monetary value in INR |
| `sent_date` | `DATE` | `NOT NULL` | Date proposal was dispatched |
| `valid_until` | `DATE` | `NOT NULL` | Proposal expiration deadline |
| `status` | `ENUM` | `NOT NULL`, Default: `'Draft'` | Status: `Draft`, `Sent`, `Viewed`, `Negotiation`, `Accepted`, `Rejected`, `Expired` |
| `benefits` | `TEXT` | `NULLABLE` | Itemized entitlements and deliverables offered |
| `notes` | `TEXT` | `NULLABLE` | Negotiation notes |
| `created_at` | `TIMESTAMP` | Default: `CURRENT_TIMESTAMP` | Timestamp |
| `updated_at` | `TIMESTAMP` | `ON UPDATE CURRENT_TIMESTAMP` | Last updated timestamp |

### Foreign Keys
- `fk_proposals_company`: `company_id` references `companies(id)` `ON DELETE CASCADE`.
- `fk_proposals_contact`: `contact_id` references `contacts(id)` `ON DELETE SET NULL`.

---

## 7. `sponsorships` Table
### Purpose
Represents executed commercial commitments. Can be generated directly or converted from an accepted proposal.
### Columns
| Column Name | Data Type | Constraints | Description |
|---|---|---|---|
| `id` | `INT` | `AUTO_INCREMENT`, `PRIMARY KEY` | Sponsorship agreement ID |
| `company_id` | `INT` | `NOT NULL` | Foreign key referencing `companies(id)` |
| `proposal_id` | `INT` | `NULLABLE` | Foreign key referencing `proposals(id)` |
| `package_name` | `VARCHAR(100)` | `NOT NULL` | Agreed package name |
| `sponsorship_type`| `ENUM` | `NOT NULL` | Tier: `Title Sponsor`, `Gold Sponsor`, `Silver Sponsor`, `Bronze Sponsor`, `Technology Partner`, `Media Partner`, `Community Partner`, `Internship Partner`, `Industry Partner` |
| `amount` | `DECIMAL(12,2)`| `NOT NULL`, Default: `0.00` | Committed contract amount in INR |
| `start_date` | `DATE` | `NOT NULL` | Partnership contract start date |
| `end_date` | `DATE` | `NOT NULL` | Partnership contract end date |
| `payment_status`| `ENUM` | `NOT NULL`, Default: `'Pending'` | Status: `Pending`, `Partial`, `Paid`, `Overdue` |
| `contract_status`| `ENUM`| `NOT NULL`, Default: `'Not Started'` | Status: `Not Started`, `Draft`, `Sent`, `Signed` |
| `overall_status`| `ENUM` | `NOT NULL`, Default: `'Active'` | Status: `Active`, `Completed`, `On Hold`, `Cancelled` |
| `created_at` | `TIMESTAMP` | Default: `CURRENT_TIMESTAMP` | Timestamp |
| `updated_at` | `TIMESTAMP` | `ON UPDATE CURRENT_TIMESTAMP` | Last updated timestamp |

### Foreign Keys
- `fk_sponsorships_company`: `company_id` references `companies(id)` `ON DELETE CASCADE`.
- `fk_sponsorships_proposal`: `proposal_id` references `proposals(id)` `ON DELETE SET NULL`.

---

## 8. `deliverables` Table
### Purpose
Tracks on-ground and digital deliverables promised to sponsors (e.g. stage banners, stall spaces, speaker slots).
### Columns
| Column Name | Data Type | Constraints | Description |
|---|---|---|---|
| `id` | `INT` | `AUTO_INCREMENT`, `PRIMARY KEY` | Deliverable ID |
| `sponsorship_id`| `INT` | `NULLABLE` | Foreign key referencing `sponsorships(id)` |
| `company_id` | `INT` | `NOT NULL` | Foreign key referencing `companies(id)` |
| `deliverable` | `VARCHAR(200)` | `NOT NULL` | Deliverable title / description |
| `event_name` | `VARCHAR(150)` | `NOT NULL`, Default: `'TechNova 2027'` | Target event name |
| `due_date` | `DATE` | `NOT NULL` | Execution deadline |
| `owner` | `VARCHAR(100)` | `NOT NULL` | Committee member responsible |
| `priority` | `ENUM('High', 'Medium', 'Low')` | `NOT NULL`, Default: `'Medium'` | Priority level |
| `status` | `ENUM` | `NOT NULL`, Default: `'Not Started'` | Status: `Not Started`, `In Progress`, `Completed`, `Delayed` |
| `completion_date`| `DATE` | `NULLABLE` | Date when deliverable was completed |
| `notes` | `TEXT` | `NULLABLE` | Execution specifications |
| `created_at` | `TIMESTAMP` | Default: `CURRENT_TIMESTAMP` | Timestamp |
| `updated_at` | `TIMESTAMP` | `ON UPDATE CURRENT_TIMESTAMP` | Last updated timestamp |

### Foreign Keys
- `fk_deliverables_sponsorship`: `sponsorship_id` references `sponsorships(id)` `ON DELETE CASCADE`.
- `fk_deliverables_company`: `company_id` references `companies(id)` `ON DELETE CASCADE`.

---

## 9. `activities` Table
### Purpose
Provides a chronological audit trail of all actions, updates, meetings, and milestone changes per company.
### Columns
| Column Name | Data Type | Constraints | Description |
|---|---|---|---|
| `id` | `INT` | `AUTO_INCREMENT`, `PRIMARY KEY` | Activity event ID |
| `company_id` | `INT` | `NOT NULL` | Foreign key referencing `companies(id)` |
| `activity_type` | `VARCHAR(50)` | `NOT NULL` | Category (e.g. Company Created, Outreach, Proposal, Payment) |
| `description` | `TEXT` | `NOT NULL` | Summary description of the event |
| `activity_date` | `DATE` | `NOT NULL` | Date when event occurred |
| `created_by` | `VARCHAR(100)` | `NOT NULL`, Default: `'System'` | Username or committee member who logged the activity |
| `created_at` | `TIMESTAMP` | Default: `CURRENT_TIMESTAMP` | Timestamp |

### Foreign Keys
- `fk_activities_company`: `company_id` references `companies(id)` `ON DELETE CASCADE`.
