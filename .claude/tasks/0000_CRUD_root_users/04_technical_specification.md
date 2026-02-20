# Step 4: Technical Specification

> Status: **COMPLETE**

---

## Endpoints

All endpoints below are central API routes (in `routes/api.php`), not tenant-scoped. Unless otherwise noted, all CRUD and audit-log endpoints require the `require.2fa` middleware (authenticated + 2FA verified).

---

### Root User CRUD

#### GET /api/root-users

**Description:** List all root users with pagination.

**Middleware:** `web`, `require.2fa`

**Query Parameters:**
| Parameter | Type   | Required | Description               |
|-----------|--------|----------|---------------------------|
| page      | int    | No       | Page number (default: 1)  |
| per_page  | int    | No       | Items per page (default: 15, max: 100) |

**Response 200:**
```json
{
  "data": [
    {
      "id": "uuid",
      "username": "string",
      "firstName": "string",
      "lastName": "string",
      "email": "string",
      "avatarUrl": "string|null",
      "isActive": "boolean",
      "emailVerifiedAt": "ISO8601|null",
      "twoFactorEnabled": "boolean",
      "createdAt": "ISO8601"
    }
  ],
  "meta": {
    "total": "int",
    "currentPage": "int",
    "lastPage": "int",
    "perPage": "int"
  }
}
```

**Errors:**
| Code | Cause |
|------|-------|
| 401  | Not authenticated |
| 403  | 2FA not verified |

---

#### GET /api/root-users/{id}

**Description:** Get a single root user by ID.

**Middleware:** `web`, `require.2fa`

**Response 200:**
```json
{
  "id": "uuid",
  "username": "string",
  "firstName": "string",
  "lastName": "string",
  "email": "string",
  "avatarUrl": "string|null",
  "isActive": "boolean",
  "emailVerifiedAt": "ISO8601|null",
  "twoFactorEnabled": "boolean",
  "twoFactorConfirmedAt": "ISO8601|null",
  "createdAt": "ISO8601",
  "updatedAt": "ISO8601"
}
```

**Errors:**
| Code | Cause |
|------|-------|
| 401  | Not authenticated |
| 403  | 2FA not verified |
| 404  | User not found |

---

#### POST /api/root-users

**Description:** Create (invite) a new root user. Sends a verification/set-password email. No password is set at this point.

**Middleware:** `web`, `require.2fa`

**Request (JSON):**
```json
{
  "username": "string - required - unique, no spaces, max 50 chars, alphanumeric + underscores/hyphens",
  "first_name": "string - required - max 255",
  "last_name": "string - required - max 255",
  "email": "string - required - valid email, unique"
}
```

**Validation Rules:**
| Field      | Rules |
|------------|-------|
| username   | required, string, max:50, regex:/^[a-zA-Z0-9_-]+$/, unique:root_users |
| first_name | required, string, max:255 |
| last_name  | required, string, max:255 |
| email      | required, email, max:255, unique:root_users |

**Response 201:**
```json
{
  "id": "uuid",
  "username": "string",
  "firstName": "string",
  "lastName": "string",
  "email": "string",
  "avatarUrl": null,
  "isActive": true,
  "emailVerifiedAt": null,
  "twoFactorEnabled": false,
  "createdAt": "ISO8601"
}
```

**Side Effects:**
- Generates a signed verification token (stored in `email_verification_tokens` table)
- Sends a `RootUserInvitationMail` to the new user's email
- Creates an audit log entry: `root_user.created`

**Errors:**
| Code | Cause |
|------|-------|
| 401  | Not authenticated |
| 403  | 2FA not verified |
| 422  | Validation failed (duplicate email, duplicate username, invalid format) |

---

#### PUT /api/root-users/{id}

**Description:** Update an existing root user's profile fields.

**Middleware:** `web`, `require.2fa`

**Request (JSON):**
```json
{
  "username": "string - required - unique (excluding current user), no spaces",
  "first_name": "string - required - max 255",
  "last_name": "string - required - max 255",
  "email": "string - required - valid email, unique (excluding current user)"
}
```

**Note:** If the email is changed, the user's `email_verified_at` is reset to null, a new verification email is sent, and the user must re-verify. Their password is NOT cleared -- they keep their existing password but must re-verify the new email.

**Response 200:** Same shape as GET /api/root-users/{id}.

**Side Effects:**
- Creates an audit log entry: `root_user.updated` with `old_values` and `new_values`
- If email changed: resets `email_verified_at`, sends new verification email

**Errors:**
| Code | Cause |
|------|-------|
| 401  | Not authenticated |
| 403  | 2FA not verified |
| 404  | User not found |
| 422  | Validation failed |

---

#### DELETE /api/root-users/{id}

**Description:** Permanently delete a root user.

**Middleware:** `web`, `require.2fa`

**Response 204:** No content.

**Side Effects:**
- Removes avatar file from storage if present
- Deletes all verification tokens for the user
- Creates an audit log entry: `root_user.deleted`

**Errors:**
| Code | Cause |
|------|-------|
| 401  | Not authenticated |
| 403  | 2FA not verified / Cannot delete your own account |
| 404  | User not found |
| 409  | Cannot delete the last active root user |

---

### Activation / Deactivation

#### PATCH /api/root-users/{id}/deactivate

**Description:** Deactivate a root user (sets `is_active = false`).

**Middleware:** `web`, `require.2fa`

**Response 200:**
```json
{
  "id": "uuid",
  "isActive": false,
  "message": "User deactivated successfully"
}
```

**Side Effects:**
- Creates an audit log entry: `root_user.deactivated`

**Errors:**
| Code | Cause |
|------|-------|
| 401  | Not authenticated |
| 403  | 2FA not verified |
| 404  | User not found |
| 409  | Cannot deactivate the last active root user |
| 409  | User is already deactivated |

---

#### PATCH /api/root-users/{id}/activate

**Description:** Reactivate a deactivated root user (sets `is_active = true`).

**Middleware:** `web`, `require.2fa`

**Response 200:**
```json
{
  "id": "uuid",
  "isActive": true,
  "message": "User activated successfully"
}
```

**Side Effects:**
- Creates an audit log entry: `root_user.activated`

**Errors:**
| Code | Cause |
|------|-------|
| 401  | Not authenticated |
| 403  | 2FA not verified |
| 404  | User not found |
| 409  | User is already active |

---

### Avatar

#### POST /api/root-users/{id}/avatar

**Description:** Upload or replace the avatar for a root user. Accepts `multipart/form-data`.

**Middleware:** `web`, `require.2fa`

**Request (multipart/form-data):**
| Field  | Type | Required | Description |
|--------|------|----------|-------------|
| avatar | file | Yes      | Image file (WebP, PNG, JPG), max 1MB, 1:1 aspect ratio |

**Validation Rules:**
| Field  | Rules |
|--------|-------|
| avatar | required, file, mimes:webp,png,jpg,jpeg, max:1024, dimensions:ratio=1/1 |

**Response 200:**
```json
{
  "avatarUrl": "string"
}
```

**Side Effects:**
- Stores file at `storage/app/avatars/{user_id}.{ext}`
- Deletes previous avatar file if it exists
- Creates an audit log entry: `root_user.avatar_updated`

**Errors:**
| Code | Cause |
|------|-------|
| 401  | Not authenticated |
| 403  | 2FA not verified |
| 404  | User not found |
| 422  | Invalid file (format, size, dimensions) |

---

#### DELETE /api/root-users/{id}/avatar

**Description:** Remove the avatar for a root user.

**Middleware:** `web`, `require.2fa`

**Response 200:**
```json
{
  "message": "Avatar removed",
  "avatarUrl": null
}
```

**Side Effects:**
- Deletes avatar file from storage
- Sets `avatar_path` to null in DB
- Creates an audit log entry: `root_user.avatar_removed`

**Errors:**
| Code | Cause |
|------|-------|
| 401  | Not authenticated |
| 403  | 2FA not verified |
| 404  | User not found |

---

### Email Verification / Password Setup

#### POST /api/auth/verify-email

**Description:** Public endpoint (no auth required). Verifies the email and sets the user's password via a signed token. This is the endpoint the frontend calls after the user clicks the link in the invitation email.

**Middleware:** `web`, `throttle:verification`

**Request (JSON):**
```json
{
  "token": "string - required",
  "password": "string - required - min:8, confirmed",
  "password_confirmation": "string - required"
}
```

**Response 200:**
```json
{
  "message": "Email verified and password set successfully"
}
```

**Side Effects:**
- Sets `email_verified_at` to current timestamp
- Hashes and stores the password
- Deletes the used verification token
- Creates an audit log entry: `root_user.email_verified`

**Errors:**
| Code | Cause |
|------|-------|
| 400  | Token is invalid or expired |
| 422  | Validation failed (password too short, passwords don't match) |
| 429  | Rate limited |

---

#### POST /api/root-users/{id}/resend-verification

**Description:** Resend the verification/set-password email for a root user who hasn't verified yet.

**Middleware:** `web`, `require.2fa`

**Response 200:**
```json
{
  "message": "Verification email sent"
}
```

**Side Effects:**
- Invalidates (deletes) any existing verification tokens for the user
- Generates a new token with 24hr expiry
- Sends the verification email

**Errors:**
| Code | Cause |
|------|-------|
| 401  | Not authenticated |
| 403  | 2FA not verified |
| 404  | User not found |
| 409  | User has already been verified |

---

### Login Flow (Modifications)

#### POST /api/auth/login (existing, modified)

**Changes to existing endpoint:**
1. After verifying credentials, check `is_active`. If false, return 403 with `{ "message": "Account is deactivated", "code": "ACCOUNT_DEACTIVATED" }`.
2. After verifying credentials, check `email_verified_at`. If null, return 403 with `{ "message": "Email not verified", "code": "EMAIL_NOT_VERIFIED" }`.
3. On successful login (after 2FA verification), create audit log entry: `auth.login`.

#### POST /api/auth/logout (existing, modified)

**Changes to existing endpoint:**
1. Before invalidating the session, create audit log entry: `auth.logout`.

---

### Audit Logs

#### GET /api/audit-logs

**Description:** List audit log entries with filtering and pagination.

**Middleware:** `web`, `require.2fa`

**Query Parameters:**
| Parameter   | Type   | Required | Description |
|-------------|--------|----------|-------------|
| page        | int    | No       | Page number (default: 1) |
| per_page    | int    | No       | Items per page (default: 25, max: 100) |
| user_id     | uuid   | No       | Filter by acting user ID |
| action      | string | No       | Filter by action type (e.g., "root_user.created") |
| entity_type | string | No       | Filter by entity type (e.g., "root_user", "tenant") |
| entity_id   | uuid   | No       | Filter by specific entity ID |
| from        | date   | No       | Start date (inclusive, ISO8601) |
| to          | date   | No       | End date (inclusive, ISO8601) |

**Response 200:**
```json
{
  "data": [
    {
      "id": "uuid",
      "userId": "uuid",
      "userName": "string",
      "action": "string",
      "entityType": "string",
      "entityId": "uuid|null",
      "oldValues": "object|null",
      "newValues": "object|null",
      "ipAddress": "string",
      "userAgent": "string",
      "createdAt": "ISO8601"
    }
  ],
  "meta": {
    "total": "int",
    "currentPage": "int",
    "lastPage": "int",
    "perPage": "int"
  }
}
```

**Errors:**
| Code | Cause |
|------|-------|
| 401  | Not authenticated |
| 403  | 2FA not verified |

---

#### GET /api/audit-logs/{id}

**Description:** Get a single audit log entry.

**Middleware:** `web`, `require.2fa`

**Response 200:** Same shape as a single item from the list above.

**Errors:**
| Code | Cause |
|------|-------|
| 401  | Not authenticated |
| 403  | 2FA not verified |
| 404  | Log entry not found |

---

#### All other methods on /api/audit-logs

PUT, PATCH, DELETE on `/api/audit-logs` and `/api/audit-logs/{id}` return **405 Method Not Allowed**. This is enforced by only registering `index` and `show` routes.

---

## Database

### Renamed table: `admin_users` -> `root_users`

A new migration will:
1. Rename `admin_users` to `root_users`.
2. Add the new columns.

#### Table: `root_users`

| Column                     | Type           | Nullable | Default | Description |
|----------------------------|----------------|----------|---------|-------------|
| id                         | uuid PK        | No       |         | Primary key (HasUuids) |
| username                   | varchar(50)    | No       |         | Unique display handle, no spaces |
| first_name                 | varchar(255)   | No       |         | First name |
| last_name                  | varchar(255)   | No       |         | Last name |
| email                      | varchar(255)   | No       |         | Unique email, used for login |
| password                   | varchar(255)   | Yes      | null    | Nullable -- null until user sets it via verification |
| avatar_path                | varchar(500)   | Yes      | null    | Path to avatar file in storage |
| is_active                  | boolean        | No       | true    | Whether the user can log in |
| email_verified_at          | timestamp      | Yes      | null    | When email was verified |
| two_factor_secret          | varchar(255)   | Yes      | null    | Encrypted TOTP secret |
| two_factor_enabled         | boolean        | No       | false   | Whether 2FA is active |
| two_factor_recovery_codes  | text           | Yes      | null    | Encrypted JSON array of hashed recovery codes |
| two_factor_confirmed_at    | timestamp      | Yes      | null    | When 2FA was first confirmed |
| remember_token             | varchar(100)   | Yes      | null    | Laravel remember token |
| created_at                 | timestamp      | No       |         | Record creation |
| updated_at                 | timestamp      | No       |         | Last modification |

**Indexes:**
- `root_users_email_unique` UNIQUE on (`email`)
- `root_users_username_unique` UNIQUE on (`username`)
- `root_users_is_active_index` on (`is_active`)

---

### New table: `email_verification_tokens`

| Column     | Type         | Nullable | Description |
|------------|--------------|----------|-------------|
| id         | uuid PK      | No       | Primary key |
| user_id    | uuid FK      | No       | References root_users(id) ON DELETE CASCADE |
| token      | varchar(255) | No       | Hashed token (SHA-256 of the raw token sent in the email) |
| expires_at | timestamp    | No       | Token expiration (created_at + 24 hours) |
| created_at | timestamp    | No       | Record creation |

**Indexes:**
- `email_verification_tokens_user_id_index` on (`user_id`)
- `email_verification_tokens_token_unique` UNIQUE on (`token`)

---

### New table: `audit_logs`

| Column      | Type         | Nullable | Description |
|-------------|--------------|----------|-------------|
| id          | uuid PK      | No       | Primary key |
| user_id     | uuid         | No       | The root user who performed the action. NOT a FK -- kept for reference even if user is deleted. |
| action      | varchar(100) | No       | Action identifier (e.g., "root_user.created", "auth.login") |
| entity_type | varchar(100) | Yes      | Entity type (e.g., "root_user", "tenant") |
| entity_id   | uuid         | Yes      | ID of the affected entity |
| old_values  | jsonb        | Yes      | Previous values (for updates) |
| new_values  | jsonb        | Yes      | New values (for creates/updates) |
| ip_address  | varchar(45)  | Yes      | Client IP address |
| user_agent  | varchar(500) | Yes      | Client user agent |
| created_at  | timestamp    | No       | When the action occurred |

**Indexes:**
- `audit_logs_user_id_index` on (`user_id`)
- `audit_logs_action_index` on (`action`)
- `audit_logs_entity_type_index` on (`entity_type`)
- `audit_logs_entity_type_entity_id_index` on (`entity_type`, `entity_id`)
- `audit_logs_created_at_index` on (`created_at`)

**Note:** This table has NO `updated_at` column and NO foreign keys. It is append-only. The model should not allow updates or deletes.

---

## Audit Log Action Types

| Action                     | Entity Type | Description |
|----------------------------|-------------|-------------|
| `root_user.created`        | root_user   | New root user invited |
| `root_user.updated`        | root_user   | Root user profile updated |
| `root_user.deleted`        | root_user   | Root user permanently deleted |
| `root_user.deactivated`    | root_user   | Root user deactivated |
| `root_user.activated`      | root_user   | Root user reactivated |
| `root_user.email_verified` | root_user   | Email verified and password set |
| `root_user.avatar_updated` | root_user   | Avatar uploaded or replaced |
| `root_user.avatar_removed` | root_user   | Avatar removed |
| `tenant.created`           | tenant      | Tenant created |
| `tenant.updated`           | tenant      | Tenant updated |
| `tenant.deleted`           | tenant      | Tenant deleted |
| `auth.login`               | root_user   | Successful login (after 2FA) |
| `auth.logout`              | root_user   | Successful logout |

---

## Domain Layer Changes

### New / Modified Domain Entities

**File:** `app/Domain/Auth/Entities/RootUser.php` (replaces `AdminUser.php`)

```
final class RootUser implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $username,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly ?string $avatarUrl,
        public readonly bool $isActive,
        public readonly ?DateTimeImmutable $emailVerifiedAt,
        public readonly bool $twoFactorEnabled,
        public readonly ?DateTimeImmutable $twoFactorConfirmedAt,
        public readonly DateTimeImmutable $createdAt,
    ) {}
}
```

**File:** `app/Domain/AuditLog/Entities/AuditLog.php` (new)

```
final class AuditLog implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly ?string $userName,
        public readonly string $action,
        public readonly ?string $entityType,
        public readonly ?string $entityId,
        public readonly ?array $oldValues,
        public readonly ?array $newValues,
        public readonly ?string $ipAddress,
        public readonly ?string $userAgent,
        public readonly DateTimeImmutable $createdAt,
    ) {}
}
```

### New / Modified Domain Ports

**File:** `app/Domain/Auth/Ports/RootUserRepositoryInterface.php` (replaces `AdminUserRepositoryInterface.php`)

Extends the existing interface with:
- `findAll(int $page, int $perPage): PaginatedResult`
- `create(array $data): RootUser`
- `update(string $id, array $data): RootUser`
- `delete(string $id): void`
- `countActive(): int`
- All existing 2FA methods (renamed from AdminUser)

**File:** `app/Domain/Auth/Ports/EmailVerificationServiceInterface.php` (new)

```
interface EmailVerificationServiceInterface
{
    public function createToken(string $userId): string;
    public function verifyToken(string $token): ?string; // returns userId or null
    public function deleteTokensForUser(string $userId): void;
}
```

**File:** `app/Domain/AuditLog/Ports/AuditLogRepositoryInterface.php` (new)

```
interface AuditLogRepositoryInterface
{
    public function log(string $userId, string $action, ?string $entityType, ?string $entityId, ?array $oldValues, ?array $newValues, ?string $ipAddress, ?string $userAgent): void;
    public function findAll(array $filters, int $page, int $perPage): PaginatedResult;
    public function findById(string $id): ?AuditLog;
}
```

---

## Business Rules

1. **BR-001: No self-registration.** Root users are only created by other authenticated root users. There is no public registration endpoint.

2. **BR-002: No self-deletion.** A root user cannot delete their own account. The API must compare the authenticated user's ID with the target ID.

3. **BR-003: Last active user protection.** The system must prevent deleting or deactivating the last active root user. Before delete/deactivate, query `countActive()` and ensure it remains >= 1 after the operation.

4. **BR-004: Email uniqueness.** Email must be unique across all root users (case-insensitive comparison recommended via `lower(email)` unique index or application-level normalization).

5. **BR-005: Username uniqueness.** Username must be unique, no spaces allowed. Validated with regex: `/^[a-zA-Z0-9_-]+$/`.

6. **BR-006: Password is set via email verification only.** On user creation, `password` is null. The user sets it by clicking the verification link and submitting the set-password form. Users with null password cannot log in.

7. **BR-007: Verification token expiry.** Tokens expire 24 hours after creation. Expired tokens are rejected. A new token invalidates all previous tokens for that user.

8. **BR-008: Deactivated users cannot log in.** The login flow must check `is_active` before proceeding.

9. **BR-009: Unverified users cannot log in.** The login flow must check `email_verified_at` is not null before proceeding. Additionally, users without a password (null) will naturally fail credential verification.

10. **BR-010: Audit logs are immutable.** No update or delete operations are permitted on the `audit_logs` table. The Eloquent model should not expose these operations.

11. **BR-011: Email change triggers re-verification.** When a root user's email is updated, `email_verified_at` is reset to null, and a new verification email is sent. The user keeps their existing password.

12. **BR-012: Avatar constraints.** Max 1MB, formats: WebP/PNG/JPG, aspect ratio 1:1. Validated server-side.

13. **BR-013: Seeded root user is pre-verified.** The database seeder creates the initial root user with `email_verified_at` set, `is_active = true`, and a hashed password. This user still needs to set up 2FA on first login.

---

## Edge Cases

1. **Concurrent last-user deletion:** Two root users simultaneously try to delete the other. A database-level check or pessimistic locking should ensure the active count never drops to zero.

2. **Token reuse:** A used verification token must be deleted immediately. If a user tries to use the same token twice, it should return 400.

3. **Email change while unverified:** If a newly created user (not yet verified) has their email changed by an admin, the old token should be invalidated and a new one sent to the new email.

4. **Deactivated user with active sessions:** When a user is deactivated, their existing sessions are not immediately invalidated (sessions are stateless from the DB side). The login check on `is_active` prevents new logins. Consider adding session invalidation as an enhancement.

5. **Avatar upload for non-existent user:** Returns 404. No file is stored.

6. **Deleting a user who has audit logs:** Audit logs reference `user_id` but it is NOT a foreign key. Logs are preserved even after user deletion. The `userName` in the API response may show "Deleted User" or similar for deleted users.

7. **Updating username to one that was freed by a deleted user:** Allowed -- uniqueness is checked against current records only.

8. **Very long user agent strings:** Truncated to 500 characters before storage.

---

## Impact on Existing Code

### Files to Rename / Replace

| Current File | New File | Change |
|---|---|---|
| `app/Domain/Auth/Entities/AdminUser.php` | `app/Domain/Auth/Entities/RootUser.php` | Rename class, add new fields |
| `app/Domain/Auth/Ports/AdminUserRepositoryInterface.php` | `app/Domain/Auth/Ports/RootUserRepositoryInterface.php` | Rename, add CRUD methods |
| `app/Infrastructure/Auth/Models/AdminUser.php` | `app/Infrastructure/Auth/Models/RootUser.php` | Rename, update table name + fillable + toEntity |
| `app/Infrastructure/Auth/Adapters/EloquentAdminUserRepository.php` | `app/Infrastructure/Auth/Adapters/EloquentRootUserRepository.php` | Rename, implement new methods |
| `app/Infrastructure/Auth/Providers/AuthServiceProvider.php` | Same file | Update binding from AdminUser to RootUser |
| `app/Http/Controllers/Api/Auth/LoginController.php` | Same file | Update type hints, add is_active/email_verified checks, add audit logging |
| `app/Http/Controllers/Api/Auth/TwoFactorController.php` | Same file | Update type hints from AdminUser to RootUser |
| `app/Http/Middleware/Require2FA.php` | Same file | No changes needed (session-based) |
| `app/Http/Middleware/EnsureTwoFactorSetup.php` | Same file | Update type hint from AdminUserRepositoryInterface to RootUserRepositoryInterface |
| `database/seeders/AdminUserSeeder.php` | `database/seeders/RootUserSeeder.php` | Rename, add username/first_name/last_name/email_verified_at |
| `routes/api.php` | Same file | Add root-users and audit-logs routes |

### New Files to Create

| File | Layer | Description |
|---|---|---|
| `app/Http/Controllers/Api/RootUserController.php` | App | CRUD controller |
| `app/Http/Controllers/Api/AuditLogController.php` | App | Read-only controller |
| `app/Http/Controllers/Api/Auth/EmailVerificationController.php` | App | Verify-email + resend endpoints |
| `app/Domain/Auth/Entities/RootUser.php` | Domain | Entity |
| `app/Domain/Auth/Ports/RootUserRepositoryInterface.php` | Domain | Port |
| `app/Domain/Auth/Ports/EmailVerificationServiceInterface.php` | Domain | Port |
| `app/Domain/AuditLog/Entities/AuditLog.php` | Domain | Entity |
| `app/Domain/AuditLog/Ports/AuditLogRepositoryInterface.php` | Domain | Port |
| `app/Domain/AuditLog/Ports/AuditLogServiceInterface.php` | Domain | Port (convenience service) |
| `app/Infrastructure/Auth/Models/RootUser.php` | Infrastructure | Eloquent model |
| `app/Infrastructure/Auth/Adapters/EloquentRootUserRepository.php` | Infrastructure | Repository |
| `app/Infrastructure/Auth/Adapters/EmailVerificationService.php` | Infrastructure | Token management |
| `app/Infrastructure/AuditLog/Models/AuditLog.php` | Infrastructure | Eloquent model (no update/delete) |
| `app/Infrastructure/AuditLog/Adapters/EloquentAuditLogRepository.php` | Infrastructure | Repository |
| `app/Infrastructure/AuditLog/Providers/AuditLogServiceProvider.php` | Infrastructure | Bindings |
| `app/Mail/RootUserInvitationMail.php` | App | Mailable for verification email |
| `database/migrations/xxxx_rename_admin_users_to_root_users.php` | - | Migration |
| `database/migrations/xxxx_create_email_verification_tokens_table.php` | - | Migration |
| `database/migrations/xxxx_create_audit_logs_table.php` | - | Migration |

### Existing TenantController Impact

The `TenantController` must be updated to record audit log entries for `tenant.created`, `tenant.updated`, and `tenant.deleted`. This can be done by injecting the `AuditLogServiceInterface` or using a domain event pattern.
