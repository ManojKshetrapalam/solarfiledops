# SolarOps — Sun on Earth Solar Group
## Client Requirements: Corrected Scope Baseline

**Document Reference:** `docs/CLIENT_REQUIREMENTS_SCOPE_BASELINE.md`  
**Project:** SolarOps (Solar Operations & Field Management System)  
**Client:** Sun on Earth Solar Group  
**Repository:** `E:\Projects\Solar`  
**Branch:** `main`  
**Primary Requirements Source:** Original Client Workbook `Book1 (version 1)(1).xlsb`  
**Reference Assessment:** `docs/CLIENT_EXCEL_REQUIREMENTS_GAP_ASSESSMENT.md`  
**Status:** Approved Requirements & Scope Baseline — Implementation & Deployment PENDING EXPLICIT OWNER AUTHORIZATION  

---

## 1. Scope and Governing Principles

1. **Workbook as Primary Source of Truth:** The client's Excel workbook (`Book1 (version 1)(1).xlsb`) is the primary source of truth.
2. **Modify Over Multiply:** Modify existing screens, workflows, terminology, and report templates wherever possible.
3. **No Unnecessary Elements:** Do not introduce new report types, duplicate templates, or unrelated functionality.
4. **Preserve Internal Identifiers:** Preserve existing database identifiers, routes, and internal model names unless a specific technical change is necessary and separately approved.
5. **Historical Report Backward Compatibility:** Preserve complete backward compatibility for historical reports.
6. **No Speculative Expansion:** Do not silently interpret ambiguous requirements as authorization for functional changes. Any unresolved client requirement must remain explicitly documented.
7. **Report Scope Invariant:** Exactly **eight (8) existing printable templates**. **Zero (0) new templates**.
   - Four (4) templates require additional targeted functional or structural changes.
   - All eight (8) receive applicable shared branding and terminology updates.

---

## 2. Branding and Company Identity

### Application Identity
- **Application Brand:** Sun on Earth Solar Group
- **Sidebar/Header Identity:** Sun on Earth / Solar Group
- **Website:** `www.sunonearth.com`

### Initial Operating Company
- **Legal Name:** Sun on Earth Solar Solutions Pvt Ltd
- **Certification:** ISO 9001:2015 Certified
- **Phone:** `+91-9141930009`
- **Email:** `service@sunonearthsolar.com`
- **Registered Address:** Leave blank/`NULL` until confirmed by the client.

### Mandatory Separation
- **Strict Separation of Identities:** Application branding and operating-company identity are separate concepts.
- **Dynamic Resolution:** Reports must display the operating company associated with the relevant service, installation, site, or report record. They must **not** automatically display the employee's current or primary company.
- **Multi-Tenant Safeguard:** Do not hardcode *Sun on Earth Solar Solutions Pvt Ltd* into every report in a multi-company application.

---

## 3. Terminology and Interface Changes

Apply consistent user-facing terminology across the platform:

| Existing Terminology | Required User-Facing Terminology | Context / Scope |
|:---|:---|:---|
| `SolarOps` / `Field Management` | `Sun on Earth` / `Solar Group` | Sidebar branding, header |
| `Companies & Entities` | `Companies` | Sidebar navigation link |
| `Registered Corporate Entities` | `Registered Companies` | Companies page heading (`<h2>`) |
| `Manage multiple legal entities` | `Manage multiple legal Companies` | Companies page subtitle |
| `Employees / Engineers` | `Engineers / Technicians` | Sidebar navigation link |
| `Field Engineers & Staff` | `Field Engineers & Technicians` | Employees page header / title |
| `Sites` | `Solar Plants / Systems` | Sidebar navigation link, UI titles |
| `Field Services & Jobs` | `Service & Maintenance` | Sidebar navigation link, page title |
| `Field Service Orders & Job Dispatch` | `Service Operations & Maintenance` | Services page heading (`<h2>`) |
| `Create New Service Job` | `Create Service / Maintenance` | Services primary action button |
| `Routine Service` | `Regular Service & Maintenance` | Service type display name |
| `Breakdown Service` | `Complaints & Maintenance` | Service type display name |
| `Daily Work Log` | `Daily Work Report` | Timesheet / daily report label |
| `Customer Feedback & Satisfaction Survey` | `Customer Feedback` | Feedback form display label |
| `Assign Field Engineer / Employee` | `Assigned Engineers / Technicians` | Service dispatch form label |
| `Reports & Review` / `Field Service Reports` | `Maintenance & Service Reports` | Sidebar navigation link, reports header |
| `Submitted Field Service Reports` | `Submitted Reports` | Reports page heading (`<h2>`) |
| `Search Customer` | `Search Company` | Customer index filter label |
| `Commercial & Industrial Clients` | `Domestic / Non-domestic & Institutions` | Customers page heading (`<h2>`) |

### Guardrails
- Correct obvious spelling errors such as *"Maintainance"* and *"Commisioning"* when normalizing displayed wording, while preserving the original meaning.
- Changing *"Search Customer"* to *"Search Company"* is a UI label change; do not inadvertently change customer data relationships or search semantics.
- The existing internal database table `sites`, model `Site`, and associated routes remain unchanged. *"Solar Plants / Systems"* is strictly the user-facing terminology.

---

## 4. Company and Employee Membership

### Multi-Company Membership
- **Excel Requirement:** An employee must be selectable for any number of companies.
- **Architectural Direction:**
  - Introduce or use a many-to-many `company_user` membership relationship.
  - A user can belong to multiple companies simultaneously.
  - Company membership in `company_user` must be the sole authoritative basis for company-specific access.
  - Do not use `users.company_id` as an authorization fallback.
  - Preserve appropriate existing user and company records.

### Role Authority Model
The Excel identifies two administrative categories (`Super Admin` and `Admin Operations`), alongside `Engineers` and `Technicians`.

The validated role-authority model is:
- **`super_admin`:** Global platform role.
- **`admin_operations`:** Company-scoped role.
- **`engineer`:** Company-scoped role.

### Rules of Role Scoping
- Company-scoped roles must have one authoritative source: the company membership record (`company_user.role_in_company`).
- Do not duplicate role authority between global user roles and the company membership pivot.
- The complete permission matrix must be checked against existing accepted product decisions before implementation. (The Excel does not define a complete role matrix).

---

## 5. Customer Classification

- **Supported Categories:** The workbook wording strictly supports:
  1. `Domestic`
  2. `Non-domestic`
  3. `Institutions`
- **Prohibition on Speculative Values:** Do not independently introduce *Commercial* and *Industrial* as additional classification values. The workbook does not establish five distinct values.
- **Historical Record Integrity:** Existing customer records must remain unclassified (`NULL`) until appropriately classified. Do not automatically assign categories to historical records.
- **Technical Normalization:** The database slug may use a normalized technical value such as `institution`, while the user-facing label remains *"Institutions"*.

---

## 6. Installation Workflow — Preserve Existing Sequence

The existing application sequence must remain strictly unchanged:

| Execution Order | Application Workflow Domain | Internal Technical Slug |
|:---:|:---|:---|
| **Part 1** | Structure & Mounting | `installation_structure` |
| **Part 2** | Electrical & Cabling | `installation_electrical` |
| **Part 3** | PCU & Commissioning | `installation_commissioning` |

### Mandatory Requirements:
1. Do not reorder installation parts.
2. Do not alter dispatch or execution sequencing.
3. Do not change existing form-to-template mappings.
4. Do not rename internal identifiers, routes, or database values.
5. Do not create replacement installation workflows.
6. The difference between the workbook's descriptive listing and the application's existing sequence remains a documented client clarification item. It must not be resolved by guessing.

---

## 7. Existing Printable Report Inventory

The application contains exactly these eight (8) existing printable templates:

| No. | Report Name | Existing Printable Blade Template File |
|:---:|:---|:---|
| **1** | Service Report | `resources/views/admin/reports/print.blade.php` |
| **2** | Installation Structure | `resources/views/admin/reports/templates/installation_structure_print.blade.php` |
| **3** | Installation Electrical | `resources/views/admin/reports/templates/installation_electrical_print.blade.php` |
| **4** | Installation Commissioning | `resources/views/admin/reports/templates/installation_commissioning_print.blade.php` |
| **5** | Site Inspection | `resources/views/admin/reports/templates/site_inspection_print.blade.php` |
| **6** | Complaint Attending | `resources/views/admin/reports/templates/complaint_attending_print.blade.php` |
| **7** | Daily Work Report | `resources/views/admin/reports/templates/daily_work_report_print.blade.php` |
| **8** | Customer Feedback | `resources/views/admin/reports/templates/customer_feedback_print.blade.php` |

### Shared Updates Across All 8 Templates
Apply relevant common branding and terminology updates (dynamic operating company header, ISO certification, website, phone, email, and "Corporate Entity" &rarr; "Company").

### Four (4) Templates Requiring Additional Targeted Changes

#### A. Service Report (`print.blade.php`)
- Hide internal report number `NO: SSE-REP-XXXX` on customer-facing print/PDF.
- Retain internal report number in application/admin context.
- Retain service job reference `Job: #SSE-SRV-XXXX`.
- Preserve customer name and address layout.
- If report phone field is blank, automatically fall back to the linked customer's phone where available.

#### B. Installation Commissioning (`installation_commissioning_print.blade.php`)
Apply the following targeted changes:

| Existing Item / Wording | Required Treatment |
|:---|:---|
| `INSTALLATION: PCU & COMMISSIONING` | `INSTALLATION & COMMISSIONING` |
| `Commissioning Date` | `Installation date` |
| `Plant Capacity` | `Plant/System Capacity` |
| `Engineer Name` at top | **Remove from customer print view** |
| `Inverter Performance & Commissioning Test Matrix` | `Plant / System Installation & Commissioning` |
| `Make & Model` | `Model & Type` |
| `For Office Use / Approved By` | `Verified by` |
| `Operations Admin` | `Admin Operations` |
| `Senior Solar Field Engineer` | `Service Engineer / Technician` |
| `Employee ID ID: ENG-101` | **Remove from this customer print view** |

**Retain Unchanged in Commissioning Print:**
- `Commissioned By`
- `STATUS: APPROVED`
- `Engineer Signature & Date`
- Office approval timestamp

#### C. Site Inspection (`site_inspection_print.blade.php`)
Represent two separate, structured customer contacts:
1. **Customer Contact (Authority) — Authorised / Incharge:**
   - Name
   - Designation
   - Phone
2. **Customer Contact — Additional Contact:**
   - Name
   - Designation
   - Phone

*Guardrails:*
- Do not merge these into a single contact field.
- Preserve compatibility with historical reports by mapping existing flat contact fields where possible. (Compatibility requirement, not an additional feature).

#### D. Customer Feedback (`customer_feedback_print.blade.php`)
- Apply targeted changes specified in the Excel and accepted assessment.
- Remove hardcoded entity strings; bind dynamically to operating company branding.
- Retain existing report structure and template identity. Do not invent new feedback sections or fields beyond source requirements.

### Important Employee-ID Restriction
- The Excel specifically requests removal of the employee ID **only** from the Installation Commissioning customer print view.
- **Do not suppress employee IDs from Installation Structure, Installation Electrical, Complaint Attending, Daily Work Report, or other templates without a separate, explicit requirement.**

---

## 8. Report Company Details

Where applicable, report headers must support:
- Operating company name
- ISO certification
- Website
- Phone
- Email
- Registered address (only when confirmed)

*Rule:* Use the operating company associated with the underlying service, plant, or report record. Keep the registered address blank until client confirmation.

---

## 9. Explicitly Excluded From This Scope

The following items are strictly out of scope:
1. **New printable report templates** (strictly 0 new templates).
2. **Duplicate report types.**
3. **Reordering the three installation parts.**
4. **Changing internal identifiers solely to match display terminology.**
5. **Unsupported five-category customer classification.**
6. **Blanket removal of employee IDs from all report templates.**
7. **Hardcoding one operating company's identity into all company reports.**
8. **Unrequested changes to customer relationships or search behavior.**
9. **New functionality not traceable to the workbook or an already accepted product decision.**

---

## 10. Validation and Acceptance Criteria

Before implementation is declared complete, verify:
1. All eight (8) existing report templates remain available.
2. No additional report templates have been created.
3. Shared branding and terminology are consistent across all views.
4. Four (4) targeted templates meet their specific requirements.
5. Installation order and existing form/template mappings remain unchanged.
6. Multi-company employee membership works without relying on a single `users.company_id`.
7. Company-scoped authorization has one authoritative role source (`company_user.role_in_company`).
8. Reports display the correct operating company dynamically.
9. Historical reports continue rendering correctly without data loss.
10. Customer-facing prints hide only the specifically requested internal information (e.g. Commissioning top engineer and ID, Service Report internal number).
11. No unsupported customer classification values have been introduced (strictly `domestic`, `non_domestic`, `institutions`).
12. Registered address remains blank until client confirmation.

---

## 11. Outstanding Confirmation Items

1. **Registered Address:** Awaiting official client legal address details.
2. **Installation Sequence Discrepancy:** Retain existing application sequence (Structure &rarr; Electrical &rarr; Commissioning); client clarification remains pending.
3. **Customer Classification:** Use the three workbook-supported categories (`domestic`, `non_domestic`, `institutions`); confirm before any expansion.
4. **Full Role/Permission Matrix:** Validate against existing accepted product decisions before implementation.

---

## 12. Authorization Boundary

This document establishes the corrected requirements and scope baseline only.

It does **NOT** authorize:
- Application source-code modifications.
- Database schema changes or migrations.
- Production server access.
- Production database access.
- Production deployment.
- Changes to live client data or services.

**Implementation must begin only after explicit owner authorization.**

