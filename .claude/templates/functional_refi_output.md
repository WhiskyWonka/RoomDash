<!-- The agent generates this automatically -->

# [TASK-XXX] Refined task name

## 📋 Executive Summary
> One line explaining what this task does

**Status:** In refinement / Refined / Blocked
**Estimation:** X points
**Priority:** High / Medium / Low
**Affected modules:** [list]

---

## 🎯 Objective
Clear and unambiguous description of the objective.

---

## 👤 Actors
- **Actor 1**: Role and main action
- **Actor 2**: Role and main action

---

## ❓ Open Questions
<!-- The agent generates them, you answer them -->
| # | Question | Category | Impact | Status |
|---|----------|----------|--------|--------|
| 1 | ...? | Functional | High | Pending |
| 2 | ...? | Business | Medium | Answered: ... |

---

## ✅ Acceptance Criteria

### Scenario 1: [Main scenario name]
```gherkin
Given [initial context]
When [the actor performs the action]
Then [expected result]
And [additional result]
```

### Scenario 2: [Error case]
```gherkin
Given [context]
When [invalid action]
Then [expected error message]
```

---

## 🔌 Technical Specification

### Endpoints

#### POST /api/v1/resource
**Description:** Create new resource

**Request:**
```json
{
  "field": "type - description - required/optional",
  "other_field": "type - description"
}
```

**Response 201:**
```json
{
  "data": {
    "id": "integer",
    "field": "string"
  }
}
```

**Errors:**
| Code | Cause |
|------|-------|
| 422 | Validation failed |
| 409 | Conflict (duplicate) |
| 403 | No permissions |

---

### Database

#### New table: table_name
| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | bigint PK | No | Auto-increment |
| field | varchar(255) | No | Description |
| tenant_id | bigint FK | No | Multi-tenant |
| created_at | timestamp | No | |

**Indexes:**
- `idx_table_field` on (tenant_id, field)

#### Modifications to existing table:
- Add column `field` type `varchar(100)` nullable

---

### Business Rules
1. **BR-001**: [Name] - Clear description of the rule
2. **BR-002**: [Name] - Clear description of the rule

---

### Edge Cases
- What happens if [extreme situation]?
- What happens if [unexpected condition]?

---

## 🧪 Testing Scenarios
- [ ] Happy path: [description]
- [ ] Validation error: [description]
- [ ] No permissions: [description]
- [ ] [Edge case]: [description]

---

## 📦 Technical Subtasks
<!-- The agent proposes how to divide the work -->

| # | Description | Layer | Estimation |
|---|-------------|-------|------------|
| 1 | Create migration for table X | Infrastructure | 1pt |
| 2 | Create domain entity | Domain | 1pt |
| 3 | Implement use case | Application | 2pt |
| 4 | Create controller and endpoint | Infrastructure | 1pt |
| 5 | Unit tests | - | 2pt |

---

## ⚠️ Risks and Impact
- **Impact on module X**: Describe what could break
- **Technical risk**: Describe uncertainty
- **Blocking dependencies**: What must be ready first
