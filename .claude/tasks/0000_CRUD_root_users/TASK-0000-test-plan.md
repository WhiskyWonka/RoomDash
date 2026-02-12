# Test Plan - TASK-0000

## Executive Summary

- **Task:** CRUD Root Users with Email Verification, 2FA, Audit Logging
- **Total tests:** 80
- **Unit tests:** 26
- **Integration tests:** 18
- **Feature tests:** 36
- **Estimation:** 12 hours
- **Coverage projected:** Domain 96%, Application 91%, Infrastructure 83%

---

## Acceptance Criteria Mapped

### Scenario 1: Create a new root user (invitation)
**Tests that cover it:**
- `RootUserTest::it_creates_root_user_with_valid_data` (Unit)
- `CreateRootUserUseCaseTest::it_creates_root_user_and_sends_verification_email` (Integration)
- `RootUserControllerTest::it_returns_201_when_creating_root_user_with_valid_data` (Feature)
- `RootUserControllerTest::it_records_audit_log_when_root_user_created` (Feature)

### Scenario 2: Create root user with duplicate email
**Tests that cover it:**
- `RootUserControllerTest::it_returns_422_when_email_already_exists` (Feature)

### Scenario 3: Create root user with duplicate username
**Tests that cover it:**
- `RootUserControllerTest::it_returns_422_when_username_already_exists` (Feature)

### Scenario 4: Create root user with invalid username (spaces)
**Tests that cover it:**
- `UsernameTest::it_throws_exception_when_username_contains_spaces` (Unit)
- `RootUserControllerTest::it_returns_422_when_username_contains_spaces` (Feature)

### Scenario 5: List all root users
**Tests that cover it:**
- `RootUserControllerTest::it_returns_200_with_paginated_list_of_root_users` (Feature)
- `RootUserControllerTest::it_returns_correct_fields_in_root_user_list` (Feature)

### Scenario 6: Get a single root user
**Tests that cover it:**
- `RootUserControllerTest::it_returns_200_with_root_user_data` (Feature)
- `RootUserControllerTest::it_returns_404_when_root_user_not_found` (Feature)

### Scenario 7: Update a root user
**Tests that cover it:**
- `RootUserControllerTest::it_returns_200_when_updating_root_user_with_valid_data` (Feature)
- `RootUserControllerTest::it_records_audit_log_when_root_user_updated` (Feature)

### Scenario 8: Delete a root user
**Tests that cover it:**
- `RootUserControllerTest::it_returns_204_when_deleting_another_root_user` (Feature)
- `RootUserControllerTest::it_records_audit_log_when_root_user_deleted` (Feature)

### Scenario 9: Root user cannot delete themselves
**Tests that cover it:**
- `RootUserTest::it_prevents_self_deletion` (Unit)
- `RootUserControllerTest::it_returns_403_when_deleting_own_account` (Feature)

### Scenario 10: Cannot delete the last active root user
**Tests that cover it:**
- `RootUserTest::it_prevents_deletion_of_last_active_root_user` (Unit)
- `RootUserControllerTest::it_returns_409_when_deleting_last_active_root_user` (Feature)

### Scenario 11: Deactivate a root user
**Tests that cover it:**
- `RootUserControllerTest::it_returns_200_when_deactivating_root_user` (Feature)
- `RootUserControllerTest::it_records_audit_log_when_root_user_deactivated` (Feature)

### Scenario 12: Reactivate a root user
**Tests that cover it:**
- `RootUserControllerTest::it_returns_200_when_activating_root_user` (Feature)
- `RootUserControllerTest::it_records_audit_log_when_root_user_activated` (Feature)

### Scenario 13: Cannot deactivate the last active root user
**Tests that cover it:**
- `RootUserControllerTest::it_returns_409_when_deactivating_last_active_root_user` (Feature)

### Scenario 14: Verify email and set password
**Tests that cover it:**
- `EmailVerificationTokenTest::it_creates_valid_token` (Unit)
- `EmailVerificationTokenTest::it_detects_expired_token` (Unit)
- `VerifyEmailControllerTest::it_returns_200_when_verifying_email_with_valid_token` (Feature)
- `VerifyEmailControllerTest::it_consumes_token_after_successful_verification` (Feature)

### Scenario 15: Expired verification token
**Tests that cover it:**
- `VerifyEmailControllerTest::it_returns_400_when_verification_token_is_expired` (Feature)

### Scenario 16: Resend verification email
**Tests that cover it:**
- `RootUserControllerTest::it_returns_200_when_resending_verification_email` (Feature)
- `RootUserControllerTest::it_invalidates_previous_tokens_when_resending_verification` (Feature)

### Scenario 17: Resend verification for already-verified user
**Tests that cover it:**
- `RootUserControllerTest::it_returns_409_when_resending_verification_for_already_verified_user` (Feature)

### Scenario 18: Login by unverified user
**Tests that cover it:**
- `LoginControllerTest::it_returns_403_with_EMAIL_NOT_VERIFIED_when_user_is_not_verified` (Feature)

### Scenario 19: Login by deactivated user
**Tests that cover it:**
- `LoginControllerTest::it_returns_403_with_ACCOUNT_DEACTIVATED_when_user_is_deactivated` (Feature)

### Scenario 20: Successful login creates audit log
**Tests that cover it:**
- `LoginControllerTest::it_records_audit_log_on_successful_login_after_2fa` (Feature)

### Scenario 21: Logout creates audit log
**Tests that cover it:**
- `LoginControllerTest::it_records_audit_log_on_logout` (Feature)

### Scenario 22: Upload avatar
**Tests that cover it:**
- `AvatarTest::it_validates_avatar_file_is_square` (Unit)
- `RootUserControllerTest::it_returns_200_when_uploading_valid_avatar` (Feature)
- `RootUserControllerTest::it_records_audit_log_when_avatar_uploaded` (Feature)

### Scenario 23: Upload avatar exceeding size
**Tests that cover it:**
- `RootUserControllerTest::it_returns_422_when_avatar_exceeds_1mb` (Feature)

### Scenario 24: Upload avatar with invalid format
**Tests that cover it:**
- `RootUserControllerTest::it_returns_422_when_avatar_format_is_invalid` (Feature)

### Scenario 25: Delete avatar
**Tests that cover it:**
- `RootUserControllerTest::it_returns_200_when_deleting_avatar` (Feature)

### Scenarios 26-30: Audit log filtering
**Tests that cover them:**
- `AuditLogControllerTest::it_returns_200_with_paginated_audit_logs` (Feature)
- `AuditLogControllerTest::it_returns_audit_logs_filtered_by_user_id` (Feature)
- `AuditLogControllerTest::it_returns_audit_logs_filtered_by_action` (Feature)
- `AuditLogControllerTest::it_returns_audit_logs_filtered_by_date_range` (Feature)
- `AuditLogControllerTest::it_returns_audit_logs_filtered_by_entity_type` (Feature)

### Scenario 31: Audit logs are immutable
**Tests that cover it:**
- `AuditLogControllerTest::it_returns_405_for_put_on_audit_logs` (Feature)
- `AuditLogControllerTest::it_returns_405_for_patch_on_audit_logs` (Feature)
- `AuditLogControllerTest::it_returns_405_for_delete_on_audit_logs` (Feature)

### Scenario 32: Unauthenticated access
**Tests that cover it:**
- `RootUserControllerTest::it_returns_401_when_unauthenticated` (Feature)
- `AuditLogControllerTest::it_returns_401_when_unauthenticated` (Feature)

### Scenario 33: 2FA not verified
**Tests that cover it:**
- `RootUserControllerTest::it_returns_403_with_2FA_REQUIRED_when_2fa_not_verified` (Feature)
- `AuditLogControllerTest::it_returns_403_with_2FA_REQUIRED_when_2fa_not_verified` (Feature)

---

## Complete Test List

### Unit Tests (Domain Layer)

#### Entities
- [ ] **Critical** - `RootUserTest::it_creates_root_user_with_valid_data`
- [ ] **Critical** - `RootUserTest::it_serializes_root_user_to_json_correctly`
- [ ] **Critical** - `RootUserTest::it_prevents_self_deletion`
- [ ] **High** - `RootUserTest::it_prevents_deletion_of_last_active_root_user`
- [ ] **High** - `RootUserTest::it_detects_active_root_user`
- [ ] **High** - `RootUserTest::it_detects_inactive_root_user`
- [ ] **Medium** - `RootUserTest::it_detects_unverified_email`
- [ ] **Medium** - `RootUserTest::it_detects_verified_email`

#### Value Objects
- [ ] **Critical** - `UsernameTest::it_creates_valid_username`
- [ ] **Critical** - `UsernameTest::it_throws_exception_when_username_contains_spaces`
- [ ] **Critical** - `UsernameTest::it_throws_exception_when_username_is_empty`
- [ ] **High** - `UsernameTest::it_throws_exception_when_username_has_invalid_characters`
- [ ] **Medium** - `UsernameTest::it_allows_username_with_underscores_and_hyphens`
- [ ] **Critical** - `EmailVerificationTokenTest::it_creates_valid_token`
- [ ] **Critical** - `EmailVerificationTokenTest::it_detects_expired_token`
- [ ] **High** - `EmailVerificationTokenTest::it_detects_non_expired_token`

#### Domain Entities - AuditLog
- [ ] **Critical** - `AuditLogTest::it_creates_audit_log_entry_with_required_fields`
- [ ] **High** - `AuditLogTest::it_serializes_audit_log_to_json_correctly`
- [ ] **Medium** - `AuditLogTest::it_creates_audit_log_with_old_and_new_values`
- [ ] **Medium** - `AuditLogTest::it_truncates_user_agent_to_500_characters`

#### Domain Services
- [ ] **High** - `LastActiveUserGuardTest::it_allows_deletion_when_multiple_active_users_exist`
- [ ] **High** - `LastActiveUserGuardTest::it_prevents_deletion_when_only_one_active_user_exists`
- [ ] **High** - `LastActiveUserGuardTest::it_allows_deactivation_when_multiple_active_users_exist`
- [ ] **High** - `LastActiveUserGuardTest::it_prevents_deactivation_when_only_one_active_user_exists`

**Subtotal Unit Tests:** 26

---

### Integration Tests (Application Layer)

#### Use Cases - RootUser
- [ ] **Critical** - `CreateRootUserUseCaseTest::it_creates_root_user_and_sends_verification_email`
- [ ] **Critical** - `CreateRootUserUseCaseTest::it_throws_exception_when_email_already_exists`
- [ ] **Critical** - `CreateRootUserUseCaseTest::it_throws_exception_when_username_already_exists`
- [ ] **High** - `CreateRootUserUseCaseTest::it_records_audit_log_entry_on_creation`
- [ ] **High** - `UpdateRootUserUseCaseTest::it_updates_root_user_fields`
- [ ] **High** - `UpdateRootUserUseCaseTest::it_triggers_reverification_when_email_changes`
- [ ] **High** - `UpdateRootUserUseCaseTest::it_records_audit_log_entry_with_old_and_new_values_on_update`
- [ ] **Critical** - `DeleteRootUserUseCaseTest::it_deletes_root_user_successfully`
- [ ] **Critical** - `DeleteRootUserUseCaseTest::it_throws_exception_when_deleting_own_account`
- [ ] **High** - `DeleteRootUserUseCaseTest::it_records_audit_log_entry_on_deletion`

#### Use Cases - Email Verification
- [ ] **Critical** - `VerifyEmailUseCaseTest::it_sets_email_verified_at_and_password_on_valid_token`
- [ ] **Critical** - `VerifyEmailUseCaseTest::it_consumes_token_after_verification`
- [ ] **Critical** - `VerifyEmailUseCaseTest::it_throws_exception_for_expired_token`
- [ ] **High** - `ResendVerificationUseCaseTest::it_invalidates_previous_tokens_and_sends_new_email`
- [ ] **High** - `ResendVerificationUseCaseTest::it_throws_exception_when_user_already_verified`

#### Use Cases - AuditLog
- [ ] **High** - `RecordAuditLogUseCaseTest::it_records_audit_log_with_correct_data`
- [ ] **High** - `ListAuditLogsUseCaseTest::it_returns_paginated_results_sorted_by_newest_first`
- [ ] **Medium** - `ListAuditLogsUseCaseTest::it_filters_by_user_id_action_entity_type_and_date_range`

**Subtotal Integration Tests:** 18

---

### Feature Tests (Infrastructure Layer)

#### Root User CRUD
- [ ] **Critical** - `RootUserControllerTest::it_returns_401_when_unauthenticated`
- [ ] **Critical** - `RootUserControllerTest::it_returns_403_with_2FA_REQUIRED_when_2fa_not_verified`
- [ ] **Critical** - `RootUserControllerTest::it_returns_201_when_creating_root_user_with_valid_data`
- [ ] **Critical** - `RootUserControllerTest::it_returns_422_when_email_already_exists`
- [ ] **Critical** - `RootUserControllerTest::it_returns_422_when_username_already_exists`
- [ ] **Critical** - `RootUserControllerTest::it_returns_422_when_username_contains_spaces`
- [ ] **Critical** - `RootUserControllerTest::it_returns_422_when_required_fields_are_missing`
- [ ] **High** - `RootUserControllerTest::it_records_audit_log_when_root_user_created`
- [ ] **Critical** - `RootUserControllerTest::it_returns_200_with_paginated_list_of_root_users`
- [ ] **High** - `RootUserControllerTest::it_returns_correct_fields_in_root_user_list`
- [ ] **Critical** - `RootUserControllerTest::it_returns_200_with_root_user_data`
- [ ] **Critical** - `RootUserControllerTest::it_returns_404_when_root_user_not_found`
- [ ] **Critical** - `RootUserControllerTest::it_returns_200_when_updating_root_user_with_valid_data`
- [ ] **High** - `RootUserControllerTest::it_records_audit_log_when_root_user_updated`
- [ ] **Critical** - `RootUserControllerTest::it_returns_204_when_deleting_another_root_user`
- [ ] **Critical** - `RootUserControllerTest::it_returns_403_when_deleting_own_account`
- [ ] **Critical** - `RootUserControllerTest::it_returns_409_when_deleting_last_active_root_user`
- [ ] **High** - `RootUserControllerTest::it_records_audit_log_when_root_user_deleted`

#### Activation / Deactivation
- [ ] **High** - `RootUserControllerTest::it_returns_200_when_deactivating_root_user`
- [ ] **High** - `RootUserControllerTest::it_records_audit_log_when_root_user_deactivated`
- [ ] **High** - `RootUserControllerTest::it_returns_200_when_activating_root_user`
- [ ] **High** - `RootUserControllerTest::it_records_audit_log_when_root_user_activated`
- [ ] **Critical** - `RootUserControllerTest::it_returns_409_when_deactivating_last_active_root_user`

#### Resend Verification
- [ ] **High** - `RootUserControllerTest::it_returns_200_when_resending_verification_email`
- [ ] **High** - `RootUserControllerTest::it_invalidates_previous_tokens_when_resending_verification`
- [ ] **High** - `RootUserControllerTest::it_returns_409_when_resending_verification_for_already_verified_user`

#### Email Verification
- [ ] **Critical** - `VerifyEmailControllerTest::it_returns_200_when_verifying_email_with_valid_token`
- [ ] **Critical** - `VerifyEmailControllerTest::it_consumes_token_after_successful_verification`
- [ ] **Critical** - `VerifyEmailControllerTest::it_returns_400_when_verification_token_is_expired`
- [ ] **Critical** - `VerifyEmailControllerTest::it_returns_400_when_token_has_already_been_used`
- [ ] **Medium** - `VerifyEmailControllerTest::it_returns_422_when_password_is_too_short`

#### Login Flow Updates
- [ ] **Critical** - `LoginControllerTest::it_returns_403_with_EMAIL_NOT_VERIFIED_when_user_is_not_verified`
- [ ] **Critical** - `LoginControllerTest::it_returns_403_with_ACCOUNT_DEACTIVATED_when_user_is_deactivated`
- [ ] **High** - `LoginControllerTest::it_records_audit_log_on_successful_login_after_2fa`
- [ ] **High** - `LoginControllerTest::it_records_audit_log_on_logout`

#### Avatar
- [ ] **High** - `RootUserControllerTest::it_returns_200_when_uploading_valid_avatar`
- [ ] **High** - `RootUserControllerTest::it_records_audit_log_when_avatar_uploaded`
- [ ] **High** - `RootUserControllerTest::it_returns_422_when_avatar_exceeds_1mb`
- [ ] **High** - `RootUserControllerTest::it_returns_422_when_avatar_format_is_invalid`
- [ ] **Medium** - `RootUserControllerTest::it_returns_200_when_deleting_avatar`
- [ ] **Low** - `RootUserControllerTest::it_returns_404_when_uploading_avatar_for_non_existent_user`

#### Audit Logs
- [ ] **Critical** - `AuditLogControllerTest::it_returns_401_when_unauthenticated`
- [ ] **Critical** - `AuditLogControllerTest::it_returns_403_with_2FA_REQUIRED_when_2fa_not_verified`
- [ ] **Critical** - `AuditLogControllerTest::it_returns_200_with_paginated_audit_logs`
- [ ] **High** - `AuditLogControllerTest::it_returns_audit_logs_filtered_by_user_id`
- [ ] **High** - `AuditLogControllerTest::it_returns_audit_logs_filtered_by_action`
- [ ] **High** - `AuditLogControllerTest::it_returns_audit_logs_filtered_by_date_range`
- [ ] **High** - `AuditLogControllerTest::it_returns_audit_logs_filtered_by_entity_type`
- [ ] **Critical** - `AuditLogControllerTest::it_returns_200_for_get_single_audit_log`
- [ ] **Critical** - `AuditLogControllerTest::it_returns_404_when_audit_log_not_found`
- [ ] **Critical** - `AuditLogControllerTest::it_returns_405_for_put_on_audit_logs`
- [ ] **Critical** - `AuditLogControllerTest::it_returns_405_for_patch_on_audit_logs`
- [ ] **Critical** - `AuditLogControllerTest::it_returns_405_for_delete_on_audit_logs`

**Subtotal Feature Tests:** 36

---

## Dependency Map

```
UsernameTest (Unit) -- no dependencies
EmailVerificationTokenTest (Unit) -- no dependencies
AuditLogTest (Unit) -- no dependencies
    |
RootUserTest (Unit) -- depends on Username
LastActiveUserGuardTest (Unit) -- depends on RootUser
    |
CreateRootUserUseCaseTest (Integration) -- mocks UserRepository, EmailService
UpdateRootUserUseCaseTest (Integration) -- mocks UserRepository, AuditLogRepository
DeleteRootUserUseCaseTest (Integration) -- mocks UserRepository, AuditLogRepository
VerifyEmailUseCaseTest (Integration) -- mocks TokenRepository, UserRepository
ResendVerificationUseCaseTest (Integration) -- mocks TokenRepository, EmailService
RecordAuditLogUseCaseTest (Integration) -- mocks AuditLogRepository
ListAuditLogsUseCaseTest (Integration) -- mocks AuditLogRepository
    |
RootUserControllerTest (Feature) -- real DB, mocks Mail
VerifyEmailControllerTest (Feature) -- real DB
LoginControllerTest (Feature) -- real DB
AuditLogControllerTest (Feature) -- real DB
```

**Recommended execution order:**
1. Value Objects (Username, EmailVerificationToken)
2. Domain Entities (RootUser, AuditLog)
3. Domain Services (LastActiveUserGuard)
4. Use Cases (Create/Update/Delete/VerifyEmail/ResendVerification/AuditLog)
5. Controllers (RootUser, VerifyEmail, Login, AuditLog)

---

## Test Data Required

### Factories
- [ ] `RootUserFactory` -- create new factory for `root_users` table
- [ ] `EmailVerificationTokenFactory` -- create new
- [ ] `AuditLogFactory` -- create new

### Builders
- [ ] `RootUserBuilder` -- for domain entity tests
- [ ] `AuditLogBuilder` -- for domain entity tests

---

## Priority Summary

### Critical (required for deployment)
- All entity creation tests
- Authentication flow tests (unverified, deactivated)
- Self-deletion and last-active-user guards
- Token expiry and consumption
- Immutability of audit logs
- Authorization (401/403)

### High (core functionality)
- All audit log recording tests
- Deactivation/activation flows
- Email change re-verification trigger
- Avatar upload/delete

### Medium (validations and edge cases)
- Password minimum length validation
- Username character validation
- Avatar aspect ratio validation

### Low (edge cases)
- Avatar upload for non-existent user

---

## Time Estimate

| Layer | Tests | Estimated Time |
|-------|-------|----------------|
| Unit | 26 | 3 hours |
| Integration | 18 | 4 hours |
| Feature | 36 | 5 hours |
| Builders/Factories | - | 1 hour |
| **TOTAL** | **80** | **13 hours** |

---

## Projected Coverage

| Layer | Projected | Target | Status |
|-------|-----------|--------|--------|
| Domain | 96% | >= 95% | OK |
| Application | 91% | >= 90% | OK |
| Infrastructure | 83% | >= 80% | OK |

---

## Risks and Considerations

- The existing `AdminUser` model/entity will be referenced in existing tests. The new `RootUser` tests assume the rename is done (or coexist until then). New test files use `RootUser` naming exclusively.
- Feature tests for Login use `RefreshDatabase` and need the new `root_users` table with `username`, `first_name`, `last_name`, `is_active`, `email_verified_at` columns.
- Mail must be faked (`Mail::fake()`) in feature tests involving invitation emails.
- Storage must be faked (`Storage::fake()`) in avatar upload tests.

---

## Approval

- [ ] Test plan reviewed
- [ ] Priorities agreed
- [ ] Estimate approved
- [ ] Test data identified

**Approval date:** ___________
**Approved by:** ___________
