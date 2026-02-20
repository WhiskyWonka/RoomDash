# Step 3: Acceptance Criteria

> Status: **COMPLETE**

---

## Root User CRUD

### Scenario 1: Create a new root user (invitation)
```gherkin
Given an authenticated root user with verified 2FA
When they send a POST request to /api/root-users with valid data:
  | field      | value              |
  | username   | jdoe               |
  | first_name | John               |
  | last_name  | Doe                |
  | email      | john@example.com   |
Then the system creates a new root user with status "pending_verification"
And the new user has no password set
And the system sends an email verification/set-password email to john@example.com
And the verification token expires in 24 hours
And the API returns 201 with the new user data
And an audit log entry is recorded for "root_user.created"
```

### Scenario 2: Create root user with duplicate email
```gherkin
Given an authenticated root user with verified 2FA
And a root user with email "john@example.com" already exists
When they send a POST request to /api/root-users with email "john@example.com"
Then the API returns 422 with a validation error for the email field
And no user is created
And no email is sent
```

### Scenario 3: Create root user with duplicate username
```gherkin
Given an authenticated root user with verified 2FA
And a root user with username "jdoe" already exists
When they send a POST request to /api/root-users with username "jdoe"
Then the API returns 422 with a validation error for the username field
```

### Scenario 4: Create root user with invalid username (contains spaces)
```gherkin
Given an authenticated root user with verified 2FA
When they send a POST request to /api/root-users with username "john doe"
Then the API returns 422 with a validation error indicating username cannot contain spaces
```

### Scenario 5: List all root users
```gherkin
Given an authenticated root user with verified 2FA
And there are 5 root users in the system
When they send a GET request to /api/root-users
Then the API returns 200 with a paginated list of all 5 root users
And each user entry includes: id, username, first_name, last_name, email, avatar_url, is_active, email_verified_at, two_factor_enabled, created_at
```

### Scenario 6: Get a single root user
```gherkin
Given an authenticated root user with verified 2FA
And a root user with ID "uuid-123" exists
When they send a GET request to /api/root-users/uuid-123
Then the API returns 200 with the full user data
```

### Scenario 7: Update a root user
```gherkin
Given an authenticated root user with verified 2FA
And a root user with ID "uuid-123" exists
When they send a PUT request to /api/root-users/uuid-123 with:
  | field      | value           |
  | first_name | Jane            |
  | last_name  | Smith           |
Then the system updates the root user record
And the API returns 200 with the updated user data
And an audit log entry is recorded for "root_user.updated" with old and new values
```

### Scenario 8: Delete a root user
```gherkin
Given an authenticated root user "A" with verified 2FA
And a different root user "B" with ID "uuid-456" exists
When user "A" sends a DELETE request to /api/root-users/uuid-456
Then the system permanently deletes root user "B"
And the API returns 204
And an audit log entry is recorded for "root_user.deleted"
```

### Scenario 9: Root user cannot delete themselves
```gherkin
Given an authenticated root user with ID "uuid-123" and verified 2FA
When they send a DELETE request to /api/root-users/uuid-123
Then the API returns 403 with message "Cannot delete your own account"
And no user is deleted
```

### Scenario 10: Cannot delete the last active root user
```gherkin
Given there is exactly 1 active root user in the system with ID "uuid-only"
And another authenticated root user (deactivated) with verified 2FA tries to delete them
When they send a DELETE request to /api/root-users/uuid-only
Then the API returns 409 with message "Cannot delete the last active root user"
```

---

## Deactivation

### Scenario 11: Deactivate a root user
```gherkin
Given an authenticated root user "A" with verified 2FA
And a different active root user "B" with ID "uuid-456" exists
When user "A" sends a PATCH request to /api/root-users/uuid-456/deactivate
Then root user "B" is marked as inactive (is_active = false)
And root user "B" can no longer log in
And the API returns 200
And an audit log entry is recorded for "root_user.deactivated"
```

### Scenario 12: Reactivate a root user
```gherkin
Given an authenticated root user with verified 2FA
And a deactivated root user with ID "uuid-456" exists
When they send a PATCH request to /api/root-users/uuid-456/activate
Then the root user is marked as active (is_active = true)
And the API returns 200
And an audit log entry is recorded for "root_user.activated"
```

### Scenario 13: Cannot deactivate the last active root user
```gherkin
Given there is exactly 1 active root user in the system
When that user tries to deactivate themselves
Then the API returns 409 with message "Cannot deactivate the last active root user"
```

---

## Email Verification and Password Setup

### Scenario 14: Verify email and set password via token
```gherkin
Given a root user was created with email "john@example.com"
And the user received an email with a verification/set-password link
And the token has not expired (within 24 hours)
When the user submits a POST to /api/auth/verify-email with:
  | field                 | value          |
  | token                 | valid-token    |
  | password              | SecurePass123! |
  | password_confirmation | SecurePass123! |
Then the system verifies the email (sets email_verified_at)
And the system sets the user's password
And the API returns 200 with a success message
And an audit log entry is recorded for "root_user.email_verified"
```

### Scenario 15: Expired verification token
```gherkin
Given a root user was created 25 hours ago
And they received a verification email with a token
When they submit a POST to /api/auth/verify-email with the expired token
Then the API returns 400 with message "Verification token has expired"
And no email is verified
And no password is set
```

### Scenario 16: Resend verification email
```gherkin
Given an authenticated root user with verified 2FA
And a root user "B" with ID "uuid-456" has not yet verified their email
When user "A" sends a POST to /api/root-users/uuid-456/resend-verification
Then the system invalidates any previous verification tokens for user "B"
And the system sends a new verification/set-password email
And the API returns 200
```

### Scenario 17: Resend verification for already verified user
```gherkin
Given an authenticated root user with verified 2FA
And root user "B" with ID "uuid-456" has already verified their email
When user "A" sends a POST to /api/root-users/uuid-456/resend-verification
Then the API returns 409 with message "User has already been verified"
```

---

## Login Flow (Updated)

### Scenario 18: Login attempt by unverified root user
```gherkin
Given a root user with email "john@example.com" exists
And their email has NOT been verified (email_verified_at is null)
When they send a POST to /api/auth/login with valid email and password
Then the API returns 403 with message "Email not verified"
And code "EMAIL_NOT_VERIFIED"
```

### Scenario 19: Login attempt by deactivated root user
```gherkin
Given a root user with email "john@example.com" exists
And they are deactivated (is_active = false)
When they send a POST to /api/auth/login with valid credentials
Then the API returns 403 with message "Account is deactivated"
And code "ACCOUNT_DEACTIVATED"
```

### Scenario 20: Successful login logs an audit entry
```gherkin
Given an active, verified root user with 2FA enabled
When they successfully complete the full login flow (credentials + 2FA)
Then an audit log entry is recorded for "auth.login" with their user ID
```

### Scenario 21: Successful logout logs an audit entry
```gherkin
Given an authenticated root user
When they send a POST to /api/auth/logout
Then the session is invalidated
And an audit log entry is recorded for "auth.logout"
```

---

## Avatar Upload

### Scenario 22: Upload avatar
```gherkin
Given an authenticated root user with verified 2FA
And a root user with ID "uuid-123" exists
When they send a POST to /api/root-users/uuid-123/avatar with a valid image:
  | constraint | value     |
  | format     | PNG       |
  | size       | 400KB     |
  | dimensions | 200x200   |
Then the system stores the avatar file
And the user's avatar_path is updated
And the API returns 200 with the new avatar URL
And an audit log entry is recorded for "root_user.avatar_updated"
```

### Scenario 23: Upload avatar exceeding size limit
```gherkin
Given an authenticated root user with verified 2FA
When they upload an avatar file of 2MB
Then the API returns 422 with message "Avatar must not exceed 1MB"
```

### Scenario 24: Upload avatar with invalid format
```gherkin
Given an authenticated root user with verified 2FA
When they upload an avatar in GIF format
Then the API returns 422 with message "Avatar must be in WebP, PNG, or JPG format"
```

### Scenario 25: Delete avatar
```gherkin
Given an authenticated root user with verified 2FA
And root user "uuid-123" has an avatar
When they send a DELETE to /api/root-users/uuid-123/avatar
Then the avatar file is removed from storage
And the user's avatar_path is set to null
And the API returns 200
```

---

## Audit Logs

### Scenario 26: List audit logs with pagination
```gherkin
Given an authenticated root user with verified 2FA
And there are 150 audit log entries
When they send a GET request to /api/audit-logs?page=1&per_page=25
Then the API returns 200 with the first 25 log entries (newest first)
And the response includes pagination metadata (total, current_page, last_page, per_page)
```

### Scenario 27: Filter audit logs by user
```gherkin
Given an authenticated root user with verified 2FA
And there are audit logs from multiple users
When they send a GET request to /api/audit-logs?user_id=uuid-123
Then the API returns only log entries for user "uuid-123"
```

### Scenario 28: Filter audit logs by action type
```gherkin
Given an authenticated root user with verified 2FA
When they send a GET request to /api/audit-logs?action=root_user.created
Then the API returns only log entries with action "root_user.created"
```

### Scenario 29: Filter audit logs by date range
```gherkin
Given an authenticated root user with verified 2FA
When they send a GET request to /api/audit-logs?from=2026-01-01&to=2026-01-31
Then the API returns only log entries within that date range
```

### Scenario 30: Filter audit logs by entity type
```gherkin
Given an authenticated root user with verified 2FA
When they send a GET request to /api/audit-logs?entity_type=root_user
Then the API returns only log entries related to root_user entities
```

### Scenario 31: Audit logs are immutable
```gherkin
Given an authenticated root user with verified 2FA
When they attempt to send a PUT, PATCH, or DELETE request to /api/audit-logs or /api/audit-logs/{id}
Then the API returns 405 Method Not Allowed
```

---

## Authorization Guard

### Scenario 32: Unauthenticated access to root user endpoints
```gherkin
Given no active session
When a request is made to any /api/root-users endpoint
Then the API returns 401 Unauthenticated
```

### Scenario 33: Authenticated but 2FA not verified
```gherkin
Given an authenticated root user who has NOT completed 2FA verification
When they send a request to /api/root-users
Then the API returns 403 with code "2FA_REQUIRED"
```
