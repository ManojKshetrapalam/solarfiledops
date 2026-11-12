# Client Change Request — Security Review & Multi-Company Authorization
**Document Reference**: `docs/CLIENT_CHANGE_SECURITY_REVIEW.md`  
**Date**: November 11, 2026 (Documentation Revision 4.6 — Operational Trust Enforcement & Final Governance Reconciliation)  
**Lead Architect & Auditor**: Lead Software Architect & Senior Full-Stack Engineer  
**Client Branding**: Sun on Earth Solar Group  
**Security Level**: Critical Architecture Policy  
**Document Status**: Proposed Security Specifications — Phase 0 Closure PENDING FORMAL OWNER REVIEW AND ACCEPTANCE; Implementation & Deployment STRICTLY UNAUTHORIZED  

---

## 1. Executive Summary & Security Objectives

This security review establishes the strict authorization boundaries, access control policies, and data isolation mechanisms required for multi-company operations in the **Sun on Earth Solar Group** platform.

### Core Security Invariants:
1. **Authoritative Membership Principle**: The `company_user` pivot table is the **sole authoritative source** for company authorization. A user's legacy `users.company_id` column remains solely for historical schema compatibility and default-company UI display. **`users.company_id` must NEVER provide authorization or access fallback.** A user detached from `company_user` who still has `users.company_id` set receives `403 Forbidden`.
2. **Global Roles vs Standardized Company-Specific Roles**: A user's global role (`users.role`) does not automatically grant administrative rights across assigned companies. Programmatic roles in `company_user.role_in_company` are standardized strictly to `admin_operations` and `engineer` (resolving any `engineer` / `technician` ambiguity). The schema explicitly defines **no default value** (`$table->string('role_in_company');`). Every company assignment requires an explicit role selection. Company membership alone never confers administrative privileges.
3. **Company-Contextual Operational Permissions (Correction 7)**: Company-specific operational permissions are determined solely by `company_user.role_in_company` for the target company. `hasCompanyRole($companyId, $role)` inspects actual recorded pivot rows only and never invents roles for Super Admin. A user's global role (`users.role`) **never silently overrides** a lower company-specific role. An administrator in Company A who is assigned as an engineer in Company B is strictly restricted to engineer capabilities in Company B. Middleware admission is not treated as final authorization; sensitive controller actions independently authorize the target company and operation.
4. **Multi-Company Data Isolation**: An employee associated with Company A and Company B can access authorized records from both companies in one session, while an employee associated only with Company A must **never** be permitted to view, query, modify, or download records from Company B.
5. **Primary Company Invariant & Zero-Fallback Getter**: Exactly one primary company (`is_primary = true`) is required for every user with one or more company memberships. A primary company must always be an active, verified membership. A Super Admin with zero company memberships may have zero primary companies. The `getPrimaryCompanyAttribute()` getter strictly returns `null` when no primary flag is set (implicit first-company fallback permanently removed). The invariant is independently validated.
6. **Private Media Storage & Relationship-Scoped IDOR Protection (Corrections 5, 6, Findings 2, 5)**: Report photos and technical inspection documents must reside on **private storage** outside `public_html` (`storage/app/private/reports/`). Direct static URL access is blocked. Streaming endpoints enforce **relationship-scoped resolution** (`$report->photos()->whereKey($photoId)->firstOrFail()`) and **separator-aware canonical path containment** (`rtrim($realBaseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR`), preventing Insecure Direct Object References and path traversal. Private media deployment state is sourced strictly from mandatory, cryptographically signed `.deployment_state.json` verified via `.deployment_state.sig` with OpenSSL against a pinned release public key (no unsafe defaults, no unsigned state files), with contradiction checks against on-disk files. Complete media scope verification computes relative path, byte size, and SHA-256 for all regular files across all declared directories using safe null-delimited processing, extracting archive to temporary staging outside `public_html` and diffing bit-for-bit.
7. **Hardened Production Rollback Guards**: Ordinary rollbacks in production are strictly prevented across all three migrations (`companies` branding, `company_user` membership, `customers` classification) to protect operational integrity and prevent data loss.
8. **Operational Security & Canonical Two-Stage Restoration Integrity (Revision 4.6 Standard)**: Deployment and restoration procedures enforce a strict `delete` journal mode baseline, fail-closed live sidecar policy (zero automatic moving or deletion of `-wal` / `-shm` sidecars), hard shared hosting writer-quiescence gate (UID `u996219523` cannot inspect root LiteSpeed workers; `CONFIRM_WRITERS_STOPPED=YES` alone rejected; external hosting worker-stop attestation artifact mandatory), canonical path containment with symlink rejection, staging outside `public_html` on a verified same filesystem (`stat -c %d` check with immediate abort on mismatch), independent cryptographic trust chain (off-server signing key, on-server public verification key `chmod 400`, detached OpenSSL signature, binding DB filename, byte size, and SHA-256, with unresolved operational dependency acknowledged until provisioned), fail-closed manifest queries (zero default fallbacks), and adherence to the **Authoritative Two-Stage Canonical Restoration Procedure** (defined in Section 2.5 and Section 2.5.1 of `docs/CLIENT_CHANGE_DATA_MIGRATION_SAFETY.md`, where Stage A concludes at Step 16 with the application locked in maintenance mode and a durable receipt, and Stage B handles explicit operator release authorization).

---

## 2. Multi-Company Authorization Architecture

```
+----------------------------------------------------------------------------------------------------+
|                                    AUTHENTICATED USER SESSION                                      |
+----------------------------------------------------------------------------------------------------+
                                                  |
                         +------------------------+------------------------+
                         |                                                 |
                         v                                                 v
             Role: Super Admin                                 Role: Admin Operations / Engineer
    (Global Cross-Company Access)                     (Authorized via `company_user` Pivot ONLY)
                         |                                                 |
                         |                                  +--------------+--------------+
                         |                                  |                             |
                         |                                  v                             v
                         |                         Company A (Allowed)           Company B (Allowed)
                         |                         Role: Admin Operations        Role: Engineer
                         |                         [Company C: BLOCKED]          [Company D: BLOCKED]
                         |                                  |                             |
                         +----------------------------------+-----------------------------+
                                                            |
                                                            v
                                            +-------------------------------+
                                            | ENFORCED AUTHORIZATION ENGINE |
                                            +-------------------------------+
```

### 2.1 Authoritative Membership & Model Helpers

In `app/Models/User.php`:
```php
/**
 * Return all company IDs the user is authoritatively authorized to access.
 *
 * CRITICAL SECURITY INVARIANT:
 * company_user is the SOLE authoritative source.
 * users.company_id is retained ONLY for legacy compatibility and default dropdown selection.
 * It NEVER provides authorization or access fallback.
 */
public function authorizedCompanyIds(): array
{
    if ($this->isSuperAdmin()) {
        return Company::pluck('id')->toArray();
    }

    return $this->companies()->pluck('companies.id')->toArray();
}

public function canAccessCompany(int $companyId): bool
{
    if ($this->isSuperAdmin()) {
        return true;
    }

    return in_array($companyId, $this->authorizedCompanyIds(), true);
}

/**
 * Check actual company-specific operational role (Correction 7).
 *
 * CRITICAL SEPARATION OF CONCERNS:
 * This method inspects actual company_user pivot table membership ONLY.
 * It returns true if and only if the user has a recorded pivot row in company_user
 * for the specified company with role_in_company matching $role.
 * Super Admin capability bypass is DELIBERATELY EXCLUDED here so this method
 * accurately reflects actual recorded membership and never invents roles.
 */
public function hasCompanyRole(int $companyId, string $role): bool
{
    $membership = $this->companies()->where('companies.id', $companyId)->first();
    return $membership?->pivot?->role_in_company === $role;
}

/**
 * Check company-specific administrative capability.
 * Super Admin bypass is explicit. Non-super-admins require role_in_company = 'admin_operations'.
 * Company membership alone never grants admin rights.
 */
public function canAdministerCompany(int $companyId): bool
{
    return $this->isSuperAdmin() || $this->hasCompanyRole($companyId, 'admin_operations');
}

/**
 * Check company-specific field engineering capability.
 * Super Admin bypass is explicit. Non-super-admins require role_in_company = 'engineer'.
 */
public function canPerformEngineerActions(int $companyId): bool
{
    return $this->isSuperAdmin() || $this->hasCompanyRole($companyId, 'engineer');
}

/**
 * Authoritative primary company accessor.
 * Returns null if no active primary company is flagged.
 * Implicit first-company fallback has been permanently removed.
 */
public function getPrimaryCompanyAttribute(): ?Company
{
    return $this->companies()->wherePivot('is_primary', true)->first();
}

/**
 * Independent primary company invariant validator.
 */
public function validatePrimaryCompanyInvariant(): bool
{
    $count = $this->companies()->count();
    $primaryCount = $this->companies()->wherePivot('is_primary', true)->count();

    if ($this->isSuperAdmin() && $count === 0) {
        return $primaryCount === 0;
    }

    if ($count > 0) {
        return $primaryCount === 1;
    }

    return $primaryCount === 0;
}
```

### 2.2 Scope Enforcement by Functional Area

| Functional Area | Target Threat | Authorization Guard & Query Scope | Failure Response |
|---|---|---|---|
| **Dashboard Aggregation** | Cross-company KPI leakage | Query scoped strictly by user's pivot company IDs: `whereIn('company_id', $user->authorizedCompanyIds())`. Super Admin bypasses scope. | Filtered dataset only |
| **Customer Searches** | Viewing unauthorized clients | `Customer::whereIn('company_id', $user->authorizedCompanyIds())->where(...)` | Zero search results for unauthorized companies |
| **Solar Plant Searches** | Discovering third-party sites | `Site::whereHas('customer', fn($q) => $q->whereIn('company_id', $user->authorizedCompanyIds()))` | Zero search results |
| **Service Assignments** | Dispatching non-member technicians | Technicians displayed in dropdown verified via `company_user`: `whereHas('companies', fn($q) => $q->where('companies.id', $selectedCompanyId))` | Validation error `422 Unprocessable Entity` |
| **Engineer Job Access** | Viewing unassigned or cross-company jobs | Engineer must be assigned (`assigned_user_id === $user->id`) AND have active membership in the job's company | `403 Forbidden` |
| **Report Listings** | Snooping unassigned/foreign reports | Scoped to assigned company IDs for Admin Operations; scoped to assigned engineer ID for field technicians | `403 Forbidden` / Filtered listing |
| **Browser Print Views** | Cross-company print sheet leakage via direct ID URL | Controller checks: `$user->canAccessCompany($report->company_id)`. Field engineers restricted to own reports. | `403 Forbidden` |
| **Excel & CSV Exports** | Bulk export data exfiltration | All queries in `DataManagementController` and `AnalyticsController` constrained by `authorizedCompanyIds()`. Mixed-role users restricted to authorized admin companies. | Export contains only authorized company records (`403` if unauthorized) |
| **Direct URLs & Route Binding** | Parameter tampering (`/admin/services/{id}`) | Explicit route model binding checks in controller methods (`authorize('view', $model)`) | `404 Not Found` or `403 Forbidden` |
| **JSON AJAX Endpoints** | Drafting or uploading to foreign reports | `saveDraft`, `uploadPhoto`, and `deletePhoto` verify `$report->engineer_id === auth()->id() && $user->canAccessCompany($report->company_id)` | `403 JSON Error` |

---

## 3. Media Storage Security & Relationship-Scoped Streaming Architecture

### 3.1 Vulnerability of Existing Public-Disk Storage
Currently, uploaded report photos (`reports/photos/`) and test documents (`reports/documents/`) are saved on the `public` disk (`storage/app/public`), symlinked directly to `public/storage`.
This creates a critical authorization bypass:
- Any unauthenticated third party or competing entity guessing or enumerating asset URLs (e.g. `https://myworks.sbs/solar/storage/reports/photos/...`) can download site photos, customer equipment tags, and commissioning certificates without authentication or tenant isolation.

### 3.2 Private Storage Architecture & Relationship-Scoped Streaming (Corrections 5, 6)
1. **Relocate Uploads to Private Disk**:
   - Configure a dedicated `private` storage disk in `config/filesystems.php`:
     ```php
     'private' => [
         'driver' => 'local',
         'root' => storage_path('app/private'),
         'visibility' => 'private',
     ],
     ```
   - Store report photos under `storage/app/private/reports/photos/{report_id}/` and documents under `storage/app/private/reports/documents/{report_id}/` (strictly outside `public_html`).
2. **Remove Static Public Route Access**:
   - Web server rewrite rules deny static file requests matching `^/storage/reports/.*$`.
   - Direct HTTP requests to `/storage/reports/*` return `404 Not Found` or `403 Forbidden`.
3. **Relationship-Scoped Streaming Controller Endpoints with Canonical Path Containment (Correction 6)**:
   - Endpoints:
     - `GET /reports/{report}/photos/{photo}` &rarr; `ReportAssetController@streamPhoto`
     - `GET /reports/{report}/documents/{document}` &rarr; `ReportAssetController@streamDocument`
   - **Mandatory Authorization & Canonical Path Containment Implementation**:
     ```php
     public function streamPhoto(Report $report, int $photoId)
     {
         $user = auth()->user();
         if (!$user) {
             abort(403, 'Unauthenticated access.');
         }

         // Check 1: Company tenant isolation
         if (!$user->canAccessCompany($report->company_id)) {
             abort(403, 'Unauthorized company access.');
         }

         // Check 2: Engineer job assignment scope
         if ($user->canPerformEngineerActions($report->company_id) && !$user->canAdministerCompany($report->company_id)) {
             if ((int)$report->assigned_user_id !== (int)$user->id) {
                 abort(403, 'Engineers can only stream media for assigned jobs.');
             }
         }

         // Check 3: Relationship-Scoped Resolution (Prevents Insecure Direct Object Reference)
         // Resolving strictly through the $report relationship ensures Photo A cannot be accessed via Report B URL!
         $photo = $report->photos()->whereKey($photoId)->first();
         if (!$photo) {
             abort(404, 'Media asset not found for this report.');
         }

         // Check 4: Separator-Aware Canonical Path Containment (Correction 6)
         $baseDir = realpath(storage_path('app/private/reports/photos/' . $report->id));
         if ($baseDir === false) {
             abort(500, 'Report media directory cannot be resolved.');
         }

         // Append trailing directory separator to prevent prefix collision with sibling directories (e.g. photos/1_fake)
         $baseWithSeparator = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

         // Derive file path strictly from trusted database record, never from direct request input
         $filePath = storage_path('app/private/' . ltrim($photo->file_path, '/\\'));
         $realFilePath = realpath($filePath);

         if ($realFilePath === false || !is_file($realFilePath) || !str_starts_with($realFilePath, $baseWithSeparator)) {
             abort(404, 'Invalid media asset path or path traversal detected.');
         }

         return response()->file($realFilePath, [
             'Content-Type'  => $photo->mime_type ?? 'image/jpeg',
             'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
             'Pragma'        => 'no-cache',
             'Expires'       => '0',
         ]);
     }
     ```
   - The same dual-check relationship-scoped resolution and separator-aware canonical path containment are applied to `streamDocument` using `$report->documents()->whereKey($documentId)->first()`.

---

## 4. Role Hierarchy & Privilege Separation

### 4.1 Global Account Roles vs Company-Specific Roles

Programmatic values for `company_user.role_in_company` are standardized strictly to two canonical tokens without default:

| Level | Role Identifier | Scope & Capabilities |
|---|---|---|
| **Global Account** | `super_admin` | Global platform administrator. Can create companies, configure global branding, manage all administrators, and bypass company isolation. |
| **Global Account** | `admin_operations` | Administrative account type. Operational permissions are strictly resolved per company via `company_user.role_in_company`. |
| **Global Account** | `engineer` | Field technical staff account type. Executes assigned jobs and field reporting. |
| **Company-Specific** | `company_user.role_in_company = 'admin_operations'` | Grants full operational management (service dispatch, report review/approval, plant management) **only within this specific company**. |
| **Company-Specific** | `company_user.role_in_company = 'engineer'` | Grants technician assignment and reporting capabilities **only within this specific company**. Cannot approve reports or dispatch services. |

*Schema Specification*:
```php
$table->string('role_in_company'); // STRICTLY NO DEFAULT. Must be explicitly supplied.
```

### 4.2 Explicit Role Selection Requirement
- When assigning an employee to a company, administrators **must explicitly select the company-specific role** (`admin_operations` or `engineer`).
- Generic backfilling or copying of `users.role` is permanently removed.
- Company membership alone **never** grants administrative privileges.

### 4.3 Support for Mixed Multi-Company Roles & Boundary Policy
- **Example**: User *Rajesh Kumar*
  - Global Role: `admin_operations`
  - Assigned to **Company A** with `role_in_company = 'admin_operations'` &rarr; Can dispatch jobs, review reports, and manage clients in Company A.
  - Assigned to **Company B** with `role_in_company = 'engineer'` &rarr; Operates purely as a field technician in Company B.
  - **Critical Test Boundary**:
    - Global account identity and company-specific operational permissions remain strictly separate.
    - Company-specific actions evaluate `company_user.role_in_company` for the target company.
    - The global role `users.role` **never silently overrides** a lower company-specific role.
    - **Middleware admission is NOT final authorization**: Passing `AdminMiddleware` into an `/admin` route allows basic authenticated routing, but every sensitive controller action independently evaluates `canAdministerCompany($targetCompanyId)`.
    - When *Rajesh* attempts any administrative action in Company B, the system **strictly returns `403 Forbidden`**:
      1. Report Approval: `POST /admin/reports/{companyB_report}/approve` &rarr; **`403 Forbidden`**
      2. Service Dispatch: `POST /admin/services` (for Company B) &rarr; **`403 Forbidden`**
      3. Customer Creation/Editing: `POST /admin/customers` (Company B) &rarr; **`403 Forbidden`**
      4. Solar Plant Creation/Editing: `POST /admin/sites` (Company B) &rarr; **`403 Forbidden`**
      5. Employee Assignment/Management: `POST /admin/employees` (Company B) &rarr; **`403 Forbidden`**
      6. Administrative Data Exports: `GET /admin/analytics/export?company_id={companyB}` &rarr; **`403 Forbidden`**
  - Not assigned to **Company C** &rarr; Zero access to Company C records (`403 Forbidden`).

---

## 5. Security & Isolation Automated Verification Test Cases

The test suite in `tests/Feature/ClientChangeSecurityTest.php` will enforce:
1. **Revoked Membership Barrier**: Detaching an employee from `company_user` immediately revokes access (`403 Forbidden` on services, reports, and search) even if `users.company_id` still contains the old company ID.
2. **Stale Legacy `users.company_id` Does Not Grant Access**: A user with `users.company_id = 2` but no row in `company_user` for Company 2 receives `403 Forbidden` on Company 2 resources.
3. **Mixed Company-Specific Roles Test Suite**:
   - A user with global `admin_operations` and company-specific `engineer` in Company B:
     - `POST /admin/reports/{companyB_report}/approve` &rarr; asserts status **`403`**.
     - `POST /admin/services` (for Company B) &rarr; asserts status **`403`**.
     - `POST /admin/customers` (Company B) &rarr; asserts status **`403`**.
     - `POST /admin/sites` (Company B) &rarr; asserts status **`403`**.
     - `POST /admin/employees` (Company B) &rarr; asserts status **`403`**.
     - `GET /admin/analytics/export?company_id={companyB}` &rarr; asserts status **`403`**.
4. **Company Membership Alone Does Not Grant Admin Rights**: A user assigned to Company A with `role_in_company = 'engineer'` cannot approve reports or access admin dispatch routes for Company A (`403 Forbidden`).
5. **Super Admin Role Representation (Correction 7)**:
   - `hasCompanyRole($companyId, 'engineer')` and `hasCompanyRole($companyId, 'admin_operations')` return `false` for a Super Admin who has no pivot row in `company_user`.
   - Capability helpers `canAccessCompany`, `canAdministerCompany`, and `canPerformEngineerActions` return `true` for Super Admin.
6. **Primary Company Invariant & Zero Fallback**:
   - Assert `getPrimaryCompanyAttribute()` returns `null` when no company is flagged as primary (zero fallback to `first()`).
   - Assert attempting to set a primary company not in `company_user` throws an exception.
   - Assert assigning multiple companies maintains **exactly one** `is_primary = true`.
   - Assert `validatePrimaryCompanyInvariant()` validates states correctly.
7. **Explicit Role Selection Enforcement**: Attempting to attach a company without an explicit valid `role_in_company` (`admin_operations` or `engineer`) triggers validation rejection.
8. **Private Media Storage & Relationship-Scoped IDOR Protection Tests**:
   - **Report A + Photo A**: Permitted (`200 OK`) when authorized.
   - **Report A + Photo B (Cross-Report IDOR attempt)**: Requesting `GET /reports/{reportA}/photos/{photoB}` (where `photoB` belongs to `reportB`) &rarr; strictly rejected with **`404 Not Found`** or **`403 Forbidden`**.
   - **Report A + Document B (Cross-Report IDOR attempt)**: Requesting `GET /reports/{reportA}/documents/{documentB}` &rarr; strictly rejected with **`404 Not Found`** or **`403 Forbidden`**.
   - **Unauthorized Company Media Access**: Requesting media for a report belonging to an unauthorized company &rarr; strictly rejected with **`403 Forbidden`**.
   - **Unauthorized Engineer Assignment Access**: Engineer requesting media for an unassigned report in their company &rarr; strictly rejected with **`403 Forbidden`**.
   - **Direct Static Public URL Denial**: Direct HTTP request to `/storage/reports/photos/sample.jpg` &rarr; strictly returns **`404 Not Found`** or **`403 Forbidden`**.
   - **Path Traversal Defense (Correction 6)**: Media requests with traversal tokens (`..`) or sibling directory prefix tampering &rarr; rejected (**`404 Not Found`**).
   - **Unauthenticated Media Request**: Redirected to `/login` (`302`).
9. **Direct URL Tampering & IDOR Defense**: Engineer A attempting to GET `/admin/services/{id}` or `/engineer/services/{id}` belonging to foreign Company B receives `403 Forbidden`.
10. **Post-Recovery Integrity & Independent Manifest Verification Test (Revision 4.6 Standard)**:
    - Assert that after database backup restoration following the canonical two-stage procedure (Section 2.5 and 2.5.1 of `docs/CLIENT_CHANGE_DATA_MIGRATION_SAFETY.md`), cryptographic verification of the detached OpenSSL signature (`system_state.json.sig`) confirms manifest authenticity against the release public key, and field-by-field verification (`diff -u`) against the independent trusted membership manifest (`membership_manifest.tsv`) and system-state manifest (`system_state.json`) confirms all memberships, roles, primary flags, actual User 1 role (`admin` on legacy baseline vs `super_admin` post-migration), exact migration names array, and core table structures match the authentic pre-incident baseline without discrepancy. Assert that Stage A leaves the site in maintenance mode (concluding at Step 16) and durable receipt is verified prior to Stage B release.
11. **Production Rollback Guard Verification**: Under `APP_ENV=production`, executing `down()` on all three migrations throws a `RuntimeException`, prohibiting accidental drop of tables or columns.
