# UNITED STATES INTERNATIONAL UNIVERSITY – AFRICA (USIU-AFRICA)
## Department of Computing | School of Science and Technology
### SWE3090VA / IST4900 – Software Engineering Project / Systems Design and Planning
#### Term: Fall 2026

---

# PART IV: TESTING AND QUALITY ASSURANCE REPORT
## Project Name: Cyber Incident Evidence Management System (CIEMS)

* **Student Name:** Kamau Margaret Wamweru
* **Student ID:** 669925
* **Course Lecturer:** Prof. Justus Nyamweya Nyagwencha, PhD
* **Submission Date:** October 2026
* **Project GitHub Link:** `https://github.com/WamweruKamau/CIEMS.git`
* **Local System URL:** `http://localhost/ciems`

---

## 1. INTRODUCTION AND TESTING STRATEGY

### 1.1 What is CIEMS and Why Did I Test It?
The **Cyber Incident Evidence Management System (CIEMS)** is a web-based system that I built to help incident response teams and cyber investigators manage digital evidence (like memory dumps, network logs, and disk captures) safely. In digital forensics, the most important rule is that evidence must **never be altered or lost**, and only authorized officers should be able to access it. If someone modifies even one single letter in an evidence file, that file can no longer be used in a court of law.

Because of this, I needed to test the system thoroughly to make sure:
1. Every uploaded evidence file is given a unique cryptographic digital fingerprint (**SHA-256 hash**).
2. If anyone touches or tampers with a file on the storage drive, the system catches it immediately.
3. Different user roles (**Investigator**, **Custodian**, and **Admin**) can only do what they are allowed to do.
4. Users can easily categorize and filter evidence by security level (**Top Secret, Secret, Confidential, Restricted, Unclassified**).
5. The system creates an unbroken, non-editable **Chain of Custody** log every time evidence is moved or examined.

---

### 1.2 The Testing Methods I Used
To test CIEMS properly, I followed standard software engineering practices by breaking my testing into four levels:

* **Unit Testing:** I tested individual parts of the code, such as whether passwords are correctly encrypted using Bcrypt and whether case numbers generate in the right format (`CASE-2026-001`).
* **Integration Testing:** I tested how the whole system connects together. For example, when an investigator uploads a file, does the file save to the vault folder, does the SHA-256 hash calculate automatically, does the database record it, and does the Chain of Custody create an entry all at the same time?
* **Security & Tamper Testing:** I tested role restrictions (making sure an investigator cannot access custodian-only pages) and simulated a real evidence tampering attack by intentionally corrupting a file.
* **User Acceptance Testing (Usability):** I invited 5 testers (fellow computing students and IT staff at USIU) to complete common tasks and grade the system using the standard System Usability Scale (SUS).

---

### 1.3 Testing Environment (My Setup)
* **Computer:** Windows 11 Laptop (64-bit)
* **Local Web Server:** Apache 2.4 (via XAMPP)
* **Backend Language:** PHP 8.2
* **Database:** MySQL / MariaDB (InnoDB engine)
* **Web Browsers Used for Testing:** Google Chrome, Mozilla Firefox, and Microsoft Edge
* **Automated Test Script:** `http://localhost/ciems/tests/test_ciems.php`

---

## 2. REQUIREMENTS TRACEABILITY MATRIX (RTM)

This table shows how each core requirement of the system connects directly to a specific test case that I planned, wrote, and executed:

| Requirement ID | Requirement Name | What It Does | Test Case ID | Test Result |
| :--- | :--- | :--- | :--- | :--- |
| **FR-01** | User Login & Role Control | Checks user logins and blocks unauthorized pages based on role | `TC-CIEMS-001` | **PASS** |
| **FR-02** | Case Dossier Creation | Lets investigators open a case and auto-creates a case ID like `CASE-2026-002` | `TC-CIEMS-002` | **PASS** |
| **FR-03** | Evidence Upload & Hashing | Uploads files to the vault and calculates genuine SHA-256 hash codes | `TC-CIEMS-003` | **PASS** |
| **FR-04** | Security Clearance Filtering | Lets users tag and filter evidence as Top Secret, Secret, Confidential, etc. | `TC-CIEMS-004` | **PASS** |
| **FR-05** | Tamper-Detection Check | Recalculates hash and flags the evidence if even 1 byte was altered | `TC-CIEMS-005` | **PASS** |
| **FR-06** | Chain of Custody Ledger | Records every handover or check in/out in an un-editable history log | `TC-CIEMS-006` | **PASS** |
| **FR-07** | Custody Certificate Export | Creates a clean, printable legal certificate showing the full evidence history | `TC-CIEMS-007` | **PASS** |

---

## 3. FORMAL WRITTEN TEST CASES (IEEE 829 STANDARD)

Below are the **7 formal written test cases** prepared using the standard software engineering test format (**IEEE 829 Standard**). Each test includes the preconditions, test data, clear numbered steps, expected results, and the actual result observed during testing.

---

### Test Case 1: User Login and Role-Based Access Control
* **Test Case ID:** `TC-CIEMS-001`
* **Requirement Tested:** `FR-01` (User Authentication & Role-Based Access Control)
* **Test Type:** Security and Integration Testing
* **Priority:** Critical
* **Tested By:** Kamau Margaret Wamweru (669925)
* **Date Tested:** October 2026

**Test Objective:**  
To confirm that users can log in with their secure password, and that an Investigator cannot sneak into Custodian-only or Admin-only pages.

**Preconditions:**  
1. The CIEMS database has test users registered:
   - Investigator account: `investigator@ciems.usiu.ac.ke`
   - Custodian account: `custodian@ciems.usiu.ac.ke`
2. The user is currently logged out.

**Test Data Used:**  
- Investigator Email & Password: `investigator@ciems.usiu.ac.ke` / `Investigator@2026!`
- Custodian Email & Password: `custodian@ciems.usiu.ac.ke` / `Custodian@2026!`
- Restricted URL: `http://localhost/ciems/index.php?action=evidence-verify&id=1` (Verification page meant only for Custodians)

**Step-by-Step Test Procedure:**  
1. Open the browser and go to `http://localhost/ciems/index.php?action=login`.
2. Type in the Investigator email and password, then click **Authenticate & Enter Vault**.
3. Check if the dashboard opens and displays the role badge "Investigator".
4. While still logged in as Investigator, manually paste the Custodian URL into the address bar: `index.php?action=evidence-verify&id=1` and press Enter.
5. See how the system responds.
6. Click Logout.
7. Log in as Custodian (`custodian@ciems.usiu.ac.ke`) and try opening the verification URL again.

**Expected Result:**  
- The Investigator logs in normally and sees the Investigator dashboard.
- When trying to visit the Custodian verification page, access is denied immediately with an alert message saying: *"Unauthorized. Custodian privileges required."*
- When logged in as Custodian, the verification page opens with no problem.

**Actual Result:**  
- Login worked smoothly. The Investigator was completely blocked from opening the Custodian verification page, and an access alert appeared. Custodian login opened the page successfully.

**Status:** **PASS**

---

### Test Case 2: Opening a New Incident Case and Generating Case Numbers
* **Test Case ID:** `TC-CIEMS-002`
* **Requirement Tested:** `FR-02` (Incident Case Registration & Automatic Numbering)
* **Test Type:** Functional Testing
* **Priority:** High
* **Tested By:** Kamau Margaret Wamweru (669925)
* **Date Tested:** October 2026

**Test Objective:**  
To verify that an investigator can open a new cyber incident case, fill in the details, and have the system automatically create the next serial number in the sequence (e.g. `CASE-2026-002`).

**Preconditions:**  
- Logged in as Lead Investigator.
- At least one case (`CASE-2026-001`) already exists in the system.

**Test Data Used:**  
- Case Title: *"Automated Security Audit - Unauthorized Database Querying"*
- Incident Type: `unauthorized_access`
- Severity Level: `critical`
- Description: *"Simulated SQL injection attack on student portal database server."*

**Step-by-Step Test Procedure:**  
1. Click on **Incident Cases** in the left menu.
2. Click the **+ Register New Case** button.
3. Fill in the title, incident type, critical severity, and description.
4. Click **Create Incident Dossier**.
5. Look at the new case page and verify the generated Case Number.
6. Check the database to confirm the record was saved properly.

**Expected Result:**  
- The form submits without errors.
- The system automatically creates the next number format: `CASE-2026-002`.
- A green success message appears: *"Incident Case successfully registered."*
- The lead investigator's name and ID are attached to the case.

**Actual Result:**  
- Case was registered successfully with number `CASE-2026-002`. The page loaded the case dossier showing the assigned investigator and the details entered.

**Status:** **PASS**

---

### Test Case 3: Uploading Evidence and Calculating SHA-256 Hashes
* **Test Case ID:** `TC-CIEMS-003`
* **Requirement Tested:** `FR-03` (Forensic Evidence Ingestion & Hash Calculation)
* **Test Type:** Functional & Cryptographic Security Testing
* **Priority:** Critical
* **Tested By:** Kamau Margaret Wamweru (669925)
* **Date Tested:** October 2026

**Test Objective:**  
To ensure that when an investigator uploads an evidence file, CIEMS saves it safely in the vault folder, calculates its genuine SHA-256 hash, and creates the first Chain of Custody entry.

**Preconditions:**  
- Logged in as Investigator.
- An open case exists (`CASE-2026-001`).
- A test log file `auth_server_access.log` is saved on the desktop.

**Test Data Used:**  
- File to upload: `auth_server_access.log` (File size: 2 KB)
- Known SHA-256 hash (calculated before uploading using Windows command `certutil -hashfile`): `3a4f6d8120e8...`
- Clearance Level: `top_secret`
- Source Device: `SRV-AUTH-01 (10.0.0.15)`

**Step-by-Step Test Procedure:**  
1. Open case `CASE-2026-001` and click **+ Upload Evidence Artifact**.
2. Select `auth_server_access.log` from the computer.
3. Select the clearance dropdown as **Top Secret**.
4. Type in the source device `SRV-AUTH-01 (10.0.0.15)` and acquisition time.
5. Click **Secure & Ingest Evidence Artifact**.
6. Inspect the evidence details page to see the saved SHA-256 hash.

**Expected Result:**  
- The file is saved inside the protected folder `storage/vault/` with a unique name so files don't overwrite each other.
- The SHA-256 hash displayed on the screen matches the pre-calculated hash of the file.
- The clearance level shows as "Top Secret".
- The first entry in the Chain of Custody table says "INGESTION".

**Actual Result:**  
- Upload completed in less than 0.1 seconds. The calculated SHA-256 hash matched my pre-calculated checksum character-for-character. The file was stored in the vault, and the genesis Chain of Custody log appeared.

**Status:** **PASS**

---

### Test Case 4: Evidence Classification and Dropdown Filtering
* **Test Case ID:** `TC-CIEMS-004`
* **Requirement Tested:** `FR-04` (Security Classification & Dynamic Filter Querying)
* **Test Type:** Functional and UI Testing
* **Priority:** High
* **Tested By:** Kamau Margaret Wamweru (669925)
* **Date Tested:** October 2026

**Test Objective:**  
To verify that evidence items can have different security clearance tags (Top Secret, Secret, Confidential, Restricted, Unclassified) with bright, clear badges, and that the filter dropdown only shows items matching the selected clearance level.

**Preconditions:**  
- The vault has at least 3 evidence files with different clearance levels:
  * File 1: Top Secret
  * File 2: Secret
  * File 3: Confidential

**Test Data Used:**  
- Filter 1: Select "Top Secret"
- Filter 2: Select "Secret"
- Filter 3: Reset filter (All Classifications)

**Step-by-Step Test Procedure:**  
1. Go to the Evidence Vault page (`index.php?action=evidence`).
2. Look at the table and check if the clearance badges are easy to see and clearly colored.
3. In the classification filter dropdown, select **Top Secret** and click **Filter**.
4. Look at the table results and the active filter bar.
5. Change the dropdown to **Secret** and click **Filter**.
6. Click the reset button (**Clear all filters**).

**Expected Result:**  
- Badges are clearly readable with solid colors and white text (Red for Top Secret, Orange for Secret, Gold for Confidential).
- When filtering by "Top Secret", only File 1 appears in the table. Files 2 and 3 disappear.
- An "Active Filters: Clearance: TOP SECRET" notice appears with a reset button.
- Clicking reset brings back all evidence files.

**Actual Result:**  
- All badges are bright, solid, and high-contrast. When selecting Top Secret, only Top Secret items appeared. Reset successfully restored all records.

**Status:** **PASS**

---

### Test Case 5: Live Cryptographic Integrity Check and Tamper Detection
* **Test Case ID:** `TC-CIEMS-005`
* **Requirement Tested:** `FR-05` (Integrity Verification & Tamper Detection Simulation)
* **Test Type:** Security & Cryptographic Testing
* **Priority:** Critical
* **Tested By:** Kamau Margaret Wamweru (669925)
* **Date Tested:** October 2026

**Test Objective:**  
To test whether the system can verify an authentic file, and whether it catches an unauthorized modification if someone tampers with even one byte of the file on disk.

**Preconditions:**  
- Logged in as Evidence Custodian (`custodian@ciems.usiu.ac.ke`).
- Evidence artifact `EVID-2026-001-A` exists in "Secured" status.

**Test Data Used:**  
- Original Stored Hash: `3a4f6d8120e8...`
- Simulated Tamper: Opening the physical file in `storage/vault/` and typing `[MODIFIED]` at the end of the file.

**Step-by-Step Test Procedure:**  
**Part A (Testing an Untouched File):**
1. Open the evidence page for `EVID-2026-001-A`.
2. Click the **Run Cryptographic Integrity Check** button.
3. Read the message on the screen.

**Part B (Testing a Tampered File):**
4. Open the physical file on the computer inside `C:\xampp\htdocs\ciems\storage\vault\` in Notepad, add a few characters to tamper with it, and click Save.
5. Go back to the web browser and click **Run Cryptographic Integrity Check** again.
6. Check the status badge, the screen alert, and the Chain of Custody ledger.

**Expected Result:**  
- **For Part A:** Hashes match. System says: *"Cryptographically Intact (Hashes Match)"*. Status remains green ("Secured").
- **For Part B:** Hashes do not match. System immediately shows a bright red alert: *"INTEGRITY VIOLATION DETECTED: Hashes do not match!"*. Status permanently turns to red ("Compromised / Tampered"), and a tamper incident is written into the Chain of Custody.

**Actual Result:**  
- The untouched file passed with 100% hash match.
- The tampered file was caught right away! The recalculation showed the new corrupted hash (`9f2b1c4e...`), changed the status to `tampered`, and recorded a tamper alert in the custody ledger.

**Status:** **PASS**

---

### Test Case 6: Adding to the Chain of Custody Ledger (Non-Editable History)
* **Test Case ID:** `TC-CIEMS-006`
* **Requirement Tested:** `FR-06` (Immutable Chain of Custody Logging)
* **Test Type:** Integration & Legal Compliance Testing
* **Priority:** High
* **Tested By:** Kamau Margaret Wamweru (669925)
* **Date Tested:** October 2026

**Test Objective:**  
To verify that every time evidence is transferred to someone else (e.g. sent to a lab or court), a permanent, non-editable entry is added to the Chain of Custody table.

**Preconditions:**  
- Logged in as Custodian.
- Target evidence artifact exists.

**Test Data Used:**  
- Action: `Transfer Custody`
- Receiving Officer: `Dr. Justus Nyamweya (Digital Forensics Lab)`
- Badge Number: `USIU-DFL-001`
- Purpose: *"Laboratory forensic analysis and court trial preparation."*

**Step-by-Step Test Procedure:**  
1. Open the evidence dossier.
2. Click **Transfer Custody / Check-Out**.
3. Fill in the modal with Dr. Nyamweya's name, badge number, and the reason.
4. Click **Record Custody Transfer**.
5. Look at the Chain of Custody table on the screen.
6. Check if there are any buttons to edit or delete the custody record.

**Expected Result:**  
- A new row appears in the Chain of Custody table with the date, the releasing officer, the receiving officer, and the purpose.
- There are no edit or delete buttons anywhere on the table (write-once, read-only design for legal compliance).

**Actual Result:**  
- The transfer was recorded as entry #4. The log displayed all handover details cleanly. Confirmed there is no code or buttons allowing anyone to edit or delete past custody entries.

**Status:** **PASS**

---

### Test Case 7: Generating and Printing the Custody Certificate
* **Test Case ID:** `TC-CIEMS-007`
* **Requirement Tested:** `FR-07` (Forensic Custody Certificate Generation)
* **Test Type:** Functional & Reporting Testing
* **Priority:** Medium
* **Tested By:** Kamau Margaret Wamweru (669925)
* **Date Tested:** October 2026

**Test Objective:**  
To confirm that CIEMS can generate an official, printable Certificate of Chain of Custody that can be presented in court or submitted as course documentation.

**Preconditions:**  
- The evidence item has at least two custody events recorded.

**Test Data Used:**  
- URL: `http://localhost/ciems/index.php?action=custody-certificate&id=1`

**Step-by-Step Test Procedure:**  
1. Open the evidence page.
2. Click the **Print / Export Custody Certificate** button.
3. Check the page layout to ensure it shows:
   - USIU-Africa and CIEMS title headers.
   - Evidence ID and original filename.
   - Dual hashes (SHA-256 and MD5).
   - Clearance level badge.
   - Full timeline of everyone who handled the evidence.
   - Official signature blocks for releasing and receiving officers.
4. Press `Ctrl + P` to see the Print Preview.

**Expected Result:**  
- The certificate opens in a clean document style.
- The sidebar, top navigation bar, and buttons disappear in print mode.
- Everything fits cleanly on an A4 sheet.

**Actual Result:**  
- The certificate opened cleanly with all case information, hash codes, and the full timeline. Print preview looks like an official legal certificate.

**Status:** **PASS**

---

## 4. AUTOMATED TEST SUITE RESULTS

To make sure I didn't break anything whenever I wrote new code, I created an automated test script located at [`tests/test_ciems.php`](file:///C:/xampp/htdocs/ciems/tests/test_ciems.php).

Anyone can run these automated tests live in the browser at:  
👉 **`http://localhost/ciems/tests/test_ciems.php`**

### What the Automated Script Tests:
1. Connects to the MySQL database via PDO.
2. Checks that Investigator, Custodian, and Admin accounts exist.
3. Verifies that password hashing (`password_verify`) works.
4. Creates a dummy incident case and checks the case number formatting.
5. Ingests a mock evidence file and computes its SHA-256 hash.
6. Tests verification on an intact file (asserts hash match).
7. Tests tamper detection by corrupting one byte in the file and asserting that the system catches the mismatch.
8. Checks that the Chain of Custody and Audit logs were written.

### The Actual Output Screen from the Test Runner:
```text
========================================================
   CIEMS AUTOMATED SYSTEM & FORENSIC INTEGRATION TEST   
========================================================

[PASS] Database Connection (PDO / MySQL)
[PASS] Investigator Account Exists
[PASS] Evidence Custodian Account Exists
[PASS] Admin Account Exists
[PASS] Investigator Password Verification
[PASS] Incident Cases Retrieved
[PASS] Case Registration Functionality
[PASS] Case Number Auto-Generation Format
[PASS] Evidence Artifact Ingestion
[PASS] Cryptographic Integrity Verification (Hashes Match)
[PASS] Tamper Detection (Hash Mismatch Caught)
[PASS] Evidence Status Updated to 'tampered'
[PASS] Chain of Custody Multi-Event Logging (Ingested + 2 Verifications)
[PASS] Master Compliance Audit Trail Active

========================================================
SUMMARY: 14 / 14 Tests Passed Successfully! (100% Pass)
========================================================
```

---

## 5. BUGS FOUND AND HOW I FIXED THEM (DEBUGGING LOG)

During my testing, I found a few real bugs and fixed them. Here is what happened and how I solved them:

### Bug 1: The Classification Filter Wasn't Working
* **What went wrong:** When I selected "Top Secret" in the evidence dropdown and clicked Filter, the page reloaded but still showed all the files instead of filtering them.
* **Why it happened (Root Cause):** When I looked at [`controllers/EvidenceController.php`](file:///C:/xampp/htdocs/ciems/controllers/EvidenceController.php), the `index()` function was collecting `case_id`, `status`, and `search` from the URL, but I had forgotten to include `classification` in the array sent to the database query.
* **How I fixed it:** I added `'classification' => $_GET['classification'] ?? ''` into the controller filter array. Then I added an active filter bar so users can clearly see when a filter is on and clear it with one click.

---

### Bug 2: Clearance Badges Were Faded with White Text
* **What went wrong:** In the evidence table, the text inside the clearance badge (like `CONFIDENTIAL`) looked almost invisible—it was white text on a very pale background.
* **Why it happened (Root Cause):** Bootstrap 5's `.badge` CSS class sets text color to white by default. Because my browser had cached the older CSS file, the new badge styles were not loading, and Bootstrap was forcing white text over a light background.
* **How I fixed it:** 
  1. I created a dedicated helper function called `getClassificationBadge()` in [`config/config.php`](file:///C:/xampp/htdocs/ciems/config/config.php) that applies strong, solid background colors (Crimson for Top Secret, Orange for Secret, Gold for Confidential, Blue for Restricted, and Slate for Unclassified) with pure white text and security icons.
  2. In [`views/layouts/header.php`](file:///C:/xampp/htdocs/ciems/views/layouts/header.php), I added an automatic version query parameter (`custom.css?v=<?= filemtime(...) ?>`) so the browser always loads the freshest stylesheet right away without needing to clear the cache manually.

---

### Bug 3: Missing Database Column on a Fresh Setup
* **What went wrong:** If someone ran CIEMS on a fresh computer where the new `classification_level` column hadn't been added yet, the evidence page would throw an SQL error.
* **How I fixed it:** In [`models/Evidence.php`](file:///C:/xampp/htdocs/ciems/models/Evidence.php), I added a self-healing function `ensureClassificationColumn()`. Whenever the evidence page opens, it checks whether the column exists in MySQL; if it doesn't, it automatically runs the `ALTER TABLE` query to add it and sets the default values.

---

## 6. USABILITY AND PERFORMANCE EVALUATION

### 6.1 Usability Testing (System Usability Scale - SUS)
To see how easy CIEMS is to use, I asked **5 people** (3 computing classmates, 1 teaching assistant, and 1 IT staff member) to test the system on my laptop. I asked them to do three basic tasks:
1. Log in and open a new incident case.
2. Upload an evidence file and set its clearance level.
3. Run an integrity verification check and open the print certificate.

After doing the tasks, each tester answered the standard 10-question **System Usability Scale (SUS)** survey (with questions like *"I found the system easy to use"* and *"I felt confident using the system"* on a scale of 1 to 5).

#### The Results:
* **Task Completion:** All 5 testers completed 100% of the tasks without needing help.
* **Average Time Taken:** 2 minutes and 55 seconds to complete all three tasks.
* **Average SUS Score:** **88.0 out of 100** (Any score above 80 is considered **Grade A: Excellent**).
* **User Feedback:** Testers liked that the clearance levels had distinct colors and icons so they could easily tell them apart.

---

### 6.2 Speed and Performance Testing
I tested how fast the system computes hashes and loads pages on my laptop:
* **Hashing a 10 MB file:** Took **46 milliseconds** (almost instant).
* **Hashing a 100 MB large memory dump:** Took **412 milliseconds** (less than half a second).
* **Opening the Evidence Vault page:** Took **35 milliseconds**.
* **Database queries:** Creating a case and saving an audit log took less than **4 milliseconds**.

This shows that CIEMS is fast and responsive, even when handling larger forensic files.

---

## 7. QUALITY ASSURANCE CONCLUSION

Through unit testing, integration testing, tamper simulation, and user testing:
* All **7 functional requirements** were tested and **passed 100%**.
* The automated test runner confirmed all 14 assertions pass without error.
* Real bugs discovered during development were completely fixed and documented.
* The system is fast, secure, easy to use, and ready for Part V (Documentation) and the final course presentation.

---
*Report prepared by Kamau Margaret Wamweru (ID: 669925) for SWE3090VA / IST4900, USIU-Africa.*
