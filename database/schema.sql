-- ====================================================================
-- SponsorFlow - Database Schema
-- Target: MySQL 8+ (Aiven Cloud MySQL / Local MySQL)
-- ====================================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS activities;
DROP TABLE IF EXISTS deliverables;
DROP TABLE IF EXISTS sponsorships;
DROP TABLE IF EXISTS proposals;
DROP TABLE IF EXISTS followups;
DROP TABLE IF EXISTS outreach;
DROP TABLE IF EXISTS contacts;
DROP TABLE IF EXISTS companies;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------------------
-- 1. users
-- --------------------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'staff') NOT NULL DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 2. companies
-- --------------------------------------------------------------------
CREATE TABLE companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(150) NOT NULL,
    industry VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    website VARCHAR(200) DEFAULT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    status ENUM('Prospect', 'Contacted', 'Proposal Sent', 'Interested', 'Negotiation', 'Confirmed', 'Rejected') NOT NULL DEFAULT 'Prospect',
    estimated_value DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_company_status (status),
    INDEX idx_company_industry (industry),
    INDEX idx_company_name (company_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 3. contacts
-- --------------------------------------------------------------------
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    designation VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    preferred_contact ENUM('Email', 'Phone Call', 'WhatsApp', 'LinkedIn', 'Meeting') DEFAULT 'Email',
    relationship_status ENUM('New', 'Warm', 'Engaged', 'Strategic', 'Inactive') DEFAULT 'New',
    last_contacted DATE DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_contact_company (company_id),
    INDEX idx_contact_email (email),
    CONSTRAINT fk_contacts_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 4. outreach
-- --------------------------------------------------------------------
CREATE TABLE outreach (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    contact_id INT DEFAULT NULL,
    outreach_type ENUM('Email', 'Phone Call', 'WhatsApp', 'LinkedIn', 'Meeting') NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT DEFAULT NULL,
    outreach_date DATE NOT NULL,
    status ENUM('Sent', 'Delivered', 'Responded', 'Interested', 'No Response', 'Not Interested') NOT NULL DEFAULT 'Sent',
    response TEXT DEFAULT NULL,
    assigned_to VARCHAR(100) NOT NULL,
    next_action VARCHAR(200) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_outreach_company (company_id),
    INDEX idx_outreach_date (outreach_date),
    INDEX idx_outreach_status (status),
    CONSTRAINT fk_outreach_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_outreach_contact FOREIGN KEY (contact_id) 
        REFERENCES contacts(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 5. followups
-- --------------------------------------------------------------------
CREATE TABLE followups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    contact_id INT DEFAULT NULL,
    followup_date DATE NOT NULL,
    priority ENUM('High', 'Medium', 'Low') NOT NULL DEFAULT 'Medium',
    reason VARCHAR(255) NOT NULL,
    status ENUM('Pending', 'Completed', 'Overdue', 'Rescheduled') NOT NULL DEFAULT 'Pending',
    assigned_to VARCHAR(100) NOT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_followup_company (company_id),
    INDEX idx_followup_date (followup_date),
    INDEX idx_followup_status (status),
    INDEX idx_followup_priority (priority),
    CONSTRAINT fk_followups_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_followups_contact FOREIGN KEY (contact_id) 
        REFERENCES contacts(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 6. proposals
-- --------------------------------------------------------------------
CREATE TABLE proposals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proposal_number VARCHAR(50) NOT NULL UNIQUE,
    company_id INT NOT NULL,
    contact_id INT DEFAULT NULL,
    package_name VARCHAR(100) NOT NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    sent_date DATE NOT NULL,
    valid_until DATE NOT NULL,
    status ENUM('Draft', 'Sent', 'Viewed', 'Negotiation', 'Accepted', 'Rejected', 'Expired') NOT NULL DEFAULT 'Draft',
    benefits TEXT DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_proposal_company (company_id),
    INDEX idx_proposal_status (status),
    INDEX idx_proposal_number (proposal_number),
    CONSTRAINT fk_proposals_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_proposals_contact FOREIGN KEY (contact_id) 
        REFERENCES contacts(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 7. sponsorships
-- --------------------------------------------------------------------
CREATE TABLE sponsorships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    proposal_id INT DEFAULT NULL,
    package_name VARCHAR(100) NOT NULL,
    sponsorship_type ENUM(
        'Title Sponsor',
        'Gold Sponsor',
        'Silver Sponsor',
        'Bronze Sponsor',
        'Technology Partner',
        'Media Partner',
        'Community Partner',
        'Internship Partner',
        'Industry Partner'
    ) NOT NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    payment_status ENUM('Pending', 'Partial', 'Paid', 'Overdue') NOT NULL DEFAULT 'Pending',
    contract_status ENUM('Not Started', 'Draft', 'Sent', 'Signed') NOT NULL DEFAULT 'Not Started',
    overall_status ENUM('Active', 'Completed', 'On Hold', 'Cancelled') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_sponsorship_company (company_id),
    INDEX idx_sponsorship_payment (payment_status),
    INDEX idx_sponsorship_overall (overall_status),
    CONSTRAINT fk_sponsorships_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_sponsorships_proposal FOREIGN KEY (proposal_id) 
        REFERENCES proposals(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 8. deliverables
-- --------------------------------------------------------------------
CREATE TABLE deliverables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sponsorship_id INT DEFAULT NULL,
    company_id INT NOT NULL,
    deliverable VARCHAR(200) NOT NULL,
    event_name VARCHAR(150) NOT NULL DEFAULT 'TechNova 2027',
    due_date DATE NOT NULL,
    owner VARCHAR(100) NOT NULL,
    priority ENUM('High', 'Medium', 'Low') NOT NULL DEFAULT 'Medium',
    status ENUM('Not Started', 'In Progress', 'Completed', 'Delayed') NOT NULL DEFAULT 'Not Started',
    completion_date DATE DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_deliverable_sponsorship (sponsorship_id),
    INDEX idx_deliverable_company (company_id),
    INDEX idx_deliverable_status (status),
    INDEX idx_deliverable_due (due_date),
    CONSTRAINT fk_deliverables_sponsorship FOREIGN KEY (sponsorship_id) 
        REFERENCES sponsorships(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_deliverables_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 9. activities
-- --------------------------------------------------------------------
CREATE TABLE activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    activity_date DATE NOT NULL,
    created_by VARCHAR(100) NOT NULL DEFAULT 'System',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activity_company (company_id),
    INDEX idx_activity_date (activity_date),
    CONSTRAINT fk_activities_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
