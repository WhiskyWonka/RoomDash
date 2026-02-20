# Step 5: Estimation

> Status: **COMPLETE**

---

## Overall Estimation: 13 story points

This is a large task that touches nearly every layer of the application, introduces two new domain concepts (audit logging and email verification), requires a breaking migration (rename `admin_users` to `root_users`), and modifies the existing authentication flow. It is at the upper bound of what should be a single deliverable and is recommended to be split into subtasks.

---

## Justification

### Complexity Factors

1. **Database migration with rename + schema change (Medium):** Renaming `admin_users` to `root_users` and adding 5 new columns. This is a breaking change that ripples through every file that references the old table/model name.

2. **Full CRUD with business logic guards (Medium-High):** The root user CRUD is not a simple resource -- it has self-deletion prevention, last-active-user protection, deactivation/activation toggles, and email-change-triggers-reverification logic.

3. **Email verification + password setup flow (High):** This is effectively a new authentication sub-flow: token generation, secure token storage, mailable creation, public verification endpoint, token expiry management, and resend capability. This is the most complex single piece.

4. **Audit logging system (Medium):** New domain entity, new table, new repository, immutability enforcement, filtering/pagination on reads, and integration into every existing controller (Login, Logout, Tenant CRUD) plus the new Root User CRUD.

5. **Avatar upload (Low-Medium):** File upload with validation (format, size, aspect ratio), storage, URL generation, and cleanup on delete. Straightforward but adds surface area.

6. **Refactoring existing code (Medium):** Every reference to `AdminUser`, `AdminUserRepositoryInterface`, `admin_users` must be renamed. This affects controllers, middleware, providers, seeders, and tests.

7. **Modification of existing auth flow (Medium):** The login endpoint must now check `is_active` and `email_verified_at` before proceeding. The logout endpoint must log an audit entry.

### Risk Factors

- **Breaking migration:** If any part of the rename migration fails, it could leave the database in an inconsistent state. Must be thoroughly tested.
- **Auth flow regression:** Modifying the login flow is high-risk. Existing tests for login and 2FA must be updated and pass.
- **Email deliverability:** The invitation email flow depends on mail configuration. In development, `MAIL_MAILER=log` is used, so testing requires checking the log. Production mail configuration is out of scope but a dependency for deployment.

---

## Recommended Subtask Breakdown

| # | Subtask | Layer | Points | Dependencies |
|---|---------|-------|--------|--------------|
| 1 | Migration: rename `admin_users` to `root_users`, add new columns. Create `email_verification_tokens` table. Create `audit_logs` table. | Infrastructure | 2 | None |
| 2 | Rename domain entities and ports: `AdminUser` -> `RootUser`, `AdminUserRepositoryInterface` -> `RootUserRepositoryInterface`. Update all references (providers, middleware, controllers, seeders). | Domain + Infrastructure | 2 | Subtask 1 |
| 3 | Implement Root User CRUD endpoints (index, show, store, update, destroy) with all business rules (self-deletion prevention, last-active protection). | All layers | 3 | Subtask 2 |
| 4 | Implement email verification flow: token service, mailable, verify-email endpoint, resend-verification endpoint. Update seeder for pre-verified first user. | All layers | 3 | Subtask 2 |
| 5 | Implement deactivation/activation endpoints. Update login flow to check `is_active` and `email_verified_at`. | App + Domain | 1 | Subtask 3 |
| 6 | Implement audit log domain, repository, and read-only API (index with filters, show). Integrate audit logging into all Root User CRUD, auth login/logout, and Tenant CRUD. | All layers | 2 | Subtask 2 |
| 7 | Implement avatar upload/delete endpoints with file storage and validation. | App + Infrastructure | 1 | Subtask 3 |
| 8 | Update and write tests for all new functionality. Update existing auth tests for renamed entities. | Testing | 2 | All above |
|   | **Total** | | **16** | |

**Note:** The subtask total (16) exceeds the overall estimate (13) because subtask estimates include overhead for context switching and integration. When done sequentially by one developer familiar with the codebase, the overall effort compresses to approximately 13 points.

---

## Suggested Sprint Plan

Given a standard 2-week sprint:

- **Sprint 1 (8 pts):** Subtasks 1, 2, 3, 4 -- Core migration, entity refactoring, CRUD, and email verification. At the end of Sprint 1, root users can be created, invited, and verified.
- **Sprint 2 (5 pts):** Subtasks 5, 6, 7, 8 -- Activation/deactivation, audit logs, avatar upload, and comprehensive testing. At the end of Sprint 2, the feature is complete.

Alternatively, if the team has capacity, all subtasks can be completed in a single sprint by parallelizing subtasks 6 and 7 (audit logs and avatar) since they are independent of each other.
