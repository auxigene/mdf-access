# Project-Level Permissions - Implementation Plan

## Executive Summary

**Objective:** Implement a hierarchical, context-aware role-based access control (RBAC) system that enables users to have different roles and permissions across different scopes (projects, WBS elements, tasks).

**Business Value:**
- **Flexibility:** Users can be Project Manager on one project, Technician on another
- **Security:** Principle of least privilege - users get only the access they need
- **Compliance:** Granular audit trail of who had access to what and when
- **Scalability:** Support for large, complex projects with hundreds of team members

**Scope:**
- **Phase 1-3 (MVP):** Project-level permissions (covers 80% of use cases)
- **Phase 4-5 (Optional):** WBS element and task-level permissions (for advanced scenarios)

**Timeline:** 13 weeks (MVP) or 18 weeks (Full system with granular scopes)

**Team:** 2-3 developers, 1 QA engineer, 1 product owner (part-time)

---

## Current State → Target State

### Current State
- ✅ Database schema exists (`user_roles` table with scope fields)
- ✅ Roles have scope enum (global, organization, project)
- ✅ `User::hasPermission()` accepts scope parameter
- ❌ No hierarchical permission resolution
- ❌ No project team management UI/API
- ❌ Policies don't use project context
- ❌ No permission caching

### Target State (MVP - Project-Level)
- ✅ Hierarchical permission resolution (global → org → portfolio → program → project)
- ✅ Project team management (assign users to projects with roles)
- ✅ Context-aware policies and middleware
- ✅ Permission caching for performance
- ✅ API endpoints for team management
- ✅ Vue.js UI for managing project teams
- ✅ Comprehensive test coverage

### Target State (Full - With Task/WBS)
- All of MVP plus:
- ✅ Task-level role assignments
- ✅ WBS element-level role assignments
- ✅ Extended hierarchical resolution (7 levels)
- ✅ Fine-grained access control for contractors/subcontractors

---

## Implementation Phases

### 📋 Phase 0: Planning & Setup (Week 1)

**Goal:** Prepare the team and environment for implementation

**Tasks:**
| # | Task | Owner | Effort | Status |
|---|------|-------|--------|--------|
| 0.1 | Stakeholder review and approval of plan | Product Owner | 0.5d | ⏳ |
| 0.2 | Answer clarification questions (see section below) | Product Owner | 0.5d | ⏳ |
| 0.3 | Set up feature branch and development environment | Tech Lead | 0.5d | ⏳ |
| 0.4 | Create Jira/Linear tickets for all tasks | Tech Lead | 1d | ⏳ |
| 0.5 | Database backup and staging environment setup | DevOps | 1d | ⏳ |
| 0.6 | Team kickoff meeting and architecture walkthrough | Tech Lead | 0.5d | ⏳ |

**Deliverables:**
- ✅ Approved implementation plan
- ✅ Answered clarification questions
- ✅ Feature branch: `feature/project-permissions-mvp`
- ✅ Jira epic with all subtasks
- ✅ Development environment ready

**Acceptance Criteria:**
- [ ] All stakeholders have reviewed and approved the plan
- [ ] All clarification questions answered
- [ ] Development environment can run migrations and tests

---

### 🗄️ Phase 1: Database Foundation (Week 2-3)

**Goal:** Create database schema for project teams and permission caching

**Sprint 1.1: Migrations (Week 2, Days 1-3)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 1.1.1 | Create `project_teams` migration | Dev 1 | 1d | - |
| 1.1.2 | Add permission cache columns to `users` | Dev 1 | 0.5d | - |
| 1.1.3 | Update `user_roles` table (add WBS/task FKs) | Dev 2 | 1d | - |
| 1.1.4 | Expand `roles.scope` enum | Dev 2 | 0.5d | - |
| 1.1.5 | Run migrations on staging | Dev 1 | 0.5d | 1.1.1-1.1.4 |
| 1.1.6 | Verify schema with SQL queries | QA | 0.5d | 1.1.5 |

**Sprint 1.2: Seeders (Week 2, Days 4-5)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 1.2.1 | Create project roles seeder | Dev 1 | 1d | 1.1.5 |
| 1.2.2 | Create sample project teams seeder (dev only) | Dev 2 | 1d | 1.1.5 |
| 1.2.3 | Test seeders on fresh database | QA | 0.5d | 1.2.1-1.2.2 |

**Sprint 1.3: Data Migration (Week 3, Days 1-2)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 1.3.1 | Write migration script for existing project managers | Dev 1 | 1d | 1.2.1 |
| 1.3.2 | Test migration on staging with production data copy | Dev 1 | 1d | 1.3.1 |
| 1.3.3 | Create rollback script | Dev 2 | 0.5d | 1.3.1 |

**Deliverables:**
- ✅ `project_teams` table created
- ✅ `users` table has cache columns
- ✅ `user_roles` table supports task/WBS scopes
- ✅ `roles.scope` enum expanded to 7 levels
- ✅ Project roles seeded (PM, Tech Lead, Technician, Observer, etc.)
- ✅ Migration script for existing data
- ✅ Rollback plan documented

**Acceptance Criteria:**
- [ ] All migrations run without errors
- [ ] Seeders create expected roles with correct permissions
- [ ] Can assign project manager from existing data
- [ ] Database diagram updated

**Risks & Mitigation:**
- **Risk:** Foreign key constraints fail on existing data
  - **Mitigation:** Test migration on production data copy first
- **Risk:** Enum expansion breaks existing code
  - **Mitigation:** Search codebase for hardcoded scope values

---

### 🏗️ Phase 2: Model Layer (Week 3-4)

**Goal:** Create models with validation and business logic

**Sprint 2.1: ProjectTeam Model (Week 3, Days 3-5)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 2.1.1 | Create `ProjectTeam` model with relationships | Dev 1 | 1d | 1.1.5 |
| 2.1.2 | Implement validation methods | Dev 1 | 1d | 2.1.1 |
| 2.1.3 | Implement business logic methods | Dev 1 | 1d | 2.1.2 |
| 2.1.4 | Create scopes for common queries | Dev 2 | 0.5d | 2.1.1 |
| 2.1.5 | Write unit tests for ProjectTeam | Dev 2 | 1d | 2.1.3 |

**Sprint 2.2: Enhanced User Model (Week 4, Days 1-2)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 2.2.1 | Add project team relationships to User | Dev 1 | 0.5d | 2.1.3 |
| 2.2.2 | Implement permission caching methods | Dev 1 | 1d | 2.2.1 |
| 2.2.3 | Implement `hasPermissionInContext()` | Dev 2 | 1d | 2.2.2 |
| 2.2.4 | Write unit tests for User enhancements | Dev 2 | 1d | 2.2.3 |

**Sprint 2.3: Enhanced Project & Role Models (Week 4, Days 3-5)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 2.3.1 | Add team relationships to Project model | Dev 1 | 0.5d | 2.1.3 |
| 2.3.2 | Implement team management methods | Dev 1 | 1d | 2.3.1 |
| 2.3.3 | Add scope checking methods to Role model | Dev 2 | 1d | - |
| 2.3.4 | Write unit tests for Project & Role | Dev 2 | 1d | 2.3.2-2.3.3 |

**Deliverables:**
- ✅ `ProjectTeam` model with full validation
- ✅ `User` model with permission caching and context checking
- ✅ `Project` model with team management
- ✅ `Role` model with scope helpers
- ✅ 100+ unit tests passing

**Acceptance Criteria:**
- [ ] All model methods work as documented
- [ ] Validation prevents invalid assignments
- [ ] Can assign/remove users from projects via Tinker
- [ ] Permission cache works and invalidates correctly
- [ ] Test coverage > 90%

**Risks & Mitigation:**
- **Risk:** Permission caching logic too complex
  - **Mitigation:** Start simple, optimize later based on profiling
- **Risk:** Validation too strict, blocks legitimate use cases
  - **Mitigation:** Review with product owner during implementation

---

### ⚙️ Phase 3: Service Layer (Week 5-6)

**Goal:** Centralize permission resolution and access control logic

**Sprint 3.1: PermissionResolver Service (Week 5, Days 1-3)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 3.1.1 | Create PermissionResolver service | Dev 1 | 1d | 2.2.3 |
| 3.1.2 | Implement hierarchical resolution algorithm | Dev 1 | 1.5d | 3.1.1 |
| 3.1.3 | Create PermissionResolution DTO | Dev 2 | 0.5d | 3.1.1 |
| 3.1.4 | Write integration tests | Dev 2 | 1d | 3.1.2 |

**Sprint 3.2: ProjectAccessManager Service (Week 5, Days 4-5)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 3.2.1 | Create ProjectAccessManager service | Dev 1 | 1d | 3.1.2 |
| 3.2.2 | Implement team management authorization | Dev 1 | 1d | 3.2.1 |
| 3.2.3 | Write integration tests | Dev 2 | 1d | 3.2.2 |

**Sprint 3.3: RoleHierarchyResolver Service (Week 6, Days 1-2)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 3.3.1 | Create RoleHierarchyResolver service | Dev 2 | 1d | 3.1.2 |
| 3.3.2 | Implement context hierarchy resolution | Dev 2 | 1d | 3.3.1 |
| 3.3.3 | Write integration tests | Dev 1 | 1d | 3.3.2 |

**Sprint 3.4: Service Provider & Integration (Week 6, Days 3-5)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 3.4.1 | Create service provider and register services | Dev 1 | 0.5d | 3.1-3.3 |
| 3.4.2 | Update User model to use services | Dev 1 | 1d | 3.4.1 |
| 3.4.3 | End-to-end integration tests | Dev 2 | 1.5d | 3.4.2 |
| 3.4.4 | Performance profiling and optimization | Dev 1 | 1d | 3.4.3 |

**Deliverables:**
- ✅ `PermissionResolver` service with hierarchical logic
- ✅ `ProjectAccessManager` service for access control
- ✅ `RoleHierarchyResolver` service for context hierarchy
- ✅ Services registered in container
- ✅ Integration tests passing
- ✅ Performance benchmarks documented

**Acceptance Criteria:**
- [ ] Permission resolution works for all 7 scope levels
- [ ] Context hierarchy resolves correctly (Task → WBS → Project → Program → Portfolio)
- [ ] Permission check < 50ms (p90) with cache
- [ ] Integration tests cover all permission scenarios
- [ ] Test coverage > 85%

**Risks & Mitigation:**
- **Risk:** Performance issues with hierarchical checks
  - **Mitigation:** Implement caching early, profile frequently
- **Risk:** Complex logic hard to debug
  - **Mitigation:** PermissionResolution DTO includes audit trail

---

### 🛡️ Phase 4: Policies & Middleware (Week 7-8)

**Goal:** Update policies and middleware to use context-aware permissions

**Sprint 4.1: Base Policy & ProjectPolicy (Week 7, Days 1-3)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 4.1.1 | Create `BaseProjectAwarePolicy` | Dev 1 | 1d | 3.4.2 |
| 4.1.2 | Refactor `ProjectPolicy` | Dev 1 | 1d | 4.1.1 |
| 4.1.3 | Write policy tests | Dev 2 | 1d | 4.1.2 |

**Sprint 4.2: Resource Policies (Week 7, Days 4-5)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 4.2.1 | Refactor TaskPolicy | Dev 1 | 0.5d | 4.1.1 |
| 4.2.2 | Refactor BudgetPolicy | Dev 1 | 0.5d | 4.1.1 |
| 4.2.3 | Refactor DeliverablePolicy | Dev 2 | 0.5d | 4.1.1 |
| 4.2.4 | Refactor RiskPolicy | Dev 2 | 0.5d | 4.1.1 |
| 4.2.5 | Refactor IssuePolicy | Dev 1 | 0.5d | 4.1.1 |
| 4.2.6 | Write policy tests for all | Dev 2 | 1d | 4.2.1-4.2.5 |

**Sprint 4.3: Middleware (Week 8, Days 1-3)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 4.3.1 | Create `CheckProjectPermission` middleware | Dev 1 | 1d | 3.4.2 |
| 4.3.2 | Enhance existing `CheckPermission` middleware | Dev 1 | 1d | 4.3.1 |
| 4.3.3 | Register middleware in Kernel | Dev 1 | 0.5d | 4.3.2 |
| 4.3.4 | Write middleware tests | Dev 2 | 1.5d | 4.3.3 |

**Sprint 4.4: Route Protection (Week 8, Days 4-5)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 4.4.1 | Update project routes with middleware | Dev 1 | 1d | 4.3.3 |
| 4.4.2 | Update task routes with middleware | Dev 2 | 1d | 4.3.3 |
| 4.4.3 | Update other resource routes | Dev 1 | 1d | 4.3.3 |
| 4.4.4 | Integration tests for protected routes | Dev 2 | 1d | 4.4.1-4.4.3 |

**Deliverables:**
- ✅ `BaseProjectAwarePolicy` for all policies
- ✅ All resource policies refactored (Project, Task, Budget, Deliverable, Risk, Issue)
- ✅ `CheckProjectPermission` middleware
- ✅ Enhanced `CheckPermission` middleware
- ✅ All routes protected with context-aware middleware
- ✅ Policy and middleware tests passing

**Acceptance Criteria:**
- [ ] Policies check permissions in project context
- [ ] Middleware correctly resolves context from routes
- [ ] 403 errors show clear permission denial messages
- [ ] All existing functionality still works
- [ ] Test coverage > 85%

**Risks & Mitigation:**
- **Risk:** Breaking existing authorization
  - **Mitigation:** Feature flag to toggle new permission logic
- **Risk:** Performance regression on every request
  - **Mitigation:** Cache middleware results, profile middleware

---

### 🌐 Phase 5: API Layer (Week 9-10)

**Goal:** Create API endpoints for project team management

**Sprint 5.1: Controllers (Week 9, Days 1-3)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 5.1.1 | Create `ProjectTeamController` | Dev 1 | 1d | 4.3.3 |
| 5.1.2 | Implement index (list team) endpoint | Dev 1 | 0.5d | 5.1.1 |
| 5.1.3 | Implement store (add member) endpoint | Dev 1 | 0.5d | 5.1.1 |
| 5.1.4 | Implement update (change role) endpoint | Dev 2 | 0.5d | 5.1.1 |
| 5.1.5 | Implement destroy (remove member) endpoint | Dev 2 | 0.5d | 5.1.1 |
| 5.1.6 | Implement availableRoles endpoint | Dev 2 | 0.5d | 5.1.1 |

**Sprint 5.2: Form Requests & Validation (Week 9, Days 4-5)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 5.2.1 | Create `StoreProjectTeamRequest` | Dev 1 | 1d | 5.1.3 |
| 5.2.2 | Create `UpdateProjectTeamRequest` | Dev 1 | 1d | 5.1.4 |
| 5.2.3 | Write validation tests | Dev 2 | 1d | 5.2.1-5.2.2 |

**Sprint 5.3: API Resources (Week 10, Days 1-2)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 5.3.1 | Create `ProjectTeamResource` | Dev 1 | 0.5d | 5.1.1 |
| 5.3.2 | Update `ProjectResource` with team data | Dev 1 | 0.5d | 5.3.1 |
| 5.3.3 | Create `PermissionsController` for debugging | Dev 2 | 1d | 3.4.2 |

**Sprint 5.4: API Tests (Week 10, Days 3-5)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 5.4.1 | Write API tests for team management | Dev 1 | 1.5d | 5.1-5.3 |
| 5.4.2 | Write API tests for permissions endpoint | Dev 2 | 1d | 5.3.3 |
| 5.4.3 | API documentation (OpenAPI/Swagger) | Dev 2 | 0.5d | 5.1-5.3 |

**Deliverables:**
- ✅ `ProjectTeamController` with CRUD operations
- ✅ Form requests with validation
- ✅ API resources for JSON responses
- ✅ `PermissionsController` for debugging
- ✅ Routes registered in `routes/api.php`
- ✅ API tests passing
- ✅ API documentation

**Acceptance Criteria:**
- [ ] Can list project team members via API
- [ ] Can add/remove team members via API
- [ ] Can change member roles via API
- [ ] Validation prevents invalid assignments
- [ ] API returns proper HTTP status codes
- [ ] Test coverage > 90%

**Risks & Mitigation:**
- **Risk:** API performance issues with large teams
  - **Mitigation:** Implement pagination, eager loading
- **Risk:** Authorization bypassed in API
  - **Mitigation:** Test authorization for every endpoint

---

### 🎨 Phase 6: Frontend (Week 11-12)

**Goal:** Build Vue.js UI for project team management

**Sprint 6.1: Composables (Week 11, Days 1-2)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 6.1.1 | Create `usePermissions` composable | Frontend Dev | 1d | 5.4.1 |
| 6.1.2 | Create `useProjectTeam` composable | Frontend Dev | 1d | 5.4.1 |
| 6.1.3 | Write composable tests (Vitest) | Frontend Dev | 1d | 6.1.1-6.1.2 |

**Sprint 6.2: Components (Week 11, Days 3-5)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 6.2.1 | Create TeamManagement.vue component | Frontend Dev | 1.5d | 6.1.2 |
| 6.2.2 | Create AddTeamMemberModal.vue | Frontend Dev | 1d | 6.2.1 |
| 6.2.3 | Create TeamMemberRow.vue | Frontend Dev | 0.5d | 6.2.1 |
| 6.2.4 | Write component tests | Frontend Dev | 1d | 6.2.1-6.2.3 |

**Sprint 6.3: Directives & Guards (Week 12, Days 1-2)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 6.3.1 | Create v-permission directive | Frontend Dev | 1d | 6.1.1 |
| 6.3.2 | Update navigation guards | Frontend Dev | 1d | 6.1.1 |
| 6.3.3 | Write directive tests | Frontend Dev | 0.5d | 6.3.1 |

**Sprint 6.4: Integration & Polish (Week 12, Days 3-5)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 6.4.1 | Integrate team management in project view | Frontend Dev | 1d | 6.2.1 |
| 6.4.2 | Update navigation to use v-permission | Frontend Dev | 1d | 6.3.1 |
| 6.4.3 | UI/UX polish and responsive design | Frontend Dev | 1d | 6.4.1-6.4.2 |
| 6.4.4 | E2E tests (Cypress) | QA | 2d | 6.4.1-6.4.3 |

**Deliverables:**
- ✅ `usePermissions` and `useProjectTeam` composables
- ✅ Team management UI components
- ✅ `v-permission` directive
- ✅ Updated navigation guards
- ✅ Integration in project view
- ✅ E2E tests passing
- ✅ Responsive design

**Acceptance Criteria:**
- [ ] Project managers can add/remove team members via UI
- [ ] Team members see their role and permissions
- [ ] UI elements hidden/disabled based on permissions
- [ ] Works on mobile and desktop
- [ ] E2E tests cover critical workflows
- [ ] No console errors

**Risks & Mitigation:**
- **Risk:** UI performance issues with large teams
  - **Mitigation:** Virtual scrolling for large lists
- **Risk:** Permission checks slow down UI
  - **Mitigation:** Cache permissions in Vuex/Pinia

---

### 🚀 Phase 7: Testing & QA (Week 13)

**Goal:** Comprehensive testing and bug fixes

**Sprint 7.1: Testing (Week 13, Days 1-3)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 7.1.1 | Manual QA testing of all features | QA | 2d | 6.4.4 |
| 7.1.2 | Performance testing and profiling | Dev 1 | 1d | 6.4.4 |
| 7.1.3 | Security audit of permission checks | Tech Lead | 1d | 6.4.4 |
| 7.1.4 | Accessibility testing (WCAG 2.1) | QA | 0.5d | 6.4.3 |
| 7.1.5 | Create test report with bugs | QA | 0.5d | 7.1.1-7.1.4 |

**Sprint 7.2: Bug Fixes (Week 13, Days 4-5)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 7.2.1 | Fix critical bugs (P0) | Dev 1 & 2 | 2d | 7.1.5 |
| 7.2.2 | Fix high priority bugs (P1) | Dev 1 & 2 | 1d | 7.2.1 |
| 7.2.3 | Regression testing | QA | 1d | 7.2.2 |

**Deliverables:**
- ✅ QA test report
- ✅ Performance benchmarks
- ✅ Security audit report
- ✅ All P0 and P1 bugs fixed
- ✅ Regression tests passing

**Acceptance Criteria:**
- [ ] All critical bugs fixed
- [ ] Permission checks < 50ms (p90)
- [ ] No security vulnerabilities
- [ ] Accessibility score > 90
- [ ] Test coverage > 85%

---

### 📦 Phase 8: Deployment (Week 14)

**Goal:** Deploy MVP to production

**Sprint 8.1: Pre-Production (Week 14, Days 1-2)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 8.1.1 | Deploy to staging environment | DevOps | 0.5d | 7.2.3 |
| 8.1.2 | Run data migration on staging | DevOps | 0.5d | 8.1.1 |
| 8.1.3 | Smoke testing on staging | QA | 1d | 8.1.2 |
| 8.1.4 | User acceptance testing (UAT) | Product Owner | 1d | 8.1.3 |

**Sprint 8.2: Production Deployment (Week 14, Days 3-5)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 8.2.1 | Create production deployment plan | Tech Lead | 0.5d | 8.1.4 |
| 8.2.2 | Schedule maintenance window | Product Owner | - | 8.2.1 |
| 8.2.3 | Backup production database | DevOps | 0.5d | 8.2.2 |
| 8.2.4 | Deploy to production | DevOps | 1d | 8.2.3 |
| 8.2.5 | Run data migration on production | DevOps | 0.5d | 8.2.4 |
| 8.2.6 | Smoke testing on production | QA | 1d | 8.2.5 |
| 8.2.7 | Monitor for 48 hours | Tech Lead | - | 8.2.6 |

**Sprint 8.3: Documentation & Training (Week 14, Days 4-5)**

| # | Task | Owner | Effort | Dependencies |
|---|------|-------|--------|--------------|
| 8.3.1 | Update user documentation | Product Owner | 1d | 8.2.5 |
| 8.3.2 | Create training materials | Product Owner | 1d | 8.3.1 |
| 8.3.3 | Conduct user training session | Product Owner | 0.5d | 8.3.2 |

**Deliverables:**
- ✅ MVP deployed to production
- ✅ Data migration successful
- ✅ User documentation
- ✅ Training materials
- ✅ Users trained

**Acceptance Criteria:**
- [ ] All features working in production
- [ ] No critical issues in first 48 hours
- [ ] Users can manage project teams
- [ ] Performance meets benchmarks

---

## 🎯 Optional: Extended Scope (Week 15-18)

### Phase 9: WBS Element Permissions (Week 15-16)

**Goal:** Implement work package management

**Tasks:**
- Create WBS-scoped roles (work_package_manager, phase_lead)
- Update PermissionResolver for WBS context
- Create API endpoints for WBS team assignments
- Update UI for WBS team management
- Testing and deployment

**Deliverables:**
- ✅ Users can be assigned to specific WBS elements
- ✅ Work package managers control their phase/deliverable
- ✅ Subcontractors limited to their WBS scope

### Phase 10: Task Permissions (Week 17-18)

**Goal:** Implement task-level assignments

**Tasks:**
- Create task-scoped roles (task_owner, task_assignee, task_reviewer)
- Update PermissionResolver for task context
- Create API endpoints for task assignments
- Update UI for task ownership
- Testing and deployment

**Deliverables:**
- ✅ Users can be assigned as owners of specific tasks
- ✅ Task owners can edit only their tasks
- ✅ External reviewers can access specific tasks

---

## 📊 Project Metrics

### Timeline Summary

| Phase | Duration | End Date |
|-------|----------|----------|
| Phase 0: Planning | 1 week | Week 1 |
| Phase 1: Database | 2 weeks | Week 3 |
| Phase 2: Models | 2 weeks | Week 4 |
| Phase 3: Services | 2 weeks | Week 6 |
| Phase 4: Policies | 2 weeks | Week 8 |
| Phase 5: API | 2 weeks | Week 10 |
| Phase 6: Frontend | 2 weeks | Week 12 |
| Phase 7: Testing | 1 week | Week 13 |
| Phase 8: Deployment | 1 week | Week 14 |
| **MVP Total** | **13 weeks** | **Week 14** |
| Phase 9: WBS (Optional) | 2 weeks | Week 16 |
| Phase 10: Task (Optional) | 2 weeks | Week 18 |
| **Full Total** | **18 weeks** | **Week 18** |

### Resource Allocation

| Role | Allocation | Total Effort (MVP) |
|------|------------|-------------------|
| Backend Developer 1 | 100% | 13 weeks |
| Backend Developer 2 | 100% | 13 weeks |
| Frontend Developer | 100% (Week 11-12) | 2 weeks |
| QA Engineer | 50% | 6.5 weeks |
| Tech Lead | 25% | 3.25 weeks |
| Product Owner | 10% | 1.3 weeks |

### Budget Estimate (MVP)

Assuming average rates:
- 2 Backend Devs × 13 weeks × $80/hr × 40hr/week = $83,200
- 1 Frontend Dev × 2 weeks × $80/hr × 40hr/week = $6,400
- 1 QA × 6.5 weeks × $70/hr × 40hr/week = $18,200
- 1 Tech Lead × 3.25 weeks × $120/hr × 40hr/week = $15,600
- 1 Product Owner × 1.3 weeks × $100/hr × 40hr/week = $5,200

**Total MVP Budget: ~$128,600**

---

## 🎯 Success Criteria

### Functional Requirements
- ✅ User can have different roles in different projects
- ✅ Project managers can add/remove team members
- ✅ Permissions correctly resolved with project context
- ✅ All policies respect project-level permissions
- ✅ UI shows/hides features based on permissions

### Non-Functional Requirements
- ✅ Permission check < 50ms (p90) with cache
- ✅ Team member list loads < 200ms
- ✅ No N+1 queries in permission resolution
- ✅ Test coverage > 85%
- ✅ Zero privilege escalation vulnerabilities
- ✅ Accessible (WCAG 2.1 Level AA)

### Business Metrics
- ✅ 100% of projects have assigned teams within 2 weeks
- ✅ < 5 permission-related support tickets per month
- ✅ User satisfaction score > 4/5

---

## ⚠️ Risk Management

### High Risks

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Performance degradation | Medium | High | Implement caching early, profile frequently |
| Breaking existing features | Medium | High | Feature flag, comprehensive testing |
| Scope creep (adding WBS/task too soon) | High | Medium | Strict scope control, MVP first |
| Data migration issues | Medium | High | Test on production copy, rollback plan |

### Medium Risks

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Complex logic hard to debug | Medium | Medium | PermissionResolution DTO with audit trail |
| Frontend performance issues | Low | Medium | Virtual scrolling, pagination |
| User adoption slow | Medium | Low | Training, documentation, UX polish |

---

## 📋 Clarification Questions for Stakeholders

Before starting implementation, please answer:

1. **Role Naming:** Are the proposed project roles (Project Manager, Technical Lead, Technician, Observer, Budget Controller, Quality Manager) aligned with your organization's terminology?

2. **Permission Inheritance:** Should higher-level roles automatically grant access to lower-level resources?
   - Example: Should a Program Manager automatically have view access to all projects in their program?
   - Recommendation: Yes for view, no for edit/delete

3. **Primary PM Requirement:** Should every active project REQUIRE a primary project manager, or is it optional?
   - Recommendation: Required for active projects

4. **Organization Constraints:** Can users be assigned to projects where their organization is NOT a participant (e.g., external auditors, PMO oversight)?
   - Recommendation: Allow for specific roles (auditor, observer) with explicit approval

5. **Historical Data:** Do we need to preserve historical team assignments after project completion, or can they be archived?
   - Recommendation: Preserve with `is_active=false` for audit trail

6. **Notification Requirements:** Should users be notified when added/removed from project teams?
   - Recommendation: Yes, via email and in-app notifications

7. **Scope Priority:** Start with project-level only (MVP), or include WBS/task from the beginning?
   - Recommendation: Project-level MVP first (13 weeks), evaluate need for WBS/task after

8. **Deployment Window:** Preferred deployment day/time? Weekend maintenance window acceptable?
   - Recommendation: Saturday early morning with 4-hour window

---

## 📚 Documentation Deliverables

1. **Technical Documentation:**
   - Architecture overview
   - API documentation (OpenAPI/Swagger)
   - Database schema diagram
   - Permission resolution algorithm
   - Deployment guide

2. **User Documentation:**
   - Admin guide: Managing project teams
   - User guide: Understanding permissions
   - FAQ: Common permission scenarios
   - Troubleshooting guide

3. **Training Materials:**
   - Video walkthrough of team management
   - Slide deck for training sessions
   - Quick reference card

---

## 🚦 Go/No-Go Criteria

Before each phase, verify:

**Phase 1-2 (Database/Models):**
- [ ] All migrations tested on staging with production data copy
- [ ] Unit test coverage > 90%
- [ ] Can assign/remove users via Tinker

**Phase 3-4 (Services/Policies):**
- [ ] Permission resolution works for all scenarios
- [ ] Integration tests passing
- [ ] Performance benchmarks met

**Phase 5-6 (API/Frontend):**
- [ ] API tests passing
- [ ] E2E tests covering critical workflows
- [ ] No security vulnerabilities

**Phase 8 (Deployment):**
- [ ] UAT completed successfully
- [ ] All P0/P1 bugs fixed
- [ ] Rollback plan tested
- [ ] Stakeholder approval

---

## 📞 Team Structure

### Core Team

**Tech Lead** (25% allocation)
- Architecture decisions
- Code reviews
- Performance profiling
- Security audits

**Backend Developer 1** (100% allocation)
- Database migrations
- Core models and services
- API controllers
- Bug fixes

**Backend Developer 2** (100% allocation)
- Testing (unit, integration)
- Policies and middleware
- Supporting backend tasks
- Bug fixes

**Frontend Developer** (100% Week 11-12)
- Vue.js components
- Composables and directives
- UI/UX polish
- E2E tests

**QA Engineer** (50% allocation)
- Test planning
- Manual testing
- Automation (E2E tests)
- Bug reporting

**Product Owner** (10% allocation)
- Requirements clarification
- UAT
- Documentation
- Training

---

## 📅 Milestones & Checkpoints

### Week 3: Database Complete
- ✅ All migrations run successfully
- ✅ Roles seeded with correct permissions
- ✅ Data migration tested

### Week 6: Backend Logic Complete
- ✅ Models, services, policies working
- ✅ Permission resolution algorithm tested
- ✅ Performance benchmarks met

### Week 10: API Complete
- ✅ All endpoints functional
- ✅ API tests passing
- ✅ Documentation complete

### Week 12: Frontend Complete
- ✅ UI components working
- ✅ E2E tests passing
- ✅ Ready for QA

### Week 14: MVP Deployed
- ✅ Production deployment successful
- ✅ Users trained
- ✅ No critical issues

---

## 🔄 Rollback Plan

If critical issues arise after deployment:

### Immediate Rollback (< 2 hours)
1. Revert application code to previous version
2. Disable feature flag `FEATURE_PROJECT_PERMISSIONS`
3. System returns to organization-level permissions
4. All data preserved in database

### Partial Rollback (2-24 hours)
1. Identify specific failing component (API, frontend, services)
2. Revert only that component
3. Run targeted tests
4. Re-deploy if fixed

### Full Rollback (> 24 hours)
1. Restore database from pre-migration backup
2. Revert all application code
3. Remove new tables (`project_teams`)
4. Full regression testing
5. Schedule re-deployment after fixes

---

## 📈 Post-Deployment Monitoring

### Week 1 (Intensive)
- Monitor error logs hourly
- Track permission check performance
- Review user feedback daily
- Fix critical bugs immediately

### Week 2-4 (Active)
- Daily error log review
- Weekly performance reports
- Bi-weekly user feedback review
- Prioritize bug fixes

### Month 2+ (Maintenance)
- Weekly error log review
- Monthly performance reports
- Monthly user feedback review
- Plan improvements

### Key Metrics to Monitor
- Permission check latency (p50, p90, p99)
- API response times
- Error rates (500, 403, 400)
- User adoption (% of projects with teams assigned)
- Support ticket volume
- Database query performance

---

**Document Version:** 1.0
**Author:** Claude
**Date:** 2025-11-21
**Status:** Draft - Awaiting Stakeholder Approval
