# AI Operating Constraints & Invariants (AGENTS.md)

## Critical Architectural Invariants
1. **Multi-Entity Isolation**:
   - Every Service, Customer, Site, and Report must have an explicit foreign key association with `Company`.
   - Never allow a customer or service to exist as an unassociated orphan record.
2. **Dual Status Separation**:
   - Never combine `service.status` and `report.status` into a single status field.
   - A service tracks operational dispatch state (`unassigned`, `assigned`, `in_progress`, `report_submitted`, `correction_required`, `completed`, `cancelled`).
   - A report tracks form lifecycle (`draft`, `submitted`, `correction_required`, `resubmitted`, `approved`, `rejected`).
3. **Decoupled Section Data**:
   - Do NOT create giant monolithic tables with dozens of columns for form fields.
   - Form fields must reside in `report_data` grouped by `section_key` (`customer_details`, `system_details`, `module_inspection`, `structure_inspection`, `pcu_inspection`, `battery_inspection`, `complaint_details`, `remarks`).
   - Additional forms (*Site Inspection*, *Installation*, *Complaint Attending*, *Daily Work*) must reuse `report_data` with new template slugs.

## Security & Authorization Boundaries
1. **Role Guarding**:
   - `admin` routes are strictly restricted by `AdminMiddleware`.
   - `engineer` routes are restricted by `EngineerMiddleware`.
   - Engineers can only view and update services assigned to their user ID.
2. **Report Locking**:
   - Once a report is in `submitted`, `resubmitted`, or `approved` state, it is read-only for field engineers.
   - An engineer may only edit when status is `draft` or `correction_required`.
   - Once `approved`, a report is permanently immutable.

## Prohibitions (Do NOT Build)
- Do NOT add IoT / hardware inverter communication or telemetry streaming.
- Do NOT build customer mobile apps or client login portals.
- Do NOT introduce payment gateways, billing software, or subscription systems.
- Do NOT build WhatsApp messaging bots or SMS gateways unless requested.
- Do NOT replace standard Blade + Alpine architecture with heavy SPA client frameworks.

## Testing & Verification Requirements
- Every new route, status transition, or report section must be verified with automated PHPUnit feature tests.
- Run `php artisan test` before committing any code changes.
