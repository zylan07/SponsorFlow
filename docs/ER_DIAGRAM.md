# SponsorFlow — Entity Relationship (ER) Diagram

This document models the conceptual and logical entity relationships in the **SponsorFlow** database.

---

## 1. Mermaid ER Diagram

```mermaid
erDiagram
    USERS {
        int id PK
        string name
        string email UK
        string password_hash
        string role
        timestamp created_at
    }

    COMPANIES ||--o{ CONTACTS : "employs / has"
    COMPANIES ||--o{ OUTREACH : "receives"
    COMPANIES ||--o{ FOLLOWUPS : "scheduled for"
    COMPANIES ||--o{ PROPOSALS : "receives"
    COMPANIES ||--o{ SPONSORSHIPS : "commits to"
    COMPANIES ||--o{ DELIVERABLES : "receives"
    COMPANIES ||--o{ ACTIVITIES : "records"

    CONTACTS ||--o{ OUTREACH : "participates in"
    CONTACTS ||--o{ FOLLOWUPS : "designated contact"
    CONTACTS ||--o{ PROPOSALS : "addressed to"

    PROPOSALS ||--o| SPONSORSHIPS : "converts into"
    SPONSORSHIPS ||--o{ DELIVERABLES : "specifies"

    COMPANIES {
        int id PK
        string company_name
        string industry
        string location
        string website
        string phone
        string email
        string status
        decimal estimated_value
        text notes
        timestamp created_at
        timestamp updated_at
    }

    CONTACTS {
        int id PK
        int company_id FK
        string name
        string designation
        string email
        string phone
        string preferred_contact
        string relationship_status
        date last_contacted
        text notes
    }

    OUTREACH {
        int id PK
        int company_id FK
        int contact_id FK
        string outreach_type
        string subject
        text message
        date outreach_date
        string status
        text response
        string assigned_to
        string next_action
    }

    FOLLOWUPS {
        int id PK
        int company_id FK
        int contact_id FK
        date followup_date
        string priority
        string reason
        string status
        string assigned_to
        text notes
    }

    PROPOSALS {
        int id PK
        string proposal_number UK
        int company_id FK
        int contact_id FK
        string package_name
        decimal amount
        date sent_date
        date valid_until
        string status
        text benefits
        text notes
    }

    SPONSORSHIPS {
        int id PK
        int company_id FK
        int proposal_id FK
        string package_name
        string sponsorship_type
        decimal amount
        date start_date
        date end_date
        string payment_status
        string contract_status
        string overall_status
    }

    DELIVERABLES {
        int id PK
        int sponsorship_id FK
        int company_id FK
        string deliverable
        string event_name
        date due_date
        string owner
        string priority
        string status
        date completion_date
        text notes
    }

    ACTIVITIES {
        int id PK
        int company_id FK
        string activity_type
        text description
        date activity_date
        string created_by
    }
```

---

## 2. Cardinality & Relationship Breakdown

### 1. `COMPANIES` to `CONTACTS` (1 : N)
- **Cardinality:** One company can have zero, one, or many key contacts.
- **Rule:** A contact must belong to exactly one company.
- **Foreign Key:** `contacts.company_id` references `companies.id`.
- **Constraint:** `ON DELETE CASCADE`.

### 2. `COMPANIES` to `OUTREACH` (1 : N)
- **Cardinality:** One company can have multiple outreach interactions logged over time.
- **Foreign Key:** `outreach.company_id` references `companies.id` (`ON DELETE CASCADE`).
- **Contact Link:** `outreach.contact_id` references `contacts.id` (`ON DELETE SET NULL`).

### 3. `COMPANIES` to `FOLLOWUPS` (1 : N)
- **Cardinality:** A company can have multiple scheduled follow-ups.
- **Foreign Key:** `followups.company_id` references `companies.id` (`ON DELETE CASCADE`).
- **Contact Link:** `followups.contact_id` references `contacts.id` (`ON DELETE SET NULL`).

### 4. `COMPANIES` to `PROPOSALS` (1 : N)
- **Cardinality:** Multiple proposals can be issued to a single company across negotiations.
- **Foreign Key:** `proposals.company_id` references `companies.id` (`ON DELETE CASCADE`).

### 5. `PROPOSALS` to `SPONSORSHIPS` (1 : 1 or 1 : 0)
- **Cardinality:** An accepted proposal can be converted into at most one commercial sponsorship.
- **Foreign Key:** `sponsorships.proposal_id` references `proposals.id` (`ON DELETE SET NULL`).

### 6. `SPONSORSHIPS` to `DELIVERABLES` (1 : N)
- **Cardinality:** A confirmed sponsorship agreement includes multiple contractual deliverables.
- **Foreign Key:** `deliverables.sponsorship_id` references `sponsorships.id` (`ON DELETE CASCADE`).

### 7. `COMPANIES` to `ACTIVITIES` (1 : N)
- **Cardinality:** Every interaction, status change, and proposal generation logs an entry into the company's activity audit trail.
- **Foreign Key:** `activities.company_id` references `companies.id` (`ON DELETE CASCADE`).
