---
name: solid-plan
description: Plan new code or refactor existing code following SOLID principles. Use --dry-run to see the plan without executing, or --execute to implement the plan.
disable-model-invocation: true
argument-hint: [description] [--dry-run|--execute]
allowed-tools: Read, Grep, Glob, Task, Edit, Write, Bash
---

# SOLID Principles Code Planner

Plan and optionally implement code changes following SOLID principles.

## Arguments Received
- Description/Task: $ARGUMENTS

## Mode Detection
Check the arguments for the mode flag:
- If `--dry-run` is present: Only create and display the plan, do NOT implement any changes
- If `--execute` is present: Create the plan AND implement the changes
- If neither flag is present: Ask the user which mode they want

## SOLID Principles to Apply

### S - Single Responsibility Principle (SRP)
- Each class/module should have only ONE reason to change
- Identify distinct responsibilities and separate them into different classes
- Services should focus on a single domain concern

### O - Open/Closed Principle (OCP)
- Code should be open for extension, closed for modification
- Use interfaces and abstract classes to allow new behavior without changing existing code
- Prefer composition and strategy patterns over conditionals

### L - Liskov Substitution Principle (LSP)
- Subtypes must be substitutable for their base types
- Derived classes should not break the behavior expected from the base class
- Avoid throwing unexpected exceptions in subclasses

### I - Interface Segregation Principle (ISP)
- Clients should not depend on interfaces they don't use
- Prefer many small, specific interfaces over one large interface
- Split fat interfaces into role-specific ones

### D - Dependency Inversion Principle (DIP)
- Depend on abstractions, not concretions
- High-level modules should not depend on low-level modules
- Use dependency injection and service containers

## Planning Process

### Step 1: Understand the Context
1. Read existing related code in the codebase
2. Identify the domain/module where changes will be made
3. Understand current architecture patterns in use

### Step 2: Create the Implementation Plan
Structure your plan as follows:

```markdown
## SOLID Implementation Plan

### Summary
Brief description of what will be implemented

### SOLID Analysis
For each principle, explain how it applies:
- **SRP**: [How responsibilities are separated]
- **OCP**: [How code is extensible]
- **LSP**: [How substitution is maintained]
- **ISP**: [How interfaces are segregated]
- **DIP**: [How dependencies are inverted]

### Files to Create/Modify
List each file with:
- Path
- Purpose
- Key classes/methods

### Class Diagram (if applicable)
```
Interface -> ConcreteImplementation
Service -> Repository (via interface)
```

### Implementation Steps
1. Step 1: [Description]
2. Step 2: [Description]
...

### Testing Strategy
- Unit tests for each class
- Integration tests for service interactions
```

### Step 3: Mode Execution

**If --dry-run mode:**
- Display the complete plan
- Ask if the user wants to proceed with execution
- Do NOT create or modify any files

**If --execute mode:**
- Display the plan first
- Implement each step sequentially
- Create necessary interfaces, classes, and tests
- Follow Laravel/PHP conventions for this project:
  - Services in `app/Services/`
  - Models in `app/Models/`
  - Interfaces in `app/Contracts/` or within the service directory
  - Controllers in `app/Http/Controllers/`
  - Tests in `tests/Unit/` and `tests/Feature/`

## Project-Specific Guidelines (BPlay PAM)

When planning for this Laravel codebase:
- Use Laravel's service container for DI
- Follow existing patterns in `app/Services/` for domain organization
- Use Eloquent models with repository pattern when appropriate
- API responses should use `app/Http/Resources/` transformers
- Queue jobs go in `app/Jobs/`
- Events/Listeners for decoupled communication

## Output Format

Always output the plan in a clear, structured format that can be reviewed before execution.
