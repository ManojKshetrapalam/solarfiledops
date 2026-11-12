# Client Change Request — Revised Implementation & Migration Plan
**Document Reference**: `docs/CLIENT_CHANGE_IMPLEMENTATION_PLAN.md`  
**Date**: November 11, 2026 (Documentation Revision 4.6 — Operational Trust Enforcement & Final Governance Reconciliation)  
**Lead Architect & Auditor**: Lead Software Architect & Senior Full-Stack Engineer  
**Client Branding**: Sun on Earth Solar Group  
**Target Environment**: Hostinger LiteSpeed Production (PHP 8.4.27 CLI, SQLite 3.26.0+)  
**Hosting Account**: `u996219523` (`/home/u996219523`)  
**Status**: Proposed Operational & Technical Plan — Phase 0 Closure PENDING FORMAL OWNER REVIEW AND ACCEPTANCE; Implementation & Deployment STRICTLY UNAUTHORIZED  

---

## 1. Overview & Architectural Principles

This document defines the strict, phased execution sequence for implementing the client change requests for **Sun on Earth Solar Group**.

### Core Guarantees:
1. **Preserve All Existing Functionality**: Zero breaking changes to active workflows, routes, or database records.
2. **Backward-Compatible Schema**: All schema changes are additive. No existing table or column will be deleted or renamed.
3. **Authoritative Company Membership & Legacy Separation**: `company_user` is the **sole authoritative source** for company authorization. **`users.company_id` is retained ONLY for legacy compatibility and default-company display. It must NEVER provide authorization or access fallback.** A stale `users.company_id` value grants zero access once a pivot membership is detached.
4. **Global Roles vs Standardized Company-Specific Roles**: A user's global role (`users.role`) does not automatically replicate across company assignments. Programmatic values for `company_user.role_in_company` are standardized strictly to `admin_operations` and `engineer` (resolving the `engineer` / `technician` inconsistency). **The column schema defines NO DEFAULT VALUE (`$table->string('role_in_company');`)**, requiring every assignment to explicitly specify the role.
5. **Company-Contextual Authorization & Super Admin Separation (Correction 7)**: Operational actions are authorized strictly through company-contextual helper methods on `User`:
   - `hasCompanyRole($companyId, $role): bool` inspects actual recorded `company_user` pivot table records only. Super Admin capability bypass is deliberately excluded here so this method accurately reflects actual membership and never invents roles.
   - `canAccessCompany($companyId): bool` (Requires `super_admin` OR active membership in `company_user`)
   - `canAdministerCompany($companyId): bool` (Requires `super_admin` OR `role_in_company === 'admin_operations'` for that company)
   - `canPerformEngineerActions($companyId): bool` (Requires `super_admin` OR `role_in_company === 'engineer'` for that company)
   - **Global role never overrides company role**: A global `admin_operations` user assigned as `engineer` in Company B is strictly barred (`403 Forbidden`) from administrative actions in Company B. Middleware admission provides high-level panel entry, but action-level authorization enforces company-specific role restrictions.
6. **Primary Company Invariant & Zero-Fallback Getter**: Exactly one active primary company is required for users with one or more company memberships. A Super Admin with zero company memberships may have zero primary companies. The `getPrimaryCompanyAttribute()` getter strictly returns `null` when no primary company is set (implicit first-company fallback permanently removed). The primary company invariant is independently validated.
7. **Transactional Pre-Validated Legacy Mapping (All 12 Pre-Conditions — Correction 4, 9)**: Generic backfill instructions that copy `users.role` are permanently eliminated. The migration wraps all backfill logic in `DB::transaction(...)` and executes an explicit, deterministic legacy mapping for the 8 existing user accounts. Pre-validation checks all 12 pre-conditions (raw mapping tuples uniqueness to prevent key collisions, account inventory, roles, deterministic company IDs, company existence, empty pivot table, primary flag validity, read-only User 1 check) before entering the separated Transactional Mutation Phase.
8. **Hardened Production Rollback Protection Across All Migrations**: Ordinary migration rollbacks in production are strictly prohibited across all three migrations (`companies` branding, `company_user` membership, `customers` classification). Production recovery is handled exclusively via forward corrective migrations or verified atomic database restorations.
9. **Unified SQLite Journal Policy, Independent Cryptographic Trust Chain & Canonical Two-Stage Restoration (Corrections 1, 2, 3, 4, 6, 7, 8)**:
   - Approved baseline is strictly SQLite journal mode `delete`. Normal production backup queries and normalizes `PRAGMA journal_mode;`, requiring exact `delete`. Sidecars (`-wal`, `-shm`) in `delete` mode abort backup immediately for operator investigation (never checkpointed or archived).
   - Exceptional WAL recovery is maintained as a separate, controlled procedure requiring verified writer quiescence, strict regex validation of checkpoint output format (`busy == 0`, `log == checkpointed`), sidecar verification, and journal mode reversion to `delete`.
   - Restoration strictly follows the **Canonical 16-Step Fail-Closed Two-Stage Restoration Procedure** defined in Section 2.5 of `docs/CLIENT_CHANGE_DATA_MIGRATION_SAFETY.md`.
   - Stage A executes in-place restoration and verification (Steps 1–15), outputs a durable restoration receipt, flushes caches, and concludes at Step 16 with the application held strictly in maintenance mode.
   - Stage B executes independent operator release authorization via `./release_application.sh` after receipt validation and smoke tests.
   - Restoration enforces a hard writer quiescence gate: under Hostinger shared hosting (`u996219523`), unprivileged tools cannot inspect root-owned LiteSpeed workers (`INSPECTION_UNAVAILABLE`); an operator assertion `CONFIRM_WRITERS_STOPPED=YES` alone does NOT establish quiescence; an external hosting worker-stop attestation artifact is mandatory, and where unavailable, restoration remains BLOCKED.
   - Cryptographic trust chain requires off-server signed manifests verified via detached OpenSSL signatures (`openssl dgst -sha256 -verify`) against a pinned public key, binding database backup filename, byte size, and SHA-256 alongside membership manifest hash. Storing local checksums on-server alone is NOT independent trust (declared as an unresolved operational dependency).
   - Mandatory same-filesystem staging verification aborts immediately if filesystem device identity differs or cannot be resolved (zero warning fallback).
   - Post-restore verification compares restored records field-by-field against the signed baseline manifest.
10. **Authenticated Deployment State Record & Genuine Content-Level Verification (Corrections 2, 5)**:
    - Private media deployment state is sourced strictly from mandatory, cryptographically signed `.deployment_state.json` verified via `.deployment_state.sig` with OpenSSL against a pinned release public key (no unsafe defaults, no unsigned state files). Contradictions between declared state and on-disk files abort immediately.
    - Media verification computes relative path, byte size, AND SHA-256 for all regular files across all declared directories (`public` and `private/reports`) using safe null-delimited processing, unpacks archive to temporary staging outside `public_html`, and diffs inventories bit-for-bit (symlinks, special files, and path traversals rejected).
    - Technical report photos and documents are served via authorized streaming endpoints (`/reports/{report}/photos/{photo}`) with relationship scoping (`$report->photos()->whereKey($photoId)->firstOrFail()`) and separator-aware canonical path validation (`rtrim($realBaseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR`). Direct static requests return `404` or `403`.
11. **Two-Tier Dynamic Branding**:
    - **Layer A**: Application identity: *Sun on Earth Solar Group*.
    - **Layer B**: Operating company identity: dynamically resolved strictly from the underlying service/customer/plant association (never an employee's primary or current UI company).
    - *Company Registered Address*: Kept blank until officially confirmed by the client.
12. **Zero-Inference Customer Classification**: Existing customer records remain `NULL` (unclassified). New or updated records choose from `domestic`, `non_domestic`, `institutions`.
13. **Historical Report Backward Compatibility**: Two-tier parser guarantees all existing JSON reports (Site Inspection, Installation, Service) remain 100% readable and printable.
14. **Browser Print Engine Alignment**: All customer-facing and internal documents are rendered as **printable Blade templates** styled with CSS `@media print` for browser print/export. Binary server-side PDF generation is explicitly not used.
15. **Explicit Administrator Roles**: User ID 1 (`admin@solar.local`) explicitly designated as `super_admin`. New admins default to company-scoped `admin_operations`.
16. **Automated Verification**: Every modified flow will be verified with dedicated PHPUnit feature tests.

---

## 2. Proposed Database Changes & Migrations

Three backward-compatible migrations will be introduced in `database/migrations/`:

### Migration 1: `add_branding_fields_to_companies_table.php`
- **Target Table**: `companies`
- **Columns Added**:
  - `legal_name` (`string`, nullable) — Legal corporate name
  - `secondary_logo_path` (`string`, nullable) — Optional secondary emblem
  - `website` (`string`, nullable) — Corporate website URL
  - `registration_details` (`string`, nullable) — Corporate CIN / Registration details
  - `certification_info` (`string`, nullable) — e.g. "ISO 9001:2015 Certified"
  - `report_footer_info` (`text`, nullable) — Standard footer terms and disclaimers
  - `brand_color` (`string`, nullable) — Primary brand theme color (hex code)
- **Initial Profile Configuration**:
  - Company: `Sun on Earth Solar Solutions Pvt Ltd` (`SOE`)
  - Legal Name: `Sun on Earth Solar Solutions Pvt Ltd`
  - Certification: `ISO 9001:2015 Certified`
  - Website: `www.sunonearth.com`
  - Phone: `+91-9141930009`
  - Email: `service@sunonearthsolar.com`
  - Address: `NULL` (Left blank as instructed until verified by client)
- **Production Rollback Guard**:
  ```php
  public function down(): void
  {
      if (app()->environment('production')) {
          throw new \RuntimeException(
              'CRITICAL SAFETY EXCEPTION: Dropping branding columns from companies in production is prohibited to prevent data loss. Use forward corrective migrations or verified database restoration.'
          );
      }
      Schema::table('companies', fn($table) => $table->dropColumn([
          'legal_name', 'secondary_logo_path', 'website', 'registration_details',
          'certification_info', 'report_footer_info', 'brand_color'
      ]));
  }
  ```

### Migration 2: `create_company_user_pivot_table.php`
- **Target Table**: `company_user` (New Pivot Table — Authoritative)
- **Schema**:
  ```php
  Schema::create('company_user', function (Blueprint $table) {
      $table->id();
      $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
      $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
      $table->string('role_in_company'); // STRICTLY NO DEFAULT. Permitted: 'admin_operations', 'engineer'
      $table->boolean('is_primary')->default(false);
      $table->timestamps();

      $table->unique(['company_id', 'user_id']);
  });
  ```
- **Transactional, Pre-Validated Legacy Backfill Routine (All 12 Pre-Conditions — Correction 4)**:
  ```php
  DB::transaction(function () {
      $users = DB::table('users')->get();
      $expectedUserIds = [1, 2, 3, 4, 5, 6, 7, 8];

      // Pre-condition 1: Verify exact count of legacy accounts is 8
      if ($users->count() !== 8) {
          throw new \RuntimeException("ABORT MIGRATION: Expected exactly 8 legacy user accounts, found {$users->count()}.");
      }

      // Pre-condition 2: Verify no expected user is missing
      $foundUserIds = $users->pluck('id')->sort()->values()->toArray();
      if ($foundUserIds !== $expectedUserIds) {
          throw new \RuntimeException("ABORT MIGRATION: User ID mismatch. Expected [1..8], found: " . json_encode($foundUserIds));
      }

      // Pre-condition 3: Verify no unexpected user is included
      foreach ($foundUserIds as $id) {
          if (!in_array($id, $expectedUserIds, true)) {
              throw new \RuntimeException("ABORT MIGRATION: Unexpected user ID detected: {$id}.");
          }
      }

      // Pre-condition 4: Verify User 1 has exact legacy role 'admin'
      $userMap = $users->keyBy('id');
      if ($userMap[1]->role !== 'admin') {
          throw new \RuntimeException("ABORT MIGRATION: User 1 expected role 'admin', found '{$userMap[1]->role}'.");
      }

      // Pre-condition 5: Verify Users 2-8 have exact legacy role 'engineer'
      for ($i = 2; $i <= 8; $i++) {
          if ($userMap[$i]->role !== 'engineer') {
              throw new \RuntimeException("ABORT MIGRATION: User {$i} expected role 'engineer', found '{$userMap[$i]->role}'.");
          }
      }

      // Pre-condition 6: Verify each engineer's company_id matches the deterministic mapping
      $expectedCompanyMappings = [
          2 => 1,
          3 => 2,
          4 => 1,
          5 => 1,
          6 => 1,
          7 => 1,
          8 => 1,
      ];
      foreach ($expectedCompanyMappings as $userId => $expectedCompanyId) {
          if ((int)$userMap[$userId]->company_id !== $expectedCompanyId) {
              throw new \RuntimeException("ABORT MIGRATION: User ID {$userId} expected company_id {$expectedCompanyId}, found '{$userMap[$userId]->company_id}'.");
          }
      }

      // Pre-condition 7: Verify Company 1 and Company 2 exist in companies table
      $companyIds = DB::table('companies')->whereIn('id', [1, 2])->pluck('id')->toArray();
      if (!in_array(1, $companyIds, true) || !in_array(2, $companyIds, true)) {
          throw new \RuntimeException("ABORT MIGRATION: Required legacy companies (ID 1 and 2) do not exist in companies table.");
      }

      // Pre-condition 8: Define source legacy engineer mappings as raw tuples BEFORE associative indexing
      $sourceLegacyMappings = [
          ['user_id' => 2, 'company_id' => 1, 'role_in_company' => 'engineer', 'is_primary' => true],
          ['user_id' => 3, 'company_id' => 2, 'role_in_company' => 'engineer', 'is_primary' => true],
          ['user_id' => 4, 'company_id' => 1, 'role_in_company' => 'engineer', 'is_primary' => true],
          ['user_id' => 5, 'company_id' => 1, 'role_in_company' => 'engineer', 'is_primary' => true],
          ['user_id' => 6, 'company_id' => 1, 'role_in_company' => 'engineer', 'is_primary' => true],
          ['user_id' => 7, 'company_id' => 1, 'role_in_company' => 'engineer', 'is_primary' => true],
          ['user_id' => 8, 'company_id' => 1, 'role_in_company' => 'engineer', 'is_primary' => true],
      ];
      foreach ($sourceLegacyMappings as $m) {
          if (!isset($userMap[$m['user_id']]) || !in_array($m['company_id'], $companyIds, true)) {
              throw new \RuntimeException("ABORT MIGRATION: Mapping references non-existent user or company.");
          }
      }

      // Pre-condition 9: Verify user_id uniqueness on raw source tuples to prevent associative key collision
      $rawUserIds = array_column($sourceLegacyMappings, 'user_id');
      if (count($rawUserIds) !== count(array_unique($rawUserIds))) {
          throw new \RuntimeException("ABORT MIGRATION: Duplicate user_id detected in raw source mapping tuples.");
      }

      // Pre-condition 10: Verify company_user table is currently empty
      if (DB::table('company_user')->count() > 0) {
          throw new \RuntimeException("ABORT MIGRATION: company_user table is not empty. Cannot run legacy backfill.");
      }

      // Pre-condition 11: Verify primary membership mapping is valid (each assigned user has exactly one primary)
      $primaryCountByUser = [];
      foreach ($sourceLegacyMappings as $m) {
          $uid = $m['user_id'];
          $primaryCountByUser[$uid] = ($primaryCountByUser[$uid] ?? 0) + ($m['is_primary'] ? 1 : 0);
      }
      foreach ($primaryCountByUser as $uid => $count) {
          if ($count !== 1) {
              throw new \RuntimeException("ABORT MIGRATION: User {$uid} must have exactly one primary company flagged.");
          }
      }

      // Pre-condition 12: Strictly read-only validation of User 1 account state (NO mutation in validation phase)
      $user1 = DB::table('users')->where('id', 1)->first();
      if (!$user1 || $user1->role !== 'admin') {
          throw new \RuntimeException("ABORT MIGRATION: User 1 validation failed. Expected existing account with role 'admin'.");
      }

      // =========================================================================
      // TRANSACTIONAL MUTATION PHASE (Executed ONLY after Pre-conditions 1-12 pass)
      // =========================================================================
      // Mutation Step 1: Promote User 1 to global super_admin
      $updatedUser1 = DB::table('users')->where('id', 1)->update(['role' => 'super_admin']);
      if ($updatedUser1 !== 1) {
          throw new \RuntimeException("ABORT MIGRATION: Failed to update User 1 role to 'super_admin'.");
      }

      // Mutation Step 2: Insert validated legacy engineer company memberships
      $insertedCount = 0;
      foreach ($sourceLegacyMappings as $m) {
          DB::table('company_user')->insert([
              'company_id'      => $m['company_id'],
              'user_id'         => $m['user_id'],
              'role_in_company' => $m['role_in_company'], // Explicit 'engineer', never defaulted
              'is_primary'      => $m['is_primary'],
              'created_at'      => now(),
              'updated_at'      => now(),
          ]);
          $insertedCount++;
      }

      // Mutation Step 3: Verify exact inserted record count
      if ($insertedCount !== 7 || DB::table('company_user')->count() !== 7) {
          throw new \RuntimeException("ABORT MIGRATION: Inserted count mismatch. Expected 7, found " . DB::table('company_user')->count());
      }
  });
  ```
- **Production Rollback Guard**:
  ```php
  public function down(): void
  {
      if (app()->environment('production')) {
          throw new \RuntimeException(
              'CRITICAL SAFETY EXCEPTION: Dropping company_user in production is prohibited to prevent operational data loss. Use forward corrective migrations or verified database restoration.'
          );
      }
      Schema::dropIfExists('company_user');
  }
  ```
- **Legacy Invariant**:
  - `users.company_id` is retained **ONLY for legacy compatibility and default-company UI display**.
  - **`users.company_id` NEVER provides authorization or access fallback.**
  - `company_user` is the **sole authoritative source** for active authorization.

### Migration 3: `add_classification_to_customers_table.php`
- **Target Table**: `customers`
- **Column Added**:
  - `classification` (`string`, nullable, indexed) — Values: `domestic`, `non_domestic`, `institutions`
- **Zero-Inference Handling**: Existing customer records default to `NULL` (unclassified).
- **Production Rollback Guard**:
  ```php
  public function down(): void
  {
      if (app()->environment('production')) {
          throw new \RuntimeException(
              'CRITICAL SAFETY EXCEPTION: Dropping customer classification in production is prohibited to prevent data loss. Use forward corrective migrations or verified database restoration.'
          );
      }
      Schema::table('customers', fn($table) => $table->dropColumn('classification'));
  }
  ```

---

## 3. Phased Implementation Sequence

```
+--------------------------------------------------------------------------------------------------+
|                                    PHASED EXECUTION SEQUENCE                                     |
+--------------------------------------------------------------------------------------------------+
| PHASE 1: Application & Configurable Company Branding                                             |
|          - Update login, layouts, and header identity to "Sun on Earth Solar Group"              |
|          - Execute Migration 1; configure "Sun on Earth Solar Solutions Pvt Ltd" profile         |
|          - Keep registered address NULL until confirmed                                          |
|                                                                                                  |
| PHASE 2: Sidebar & Application Terminology Alignment                                             |
|          - Update sidebar, page headers, buttons, breadcrumbs, search labels across admin panels  |
|                                                                                                  |
| PHASE 3: Multi-Company Employee Management & Simultaneous Access                                 |
|          - Execute Migration 2 with transactional pre-validation for 8 accounts (no default role) |
|          - Update User & Company models with belongsToMany                                       |
|          - Implement company-contextual helpers (hasCompanyRole, canAdministerCompany)           |
|          - Enforce company_user as sole authoritative source (stale users.company_id ignored)    |
|          - Update Employee CRUD to multi-select Assigned Companies and select single Primary     |
|          - Require explicit company-specific role selection (role_in_company without default)    |
|          - Update Service Dispatch engineer dropdowns to filter by company                       |
|          - Enforce company scoping across dashboards, search, and reports                        |
|                                                                                                  |
| PHASE 4: Customer Management & Classification                                                    |
|          - Execute Migration 3; support Domestic, Non-domestic, Institutions classifications     |
|          - Keep existing records unclassified (NULL)                                             |
|          - Update customer search label to "Search Company"                                      |
|                                                                                                  |
| PHASE 5: Solar Plants / Systems Refactoring                                                      |
|          - Refactor UI presentation of Sites to "Solar Plants / Systems" (preserving DB keys)    |
|                                                                                                  |
| PHASE 6: Service & Maintenance Terminology Alignment                                             |
|          - Align service types: "Regular Service & Maintenance", "Complaints & Maintenance"      |
|                                                                                                  |
| PHASE 7: Installation & Commissioning Printable Blade Template Updates                           |
|          - Refactor Installation & Commissioning printable Blade template to match client matrix |
|          - Update "Verified By", "Model & Type", "Installation Date", and remove Engineer ID     |
|                                                                                                  |
| PHASE 8: Private Media Storage & Service Report Enhancements                                     |
|          - Declare PRIVATE_MEDIA_STATE = "PRIVATE_MEDIA_DEPLOYED" in deployment record           |
|          - Relocate report photos and technical documents to private disk outside public_html    |
|          - Implement authorized streaming controller with relationship-scoped IDOR defense       |
|          - Implement separator-aware canonical path containment on private media                 |
|          - Block direct static URL access to /storage/reports/*                                  |
|          - Suppress internal report number in customer-facing printable Blade view               |
|          - Guarantee customer phone number autofill from customer relationship                   |
|                                                                                                  |
| PHASE 9: Reports & Review Terminology Alignment                                                  |
|          - "Maintenance & Service Reports", "Submitted Reports", "Site Inspection Report"        |
|                                                                                                  |
| PHASE 10: Site Inspection Contact Directory Refactoring                                          |
|          - Structure Contact Section 1 (Authorised / In-charge) & Section 2 (Additional Contact) |
|          - Implement two-tier parser preserving historical report readability                    |
|                                                                                                  |
| PHASE 11: Daily Work Report & Customer Feedback Terminology                                      |
|          - Update titles and dynamic company headers in Daily Work & Feedback forms              |
|                                                                                                  |
| PHASE 12: Super Admin & Admin Operations Role Management                                         |
|          - Explicitly designate User ID 1 (admin@solar.local) as super_admin                     |
|          - Enforce company-scoped permissions for Admin Operations via canAdministerCompany()    |
|          - Bar mixed-role administrative actions in unauthorized company contexts (403)          |
|                                                                                                  |
| PHASE 13: Company-Specific Printable Blade Template Verification                                |
|          - Verify all 8 discovered printable Blade views dynamically pull from associated company|
|                                                                                                  |
| PHASE 14-16: Comprehensive Testing, Regression & Quality Audit                                   |
|          - Create dedicated automated feature tests and run full test suite                      |
|                                                                                                  |
| PHASE 17: Implementation, Test & Deployment Documentation                                        |
|          - Generate CLIENT_CHANGE_IMPLEMENTATION_REPORT.md and DEPLOYMENT_REPORT.md              |
+--------------------------------------------------------------------------------------------------+
```

---

## 4. Verification & Testing Strategy

### Automated PHPUnit Test Specifications (`tests/Feature/ClientChangeRequestTest.php` and `tests/Feature/ClientChangeSecurityTest.php`):
1. **Revoked Company Membership Barrier**:
   - Detach employee from `company_user`.
   - Assert access to that company's jobs and reports is **immediately denied (`403 Forbidden`)**, even if `users.company_id` still has that company ID.
2. **Stale Legacy `users.company_id` Protection**:
   - Set `users.company_id = 2` without corresponding row in `company_user`.
   - Assert user receives `403 Forbidden` on Company 2 resources.
3. **Mixed Company-Specific Roles Test (Complete 6-Action Administrative Boundary)**:
   - Create user with global `admin_operations`, assigned to Company A with `role_in_company = 'admin_operations'` and Company B with `role_in_company = 'engineer'`.
   - Assert user can approve reports and dispatch jobs in Company A.
   - Assert user receives **`403 Forbidden`** when attempting all 6 administrative actions in Company B:
     1. `POST /admin/reports/{companyB_report}/approve`
     2. `POST /admin/reports/{companyB_report}/reject`
     3. `POST /admin/reports/{companyB_report}/request-correction`
     4. `POST /admin/services` (dispatching/assigning services for Company B)
     5. `PUT /admin/services/{companyB_service}` (updating/reassigning Company B service)
     6. `GET /admin/companies/{companyB}/edit` (modifying Company B profile)
4. **Company Membership Alone Does Not Grant Admin Rights**:
   - Create user with `role_in_company = 'engineer'` in Company A.
   - Assert user cannot access `/admin/*` operations for Company A.
5. **Super Admin Role Semantics (Correction 7)**:
   - Assert `hasCompanyRole($companyId, 'engineer')` and `hasCompanyRole($companyId, 'admin_operations')` return `false` for Super Admin with no row in `company_user`.
   - Assert capability helpers `canAccessCompany`, `canAdministerCompany`, and `canPerformEngineerActions` return `true` for Super Admin.
   - Assert `getPrimaryCompanyAttribute()` returns `null` for Super Admin with zero memberships without error.
6. **Primary Company Invariant & Zero Fallback**:
   - Assert `getPrimaryCompanyAttribute()` returns `null` when no primary company is set (implicit first-company fallback permanently removed).
   - Assert that attempting to set a primary company not in `company_user` throws an exception.
   - Assert assigning multiple companies maintains **exactly one** `is_primary = true`.
   - Assert `validatePrimaryCompanyInvariant()` returns true for valid single-primary states and false if multiple or zero are flagged.
7. **Explicit Role Selection Enforcement (No Default)**:
   - Assert assigning an employee to a company requires explicitly choosing `admin_operations` or `engineer`.
   - Assert omitting `role_in_company` fails database insertion and validation.
8. **Private Media Storage & Relationship-Scoped IDOR Security Tests (Corrections 5, 6)**:
   - Direct HTTP request to static `/storage/reports/photos/sample.jpg` returns `404 Not Found` or `403 Forbidden`.
   - Authorized streaming request (`GET /reports/{report}/photos/{photo}`) returns `200 OK` for authorized company members.
   - Cross-Report IDOR check: User authorized for Report A requests Photo B (associated with Report B) via `/reports/{reportA}/photos/{photoB}` &rarr; relationship scoping (`$report->photos()->whereKey($photoId)->firstOrFail()`) returns **`404 Not Found`**.
   - Path traversal attempt (e.g. `../../etc/passwd`, null bytes, or sibling prefix `photos/1_fake`) is rejected (**`404 Not Found`**).
   - Streaming request from a user not belonging to `report->company_id` returns `403 Forbidden`.
   - Unauthenticated streaming request redirects to `/login` (`302`).
9. **Migration Abort on Unexpected Accounts**:
   - Test that introducing an unmapped user, an engineer with a null `company_id`, or missing company records throws a `RuntimeException` and aborts migration within transaction.
10. **Post-Recovery Integrity & Canonical 16-Step Verification (Corrections 1, 6)**:
    - Assert that after backup restoration via the canonical 16-step sequence, field-by-field verification against the independent trusted baseline manifest confirms all memberships, roles, and primary flags match the baseline without discrepancy.
    - Assert that any discrepancy between restored records and the manifest immediately halts recovery and keeps the application in maintenance mode.
11. **Customer Classification & Zero-Inference**:
    - Verify existing customers have `classification === null`.
    - Create `Domestic`, `Non-domestic`, and `Institutions` customers.
    - Verify filtering and searching by company and classification.
12. **Customer-Facing Printable Service Report Blade View**:
    - Render service report printable view.
    - Assert customer phone number automatically populates from `customer->phone`.
    - Assert internal report number (`SOE-REP-XXXX`) is hidden from customer printable view.
    - Assert internal report number remains visible in admin review view.
    - Assert company branding reflects the associated company.
13. **Installation & Commissioning Printable Blade View**:
    - Assert title renders as `INSTALLATION & COMMISSIONING`.
    - Assert `Installation Date`, `Plant / System Capacity`, `Model & Type`, `Verified By`, and `Admin Operations` are present.
    - Assert `Engineer ID` is suppressed from customer printable sheet.
    - Assert `Commissioned By` is retained.
14. **Site Inspection Contact Directory & Historical Compatibility**:
    - Assert Contact Section 1 (`Authorised / In-charge`) holds Name, Designation, Phone.
    - Assert Contact Section 2 (`Additional Customer Contact`) holds Name, Designation, Phone.
    - Create mock historical report with old unstructured fields (`main_incharge`, `client_contact`) and assert print view renders without errors or data loss.
15. **Production Rollback Protection Across All 3 Migrations**:
    - Assert executing `down()` under `APP_ENV=production` on Migration 1, Migration 2, and Migration 3 throws a `RuntimeException`, prohibiting destructive rollback.
16. **Full Regression Test Suite**:
    - Execute all existing test suites (`AuthAndAccessTest`, `MultiTemplateFormsTest`, `SolarFieldOperationsWorkflowTest`, `DataMigrationAndOnboardingTest`).
    - Acceptance target: **100% passing tests** (30+ tests, 300+ assertions).

---

## 5. Proposed Deployment & Operational Procedures (Unexecuted)

> [!IMPORTANT]
> The scripts and operational specifications below are proposed safety procedures for execution during Phase 1 deployment upon receiving explicit client authorization. They are currently unexecuted.

### 5.1 Proposed Production Deployment & Migration Script (with Integrated Pre-Flight Backup — Revision 4.6):
The deployment script integrates pre-flight backup, adheres strictly to the unified SQLite `delete` policy, generates schema-aware membership and system-state manifests with fail-closed queries (safely supporting both pre-migration legacy databases and post-migration environments), enforces mandatory, cryptographically signed `.deployment_state.json`, performs true content-level media verification (byte sizes and SHA-256 hashes against an unpacked temporary staging copy using null-delimited file discovery), executes additive migrations, and clears caches while managing maintenance state via an `EXIT` trap with ephemeral secret protection:

```bash
#!/usr/bin/env bash
set -euo pipefail

PHP_BIN="/opt/alt/php84/usr/bin/php"
APP_DIR="/home/u996219523/domains/myworks.sbs/public_html/solar"
BACKUP_DIR="/home/u996219523/backups"
STAGING_DIR="/home/u996219523/staging"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
DB_PATH="$APP_DIR/database/database.sqlite"
DB_BACKUP="$BACKUP_DIR/solar_db_backup_${TIMESTAMP}.sqlite"
MEDIA_BACKUP="$BACKUP_DIR/solar_media_backup_${TIMESTAMP}.tar.gz"
MEMBERSHIP_MANIFEST="$BACKUP_DIR/membership_manifest_${TIMESTAMP}.tsv"
SYSTEM_STATE_MANIFEST="$BACKUP_DIR/system_state_${TIMESTAMP}.json"
TRUSTED_STORE_DIR="/home/u996219523/governance_vault"
RELEASE_PUBKEY="$TRUSTED_STORE_DIR/release_verification_pubkey.pem"

mkdir -p "$BACKUP_DIR" "$STAGING_DIR"
chmod 700 "$BACKUP_DIR" "$STAGING_DIR"

# Explicit maintenance state modeling
MAINT_STATE="PRE_MAINTENANCE"
MAINT_SECRET_FILE=""

cleanup_on_exit() {
    local exit_code=$?
    if [ $exit_code -ne 0 ]; then
        echo "======================================================================"
        echo "FATAL: Deployment aborted with exit code $exit_code!"
        echo "Maintenance state at abort: $MAINT_STATE"
        if [ "$MAINT_STATE" = "MAINTENANCE_CONFIRMED" ] || [ "$MAINT_STATE" = "TRANSITION_IN_PROGRESS" ]; then
            echo "SAFETY INVARIANT: System remains locked in MAINTENANCE MODE."
            echo "Automated release is STRICTLY PROHIBITED on abort."
            if [ -n "$MAINT_SECRET_FILE" ] && [ -f "$MAINT_SECRET_FILE" ]; then
                echo "Maintenance secret file: $MAINT_SECRET_FILE (chmod 600)"
            fi
            echo "Operator manual recovery or rollback required before resuming traffic."
            echo "To bring online manually after remediation: $PHP_BIN $APP_DIR/artisan up"
        else
            echo "Failure occurred prior to entering maintenance mode. System remains online."
        fi
        echo "======================================================================"
    fi
}
trap cleanup_on_exit EXIT

# 1. Quiesce Application with Ephemeral Secret File & State Verification
echo "STEP 1: Quiescing application into maintenance mode..."
MAINT_STATE="TRANSITION_IN_PROGRESS"
MAINT_SECRET=$(openssl rand -hex 16 2>/dev/null || $PHP_BIN -r 'echo bin2hex(random_bytes(16));')
MAINT_SECRET_FILE="$STAGING_DIR/.maint_secret_${TIMESTAMP}"
echo "$MAINT_SECRET" > "$MAINT_SECRET_FILE"
chmod 600 "$MAINT_SECRET_FILE"

$PHP_BIN "$APP_DIR/artisan" down --secret="$MAINT_SECRET"

if [ ! -f "$APP_DIR/storage/framework/down" ]; then
    MAINT_STATE="TRANSITION_UNCERTAIN"
    echo "FATAL: Maintenance down-file was not created! State uncertain. Aborting."
    exit 1
fi
MAINT_STATE="MAINTENANCE_CONFIRMED"
echo "PASS: Maintenance mode active (Secret in: $MAINT_SECRET_FILE, down file verified)."

# 2. Source Private Media Deployment State from Authenticated .deployment_state.json
STATE_FILE="$APP_DIR/.deployment_state.json"
STATE_SIG="$APP_DIR/.deployment_state.sig"

if [ ! -f "$STATE_FILE" ]; then
    echo "FATAL: Mandatory deployment state file $STATE_FILE does not exist!"
    echo "Operational safety prohibits defaulting PRIVATE_MEDIA_STATE. Aborting backup."
    exit 1
fi

if [ ! -f "$RELEASE_PUBKEY" ]; then
    echo "FATAL: Release verification public key missing: $RELEASE_PUBKEY"
    echo "TRUST CHAIN UNRESOLVED: Offline cryptographic authority is an UNRESOLVED OPERATIONAL DEPENDENCY."
    exit 1
fi

if [ ! -f "$STATE_SIG" ]; then
    echo "FATAL: Detached signature $STATE_SIG missing for $STATE_FILE!"
    echo "Unsigned deployment states are strictly prohibited. Aborting backup."
    exit 1
fi

# Cryptographic verification of deployment state record
if ! openssl dgst -sha256 -verify "$RELEASE_PUBKEY" -signature "$STATE_SIG" "$STATE_FILE" >/dev/null 2>&1; then
    echo "FATAL: Cryptographic signature verification failed for $STATE_FILE!"
    exit 1
fi
echo "PASS: Deployment state signature verified."

# Parse authenticated deployment state
DEPLOY_VERSION=$(grep -o '"version": "[^"]*"' "$STATE_FILE" | cut -d'"' -f4)
DEPLOY_SYSTEM=$(grep -o '"system": "[^"]*"' "$STATE_FILE" | cut -d'"' -f4)
DEPLOY_ENV=$(grep -o '"environment": "[^"]*"' "$STATE_FILE" | cut -d'"' -f4)
PRIVATE_MEDIA_STATE=$(grep -o '"private_media_state": "[^"]*"' "$STATE_FILE" | cut -d'"' -f4)

if [ "$DEPLOY_VERSION" != "1.0" ] || [ "$DEPLOY_SYSTEM" != "solarops" ] || [ "$DEPLOY_ENV" != "production" ]; then
    echo "FATAL: Deployment state envelope mismatch (Version: $DEPLOY_VERSION, System: $DEPLOY_SYSTEM, Env: $DEPLOY_ENV)."
    exit 1
fi

case "$PRIVATE_MEDIA_STATE" in
    "PRIVATE_MEDIA_DEPLOYED"|"PRIVATE_MEDIA_NOT_DEPLOYED")
        echo "Authoritative private media deployment state verified: $PRIVATE_MEDIA_STATE"
        ;;
    *)
        echo "FATAL: Unrecognized private_media_state token '$PRIVATE_MEDIA_STATE' in $STATE_FILE."
        exit 1
        ;;
esac

# Cross-check declared state against on-disk filesystem to detect contradictions
PRIVATE_REPORTS_DIR="$APP_DIR/storage/app/private/reports"
if [ "$PRIVATE_MEDIA_STATE" = "PRIVATE_MEDIA_NOT_DEPLOYED" ]; then
    if [ -d "$PRIVATE_REPORTS_DIR" ] && [ "$(find "$PRIVATE_REPORTS_DIR" -type f 2>/dev/null | head -n 1)" ]; then
        echo "FATAL: Contradiction! State is PRIVATE_MEDIA_NOT_DEPLOYED but non-empty $PRIVATE_REPORTS_DIR exists."
        echo "Aborting backup to prevent unarchived private report files."
        exit 1
    fi
elif [ "$PRIVATE_MEDIA_STATE" = "PRIVATE_MEDIA_DEPLOYED" ]; then
    if [ ! -d "$PRIVATE_REPORTS_DIR" ]; then
        echo "FATAL: Contradiction! State is PRIVATE_MEDIA_DEPLOYED but $PRIVATE_REPORTS_DIR is missing."
        echo "Aborting backup to prevent incomplete backup."
        exit 1
    fi
fi

# 3. Runtime Verification of SQLite Journal Mode & Sidecars
echo "Verifying SQLite journal mode and WAL state..."
RAW_JOURNAL_MODE=$(sqlite3 "$DB_PATH" "PRAGMA journal_mode;")
JOURNAL_MODE=$(echo "$RAW_JOURNAL_MODE" | tr '[:upper:]' '[:lower:]' | tr -d '[:space:]')
echo "Current Journal Mode: $JOURNAL_MODE"

if [ "$JOURNAL_MODE" != "delete" ]; then
    echo "FATAL: Unexpected SQLite journal mode '$JOURNAL_MODE'. Expected 'delete'."
    echo "Aborting deployment to avoid inconsistent backup."
    exit 1
fi

if [ -f "${DB_PATH}-wal" ] || [ -f "${DB_PATH}-shm" ]; then
    echo "FATAL: Unexpected WAL/SHM sidecars detected while database is in DELETE mode."
    echo "This is an unresolved operational condition. Blind checkpoints in DELETE mode are prohibited."
    echo "Aborting backup immediately for operator investigation. Live files preserved."
    exit 1
fi

# 4. Execute Consistent Online SQLite Backup via Native Backup API
echo "Creating online SQLite backup..."
sqlite3 "$DB_PATH" ".backup '$DB_BACKUP'"
chmod 600 "$DB_BACKUP"

# 5. Verify SQLite Backup Integrity
INTEGRITY=$(sqlite3 "$DB_BACKUP" "PRAGMA integrity_check;")
if [ "$INTEGRITY" != "ok" ]; then
    echo "FATAL: Database backup integrity check failed: $INTEGRITY"
    rm -f "$DB_BACKUP"
    exit 1
fi

FK_CHECK=$(sqlite3 "$DB_BACKUP" "PRAGMA foreign_key_check;")
if [ -n "$FK_CHECK" ]; then
    echo "FATAL: Database backup foreign key violations detected: $FK_CHECK"
    rm -f "$DB_BACKUP"
    exit 1
fi
echo "Verified Database Backup: $DB_BACKUP (Integrity: OK, Foreign Keys: OK)"

# 6. Schema-Aware Manifest Generation with Fail-Closed Queries
echo "Generating membership manifest and system-state manifest from actual database state..."
HAS_COMPANY_USER=$(sqlite3 "$DB_BACKUP" "SELECT count(*) FROM sqlite_master WHERE type='table' AND name='company_user';")

# Fail-closed queries: zero fallbacks to default values
ACTUAL_USER1_ROLE=$(sqlite3 "$DB_BACKUP" "SELECT role FROM users WHERE id = 1;")
if [ -z "$ACTUAL_USER1_ROLE" ]; then
    echo "FATAL: Failed to query User 1 role from database backup. Aborting."
    exit 1
fi

MIGRATIONS_JSON=$(sqlite3 "$DB_BACKUP" "SELECT json_group_array(migration) FROM (SELECT migration FROM migrations ORDER BY id ASC);")
if [ -z "$MIGRATIONS_JSON" ]; then
    echo "FATAL: Failed to query migrations array from database backup. Aborting."
    exit 1
fi

APPLIED_MIGRATIONS=$(sqlite3 "$DB_BACKUP" "SELECT count(*) FROM migrations;")
if [ -z "$APPLIED_MIGRATIONS" ]; then
    echo "FATAL: Failed to query migrations count from database backup. Aborting."
    exit 1
fi

if [ "$HAS_COMPANY_USER" -eq 1 ]; then
    echo "Modern schema detected: exporting company_user memberships..."
    sqlite3 "$DB_BACKUP" <<'EOF' > "$MEMBERSHIP_MANIFEST"
.separator "\t"
SELECT user_id, company_id, role_in_company, is_primary FROM company_user ORDER BY user_id ASC, company_id ASC;
EOF
    MEM_COUNT=$(wc -l < "$MEMBERSHIP_MANIFEST" | tr -d '[:space:]')
    SCHEMA_STATE="modern_post_migration"
    COMPANY_USER_PRESENT=true
else
    echo "Legacy pre-migration schema detected: company_user does not exist yet."
    echo "Recording legacy baseline mappings from users table..."
    echo "# LEGACY_PRE_MIGRATION_BASELINE: company_user table not yet created" > "$MEMBERSHIP_MANIFEST"
    sqlite3 "$DB_BACKUP" <<'EOF' >> "$MEMBERSHIP_MANIFEST"
.separator "\t"
SELECT id, company_id, role, 1 FROM users WHERE company_id IS NOT NULL ORDER BY id ASC;
EOF
    MEM_COUNT=$(sqlite3 "$DB_BACKUP" "SELECT count(*) FROM users WHERE company_id IS NOT NULL;")
    SCHEMA_STATE="legacy_pre_migration"
    COMPANY_USER_PRESENT=false
fi

DB_SHA=$(sha256sum "$DB_BACKUP" | awk '{print $1}')
DB_BYTES=$(stat -c%s "$DB_BACKUP" 2>/dev/null || stat -f%z "$DB_BACKUP")
MEMBERSHIP_SHA=$(sha256sum "$MEMBERSHIP_MANIFEST" | awk '{print $1}')
MANIFEST_SEQ_ID=$(date +%s)

cat <<EOF > "$SYSTEM_STATE_MANIFEST"
{
  "manifest_schema_version": "4.6",
  "manifest_sequence_id": $MANIFEST_SEQ_ID,
  "generated_at": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
  "schema_state": "$SCHEMA_STATE",
  "company_user_table_present": $COMPANY_USER_PRESENT,
  "user_1_role": "$ACTUAL_USER1_ROLE",
  "applied_migration_count": $APPLIED_MIGRATIONS,
  "applied_migration_names": $MIGRATIONS_JSON,
  "core_tables_inventory": ["companies", "users", "customers", "sites", "services", "reports", "report_data", "migrations"],
  "database_backup_file": "$(basename "$DB_BACKUP")",
  "database_backup_sha256": "$DB_SHA",
  "database_backup_bytes": $DB_BYTES,
  "membership_manifest_sha256": "$MEMBERSHIP_SHA",
  "membership_records_count": $MEM_COUNT
}
EOF

sha256sum "$MEMBERSHIP_MANIFEST" | awk '{print $1}' > "${MEMBERSHIP_MANIFEST}.sha256"
sha256sum "$SYSTEM_STATE_MANIFEST" | awk '{print $1}' > "${SYSTEM_STATE_MANIFEST}.sha256"
chmod 600 "$MEMBERSHIP_MANIFEST" "${MEMBERSHIP_MANIFEST}.sha256" "$SYSTEM_STATE_MANIFEST" "${SYSTEM_STATE_MANIFEST}.sha256"
echo "Generated Manifests in $BACKUP_DIR (Schema State: $SCHEMA_STATE, Sequence ID: $MANIFEST_SEQ_ID)"
echo "NOTE: Off-server cryptographic signing of $SYSTEM_STATE_MANIFEST is required for independent trust."

# 7. Complete Media Scope Inventory & Archive Verification (Null-Delimited Processing)
echo "Archiving media files under deployment state: $PRIVATE_MEDIA_STATE..."
MEDIA_DIRS=()

if [ ! -d "$APP_DIR/storage/app/public" ]; then
    echo "FATAL: Required media directory storage/app/public is missing."
    exit 1
fi
MEDIA_DIRS+=("public")

if [ "$PRIVATE_MEDIA_STATE" = "PRIVATE_MEDIA_DEPLOYED" ]; then
    MEDIA_DIRS+=("private")
fi

ON_DISK_INVENTORY="$BACKUP_DIR/media_disk_inventory_${TIMESTAMP}.tsv"

# Generate complete on-disk inventory with relative path, byte size, and SHA-256 using null delimiters
> "$ON_DISK_INVENTORY"
for mdir in "${MEDIA_DIRS[@]}"; do
    (
        cd "$APP_DIR/storage/app"
        if [ -d "$mdir" ]; then
            find "$mdir" -type f -print0 | while IFS= read -r -d '' filepath; do
                # Security checks: reject symlinks and path traversals
                if [ -L "$filepath" ]; then
                    echo "FATAL: Symlink detected in media directory: $filepath. Aborting." >&2
                    exit 1
                fi
                if [[ "$filepath" == *".."* ]]; then
                    echo "FATAL: Path traversal sequence detected: $filepath. Aborting." >&2
                    exit 1
                fi
                fsize=$(stat -c%s "$filepath" 2>/dev/null || stat -f%z "$filepath")
                fhash=$(sha256sum "$filepath" | awk '{print $1}')
                printf "%s\t%s\t%s\n" "$filepath" "$fsize" "$fhash"
            done
        fi
    ) >> "$ON_DISK_INVENTORY"
done

sort -k1,1 "$ON_DISK_INVENTORY" -o "$ON_DISK_INVENTORY"
EXPECTED_FILE_COUNT=$(wc -l < "$ON_DISK_INVENTORY" | tr -d '[:space:]')

# Create tar archive
tar -czf "$MEDIA_BACKUP" -C "$APP_DIR/storage/app" "${MEDIA_DIRS[@]}"
chmod 600 "$MEDIA_BACKUP"

# Content-level verification: extract to staging outside public_html and diff inventories
VERIFY_EXTRACT_DIR="$STAGING_DIR/media_verify_${TIMESTAMP}"
mkdir -p "$VERIFY_EXTRACT_DIR"
chmod 700 "$VERIFY_EXTRACT_DIR"
tar -xzf "$MEDIA_BACKUP" -C "$VERIFY_EXTRACT_DIR"

ARCHIVE_INVENTORY="$BACKUP_DIR/media_archive_inventory_${TIMESTAMP}.tsv"
> "$ARCHIVE_INVENTORY"
for mdir in "${MEDIA_DIRS[@]}"; do
    (
        cd "$VERIFY_EXTRACT_DIR"
        if [ -d "$mdir" ]; then
            find "$mdir" -type f -print0 | while IFS= read -r -d '' filepath; do
                if [ -L "$filepath" ]; then
                    echo "FATAL: Symlink in extracted archive: $filepath" >&2
                    exit 1
                fi
                fsize=$(stat -c%s "$filepath" 2>/dev/null || stat -f%z "$filepath")
                fhash=$(sha256sum "$filepath" | awk '{print $1}')
                printf "%s\t%s\t%s\n" "$filepath" "$fsize" "$fhash"
            done
        fi
    ) >> "$ARCHIVE_INVENTORY"
done

sort -k1,1 "$ARCHIVE_INVENTORY" -o "$ARCHIVE_INVENTORY"

if ! diff -u "$ON_DISK_INVENTORY" "$ARCHIVE_INVENTORY"; then
    echo "FATAL: Media archive verification failed! Inventory diff mismatch detected!"
    rm -rf "$VERIFY_EXTRACT_DIR" "$ON_DISK_INVENTORY" "$ARCHIVE_INVENTORY"
    exit 1
fi

rm -rf "$VERIFY_EXTRACT_DIR" "$ON_DISK_INVENTORY" "$ARCHIVE_INVENTORY"
MEDIA_CHECKSUM=$(sha256sum "$MEDIA_BACKUP" | awk '{print $1}')
echo "Media Archive Verified: $MEDIA_BACKUP (Files: $EXPECTED_FILE_COUNT, SHA256: $MEDIA_CHECKSUM, Permissions: 600)"

# 8. Execute Non-Destructive Additive Migrations
echo "Executing migrations..."
$PHP_BIN "$APP_DIR/artisan" migrate --force

# 9. Clear Caches & Bring Online
echo "Clearing caches and restoring application online..."
$PHP_BIN "$APP_DIR/artisan" view:clear
$PHP_BIN "$APP_DIR/artisan" route:clear
$PHP_BIN "$APP_DIR/artisan" config:clear
$PHP_BIN "$APP_DIR/artisan" up
MAINT_STATE="PRE_MAINTENANCE"
rm -f "$MAINT_SECRET_FILE"

echo "Deployment completed successfully. System is live."
```

### 5.2 Proposed Exceptional Controlled WAL Recovery Procedure (Correction 2, 4, 8):
The exceptional WAL recovery procedure is strictly separated from normal operations and is defined in [`docs/CLIENT_CHANGE_DATA_MIGRATION_SAFETY.md`](file:///e:/Projects/Solar/docs/CLIENT_CHANGE_DATA_MIGRATION_SAFETY.md), Section 2.6. It enforces the shared hosting writer quiescence hard gate requiring an external hosting worker-stop attestation artifact, validates `wal_checkpoint(TRUNCATE)` output with strict regex formatting (`busy == 0`, `checkpointed == log`), checks both `${DB_PATH}-wal` and `${DB_PATH}-shm` sidecars, runs database integrity and foreign key checks, cleanly reverts the journal mode to `delete` (verifying exact return string and sidecar removal), and maintains the application in maintenance mode until explicit Stage B release authorization.

### 5.3 Authoritative Canonical Two-Stage Restoration Procedure Reference (Correction 6, 7):
To eliminate script duplication and prevent conflicting operational instructions, **the platform designates [`docs/CLIENT_CHANGE_DATA_MIGRATION_SAFETY.md`](file:///e:/Projects/Solar/docs/CLIENT_CHANGE_DATA_MIGRATION_SAFETY.md), Section 2.5 and Section 2.5.1 ('The Canonical 16-Step Fail-Closed Two-Stage Restoration Procedure') as the sole authoritative restoration specification**.

All operational rehearsals, disaster recovery drills, and emergency restorations must strictly execute this two-stage lifecycle:
- **Stage A: Restoration & In-Place Verification (Steps 1–15)**:
  1. Step 1: Verify Operator Authorization & Recovery Scope
  2. Step 2: Verify Independently Protected Restoration Baseline (Detached OpenSSL Signature & Pinned Public Key)
  3. Step 3: Validate Backup Identity, Canonical Path Containment & Symlink Rejection
  4. Step 4: Enter Application Maintenance Mode (Unique Dynamic Secret & Ephemeral File)
  5. Step 5: Enforce Writer Quiescence via Hard Shared Hosting Gate (External Worker Stop Attestation Mandatory)
  6. Step 6: Verify Live SQLite Journal Mode (Strict Delete Mode; for WAL recovery, Section 2.6)
  7. Step 7: Inspect Live SQLite Sidecars & Enforce Fail-Closed Sidecar Policy (Zero Auto-Move)
  8. Step 8: Verify Mandatory Same-Filesystem Staging Requirements (Zero Warning Fallback)
  9. Step 9: Stage Database Backup to Temporary File Outside public_html (chmod 600)
  10. Step 10: Verify Staging Database Structural Integrity (PRAGMA integrity_check)
  11. Step 11: Verify Staging Database Foreign-Key Integrity (PRAGMA foreign_key_check)
  12. Step 12: Verify Core Application Tables & Schema Migration State Against Manifest
  13. Step 13: Compare Membership State & Validate Primary Invariant Against Independent Baseline
  14. Step 14: Execute Atomic Filesystem Replacement (mv -f; chmod 660)
  15. Step 15: Post-Replacement In-Place Verification & Application Connectivity (PDO Check & Durable Receipt Generation)
  16. Step 16: Flush Application Caches & Conclude Stage A (**Maintenance Lock Preserved**)
- **Stage B: Independent Release Authorization (Section 2.5.1)**:
  - Performed via dedicated release script (`./release_application.sh "$RECEIPT_FILE"`).
  - Validates restoration receipt, confirms database SHA-256 match, executes smoke test, and explicitly brings the application online (`artisan up`) with operator confirmation.

---

## 6. Stop Gate & Implementation Authorization

As required by governance:
- **All Phase 0 audit corrections and supplementary governance documents are finalized in Revision 4.6 (Operational Trust Enforcement & Final Governance Reconciliation).**
- **Phase 0 closure remains PENDING FORMAL OWNER REVIEW AND ACCEPTANCE.**
- **No application code, migrations, database records, or production files have been altered.**
- **Phase 1 implementation and production deployment remain STRICTLY UNAUTHORIZED until receiving explicit owner authorization.**
