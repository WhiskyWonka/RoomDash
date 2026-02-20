# Functional Analyst Memory

## Project Architecture Patterns
- See [architecture.md](./architecture.md) for detailed patterns

## Key Decisions Log
- Root users replace admin_users (Task 0000) - new table, not ALTER
- Audit logs use 3-layer immutability: domain port (no write methods), Eloquent boot (throw on update/delete), PG trigger
- Invitation flow: token hashed with SHA-256 in DB, plain token in email
- All root user/audit log tables are central-schema only (not tenant)

## User Preferences
- Specs written in English (even though requirements arrive in Spanish)
- Decisions documented inline with [DECISION: ...] markers
- Assumptions labeled with [ASSUMPTION: ...]
