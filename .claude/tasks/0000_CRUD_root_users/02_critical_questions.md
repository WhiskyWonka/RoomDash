# Step 2: Critical Questions

> Status: **ANSWERED**

## Functional

| # | Question | Category | Impact | Status |
|---|----------|----------|--------|--------|
| Q1 | **Root users vs existing `admin_users` table.** The codebase already has an `admin_users` table (with `email`, `password`, 2FA fields). Should we: (a) Extend `admin_users` by adding the missing columns (`username`, `first_name`, `last_name`, `avatar`), or (b) Create an entirely separate `root_users` table? | Functional | High | Answered |
| | **Answer:** `admin_users` must be replaced by `root_users`, adding the needed fields. This is a rename + schema change, not just new columns. |
| Q2 | **What is `username` for?** Is it a unique login identifier (alternative to email), or just a display handle? Can it contain spaces? | Functional | Medium | Answered |
| | **Answer:** It is unique. It is NOT an alternative to email -- login remains with email. It is just a display handle. It cannot contain spaces. |
| Q3 | **Who creates the first root user?** Currently there's an `AdminUserSeeder`. Options: (a) Seeder creates first user pre-verified, (b) Artisan command, (c) Public registration endpoint. | Functional | High | Answered |
| | **Answer:** No public registration endpoint. Option (a) -- seeder creates the first user pre-verified (bypassing email verification). |
| Q4 | **Who creates new root users?** Does an existing root user invite/create them, or is there self-registration? | Functional | High | Answered |
| | **Answer:** An existing root user invites/creates them. NO self-registration. |
| Q5 | **Email verification flow.** Does the verification link redirect to the React frontend or directly to a backend route? Token expiration time? "Resend verification email" feature? | Functional | Medium | Answered |
| | **Answer:** Verification link redirects to a mandatory set-password frontend route. Token expiration: 24 hours. Yes, resend verification email feature is required. |
| Q6 | **Password assignment.** The task doesn't mention `password`. How is it set? (a) Creating user sets a temp password, (b) New user receives a "set password" link in the verification email, (c) Both. | Functional | High | Answered |
| | **Answer:** Option (b) -- new user receives a "set password" link in the verification email. No temporary password is set at creation time. |

## Business

| # | Question | Category | Impact | Status |
|---|----------|----------|--------|--------|
| Q7 | **Can a root user delete themselves?** Should deleting the last root user be prevented? | Business | High | Answered |
| | **Answer:** No, users cannot delete themselves. (Implied: the last root user cannot be deleted since they cannot self-delete and there is no one else to delete them, but an explicit guard should still be in place.) |
| Q8 | **Is there a concept of "deactivating" a root user** (disabling access without deleting)? | Business | Medium | Answered |
| | **Answer:** Yes, deactivation is required. |
| Q9 | **Avatar constraints.** Max file size? Accepted formats? Storage (local vs S3)? Default avatar? | Business | Low | Answered |
| | **Answer:** 500KB-1MB max. Accepted: WebP, PNG, JPG. Ratio must be 1:1 (square). Storage location not specified -- default to local Laravel storage for now. |

## Technical

| # | Question | Category | Impact | Status |
|---|----------|----------|--------|--------|
| Q10 | **Audit log scope.** Which actions to log? Only CRUD on users/tenants, or also login/logout, 2FA events? What data per entry? Filtering/pagination on the logs endpoint? | Technical | High | Answered |
| | **Answer:** Log CRUD operations on root users and tenants, AND login/logout events. Yes, filtering and pagination are required on the logs endpoint. |
| Q11 | **Log failed actions too?** (failed logins, validation errors, etc.) | Technical | Medium | Answered |
| | **Answer:** No, do not log failed actions. Only successful actions. |

## UX / Frontend

| # | Question | Category | Impact | Status |
|---|----------|----------|--------|--------|
| Q12 | **Frontend scope.** (a) Backend only? (b) Backend + frontend? (c) Backend + frontend + Audit Logs page? | UX | High | Answered |
| | **Answer:** Backend only. |
| Q13 | **Show verification/2FA status** in the users table? (badges like "Email Verified", "2FA Enabled") | UX | Low | Answered |
| | **Answer:** Yes, the API response must include verification and 2FA status fields so the frontend can display them. |
