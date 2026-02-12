# [TASK-0000] CRUD Root Users with Email Verification, 2FA, Audit Logging

## Executive Summary
> Implement a complete CRUD for central root users (replacing the existing `admin_users` system), including invitation-based onboarding with email verification and password setup, user activation/deactivation, avatar management, and an immutable audit log for all root user and tenant actions.

**Status:** Refined
**Estimation:** 13 points
**Priority:** High
**Affected modules:** Domain/Auth, Domain/AuditLog, Infrastructure/Auth, Infrastructure/AuditLog, App/Http (Controllers, Middleware, Mail), Routes, Database Migrations, Seeders

---

## Objective

Replace the current `admin_users` system with a fully featured `root_users` CRUD. Root users are central (non-tenant-scoped) users who are the only actors allowed to manage tenants and other root users. New root users are created by existing root users (no self-registration) and must verify their email, set their password, and enable 2FA before gaining access. All successful actions performed by root users are recorded in an immutable audit log that can be queried but never modified.

---

## Actors
- **Authenticated Root User**: Creates, reads, updates, deletes, activates, and deactivates other root users. Manages tenants. Views audit logs. Uploads avatars. Resends verification emails.
- **Invited Root User (new)**: Receives an invitation email, clicks the verification link, sets their password on the frontend, then sets up 2FA on first login.
- **System**: Sends verification emails, enforces email verification and 2FA before granting access, records audit log entries, validates avatar uploads.

---

## Open Questions

| # | Question | Category | Impact | Status |
|---|----------|----------|--------|--------|
| 1 | Root users vs existing `admin_users` table? | Functional | High | Answered: Replace `admin_users` with `root_users` |
| 2 | What is `username` for? | Functional | Medium | Answered: Unique display handle, no spaces, not used for login |
| 3 | Who creates the first root user? | Functional | High | Answered: Seeder, pre-verified |
| 4 | Who creates new root users? | Functional | High | Answered: Existing root users (invitation, no self-registration) |
| 5 | Email verification flow details? | Functional | Medium | Answered: Link to frontend set-password route, 24hr expiry, resend supported |
| 6 | How is the password set? | Functional | High | Answered: Via "set password" link in verification email |
| 7 | Can a root user delete themselves? | Business | High | Answered: No |
| 8 | Is there a deactivation concept? | Business | Medium | Answered: Yes |
| 9 | Avatar constraints? | Business | Low | Answered: 500KB-1MB, WebP/PNG/JPG, 1:1 ratio |
| 10 | Audit log scope? | Technical | High | Answered: CRUD + login/logout, with filtering and pagination |
| 11 | Log failed actions? | Technical | Medium | Answered: No, successful actions only |
| 12 | Frontend scope? | UX | High | Answered: Backend only |
| 13 | Show verification/2FA status? | UX | Low | Answered: Yes, include in API responses |

---

## Acceptance Criteria

### Root User CRUD

#### Scenario 1: Create a new root user (invitation)
```gherkin
Given an authenticated root user with verified 2FA
When they send a POST request to /api/root-users with valid data (username, first_name, last_name, email)
Then the system creates a new root user with no password and email_verified_at = null
And the system sends a verification/set-password email to the provided address
And the verification token expires in 24 hours
And the API returns 201 with the new user data
And an audit log entry is recorded for "root_user.created"
```

#### Scenario 2: Create root user with duplicate email
```gherkin
Given an authenticated root user with verified 2FA
And a root user with email "john@example.com" already exists
When they send a POST request to /api/root-users with email "john@example.com"
Then the API returns 422 with a validation error for the email field
```

#### Scenario 3: Create root user with duplicate username
```gherkin
Given an authenticated root user with verified 2FA
And a root user with username "jdoe" already exists
When they send a POST request to /api/root-users with username "jdoe"
Then the API returns 422 with a validation error for the username field
```

#### Scenario 4: Create root user with invalid username (contains spaces)
```gherkin
Given an authenticated root user with verified 2FA
When they send a POST request to /api/root-users with username "john doe"
Then the API returns 422 with a validation error indicating username cannot contain spaces
```

#### Scenario 5: List all root users
```gherkin
Given an authenticated root user with verified 2FA
When they send a GET request to /api/root-users
Then the API returns 200 with a paginated list of root users
And each entry includes id, username, firstName, lastName, email, avatarUrl, isActive, emailVerifiedAt, twoFactorEnabled, createdAt
```

#### Scenario 6: Get a single root user
```gherkin
Given an authenticated root user with verified 2FA
And a root user with the given ID exists
When they send a GET request to /api/root-users/{id}
Then the API returns 200 with the full user data
```

#### Scenario 7: Update a root user
```gherkin
Given an authenticated root user with verified 2FA
When they send a PUT request to /api/root-users/{id} with updated fields
Then the system updates the record
And the API returns 200 with the updated data
And an audit log entry is recorded for "root_user.updated" with old and new values
```

#### Scenario 8: Delete a root user
```gherkin
Given an authenticated root user "A" with verified 2FA
And a different root user "B" exists
When user "A" sends a DELETE request to /api/root-users/{B.id}
Then user "B" is permanently deleted
And the API returns 204
And an audit log entry is recorded for "root_user.deleted"
```

#### Scenario 9: Root user cannot delete themselves
```gherkin
Given an authenticated root user with verified 2FA
When they send a DELETE request to /api/root-users/{own_id}
Then the API returns 403 with message "Cannot delete your own account"
```

#### Scenario 10: Cannot delete the last active root user
```gherkin
Given there is exactly 1 active root user in the system
When an attempt is made to delete that user
Then the API returns 409 with message "Cannot delete the last active root user"
```

### Deactivation

#### Scenario 11: Deactivate a root user
```gherkin
Given an authenticated root user with verified 2FA
And a different active root user exists
When they send a PATCH to /api/root-users/{id}/deactivate
Then the target user is marked inactive
And the API returns 200
And an audit log entry is recorded for "root_user.deactivated"
```

#### Scenario 12: Reactivate a root user
```gherkin
Given an authenticated root user with verified 2FA
And a deactivated root user exists
When they send a PATCH to /api/root-users/{id}/activate
Then the target user is marked active
And the API returns 200
And an audit log entry is recorded for "root_user.activated"
```

#### Scenario 13: Cannot deactivate the last active root user
```gherkin
Given there is exactly 1 active root user
When an attempt is made to deactivate them
Then the API returns 409 with message "Cannot deactivate the last active root user"
```

### Email Verification and Password Setup

#### Scenario 14: Verify email and set password
```gherkin
Given a root user was created and received a verification email
And the token has not expired
When they submit POST /api/auth/verify-email with a valid token and new password
Then the system sets email_verified_at and the hashed password
And the API returns 200
And the token is consumed (deleted)
```

#### Scenario 15: Expired verification token
```gherkin
Given a verification token older than 24 hours
When the user submits POST /api/auth/verify-email with it
Then the API returns 400 with message "Verification token has expired"
```

#### Scenario 16: Resend verification email
```gherkin
Given an authenticated root user with verified 2FA
And a target root user has not yet verified their email
When they send POST /api/root-users/{id}/resend-verification
Then any existing tokens are invalidated
And a new verification email is sent
And the API returns 200
```

#### Scenario 17: Resend verification for already-verified user
```gherkin
Given an authenticated root user with verified 2FA
And the target root user has already verified their email
When they send POST /api/root-users/{id}/resend-verification
Then the API returns 409 with message "User has already been verified"
```

### Login Flow Updates

#### Scenario 18: Login attempt by unverified user
```gherkin
Given a root user whose email is not verified
When they attempt to log in with valid credentials
Then the API returns 403 with code "EMAIL_NOT_VERIFIED"
```

#### Scenario 19: Login attempt by deactivated user
```gherkin
Given a deactivated root user
When they attempt to log in with valid credentials
Then the API returns 403 with code "ACCOUNT_DEACTIVATED"
```

#### Scenario 20: Successful login creates audit log
```gherkin
Given a fully verified, active root user
When they complete login + 2FA
Then an audit log entry "auth.login" is recorded
```

#### Scenario 21: Logout creates audit log
```gherkin
Given an authenticated root user
When they log out
Then an audit log entry "auth.logout" is recorded
```

### Avatar

#### Scenario 22: Upload avatar
```gherkin
Given an authenticated root user with verified 2FA
When they upload a valid image (PNG/JPG/WebP, under 1MB, 1:1 ratio) to /api/root-users/{id}/avatar
Then the avatar is stored and the URL is returned
And an audit log entry "root_user.avatar_updated" is recorded
```

#### Scenario 23: Upload avatar exceeding size
```gherkin
Given an authenticated root user with verified 2FA
When they upload a 2MB avatar
Then the API returns 422 with message "Avatar must not exceed 1MB"
```

#### Scenario 24: Upload avatar with invalid format
```gherkin
Given an authenticated root user with verified 2FA
When they upload a GIF avatar
Then the API returns 422 with message "Avatar must be in WebP, PNG, or JPG format"
```

#### Scenario 25: Delete avatar
```gherkin
Given an authenticated root user with verified 2FA
And the target user has an avatar
When they send DELETE /api/root-users/{id}/avatar
Then the avatar is removed from storage and the path is set to null
```

### Audit Logs

#### Scenario 26: List with pagination
```gherkin
Given an authenticated root user with verified 2FA
When they send GET /api/audit-logs?page=1&per_page=25
Then the API returns paginated log entries (newest first)
```

#### Scenario 27: Filter by user
```gherkin
Given an authenticated root user with verified 2FA
When they send GET /api/audit-logs?user_id={uuid}
Then only entries for that user are returned
```

#### Scenario 28: Filter by action
```gherkin
Given an authenticated root user with verified 2FA
When they send GET /api/audit-logs?action=root_user.created
Then only entries with that action are returned
```

#### Scenario 29: Filter by date range
```gherkin
Given an authenticated root user with verified 2FA
When they send GET /api/audit-logs?from=2026-01-01&to=2026-01-31
Then only entries within that range are returned
```

#### Scenario 30: Filter by entity type
```gherkin
Given an authenticated root user with verified 2FA
When they send GET /api/audit-logs?entity_type=root_user
Then only entries for that entity type are returned
```

#### Scenario 31: Audit logs are immutable
```gherkin
Given an authenticated root user with verified 2FA
When they attempt PUT, PATCH, or DELETE on /api/audit-logs
Then the API returns 405 Method Not Allowed
```

### Authorization

#### Scenario 32: Unauthenticated access
```gherkin
Given no active session
When a request is made to /api/root-users or /api/audit-logs
Then the API returns 401
```

#### Scenario 33: 2FA not verified
```gherkin
Given an authenticated root user without completed 2FA
When they access /api/root-users or /api/audit-logs
Then the API returns 403 with code "2FA_REQUIRED"
```

---

## Technical Specification

### Endpoints

#### Root User CRUD
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | /api/root-users | require.2fa | List root users (paginated) |
| GET | /api/root-users/{id} | require.2fa | Get single root user |
| POST | /api/root-users | require.2fa | Create (invite) root user |
| PUT | /api/root-users/{id} | require.2fa | Update root user |
| DELETE | /api/root-users/{id} | require.2fa | Delete root user |

#### Activation/Deactivation
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| PATCH | /api/root-users/{id}/deactivate | require.2fa | Deactivate user |
| PATCH | /api/root-users/{id}/activate | require.2fa | Reactivate user |

#### Avatar
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| POST | /api/root-users/{id}/avatar | require.2fa | Upload avatar (multipart) |
| DELETE | /api/root-users/{id}/avatar | require.2fa | Remove avatar |

#### Email Verification
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| POST | /api/auth/verify-email | public (throttled) | Verify email + set password |
| POST | /api/root-users/{id}/resend-verification | require.2fa | Resend verification email |

#### Audit Logs
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | /api/audit-logs | require.2fa | List logs (filtered, paginated) |
| GET | /api/audit-logs/{id} | require.2fa | Get single log entry |

#### Modified Existing Endpoints
| Method | Path | Change |
|--------|------|--------|
| POST | /api/auth/login | Add is_active + email_verified_at checks; log "auth.login" after 2FA |
| POST | /api/auth/logout | Log "auth.logout" before session invalidation |
| POST/PUT/DELETE | /api/tenants/* | Add audit logging for tenant.created/.updated/.deleted |

---

### Database

#### Renamed table: `admin_users` -> `root_users`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | uuid PK | No | | Primary key (HasUuids) |
| username | varchar(50) | No | | Unique display handle, no spaces |
| first_name | varchar(255) | No | | First name |
| last_name | varchar(255) | No | | Last name |
| email | varchar(255) | No | | Unique email, used for login |
| password | varchar(255) | Yes | null | Null until set via verification |
| avatar_path | varchar(500) | Yes | null | Storage path to avatar file |
| is_active | boolean | No | true | Active/deactivated flag |
| email_verified_at | timestamp | Yes | null | When email was verified |
| two_factor_secret | varchar(255) | Yes | null | Encrypted TOTP secret |
| two_factor_enabled | boolean | No | false | 2FA active flag |
| two_factor_recovery_codes | text | Yes | null | Encrypted hashed recovery codes |
| two_factor_confirmed_at | timestamp | Yes | null | When 2FA was confirmed |
| remember_token | varchar(100) | Yes | null | Laravel remember token |
| created_at | timestamp | No | | |
| updated_at | timestamp | No | | |

**Indexes:** UNIQUE on (email), UNIQUE on (username), INDEX on (is_active)

#### New table: `email_verification_tokens`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid PK | No | |
| user_id | uuid FK | No | References root_users(id) CASCADE |
| token | varchar(255) | No | SHA-256 hash of raw token |
| expires_at | timestamp | No | created_at + 24 hours |
| created_at | timestamp | No | |

**Indexes:** INDEX on (user_id), UNIQUE on (token)

#### New table: `audit_logs`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | uuid PK | No | |
| user_id | uuid | No | Actor ID (NOT a FK) |
| action | varchar(100) | No | e.g., "root_user.created" |
| entity_type | varchar(100) | Yes | e.g., "root_user", "tenant" |
| entity_id | uuid | Yes | Affected entity ID |
| old_values | jsonb | Yes | Previous values |
| new_values | jsonb | Yes | New values |
| ip_address | varchar(45) | Yes | Client IP |
| user_agent | varchar(500) | Yes | Client user agent |
| created_at | timestamp | No | No updated_at -- immutable |

**Indexes:** INDEX on (user_id), INDEX on (action), INDEX on (entity_type), INDEX on (entity_type, entity_id), INDEX on (created_at)

---

### Business Rules

1. **BR-001: No self-registration.** Root users are only created by authenticated root users.
2. **BR-002: No self-deletion.** A root user cannot delete their own account.
3. **BR-003: Last active user protection.** Cannot delete or deactivate the last active root user.
4. **BR-004: Email uniqueness.** Email must be unique (case-insensitive recommended).
5. **BR-005: Username uniqueness.** Username unique, no spaces, regex `/^[a-zA-Z0-9_-]+$/`.
6. **BR-006: Password via verification only.** On creation password is null; set via email verification link.
7. **BR-007: Token expiry 24h.** Expired tokens rejected. New token invalidates previous ones.
8. **BR-008: Deactivated users cannot log in.** Login checks `is_active`.
9. **BR-009: Unverified users cannot log in.** Login checks `email_verified_at`.
10. **BR-010: Audit logs immutable.** No update or delete on `audit_logs`.
11. **BR-011: Email change triggers re-verification.** Resets `email_verified_at`, sends new email.
12. **BR-012: Avatar constraints.** Max 1MB, WebP/PNG/JPG, 1:1 aspect ratio.
13. **BR-013: Seeded user pre-verified.** First user from seeder has email verified and password set.

---

### Edge Cases

1. **Concurrent last-user operations:** Two users simultaneously deleting each other. Use database-level active count check or pessimistic locking.
2. **Token reuse:** Consumed tokens are deleted. Second use returns 400.
3. **Email change while unverified:** Old tokens invalidated, new token sent to new email.
4. **Deactivated user with active sessions:** Login check on `is_active` prevents new logins. Existing sessions not immediately invalidated (enhancement candidate).
5. **Avatar upload for non-existent user:** Returns 404, no file stored.
6. **Deleted user in audit logs:** `user_id` is not a FK. Logs preserved. API shows "Deleted User" for missing users.
7. **Freed username:** Deleting a user frees their username for reuse.
8. **Long user agent strings:** Truncated to 500 characters.

---

## Testing Scenarios

- [ ] Happy path: Create root user, verify email, set password, login, complete 2FA
- [ ] Happy path: List, show, update, delete root users
- [ ] Happy path: Deactivate and reactivate a root user
- [ ] Happy path: Upload and delete avatar with valid constraints
- [ ] Happy path: List audit logs with all filter combinations
- [ ] Validation: Duplicate email, duplicate username, invalid username format
- [ ] Validation: Avatar too large, wrong format, wrong aspect ratio
- [ ] Validation: Password too short, passwords don't match
- [ ] Business rule: Self-deletion prevention returns 403
- [ ] Business rule: Last active user delete/deactivate returns 409
- [ ] Business rule: Deactivated user login returns 403 ACCOUNT_DEACTIVATED
- [ ] Business rule: Unverified user login returns 403 EMAIL_NOT_VERIFIED
- [ ] Business rule: Expired token returns 400
- [ ] Business rule: Resend for already-verified user returns 409
- [ ] Business rule: Email change resets verification and sends new email
- [ ] Security: Unauthenticated access returns 401
- [ ] Security: Non-2FA-verified access returns 403
- [ ] Security: Audit logs cannot be modified (405)
- [ ] Edge case: Token reuse after consumption returns 400
- [ ] Edge case: Audit logs preserved after user deletion
- [ ] Integration: Tenant CRUD creates audit log entries
- [ ] Integration: Login/logout create audit log entries
- [ ] Seeder: First root user is created pre-verified with password

---

## Technical Subtasks

| # | Description | Layer | Estimation |
|---|-------------|-------|------------|
| 1 | Migration: rename `admin_users` to `root_users`, add columns (`username`, `first_name`, `last_name`, `avatar_path`, `is_active`, `email_verified_at`), make `password` nullable. Create `email_verification_tokens` table. Create `audit_logs` table. | Infrastructure | 2pt |
| 2 | Rename domain entities/ports: `AdminUser` -> `RootUser`, `AdminUserRepositoryInterface` -> `RootUserRepositoryInterface`. Update all references in providers, middleware, controllers, seeders. Create new `AuditLog` domain entity and port. | Domain + Infrastructure | 2pt |
| 3 | Implement `RootUserController` with CRUD endpoints (index, show, store, update, destroy) including all business rules (self-deletion guard, last-active guard, email-change re-verification). | All layers | 3pt |
| 4 | Implement email verification flow: `EmailVerificationServiceInterface` + adapter, `RootUserInvitationMail` mailable, `POST /api/auth/verify-email` endpoint, `POST /api/root-users/{id}/resend-verification` endpoint. Update `RootUserSeeder` for pre-verified first user. | All layers | 3pt |
| 5 | Implement `PATCH /api/root-users/{id}/deactivate` and `/activate` endpoints. Update `LoginController` to check `is_active` and `email_verified_at`. | App + Domain | 1pt |
| 6 | Implement `AuditLogController` (index with filters + show), `EloquentAuditLogRepository`, integrate audit logging into Root User CRUD, Login, Logout, and Tenant CRUD. | All layers | 2pt |
| 7 | Implement avatar upload (`POST /api/root-users/{id}/avatar`) and delete (`DELETE /api/root-users/{id}/avatar`) with file storage and validation. | App + Infrastructure | 1pt |
| 8 | Write/update tests: unit tests for domain entities, feature tests for all endpoints, update existing auth tests for renamed entities. | Testing | 2pt |

---

## Risks and Impact

- **Impact on Auth module**: The entire `AdminUser` system (entity, model, repository, controllers, middleware, seeders, tests) is renamed to `RootUser`. This is a widespread refactor. All existing auth tests must be updated.
- **Impact on Tenant module**: `TenantController` must be modified to inject the audit logging service and record entries for every CRUD action. Existing tenant tests may need minor updates.
- **Impact on Frontend**: The frontend currently references `/api/users` (which does not exist as a backend route). After this task, the frontend must be updated (in a separate task) to call `/api/root-users` instead and to handle the new fields.
- **Breaking migration risk**: Renaming `admin_users` to `root_users` is irreversible in production without data loss. Migration must be tested carefully. A backup strategy should be in place.
- **Email deliverability dependency**: The verification flow requires a working mail system. In development `MAIL_MAILER=log` is used. Production deployment requires SMTP/SES/etc. configuration.
- **Session invalidation gap**: When a user is deactivated, existing sessions are not immediately revoked. This is a known limitation. A future enhancement could invalidate sessions on deactivation.
