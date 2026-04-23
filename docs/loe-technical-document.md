# LOE HUB Technical Document

## 1. Product Summary

LOE HUB is a two-panel Laravel application for monthly Level of Effort tracking.

- `Admin panel`: built with Filament and focused on workforce setup, reporting, analytics, closures, and exports.
- `Employee panel`: built with Filament employee pages for self-service reporting, history, and personal analytics.

The product measures employee effort allocation across assigned projects, manual time off, and calculated spare capacity. It is designed to help the organization understand:

- who is overallocated,
- who has remaining bandwidth,
- how project effort is distributed,
- and whether monthly reporting compliance is healthy.

## 2. Technology Stack

- PHP `8.4`
- Laravel `13.6.0`
- Filament `5.6.0`
- Livewire `4.2.4`
- Flux UI `2.13.2`
- Laravel Fortify `1.36.2`
- Pest `4.6.3`
- PHPUnit `12.5.23`

## 3. High-Level Architecture

### 3.1 Panels

#### Admin panel

- Panel ID: `admin`
- URL path: `/admin`
- Access: admin users only
- Purpose:
  - workforce setup,
  - reporting oversight,
  - analytics,
  - exports,
  - month closure management.

#### Employee panel

- Panel ID: `employee`
- URL path: `/employee`
- Access: employee users only
- Purpose:
  - current month LOE entry,
  - report submission,
  - personal history,
  - self analytics.

### 3.2 Authentication and authorization

- Authentication is provided through Laravel Fortify.
- Filament panel access is controlled through `User::canAccessPanel()`.
- An inactive user cannot access either panel.
- Role behavior:
  - `admin` can access the admin panel.
  - `employee` can access the employee panel.

## 4. Roles and Access Rules

## 4.1 User roles

- `admin`
- `employee`

## 4.2 Department model

- Each user belongs to one department.
- Departments are admin-managed from the app.
- Current seeded departments:
  - `Engineering`
  - `Experience`

## 4.3 Current panel restriction

Current implementation treats panel access as role-exclusive:

- admins access the admin panel,
- employees access the employee panel.

This means an `admin` does not automatically enter the employee panel unless future logic is added to support true dual-panel usage for the same account.

## 5. Core Business Rules

## 5.1 Reporting granularity

- One LOE report exists per employee per month.
- A report is uniquely identified by:
  - `user_id`
  - `report_year`
  - `report_month`

## 5.2 Input method

- Employees enter `hours`.
- The system calculates:
  - `days`
  - `allocation percentage`

## 5.3 Line types

Each monthly report contains line items of these types:

- `project`
- `time_off`
- `open_to_new_projects`

## 5.4 Project selection rule

- Employees can only report against their own active assigned projects.
- A project assignment is valid if:
  - the assignment is active,
  - the project is active,
  - the assignment date range is valid for the reporting period.

## 5.5 Time off rule

- Time off is entered manually inside the same monthly report.
- Time off contributes to the same `100%` capacity model.

## 5.6 Open to New Projects rule

- `Open to New Projects` is system-generated.
- It appears only when manual total allocation is below `95%`.
- It is calculated as the remaining percentage up to `100%`.
- Users do not enter it directly.

## 5.7 Submission rule

- Submission is allowed even if allocation is:
  - below `100%`
  - above `100%`
- These cases generate admin alerts.

## 5.8 Project variance rule

- Each project assignment may define an expected allocation percentage.
- If a submitted project line exceeds the expected percentage, admins are alerted.

## 6. Monthly Lifecycle Rules

## 6.1 Statuses

Monthly report statuses:

- `draft`
- `submitted`
- `closed`

## 6.2 Editability

- Reports remain editable until the month is locked.
- If a submitted report is edited before closure, it returns to `draft`.

## 6.3 Month closing policy

- The normal reporting month ends on the calendar month end.
- There is a `3-day` grace period after month end.
- During the grace period:
  - overdue reminders continue,
  - admins may close the month manually.
- If the month is not manually closed, it is auto-closed after the grace period.

## 6.4 Lock rule

A month becomes locked when either:

- a `monthly_LOE_closures` record exists for that month, or
- current time is later than month end + 3 days.

After lock:

- reports cannot be edited,
- all reports for that month are marked `closed`.

## 7. Workday and Capacity Calculations

## 7.1 Calendar basis

Working days are calculated using:

- Saturdays excluded,
- Sundays excluded,
- public holidays excluded,
- optional proration from `joining_date`.

## 7.2 Working capacity

- Standard workday = `8 hours`.
- Available monthly capacity = `working days x 8`.

## 7.3 Conversion formulas

- `days = entered_hours / 8`
- `percentage = entered_hours / available_month_hours x 100`

## 7.4 Joining date proration

- If `joining_date` falls after month start, capacity begins from the joining date.
- If the joining date is after the reporting month ends, available working days are zero.

## 7.5 Public holidays

- Public holidays are global for all employees.
- They are managed manually by admins.

## 8. Data Model

## 8.1 Main entities

- `users`
- `departments`
- `projects`
- `project_assignments`
- `public_holidays`
- `monthly_LOE_reports`
- `monthly_LOE_report_lines`
- `monthly_LOE_closures`
- `monthly_LOE_report_activities`
- `notifications`

## 8.2 Users

Key fields:

- `name`
- `email`
- `password`
- `role`
- `department_id`
- `joining_date`
- `is_active`

## 8.3 Projects

Key fields:

- `name`
- `status`

Statuses:

- `active`
- `inactive`
- `closed`

## 8.4 Project assignments

Key fields:

- `user_id`
- `project_id`
- `expected_percentage`
- `starts_on`
- `ends_on`
- `is_active`

## 8.5 Monthly LOE report

Key fields:

- `user_id`
- `department_id`
- `report_year`
- `report_month`
- `status`
- `report_notes`
- `submitted_at`
- `closed_at`
- cached totals

## 8.6 Monthly LOE report lines

Key fields:

- `monthly_LOE_report_id`
- `line_type`
- `project_id`
- `project_assignment_id`
- `entered_hours`
- `calculated_days`
- `calculated_percentage`
- `expected_percentage`
- `line_notes`
- `sort_order`

## 8.7 Month closures

Key fields:

- `closure_year`
- `closure_month`
- `closed_by_user_id`
- `closure_type`
- `closed_at`
- `notes`

Closure types:

- `manual`
- `automatic`

## 8.8 Activity log

Activity actions include:

- `created`
- `prefilled`
- `updated`
- `submitted`
- `status_changed`
- `closed`
- `notification_sent`

## 9. Employee Workflow

## 9.1 Dashboard

Employee dashboard provides:

- current report status,
- current month totals,
- remaining open capacity,
- recent reports,
- month-over-month summary cues.

## 9.2 Current month report

Employee reporting page supports:

- draft auto-creation,
- prefill from the previous month,
- hours entry by assigned project,
- manual time off entry,
- report notes,
- per-line notes,
- real-time estimated totals,
- save draft,
- submit report.

## 9.3 Report history

Employees can review:

- past monthly reports,
- statuses,
- report detail pages,
- activity trail for their reports.

## 9.4 Personal analytics

Employees can view self analytics for:

- personal trend,
- monthly allocations,
- project distribution,
- open capacity patterns.

## 10. Admin Workflow

## 10.1 Workforce setup

Admin-managed areas:

- departments,
- employees,
- projects,
- project assignments,
- public holidays.

## 10.2 Reporting oversight

Admins can:

- view monthly reports,
- filter by month, year, department, employee, project, and status,
- inspect report details,
- open employee and project analytics,
- close months manually within the grace period.

## 10.3 Analytics

Admin analytics surfaces include:

- dashboard command center,
- employee analytics,
- project analytics,
- pending report visibility,
- utilization trends,
- open-capacity indicators.

## 10.4 Exports

Admin exports are available as CSV:

- dashboard summary
- monthly reports
- project allocations

## 11. Notification and Alert Policies

## 11.1 Employee reminder notifications

Employees receive reminders when:

- they have not submitted in the final three calendar days of the current month.

Command:

- `LOE:send-reminders`

Schedule:

- daily at `09:00`

Stop condition:

- reminder stops once the current month is submitted.

## 11.2 Overdue reminder notifications

Employees receive overdue reminders when:

- the previous month is still not submitted,
- today is within days `1-3` of the next month,
- the previous month is not already closed.

Command:

- `LOE:send-overdue-reminders`

Schedule:

- daily at `09:15`

## 11.3 Auto-close job

Auto-close command:

- `LOE:auto-close-months`

Schedule:

- daily at `00:30`

Behavior:

- evaluates the previous month and two-month-back candidate windows,
- auto-closes any month whose grace deadline has passed.

## 11.4 Admin notifications

Admins are notified when:

- a report is newly submitted,
- a previously submitted report is updated,
- a report is returned to draft after post-submission edits,
- total allocation is below `100%`,
- total allocation is above `100%`,
- a project line exceeds expected allocation.

Notification channels currently used:

- database notifications
- mail notifications

## 12. Dashboard and Analytics Policies

## 12.1 Admin dashboard

The admin dashboard is scoped by:

- month,
- year,
- department.

It includes:

- reporting health,
- pending employees,
- overallocated counts,
- open capacity,
- utilization trend,
- pending reports list,
- deep links into analytics and reporting.

## 12.2 Employee analytics page

Current implementation behavior:

- defaults to current month,
- defaults to `Engineering` where available in admin analytics,
- supports multi-department filter,
- supports optional employee filter,
- supports scoped CSV exports.

## 12.3 Project analytics page

Current implementation behavior:

- defaults to current month,
- defaults to `Engineering` where available,
- supports one or multiple departments,
- supports project filtering,
- supports export actions.

## 13. Seed Data and Bootstrap

## 13.1 Demo seed data

Current seeders create:

### Departments

- `Engineering`
- `Experience`

### Projects

- `BDC`
- `LoanEdge`

### Users

- Admin:
  - `admin@admin.com`
  - password: `password`
- Employee:
  - `mubashir.akhtar@pixeledge.io`
  - password: `password`

### Assignments

- both seeded projects are assigned to `mubashir.akhtar@pixeledge.io`

## 13.2 Admin bootstrap command

Custom command:

- `php artisan LOE:create-admin <email> --name="..." --password="..."`

Optional:

- `--department="Engineering"`

Behavior:

- creates a new admin or promotes an existing user to admin.

## 14. URLs and Routing Behavior

Convenience routes exist for the employee experience:

- `/dashboard`
- `/LOE/report`
- `/LOE/history`
- `/LOE/history/{report}`
- `/LOE/analytics`

These redirect into the employee Filament panel URLs.

## 15. Current UX and UI Implementation Notes

- Admin dashboard has a custom command-center style layout.
- Workforce setup resources support compact view modals with styled infolists.
- Employee analytics and project analytics pages use custom page layouts rather than default Filament scaffolding.
- Monthly report detail and history exist for both oversight and self-service flows.

## 16. Current Constraints and Known Product Gaps

The following items are not yet implemented or are intentionally deferred:

- team lead review workflow,
- reviewed status,
- configurable thresholds from admin settings,
- `leaving_date`,
- Google Calendar time-off sync,
- true dual-panel experience for a user acting as both admin and employee under one account.

## 17. Testing and Quality Controls

- The application uses Pest for automated tests.
- Focused regression coverage exists for:
  - panel access,
  - reporting services,
  - employee pages,
  - analytics and exports,
  - automation commands,
  - workforce setup infolists,
  - seeders and admin bootstrap.

Current local testing preference:

- SQLite

## 18. Operational Summary

The product currently delivers an end-to-end monthly LOE operating cycle:

1. Admin configures workforce structure.
2. Employee receives reminders and enters monthly effort.
3. System converts hours into days and percentages.
4. System calculates open capacity and expected-vs-actual variance.
5. Admin receives submission and alert notifications.
6. Admin monitors reports and analytics.
7. Overdue reminders continue during the grace period.
8. Admin closes the month manually, or the system auto-closes it.
9. Closed months remain locked for auditability and reporting stability.
