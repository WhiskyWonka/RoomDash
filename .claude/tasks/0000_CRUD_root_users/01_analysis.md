# Step 1: Analysis

## Main Objective
Implement a full CRUD for root users — the only users allowed to manage tenants and other root users in the central (non-tenant) database.

## Involved Actors
- **Root User (existing)**: Creates, reads, updates, and deletes other root users. Also manages tenants.
- **New Root User (invited)**: Receives an invitation email, confirms their email, enables 2FA, and gains access to the system.
- **System**: Sends verification emails, enforces 2FA, records audit logs.

## Affected Domain Entities
- **Root User / Admin User**: Central user entity with fields `username`, `first_name`, `last_name`, `email`, `avatar`, `password`, 2FA configuration, email verification status.
- **Audit Log**: Immutable log table recording all root user actions (insertable and readable, never modifiable or deletable).
- **Tenant**: Existing entity — root users are the only ones who can manage tenants.

## Dependencies
- **Existing `admin_users` table**: Already has `email`, `password`, and 2FA fields (`two_factor_secret`, `two_factor_confirmed_at`). Needs to be extended or replaced.
- **Existing 2FA system**: `Admin2FAController`, `AdminAuthController`, middleware `EnsureAdmin2FAIsSetup` — already implemented. Should be reused/adapted.
- **Email system**: Laravel mail/notification system needed for email verification.
- **Multi-tenancy**: Root users are central (not tenant-scoped). Routes go through `routes/api.php`, not `routes/tenant.php`.
- **Frontend**: `UsersPage` stub exists in the React app with table + dialogs, but no backend endpoint yet.
