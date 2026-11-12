# Client Change Request — Role Mapping & Privilege Architecture
**Document Reference**: `docs/CLIENT_CHANGE_ROLE_MAPPING.md`  
**Date**: November 11, 2026 (Documentation Revision 4.6 — Operational Trust Enforcement & Final Governance Reconciliation)  
**Lead Architect & Auditor**: Lead Software Architect & Senior Full-Stack Engineer  
**Client Branding**: Sun on Earth Solar Group  
**Scope**: Standardized Roles, Explicit Legacy Migration Mapping, and Scoped Access Control  
**Document Status**: Proposed Architectural Specifications — Phase 0 Closure PENDING FORMAL OWNER REVIEW AND ACCEPTANCE; Implementation & Deployment STRICTLY UNAUTHORIZED  

---

## 1. Existing Administrator & User Inventory

A read-only audit of both the local and production database environments established the complete inventory of user accounts:

### Verified Production User Accounts (`database.sqlite`):
| ID | Name | Username | Email | Current Role | Current Company ID | Status | Proposed Global Role | Authoritative Company Mapping (`company_user`) |
|:---:|---|---|---|:---:|:---:|:---:|:---:|---|
| **1** | Operations Admin | `admin` | `admin@solar.local` | `admin` | *Unassigned* | Active | **`super_admin`** (Explicit Designation) | Global administrator; zero company memberships |
| **2** | Raj Kumar | `raj` | `raj@solar.local` | `engineer` | 1 (SOE) | Active | **`engineer`** | Company 1 (`SOE`): `role_in_company = 'engineer'`, `is_primary = true` |
| **3** | Kiran Sharma | `kiran` | `kiran@solar.local` | `engineer` | 2 (SSE) | Active | **`engineer`** | Company 2 (`SSE`): `role_in_company = 'engineer'`, `is_primary = true` |
| **4** | Anand Verma | `anand` | `anand@solar.local` | `engineer` | 1 (SOE) | Active | **`engineer`** | Company 1 (`SOE`): `role_in_company = 'engineer'`, `is_primary = true` |
| **5** | Manoj Kumar | `manoj` | `manoj.kumar@solar.local` | `engineer` | 1 (SOE) | Active | **`engineer`** | Company 1 (`SOE`): `role_in_company = 'engineer'`, `is_primary = true` |
| **6** | Rakesh Raj | `rakesh` | `rakesh.raj@solar.local` | `engineer` | 1 (SOE) | Active | **`engineer`** | Company 1 (`SOE`): `role_in_company = 'engineer'`, `is_primary = true` |
| **7** | Anand Kumar | `anand.k` | `anandr@solar.local` | `engineer` | 1 (SOE) | Active | **`engineer`** | Company 1 (`SOE`): `role_in_company = 'engineer'`, `is_primary = true` |
| **8** | D.THEERTHA PRASADHA | `dtheerthaprasadha` | `gvcdtp@gmail.com` | `engineer` | 1 (SOE) | Active | **`engineer`** | Company 1 (`SOE`): `role_in_company = 'engineer'`, `is_primary = true` |

### Key Inventory Findings & Boundaries:
1. There is currently **only one administrative user** in production: User ID 1 (`Operations Admin`, `admin@solar.local`).
2. Exactly **seven field engineers** exist in production (User IDs 2 through 8).
3. No accounts are unassigned or orphaned among field staff.
4. Generic backfill operations that blindly copy `users.role` are **permanently removed**. Migration executes an explicit, transactional legacy mapping that validates all 12 pre-conditions across these 8 accounts individually.

---

## 2. Standardized Roles & Privilege Architecture

### 2.1 Global Account Roles (`users.role`)
The global account role defines the user's platform-level identity:
1. **`super_admin`**: Master system administrator. Has global cross-company read, write, review, and export authority. Can register companies, manage administrators, and configure global application branding.
2. **`admin_operations`**: Administrative user account type. Operational permissions are **strictly resolved against the specific company being accessed** via `company_user.role_in_company`.
3. **`engineer`**: Field technical staff account type. Executes assigned jobs and field reporting.

### 2.2 Standardized Company-Specific Roles (`company_user.role_in_company`)
To eliminate role ambiguity and resolve the `engineer` / `technician` inconsistency, the programmatic values for `company_user.role_in_company` are standardized strictly to two canonical tokens:
1. **`admin_operations`**: Company Operational Administrator. Holds service dispatch, technician assignment, customer/plant management, and report approval privileges **strictly within this specific company**.
2. **`engineer`**: Field Engineer / Technician. Holds job assignment receipt, mobile draft reporting, photo capture, and report submission privileges **strictly within this specific company**. Cannot approve reports or dispatch jobs in this company.

*Schema Invariant*: The `company_user.role_in_company` column is defined **without any default value** (`$table->string('role_in_company');`). Omitting `default('engineer')` forces every controller, form, and migration insert to explicitly select and supply `'admin_operations'` or `'engineer'`.

*Terminology Resolution*: The UI presents field staff as **"Engineers / Technicians"** in accordance with client specifications, but the underlying database token is strictly standardized as `'engineer'`.

### 2.3 Explicit Role Selection Requirement
- When assigning an employee to a company, the administrator **must explicitly select the role** (`admin_operations` or `engineer`) for that company assignment.
- The system will **never** copy the user's global role automatically into company assignments.
- Company membership alone **never** grants administrative privileges.

### 2.4 Support for Mixed Multi-Company Roles & Verification Test
The system explicitly supports asymmetrical operational responsibilities:
- **Example**: User *Rajesh Kumar*
  - Global Role: `admin_operations`
  - Assigned to **Company A** with `role_in_company = 'admin_operations'` &rarr; Can dispatch jobs, review reports, and manage clients in Company A.
  - Assigned to **Company B** with `role_in_company = 'engineer'` &rarr; Operates purely as a field technician in Company B.
  - **Mandatory Mixed-Role Authorization Policy**:
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

## 3. Primary Company Invariant & Independent Validation

### 3.1 Strict Invariant Rules
1. **Users with One or More Company Memberships**: Must have **exactly one** active membership in `company_user` flagged as `is_primary = true`.
2. **Super Admin Without Company Memberships**: May have **zero** primary companies (`is_primary` not applicable).
3. **Active Membership Constraint**: A primary company **must always be an active membership** in `company_user`. Setting a primary company not in `company_user` is prohibited and rejected.
4. **No Implicit Fallbacks in Getter**:
   In `app/Models/User.php`:
   ```php
   /**
    * Get the authoritative primary company.
    * Note: Strictly returns null if no primary company is flagged.
    * Implicit first-company fallback has been permanently removed.
    */
   public function getPrimaryCompanyAttribute(): ?Company
   {
       return $this->companies()->wherePivot('is_primary', true)->first();
   }
   ```
5. **Independent Invariant Validator**:
   ```php
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

---

## 4. Eloquent Model Helpers & Company-Contextual Authorization

In `app/Models/User.php`:

```php
public function isSuperAdmin(): bool
{
    return $this->role === 'super_admin';
}

public function isAdminOperations(): bool
{
    return $this->role === 'admin_operations';
}

public function isAdmin(): bool
{
    return $this->isSuperAdmin() || $this->isAdminOperations();
}

public function isEngineer(): bool
{
    return $this->role === 'engineer';
}

/**
 * Sole authoritative company access resolution.
 *
 * CRITICAL SECURITY BOUNDARY:
 * users.company_id is retained ONLY for legacy compatibility and default-company UI display.
 * It NEVER provides authorization or access fallback.
 * If a user has users.company_id = 2 but no row in company_user for Company 2,
 * access to Company 2 is STRICTLY DENIED (403 Forbidden).
 */
public function authorizedCompanyIds(): array
{
    if ($this->isSuperAdmin()) {
        return Company::pluck('id')->toArray();
    }

    return $this->companies()->pluck('companies.id')->toArray();
}

/**
 * Check whether user has access to a specific company.
 * Explicit Super Admin capability bypass is supported.
 */
public function canAccessCompany(int $companyId): bool
{
    if ($this->isSuperAdmin()) {
        return true;
    }

    return in_array($companyId, $this->authorizedCompanyIds(), true);
}

/**
 * Authoritative check for actual company membership role (Correction 7).
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
 * Authoritative capability check for company administration.
 * Super Admin bypass is explicit. Non-super-admins require role_in_company = 'admin_operations'.
 * Company membership alone never grants admin privileges.
 */
public function canAdministerCompany(int $companyId): bool
{
    return $this->isSuperAdmin() || $this->hasCompanyRole($companyId, 'admin_operations');
}

/**
 * Authoritative capability check for company field engineering.
 * Super Admin bypass is explicit. Non-super-admins require role_in_company = 'engineer'.
 */
public function canPerformEngineerActions(int $companyId): bool
{
    return $this->isSuperAdmin() || $this->hasCompanyRole($companyId, 'engineer');
}
```

---

## 5. Permission Mapping Matrix

| Capability / Resource | Super Admin | Admin Operations (in Company A) | Engineer (in Company A) | Non-Member (Company B) | Mixed User: Global Admin / Engineer in Company B |
|---|:---:|:---:|:---:|:---:|:---:|
| **Access `/admin/dashboard`** | Global KPIs | Scoped to Company A | Denied (Redirected) | Denied | Scoped to Company A (No Company B Admin KPIs) |
| **Manage Companies & Global Branding** | Full CRUD | Denied | Denied | Denied | Denied |
| **Manage Customers & Solar Plants** | All Companies | Company A Only | View Assigned Job Sites | Denied (`403`) | Company A Only (**Denied `403` for Company B**) |
| **Dispatch & Assign Service Jobs** | All Companies | Company A Only | Denied | Denied (`403`) | Company A Only (**Denied `403` for Company B**) |
| **Review & Approve Reports** | All Companies | Company A Only | Denied | Denied (`403`) | Company A Only (**Denied `403` for Company B**) |
| **Execute Mobile Field Reports** | View Only | View Only | Execute Assigned Jobs | Denied (`403`) | Execute Assigned Jobs in Company B |
| **Access System Audit Logs** | Global | Scoped to Company A | Own Actions Only | Denied (`403`) | Scoped to Company A Only |
| **Export Operational Data** | Global | Scoped to Company A | Denied | Denied (`403`) | Scoped to Company A Only (**Denied `403` for Company B**) |

---

## 6. Explicit, Validated Legacy Migration Mapping (Proposed Procedure — Correction 4)

Generic backfills that copy `users.role` are prohibited. The proposed migration executes an explicit, pre-validated mapping within an atomic database transaction enforcing all 12 pre-conditions:

### 6.1 Transactional Migration Pre-Validation Guard:
```php
DB::transaction(function () {
    // 1. Audit and validate all existing users before inserting any pivot records
    $users = DB::table('users')->get();
    $expectedUserIds = [1, 2, 3, 4, 5, 6, 7, 8];

    // Pre-condition 1: Verify the complete expected user ID set is exactly 1-8 (count is 8)
    if ($users->count() !== 8) {
        throw new \RuntimeException(
            "ABORT MIGRATION: Expected exactly 8 legacy user accounts, found {$users->count()}."
        );
    }

    // Pre-condition 2: Verify no expected user is missing
    $foundUserIds = $users->pluck('id')->sort()->values()->toArray();
    if ($foundUserIds !== $expectedUserIds) {
        throw new \RuntimeException(
            "ABORT MIGRATION: User ID mismatch. Missing or altered user IDs. Expected [1..8], found: " . json_encode($foundUserIds)
        );
    }

    // Pre-condition 3: Verify no unexpected user is included in the legacy migration set
    foreach ($foundUserIds as $id) {
        if (!in_array($id, $expectedUserIds, true)) {
            throw new \RuntimeException("ABORT MIGRATION: Unexpected user ID detected: {$id}.");
        }
    }

    // Pre-condition 4: Verify User 1 has exactly the expected legacy role 'admin'
    $userMap = $users->keyBy('id');
    if ($userMap[1]->role !== 'admin') {
        throw new \RuntimeException("ABORT MIGRATION: User ID 1 expected role 'admin', found '{$userMap[1]->role}'.");
    }

    // Pre-condition 5: Verify Users 2-8 have exactly the expected legacy role 'engineer'
    for ($i = 2; $i <= 8; $i++) {
        if ($userMap[$i]->role !== 'engineer') {
            throw new \RuntimeException("ABORT MIGRATION: User ID {$i} expected role 'engineer', found '{$userMap[$i]->role}'.");
        }
    }

    // Pre-condition 6: Verify each engineer's users.company_id matches the exact deterministic mapping
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
            throw new \RuntimeException(
                "ABORT MIGRATION: User ID {$userId} expected company_id {$expectedCompanyId}, found '{$userMap[$userId]->company_id}'."
            );
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
        if (!isset($userMap[$m['user_id']])) {
            throw new \RuntimeException("ABORT MIGRATION: Mapping references non-existent user {$m['user_id']}.");
        }
        if (!in_array($m['company_id'], $companyIds, true)) {
            throw new \RuntimeException("ABORT MIGRATION: Mapping references non-existent company {$m['company_id']}.");
        }
    }

    // Pre-condition 9: Verify user_id uniqueness on raw source tuples to prevent associative key collision
    $rawUserIds = array_column($sourceLegacyMappings, 'user_id');
    if (count($rawUserIds) !== count(array_unique($rawUserIds))) {
        throw new \RuntimeException("ABORT MIGRATION: Duplicate user_id detected in raw source mapping tuples.");
    }

    // Pre-condition 10: Verify company_user is currently empty (prevent duplicate migration)
    if (DB::table('company_user')->count() > 0) {
        throw new \RuntimeException("ABORT MIGRATION: company_user table is not empty. Cannot run legacy seeder.");
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

### 6.2 Post-Migration Verification & Independent Trust Architecture (Revision 4.6):
For initial migration verification:
1. `SELECT COUNT(*) FROM company_user;` must return exactly `7` for the initial migration baseline.
2. `SELECT COUNT(*) FROM company_user WHERE is_primary = 1;` must return exactly `7` (one per engineer).
3. `SELECT COUNT(DISTINCT user_id) FROM company_user;` must return exactly `7`.
4. `SELECT COUNT(*) FROM company_user WHERE role_in_company NOT IN ('admin_operations', 'engineer');` must return `0`.

**Dynamic Invariant & Independent Manifest Trust Model for Operational Recoveries**:
- **Prohibition of Static Hardcoding**: Do NOT hardcode seven as a permanent invariant for future recoveries; legitimate employee additions and multi-company assignments will increase row counts over time.
- **Independent Manifest Architecture**: Pre-incident operational backups generate two distinct, schema-aware artifacts with fail-closed queries:
  1. **Membership Manifest** (`$BACKUP_DIR/membership_manifest_${TIMESTAMP}.tsv`): Tab-separated export capturing every recorded `company_user` pivot row (`user_id`, `company_id`, `role_in_company`, `is_primary`) sorted deterministically by `user_id, company_id` (or legacy user-company mappings on legacy database).
  2. **System-State Manifest** (`$BACKUP_DIR/system_state_${TIMESTAMP}.json`): Captures actual operational metadata querying the live database directly (with fail-closed queries, zero fallbacks to default values), including actual User 1 role (`admin` on legacy vs `super_admin` post-migration), actual applied migration count and names array (11 migrations on legacy baseline, 14 migrations post-migration), core tables inventory, manifest sequence ID, and backup file metadata (exact filename, byte size, and SHA-256 hash).
- **Authentic Independent Trust Architecture vs. Shared-Account Directory Illusion**:
  - Simply storing manifests in a separate local directory under `/home/u996219523/` does not constitute independent trust because the same unprivileged user account possesses write privileges over both directories. A compromised account or rogue script can modify both simultaneously.
  - Genuine cryptographic trust requires:
    1. An **off-server private signing key** held securely by authorized operations personnel (NEVER stored on the Hostinger server).
    2. A **public verification key** provisioned on the server with restricted read-only permissions (`chmod 400`).
    3. A **detached cryptographic signature** (`system_state_${TIMESTAMP}.json.sig`) generated off-server that cryptographically binds the backup database filename, byte size, and SHA-256 alongside the membership manifest hash.
  - **Declaration of Unresolved Operational Dependency**: Until off-server key generation, signing workflows, and on-server public key provisioning are established, this independent trust chain remains an **UNRESOLVED OPERATIONAL DEPENDENCY / EXTERNAL DEPENDENCY**. Proposed scripts must acknowledge this dependency and document that local SHA-256 checks protect solely against accidental corruption (bit rot), not malicious tampering.
- **Enforced Two-Stage Canonical Restoration Gate**: During Step 13 of the **Authoritative 16-Step Canonical Restoration Procedure** (defined in Section 2.5 and Section 2.5.1 of [CLIENT_CHANGE_DATA_MIGRATION_SAFETY.md](file:///E:/Projects/Solar/docs/CLIENT_CHANGE_DATA_MIGRATION_SAFETY.md)), post-recovery verification compares restored records field-by-field (`diff -u`) against the independent baseline manifest. Stage A completes at Step 16 with the application held in maintenance mode and generates a durable receipt (`restoration_receipt_${TIMESTAMP}.json`). Stage B brings the application online only after independent operator release authorization (`./release_application.sh`).
