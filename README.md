# Cyber Incident Evidence Management System (CIEMS)

**Course:** IST4900 Systems Design and Planning  
**Institution:** United States International University – Africa (USIU-Africa)  
**Author:** Kamau Margaret Wamweru (Student ID: 669925)  
**Supervisor / Instructor:** Prof. Justus Nyamweya  

---

## 1. Project Overview

The **Cyber Incident Evidence Management System (CIEMS)** is a robust, three-tier web application built to intake, preserve, catalog, and audit digital forensic artifacts (e.g. disk images, packet captures, memory dumps, system event logs) acquired during cybersecurity incident investigations.

The system guarantees an unbroken, legally defensible **Chain of Custody (CoC)** through:
- **Baseline Cryptographic Hashing (SHA-256 & MD5)** upon ingestion.
- **On-Demand Forensic Hash Verification** to immediately catch bit-level file tampering or disk corruption.
- **Role-Based Access Control (RBAC)** across three distinct roles: Lead Investigator, Evidence Custodian, and System Administrator.
- **Restricted Vault Storage (`storage/vault/`)** isolated from direct HTTP access.
- **Printable Legal Chain of Custody Certificate** ready for court presentation and regulatory audits.
- **Master Compliance Audit Trail** logging all authentication attempts, downloads, views, and integrity checks.

---

## 2. System Architecture

CIEMS strictly adheres to the Three-Tier Client-Server Architectural Pattern:
1. **Presentation Tier (Client Layer):** HTML5, CSS3, JavaScript, Bootstrap 5, FontAwesome 6, and responsive cybersecurity theme.
2. **Application Tier (Logic Layer):** PHP 8+ Object-Oriented Programming (OOP) utilizing Model-View-Controller (MVC) separation, PDO prepared statements, and secure session handling.
3. **Data Tier (Storage Layer):** MySQL/MariaDB RDBMS (InnoDB engine with strict foreign keys and cascading rules) paired with an isolated local evidence file vault.

---

## 3. Quick Setup & Access (XAMPP on Windows)

The application is deployed directly in your local XAMPP web root:
`C:\xampp\htdocs\ciems\`

### Access URL
Open your web browser and navigate to:
👉 **[http://localhost/ciems](http://localhost/ciems)**

### Pre-Configured Demo Accounts

For assessment, testing, and grading, three pre-seeded accounts are provided:

| Role | Name & Badge | Email Address | Password | Primary Functions |
| :--- | :--- | :--- | :--- | :--- |
| **Lead Investigator** | Kamau Margaret Wamweru (`INV-669925`) | `investigator@ciems.usiu.ac.ke` | `Investigator@2026!` | Register incident cases, ingest digital evidence, annotate findings. |
| **Evidence Custodian** | Michael Ochieng (`CST-201`) | `custodian@ciems.usiu.ac.ke` | `Custodian@2026!` | Verify SHA-256 integrity, inspect custody ledger, export official certificates. |
| **System Administrator** | Prof. Justus Nyamweya (`ADM-001`) | `admin@ciems.usiu.ac.ke` | `Admin@2026!` | User management, security parameters, master compliance audit trail. |

*(Note: The login screen also features quick 1-click credential buttons for swift evaluation).*

---

## 4. Key Functional Modules Walkthrough

### A. Case Management Module
- Navigate to **Incident Cases** &rarr; **Register New Case**.
- Enter incident headline, threat classification (*Ransomware, Data Exfiltration, Malware, Phishing, etc.*), severity level, and initial IoC narrative.
- The system automatically generates a unique sequential identifier (e.g., `CIEMS-2026-001`).

### B. Evidence Ingestion & Cryptographic Hashing
- Open an active case dossier and click **Ingest Evidence**.
- Attach any forensic file (*.pcap, .raw, .bin, .log, .dd, etc.*).
- The system computes the **SHA-256** and **MD5** digests on the raw temporary stream before saving the file into `storage/vault/` under a randomized UUID name.
- An initial **Chain of Custody** record (`action: ingested`) is instantly written with timestamp and handler identity.

### C. Live Integrity Verification (Tamper Detection)
- Sign in as **Evidence Custodian** (`custodian@ciems.usiu.ac.ke`).
- View the evidence artifact and click **Verify Checksum**.
- Enter an audit purpose (e.g. *"Pre-trial parity audit"*).
- The server recalculates the SHA-256 hash of the vault file on disk and compares it to the baseline:
  - If equal: Marked as **Cryptographically Intact** (Green Badge).
  - If modified: Marked as **Compromised / Tampered** (Red Alert) and recorded in the audit trail.

### D. Secure Evidence Download
- From the artifact dossier, click **Secure Download**.
- A modal prompts for an operational justification.
- The system logs the checkout/download in the Chain of Custody ledger before securely streaming the file with non-sniff HTTP headers. Direct access to `/storage/vault/` via URL returns `403 Forbidden`.

### E. Official Chain of Custody Certificate
- Click **Export CoC Certificate** or **Print Certificate**.
- Generates a court-admissible forensic document detailing the institutional seal, case details, baseline digests, chronological custody transfers, and verification signatures.

### F. Master Compliance Audit Trail
- Sign in as **System Administrator** (`admin@ciems.usiu.ac.ke`).
- Navigate to **Master Audit Trail** to view filterable, immutable logs of every login attempt, evidence access, and administrative action.

---

## 5. Automated Integration Test Suite

To run the automated verification suite from the terminal:

```powershell
php C:\xampp\htdocs\ciems\tests\test_ciems.php
```

All 14 automated tests validate:
- Database connectivity & schema constraints.
- Bcrypt password verification across all three roles.
- Case registration & auto-incrementing case numbering.
- Evidence ingestion and baseline SHA-256 calculation.
- Live cryptographic integrity verification.
- Bit-level tamper detection (simulated alteration caught).
- Chain of Custody append-only event logging.
- Master audit trail recording.

---

## 6. Directory Structure

```
ciems/
├── assets/
│   ├── css/custom.css           # Modern cybersecurity theme
│   └── js/main.js               # Client validation & clipboard utilities
├── config/
│   ├── config.php               # System constants, paths, upload rules
│   └── database.php             # Database credentials
├── controllers/
│   ├── AdminController.php      # User management, audit logs, settings
│   ├── AuthController.php       # Authentication & session security
│   ├── CaseController.php       # Case lifecycle & dossiers
│   ├── CustodyController.php    # Chain of Custody & certificates
│   ├── DashboardController.php  # Role-specific operational telemetry
│   └── EvidenceController.php   # Ingestion, SHA-256 hashing, verification & streamer
├── core/
│   ├── Auth.php                 # RBAC access guards
│   ├── Database.php             # PDO Singleton wrapper
│   └── Session.php              # Secure sessions & CSRF tokens
├── database/
│   ├── init_db.php              # Automated database setup & seeder
│   └── schema.sql               # Relational InnoDB schema
├── models/
│   ├── AuditLog.php             # Master compliance audit model
│   ├── ChainOfCustody.php       # Immutable custody ledger model
│   ├── Evidence.php             # Evidence & cryptographic verification model
│   ├── IncidentCase.php         # Case entity model
│   └── User.php                 # User entity model
├── storage/
│   └── vault/                   # Isolated evidence vault (Protected via .htaccess)
├── tests/
│   └── test_ciems.php           # Automated verification test suite
├── views/                       # Modular view templates
│   ├── admin/
│   ├── auth/
│   ├── cases/
│   ├── custody/
│   ├── dashboard/
│   ├── evidence/
│   └── layouts/
├── index.php                    # Front Controller & Request Dispatcher
└── README.md                    # Project documentation & grading manual
```
