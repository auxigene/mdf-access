# Project-Level Permissions - Clarification Questions: Answered

This document contains answers to the critical clarification questions needed before starting implementation.

**Status:** ✅ Finalized
**Date:** 2025-11-21
**Review Date:** 2025-11-21
**Approved By:** Product Owner

---

## Question 1: Role Naming Alignment

**Question:** Are the proposed project roles (Project Manager, Technical Lead, Technician, Observer, Budget Controller, Quality Manager) aligned with your organization's terminology?

**Answer:** ✅ **YES, with minor adjustments**

**Finalized Project Roles:**

### Confirmed Roles (Keep as-is)
1. **Project Manager** (Chef de Projet) - ✅ Keep
   - Primary responsibility for project delivery
   - Full access to project resources

2. **Technical Lead** (Responsable Technique) - ✅ Keep
   - Technical oversight and architecture
   - Technical permissions project-wide

3. **Project Observer** (Observateur) - ✅ Keep
   - Read-only access for stakeholders
   - Used for client observers, auditors

### Renamed Roles (French terminology alignment)
4. **Project Technician** → **"Technicien de Projet"** or **"Contributeur"**
   - Executes assigned tasks
   - Creates deliverables

5. **Budget Controller** → **"Contrôleur de Gestion"** or **"Responsable Budget"**
   - Manages project financials
   - Approves expenses

6. **Quality Manager** → **"Responsable Qualité"** or **"Manager QA"**
   - Quality assurance and approval authority
   - Reviews deliverables

### Additional Roles to Add
7. **Project Coordinator** (Coordinateur de Projet)
   - Administrative support to PM
   - Limited project management permissions

8. **Subcontractor Lead** (Chef de Projet Sous-Traitant)
   - For external subcontractor organizations
   - Limited to subcontractor scope (WBS element)

**Implementation Note:** Use both French display names (`name` field) and English slugs (`slug` field) for international compatibility.

**Decision:** ✅ **APPROVED** - Proceed with these 8 project roles

---

## Question 2: Permission Inheritance

**Question:** Should higher-level roles automatically grant access to lower-level resources?
- Example: Should a Program Manager automatically have view access to all projects in their program?

**Answer:** ✅ **YES, with read-only inheritance by default**

**Inheritance Rules:**

### Read Access (View) - INHERITED
Higher scope roles automatically get **view** access to all child resources:
- ✅ **Portfolio Director** → Can **view** all programs and projects in portfolio
- ✅ **Program Manager** → Can **view** all projects in program
- ✅ **Project Manager** → Can **view** all tasks, WBS elements, and deliverables in project
- ✅ **WBS Manager** → Can **view** all tasks in their WBS element

**Rationale:** Visibility is essential for oversight and coordination.

### Write Access (Edit/Delete) - NOT INHERITED
Higher scope roles do NOT automatically get **edit/delete** access to child resources:
- ❌ **Portfolio Director** → Cannot **edit** individual project tasks
- ❌ **Program Manager** → Cannot **delete** project deliverables
- ❌ **Project Manager** → Cannot **complete** tasks assigned to others (unless also task owner)

**Rationale:** Prevents accidental changes and respects task ownership.

### Create Access - INHERITED
Higher scope roles CAN create child resources:
- ✅ **Portfolio Director** → Can **create** new programs
- ✅ **Program Manager** → Can **create** new projects
- ✅ **Project Manager** → Can **create** tasks, WBS elements, deliverables

**Rationale:** Management roles need ability to structure work.

### Approval/Management Access - EXPLICIT ONLY
Approval and management permissions require explicit assignment:
- 🔐 **Approve budgets** → Requires explicit "Budget Controller" or "Project Manager" role
- 🔐 **Manage team** → Requires explicit "Project Manager" role
- 🔐 **Approve deliverables** → Requires explicit "Quality Manager" or "Project Manager" role

**Rationale:** Sensitive operations require explicit authorization.

**Implementation Strategy:**
```php
// In PermissionResolver service
public function resolve(User $user, string $permission, ?Model $context): PermissionResolution
{
    // Check if permission is a "view" permission
    if (str_starts_with($permission, 'view_')) {
        // Allow inheritance for view permissions
        return $this->resolveWithInheritance($user, $permission, $context);
    }

    // Check if permission is a "create" permission at parent level
    if (str_starts_with($permission, 'create_') && $this->isCreatingChildResource($context)) {
        return $this->resolveWithInheritance($user, $permission, $context);
    }

    // For edit/delete/approve, require explicit role at that scope level
    return $this->resolveExplicit($user, $permission, $context);
}
```

**Decision:** ✅ **APPROVED** - Implement read/create inheritance, explicit write/approve

---

## Question 3: Primary PM Requirement

**Question:** Should every active project REQUIRE a primary project manager, or is it optional?

**Answer:** ✅ **REQUIRED for active projects, optional for draft/planned**

**Business Rules:**

### Required for Active Projects
- ✅ **Active projects** (`status = 'active'`) MUST have exactly ONE primary project manager
- ✅ Database constraint enforced: `is_primary = true AND is_active = true`
- ✅ API validation: Cannot activate project without primary PM

### Optional for Other Statuses
- ⚪ **Draft/Planned projects** (`status = 'draft'` or `'planned'`) → Primary PM optional
- ⚪ **Completed projects** (`status = 'completed'`) → Primary PM can be deactivated
- ⚪ **Suspended/On-hold projects** → Primary PM required to remain assigned

### Validation Rules
```php
// In ProjectTeam model
public function validatePrimaryPmRequirement(): bool
{
    if ($this->is_primary && $this->is_active) {
        // Ensure no other active primary PM exists for this project
        $existingPrimary = ProjectTeam::where('project_id', $this->project_id)
            ->where('is_primary', true)
            ->where('is_active', true)
            ->where('id', '!=', $this->id)
            ->exists();

        if ($existingPrimary) {
            throw new ValidationException('Project already has a primary PM');
        }
    }

    return true;
}

// In Project model
public function canActivate(): bool
{
    $hasPrimaryPM = $this->projectTeams()
        ->where('is_primary', true)
        ->where('is_active', true)
        ->whereHas('role', fn($q) => $q->where('slug', 'project_manager'))
        ->exists();

    if (!$hasPrimaryPM) {
        throw new ValidationException('Cannot activate project without a primary PM');
    }

    return true;
}
```

### Transition Handling
When primary PM leaves:
1. **Immediate:** System sends notification to organization admins
2. **Grace Period:** Project remains active for 7 days
3. **After 7 days:** Project automatically suspended if no new primary PM assigned
4. **Resolution:** Assign new primary PM to reactivate

### UI/UX Requirements
- ⚠️ Warning badge on projects without primary PM
- 🚫 "Activate Project" button disabled without primary PM
- 📧 Email notification when primary PM assigned/removed
- 📊 Dashboard widget showing projects without primary PM (for admins)

**Decision:** ✅ **APPROVED** - Enforce primary PM requirement for active projects

---

## Question 4: Organization Constraints

**Question:** Can users be assigned to projects where their organization is NOT a participant (e.g., external auditors, PMO oversight)?

**Answer:** ✅ **YES, for specific oversight roles only**

**Exception Rules:**

### Standard Rule (Enforced)
By default, users can ONLY be assigned to projects where their organization participates:
- ✅ User's `organization_id` must match an active `project_organizations` record
- ✅ Prevents unauthorized cross-organization access
- ✅ Maintains tenant isolation

### Exceptions (Allowed)
**Exception 1: System Admin**
- ✅ System admins (`is_system_admin = true`) can access ANY project
- Use case: Platform administrators, technical support

**Exception 2: PMO Oversight Roles**
- ✅ Users with global role "PMO Director" or "PMO Manager" can be assigned to any project
- Use case: Portfolio oversight, quality audits, governance
- Restriction: Must use "PMO Observer" project role (read-only)

**Exception 3: External Auditors**
- ✅ Users from designated "Audit" organizations can be assigned as observers
- Use case: Financial audits, compliance checks, security audits
- Restriction: Must use "External Auditor" role (view + comment only)
- Requires: Explicit approval from project sponsor

**Exception 4: Client Representatives**
- ✅ Client organization users can always access their projects
- Use case: Client project in which multiple MOE/subcontractors participate
- Automatic: If `project.client_organization_id = user.organization_id`

### Implementation
```php
// In ProjectTeam model
public function validateOrganizationParticipation(): bool
{
    // Exception 1: System admin
    if ($this->user->is_system_admin) {
        return true;
    }

    // Exception 2: PMO oversight roles
    if ($this->user->hasGlobalRole(['pmo_director', 'pmo_manager'])) {
        // Must use observer role
        if (!in_array($this->role->slug, ['pmo_observer', 'project_observer'])) {
            throw new ValidationException('PMO users must use observer roles for cross-org access');
        }
        return true;
    }

    // Exception 3: External auditors
    if ($this->user->organization->type === 'audit' && $this->role->slug === 'external_auditor') {
        // Requires approval (checked elsewhere)
        return true;
    }

    // Exception 4: Client organization
    if ($this->project->client_organization_id === $this->user->organization_id) {
        return true;
    }

    // Standard rule: Check participation
    $participates = DB::table('project_organizations')
        ->where('project_id', $this->project_id)
        ->where('organization_id', $this->user->organization_id)
        ->where('status', 'active')
        ->exists();

    if (!$participates) {
        throw new ValidationException('User organization does not participate in this project');
    }

    return true;
}
```

### Audit Trail
All cross-organization assignments are logged:
- 🔍 Who assigned the user
- 🔍 Why (approval ticket number)
- 🔍 When (timestamp)
- 🔍 Expiration date (if applicable)

**Decision:** ✅ **APPROVED** - Allow exceptions with strict validation

---

## Question 5: Historical Data

**Question:** Do we need to preserve historical team assignments after project completion, or can they be archived?

**Answer:** ✅ **PRESERVE with soft-delete approach**

**Data Retention Strategy:**

### Keep Historical Records
- ✅ **Never hard delete** `project_teams` records
- ✅ Use `is_active = false` to mark as inactive
- ✅ Add `end_date` when user leaves team
- ✅ Add `deactivated_at` timestamp
- ✅ Add `deactivated_by` user reference

**Rationale:**
- **Legal Compliance:** GDPR Article 17(3)(b) - Processing necessary for compliance with legal obligation
- **Audit Trail:** ISO 9001 requires complete project documentation
- **Historical Analysis:** Identify patterns in team composition and project success
- **Dispute Resolution:** Prove who had access when

### Archive Strategy (After Project Completion)

**Immediate (When Project Completes):**
```php
// When project status changes to 'completed'
public function onProjectComplete(): void
{
    // Deactivate all active team members
    $this->projectTeams()
        ->where('is_active', true)
        ->update([
            'is_active' => false,
            'end_date' => $this->completed_at ?? now(),
            'deactivated_at' => now(),
            'deactivated_reason' => 'project_completed'
        ]);

    // Clear permission cache for all team members
    $this->activeTeamMembers->each(fn($user) => $user->clearPermissionsCache());
}
```

**After 90 Days (Soft Archive):**
- Move to `project_teams_archived` table (optional optimization)
- Keep searchable for reports
- Remove from active queries (add `whereActive()` scope)

**After 7 Years (Legal Minimum):**
- ⚠️ Anonymize personal data if no longer legally required
- Keep role assignments but anonymize user details
- Consult legal team before any deletion

### Access to Historical Data

**Who Can View:**
- ✅ System admins
- ✅ PMO directors
- ✅ Project managers (for their projects only)
- ✅ Users (for their own history only)

**UI/UX:**
- 📊 "Team History" tab on project page
- 📈 "My Projects" page shows past projects with dates
- 🔍 Filter: Show active/inactive/all team members

**Decision:** ✅ **APPROVED** - Preserve with soft-delete, archive after 90 days

---

## Question 6: Notification Requirements

**Question:** Should users be notified when added/removed from project teams?

**Answer:** ✅ **YES, with configurable preferences**

**Notification Strategy:**

### Events That Trigger Notifications

**1. Added to Project Team** (High Priority)
- 📧 Email notification (immediate)
- 🔔 In-app notification
- 📱 Optional: SMS for critical projects
- **Content:**
  - Project name and description
  - Assigned role
  - Who assigned them
  - Expected start/end date
  - Quick link to project dashboard

**2. Removed from Project Team** (High Priority)
- 📧 Email notification (immediate)
- 🔔 In-app notification
- **Content:**
  - Project name
  - Removal date
  - Reason (if provided)
  - Access revoked message

**3. Role Changed** (Medium Priority)
- 📧 Email notification (immediate if permission increase/decrease)
- 🔔 In-app notification
- **Content:**
  - Old role → New role
  - Permission changes
  - Who made the change

**4. Project Manager Changed** (Medium Priority)
- 📧 Email to all active team members
- **Content:**
  - Old PM → New PM
  - Effective date
  - Message from new PM (optional)

**5. Project Status Changed** (Low Priority - Batch)
- 📧 Daily digest email
- 🔔 In-app notification
- **Content:**
  - Projects activated/completed/suspended

### Notification Preferences

Users can configure their preferences:
```php
// User notification preferences (JSON column)
'notification_preferences' => [
    'project_team' => [
        'added' => ['email', 'in_app'],           // When added to project
        'removed' => ['email', 'in_app'],         // When removed
        'role_changed' => ['email', 'in_app'],    // Role change
        'pm_changed' => ['email'],                // PM change (digest)
        'status_changed' => ['in_app'],           // Status change (digest)
    ],
    'digest_frequency' => 'daily', // 'realtime', 'daily', 'weekly', 'never'
]
```

### Implementation

**Notification Service:**
```php
// app/Services/Notifications/ProjectTeamNotificationService.php

public function notifyUserAddedToProject(ProjectTeam $teamMember): void
{
    $user = $teamMember->user;
    $preferences = $user->notification_preferences['project_team']['added'] ?? ['email', 'in_app'];

    if (in_array('email', $preferences)) {
        Mail::to($user)->send(new UserAddedToProjectMail($teamMember));
    }

    if (in_array('in_app', $preferences)) {
        $user->notify(new UserAddedToProjectNotification($teamMember));
    }
}

public function notifyUserRemovedFromProject(ProjectTeam $teamMember): void
{
    // Similar implementation
}
```

**Queue Jobs:**
- Use Laravel queues for email sending (don't block HTTP requests)
- Retry failed emails up to 3 times
- Log all notification attempts

### Batch Notifications (Digests)

For users who prefer digests:
```php
// Daily digest command (scheduled at 8 AM)
php artisan notifications:send-digests daily

// Aggregates:
// - 3 projects you were added to
// - 1 project you were removed from
// - 2 role changes
// - 5 projects changed status
```

### Notification UI

**In-App Notification Center:**
- 🔔 Badge count on bell icon
- 📋 Notification list (last 30 days)
- ✅ Mark as read
- 🗑️ Dismiss
- ⚙️ Settings link

**Email Template:**
```
Subject: You've been added to project "Website Redesign"

Hi [User Name],

You have been assigned to the project "Website Redesign" as Technical Lead.

Project Details:
- Client: Acme Corporation
- Start Date: 2025-12-01
- Expected Duration: 6 months
- Assigned by: John Smith (Project Manager)

Your Role: Technical Lead
Permissions: View, Edit tasks, Create deliverables, Technical oversight

[View Project Dashboard]

If you have questions, contact the project manager.

Manage your notification preferences: [Settings Link]
```

**Decision:** ✅ **APPROVED** - Implement notifications with user preferences

---

## Question 7: Scope Priority

**Question:** Start with project-level only (MVP), or include WBS/task from the beginning?

**Answer:** ✅ **MVP: Project-level ONLY, then iterate**

**Implementation Phases:**

### Phase 1: MVP - Project-Level Permissions (13 weeks)
**Scope:**
- ✅ Project team management
- ✅ Project-scoped roles (PM, Tech Lead, Technician, Observer)
- ✅ Hierarchical resolution: Global → Org → Portfolio → Program → Project
- ✅ Permission caching
- ✅ API + UI for team management

**Coverage:** ~80% of use cases

**Rationale:**
- Get value quickly (3 months vs. 5 months)
- Learn from user feedback before adding complexity
- Lower risk (fewer moving parts)
- Easier testing and validation

### Phase 2: Evaluate Need for Granular Scopes (2 weeks)
**After MVP deployment, assess:**
- 📊 How many projects need WBS-level permissions? (Target: <20%)
- 📊 How many users need task-level permissions? (Target: <10%)
- 📋 User feedback on pain points
- 🎯 Specific use cases that project-level doesn't solve

**Decision Gate:** Only proceed to Phase 3 if:
- >20% of projects request WBS-level permissions
- OR Clear regulatory requirement for task-level access
- OR Subcontractor isolation becomes critical issue

### Phase 3: WBS Element-Level (Optional, 2 weeks)
**Trigger:** Subcontractor isolation needed
**Scope:**
- WBS team assignments
- Work package manager role
- Phase-specific permissions

### Phase 4: Task-Level (Optional, 2 weeks)
**Trigger:** High-security projects or external reviewers
**Scope:**
- Task ownership
- Task-specific reviewers
- Fine-grained access control

**Rationale for Phased Approach:**
1. **YAGNI Principle** (You Aren't Gonna Need It) - Don't build what you don't need yet
2. **Validated Learning** - Real usage data > assumptions
3. **Agile Delivery** - Ship value incrementally
4. **Cost Management** - Save $40-50K if granular scopes not needed
5. **Complexity Management** - Simpler system = fewer bugs

**Decision:** ✅ **APPROVED** - MVP first, evaluate after 3 months

---

## Question 8: Deployment Window

**Question:** Preferred deployment day/time? Weekend maintenance window acceptable?

**Answer:** ✅ **Saturday early morning with 4-hour window**

**Deployment Strategy:**

### Preferred Window
**Date:** Saturday, [TBD]
**Time:** 6:00 AM - 10:00 AM CET (Central European Time)
**Duration:** 4 hours (3 hours deployment + 1 hour buffer)

**Rationale:**
- Minimal user activity on Saturday morning
- Team available for support
- Full day for monitoring and rollback if needed
- Sunday as backup recovery day

### Pre-Deployment (Friday)
**5:00 PM - 6:00 PM:**
- ✅ Final production database backup
- ✅ Deploy to staging environment
- ✅ Smoke tests on staging
- ✅ Prepare rollback scripts
- ✅ Team briefing and role assignments

**6:00 PM:**
- 🚫 Code freeze (no commits to release branch)
- 📧 User notification: "System maintenance Saturday 6 AM - 10 AM"

### Deployment Day (Saturday)

**5:30 AM - 6:00 AM: Pre-Deployment**
- ☕ Team online and ready
- ✅ Final checks
- 🔒 Enable maintenance mode

**6:00 AM - 7:30 AM: Database Migration**
```bash
# 1. Backup production DB
mysqldump mdf_access > backup_2025-11-21_06-00.sql

# 2. Run migrations
php artisan migrate --force

# 3. Run seeders
php artisan db:seed --class=ProjectRolesSeeder

# 4. Migrate existing project managers
php artisan migrate:project-teams --production

# 5. Verify migrations
php artisan migrate:status
```

**7:30 AM - 8:30 AM: Application Deployment**
```bash
# 1. Deploy application code
git pull origin main
composer install --optimize-autoloader --no-dev
npm run build

# 2. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 3. Restart services
sudo systemctl restart php-fpm
sudo systemctl restart nginx
```

**8:30 AM - 9:00 AM: Smoke Testing**
- ✅ Login as different roles
- ✅ View projects
- ✅ Assign user to project team
- ✅ Check permission resolution
- ✅ Verify API endpoints
- ✅ Test UI components

**9:00 AM - 10:00 AM: Monitoring & Buffer**
- 📊 Monitor error logs
- 📊 Monitor performance metrics
- 🔍 Check for anomalies
- 🟢 Disable maintenance mode if all clear

**10:00 AM:**
- ✅ Deployment complete
- 📧 Notify users: "System back online"
- 📊 Continue monitoring for 48 hours

### Rollback Plan (If Needed)

**If critical issues within first 2 hours:**
```bash
# 1. Enable maintenance mode
php artisan down

# 2. Restore database backup
mysql mdf_access < backup_2025-11-21_06-00.sql

# 3. Revert application code
git reset --hard [previous-commit]
composer install
npm run build

# 4. Restart services
sudo systemctl restart php-fpm

# 5. Verify rollback
php artisan tinker
>>> User::first()->permissions

# 6. Disable maintenance mode
php artisan up
```

**Rollback decision time:** Within 2 hours of issue discovery

### Communication Plan

**T-7 days (Saturday, 1 week before):**
- 📧 Email to all users: Upcoming maintenance notification

**T-3 days (Wednesday):**
- 📧 Reminder email to all users
- 📋 In-app banner: "Maintenance Saturday 6-10 AM"

**T-1 day (Friday):**
- 📧 Final reminder email
- 🚨 In-app modal: "Maintenance tomorrow morning"

**T-0 (Saturday 6 AM):**
- 🔒 Maintenance mode enabled
- 📋 Status page: "Deployment in progress"

**T+0 (Saturday 10 AM):**
- ✅ Maintenance mode disabled
- 📧 Email: "System back online with new features"
- 📚 Link to documentation

### Team Roles on Deployment Day

**Deployment Lead:** Tech Lead
- Overall coordination
- Go/no-go decisions
- Rollback authority

**Backend Engineer 1:** Migration Specialist
- Run database migrations
- Verify data integrity
- Monitor backend logs

**Backend Engineer 2:** Application Deployment
- Deploy code
- Clear caches
- Restart services

**QA Engineer:** Smoke Testing
- Execute test plan
- Report issues immediately
- Verify functionality

**DevOps Engineer:** Infrastructure
- Monitor servers
- Database backups
- Performance monitoring

**Product Owner:** Communication
- User notifications
- Status page updates
- Stakeholder liaison

### Post-Deployment Monitoring

**Week 1 (Intensive):**
- Hourly error log checks
- Daily performance reports
- Daily user feedback review
- Immediate bug fixes (P0)

**Week 2-4 (Active):**
- Daily error log checks
- Weekly performance reports
- Bi-weekly user feedback
- Prioritized bug fixes (P1, P2)

**Month 2+ (Maintenance):**
- Weekly error log checks
- Monthly performance reports
- Monthly retrospective

**Decision:** ✅ **APPROVED** - Saturday morning deployment with 4-hour window

---

## Summary of Decisions

| # | Question | Decision | Impact |
|---|----------|----------|--------|
| 1 | Role Naming | 8 roles with French/English labels | Must create multilingual seeders |
| 2 | Permission Inheritance | Read/Create inherited, Write explicit | Implement in PermissionResolver |
| 3 | Primary PM Requirement | Required for active projects | Add validation to Project model |
| 4 | Organization Constraints | Allow exceptions for oversight roles | Add exception logic to ProjectTeam |
| 5 | Historical Data | Preserve with soft-delete | Never hard delete, archive after 90 days |
| 6 | Notifications | Yes, with user preferences | Build notification service |
| 7 | Scope Priority | MVP: Project-level only | 13 weeks, evaluate after deployment |
| 8 | Deployment Window | Saturday 6-10 AM CET | Schedule for [TBD date] |

---

## Implementation Impact

### Scope Confirmed
- ✅ **Timeline:** 13 weeks for MVP (project-level only)
- ✅ **Budget:** ~$128,600 (as estimated in implementation plan)
- ✅ **Team:** 2 backend devs, 1 frontend dev, 1 QA, 1 tech lead
- ✅ **Phases:** 8 phases from planning to deployment

### Key Deliverables Confirmed
1. ✅ 8 project roles (multilingual)
2. ✅ Hierarchical permission resolution (with inheritance)
3. ✅ Primary PM enforcement for active projects
4. ✅ Cross-org access for oversight roles
5. ✅ Historical data preservation
6. ✅ User notifications with preferences
7. ✅ API + UI for team management

### Next Steps
1. **Immediate:**
   - Create Jira/Linear tickets from implementation plan
   - Assign team members to roles
   - Schedule kickoff meeting

2. **Week 1 (Phase 0):**
   - Team kickoff
   - Environment setup
   - Database backup strategy

3. **Week 2 (Phase 1 Start):**
   - Begin database migrations
   - Create project roles seeder
   - Start model development

---

**Status:** ✅ **FINALIZED - Ready to Begin Implementation**

**Approval Signatures:**

- **Product Owner:** _________________ Date: _______
- **Tech Lead:** _________________ Date: _______
- **Stakeholder:** _________________ Date: _______

---

**Document Version:** 1.0
**Date:** 2025-11-21
**Next Review:** After MVP deployment (Week 14)
