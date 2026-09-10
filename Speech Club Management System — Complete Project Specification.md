# Speech Club Management System

## 1. Project Overview

Build a web-based **Speech Club Management System** for an office environment.

The system is inspired by the way Toastmasters clubs conduct meetings, but this is **not intended to be a Toastmasters clone**. It is an internal office application for managing multiple speech clubs, their members, meetings, meeting assignments, attendance, speakers, evaluations, and meeting reports.

The application must support:

- Multiple speech clubs
- Club members
- Club-specific positions/roles
- Global users who can manage multiple clubs
- System roles and permissions
- Club-scoped access
- Meeting creation and management
- Fixed meeting role assignments
- Dynamic prepared speakers
- Dynamic TTM speakers
- Dynamic evaluator assignments
- Meeting attendance
- Meeting reports
- Historical meeting information
- Upcoming meetings
- Club-specific dashboards
- Global dashboards
- Role-based navigation
- Club-aware navigation/context

The application must be designed so that the architecture can grow later without requiring major database restructuring.

---

# 2. Technology Stack

Use the following stack.

## Backend

- Laravel 13
- PHP 8.3+
- MySQL
- Eloquent ORM
- Laravel authentication
- Spatie Laravel Permission

Laravel 13 is the current Laravel major release and supports PHP 8.3–8.5.

## Frontend

- Livewire 4
- Alpine.js
- Tailwind CSS

Livewire 4 must be used for reactive application functionality.

**Alpine.js is mandatory.**

Do not avoid Alpine.js by implementing everything through Livewire.

Livewire already integrates Alpine.js, so Alpine can be used directly for client-side UI state and interactions.

## UI

Use Tailwind CSS throughout the application.

The UI must have:

- Proper spacing
- Good typography
- Comfortable card padding
- Comfortable table cell padding
- Consistent gaps between form fields
- Clear visual hierarchy
- Responsive layouts
- Clean dashboards
- Professional office-application appearance

Do NOT create cramped interfaces.

Avoid layouts where everything is packed tightly together.

---

# 3. Important Frontend Rule — Alpine.js

Alpine.js is a required part of the project.

Use Alpine.js for client-side interactions such as:

- Sidebar open/close
- Mobile navigation
- Dropdown menus
- User menus
- Modal open/close
- Confirmation dialogs
- Tabs
- Accordions
- Expand/collapse sections
- Dynamic UI state
- Show/hide elements
- Client-side interaction state
- Dropdown behavior
- Meeting form interaction where appropriate

Use Livewire for:

- Database operations
- CRUD
- Validation
- Searching
- Filtering
- Pagination
- Server-side state
- Meeting creation
- Meeting updates
- Attendance
- Meeting reports
- Dynamic database-backed lists

Do not duplicate server-side logic in Alpine.

Use Alpine for presentation/client state and Livewire for server state.

---

# 4. Core Concept

The application has two major scopes:

## Club Scope

A user who has a **club role** belongs to a particular club and works only within that club.

Example:

```text
User:
Arun

Club:
Chola Speech Club

Club Role:
President
```

Arun sees only Chola's information.

He must not see:

- Chera club members
- Pandiya club meetings
- Other club reports
- Other club attendance
- Other club data

The club name should appear in the navigation/header.

Example:

```text
Speech Club
Chola Club
```

---

# 5. Global Scope

A user who does **not** have a club role is considered a global/non-club user.

Global users can be assigned access to multiple clubs.

Example:

```text
User:
Admin User

Role:
Admin

Assigned Clubs:
- Chola
- Chera
- Pandiya
```

The global user can manage/view data from the clubs they are assigned to.

The global user should have a club context/switcher.

Example:

```text
Speech Club

[ All Clubs ▼ ]
```

or:

```text
Speech Club

[ Chola Club ▼ ]
```

When a global user selects a club, the application operates in that club's context.

---

# 6. Important Scope Rule

The most important authorization rule in the application is:

```text
Club Role User
    ↓
One club
    ↓
Only that club's data


Global User
    ↓
Multiple assigned clubs
    ↓
Can switch between assigned clubs
    ↓
Can manage/view data according to permissions
```

A global user must not automatically have access to every club unless their permissions/assignment allow it.

A Super Admin can be treated as having global access to all clubs.

---

# 7. Application Branding / Navigation

The application name is:

**Speech Club**

For club-scoped users:

```text
Speech Club | Chola Club
```

For global users:

```text
Speech Club | All Clubs
```

or:

```text
Speech Club | Chola Club
```

depending on the selected context.

The application should always clearly indicate the current scope.

---

# 8. Users

There should be a single `users` table.

Do not create separate user tables for club users and global users.

The same user can be associated with:

- One club
- Multiple clubs

depending on the user-management workflow.

---

# 9. User Management Architecture

There are two user creation workflows.

## 9.1 Club User Creation

A club-level authorized user can create a user for their own club.

The club must be determined automatically from the authenticated user's club context.

There should be **no club selection field** in normal club-user creation.

Example:

```text
Logged-in User
    ↓
Chola Club
    ↓
Create User
```

The form:

```text
Name
Email
Phone
Role
Password / Invitation
Status
```

The user is automatically assigned to Chola.

The club user creation module must not allow the creator to select another club.

---

# 10. Global User Creation

A global authorized user gets a separate user-management workflow.

They can create a global user and assign that user to multiple clubs.

Example:

```text
Create Global User

Name:
Meiyarasan

Email:
meiyarasan@example.com

Clubs:

☑ Chola
☑ Chera
☑ Pandiya
☐ Pallava

Role:
Admin
```

This requires a many-to-many relationship:

```text
user_clubs
-----------
user_id
club_id
```

Therefore:

```text
User
  ├── Chola
  ├── Chera
  └── Pandiya
```

is supported.

---

# 11. User-to-Club Relationship

Use:

```text
users
clubs
user_clubs
```

Relationship:

```text
User belongs to many Clubs
Club has many Users
```

The pivot table:

```text
user_clubs
-----------
user_id
club_id
```

should have a unique constraint:

```text
unique(user_id, club_id)
```

This prevents duplicate membership records.

---

# 12. Important User Scope Rule

A user can belong to:

```text
One club
```

or:

```text
Multiple clubs
```

but the UI determines how this happens.

### Club user workflow

```text
One club
```

### Global user workflow

```text
Multiple clubs
```

Do not create two different user models.

---

# 13. System Roles and Permissions

Use **Spatie Laravel Permission**.

Spatie should be responsible for:

> What is the user allowed to do in the application?

Examples:

```text
Super Admin
Admin
Global Viewer
Member
President
VP Education
VP Membership
VP Public Relations
Secretary
Treasurer
Sergeant at Arms
```

However, the important distinction is that **club positions and application permissions are related but should not be confused with meeting assignments**.

Spatie Laravel Permission supports Laravel 13 with its current supported package versions.

---

# 14. Club Roles

Club roles represent the user's position within a club.

Examples:

```text
Member
President
VP Education
VP Membership
VP Public Relations
Secretary
Treasurer
Sergeant at Arms
```

These are different from meeting roles.

For example:

```text
Arun
Club Role:
President
```

does not mean:

```text
Arun is always TMOD
```

Meeting assignments are separate.

---

# 15. Identifying Club Roles

The system needs to know whether a Spatie role is a **club role**.

The preferred design is to maintain a separate relationship/configuration that identifies roles as club roles rather than hard-coding this logic throughout the application.

A club-role mapping can be maintained through a pivot/configuration structure.

Conceptually:

```text
roles
-----
id
name
guard_name
```

and:

```text
club_roles
----------
club_id
role_id
```

This allows each club to decide which roles are available.

Example:

```text
Chola
├── Member
├── President
├── VP Education
├── VP Membership
├── VP Public Relations
├── Secretary
├── Treasurer
└── Sergeant at Arms
```

Another club can have a different set of available club roles.

---

# 16. Club Role Scope

If the authenticated user has a club role:

```text
Club Role = President
Club = Chola
```

then the user is club-scoped.

They should see only:

```text
Chola
```

They should not see:

```text
Chera
Pandiya
Pallava
```

---

# 17. Global Role Scope

If the authenticated user's role is not a club role, the user is treated as a global user.

Example:

```text
Admin
```

or:

```text
Global Viewer
```

The user can be assigned multiple clubs through:

```text
user_clubs
```

Their available club selector should only show clubs assigned to them.

---

# 18. Super Admin

Super Admin is a global system role.

A Super Admin can:

- Manage all clubs
- Manage all users
- Manage roles
- Manage permissions
- Manage meetings
- Manage reports
- Manage club configuration
- Manage global settings

A Super Admin can be treated as having access to all clubs without requiring every club to be inserted into `user_clubs`.

---

# 19. Club Management

The application needs a Clubs module.

A club should contain at minimum:

```text
id
name
code / slug
description
status
created_at
updated_at
```

Potential additional fields:

```text
logo
meeting_day
meeting_time
location
timezone
```

These can be added as needed.

---

# 20. Club Role Configuration

Each club should be able to have a configurable list of roles.

Example:

```text
Club:
Chola

Available Club Roles:

☑ Member
☑ President
☑ VP Education
☑ VP Membership
☑ VP Public Relations
☑ Secretary
☑ Treasurer
☑ Sergeant at Arms
```

Another club may have:

```text
Chera

☑ Member
☑ President
☑ VP Education
☑ Secretary
```

The role configuration should use a pivot relationship so roles can be added/removed easily.

---

# 21. Members

All people participating in a club are users.

A member does not need a separate `members` table unless a future requirement demands additional profile information.

A normal person can simply have:

```text
Role:
Member
```

A club officer can have:

```text
Role:
President
```

The user can therefore be managed using the same user system.

---

# 22. Meeting Module

The meeting module is the core part of the application.

A meeting belongs to exactly one club.

Example:

```text
Meeting #25
Club: Chola
Date: September 15, 2026
```

---

# 23. Meeting Creation — Very Important Rule

A normal club user creating a meeting **must not select a club manually**.

The application automatically determines the club from the authenticated user's club context.

Example:

```text
Logged-in User:
Arun

Club:
Chola

Create Meeting
```

The system automatically stores:

```text
meeting.club_id = Chola
```

There is no:

```text
Club: [ Select Club ]
```

field in the normal meeting creation form.

---

# 24. Global User Meeting Creation

A global user can work within a selected club context.

For example:

```text
Global Admin
    ↓
Select Chola
    ↓
Create Meeting
```

The meeting automatically belongs to Chola.

Again, the meeting form itself should not need a club selector.

The current club context determines the club.

---

# 25. Meeting Member Selection

This is extremely important.

When creating a meeting, **all user-selection dropdowns must show only members from the current meeting's club**.

Example:

```text
Current Club:
Chola
```

The following fields must only show Chola members:

- TMOD
- GE
- TTM
- Listening Master
- Grammarian
- Ah Counter
- Timer
- Prepared Speakers
- TTM Speakers
- Evaluators

Never show users from another club.

---

# 26. Example

Suppose:

```text
Chola:
Arun
Kumar
Priya
Suresh
```

and:

```text
Chera:
Ravi
Meena
Karthik
```

While creating a Chola meeting:

```text
TMOD:
[ Arun
  Kumar
  Priya
  Suresh ]
```

Chera members must not appear.

---

# 27. Meeting Fixed Roles

The following are fixed meeting roles:

```text
TMOD
GE
TTM
Listening Master
Grammarian
Ah Counter
Timer
```

Each role is assigned to a specific user for the particular meeting.

Example:

```text
TMOD             → Arun
GE               → Kumar
TTM              → Priya
Listening Master → Suresh
Grammarian       → Meena
Ah Counter       → Ravi
Timer            → Karthik
```

These are **meeting assignments**, not Spatie roles.

---

# 28. Do NOT Put Meeting Roles Into Spatie

This is a critical architectural rule.

Do not create Spatie roles such as:

```text
TMOD
GE
TTM
Listening Master
Grammarian
Ah Counter
Timer
```

inside the authorization role system.

They are not application permissions.

They describe what someone is doing during a specific meeting.

---

# 29. Meeting Role Types

Use a configurable meeting-role-type table.

Example:

```text
meeting_role_types
------------------
id
name
slug
sort_order
is_active
created_at
updated_at
```

Seed the initial fixed roles:

```text
TMOD
GE
TTM
Listening Master
Grammarian
Ah Counter
Timer
```

The system should allow future meeting roles to be added without modifying the database schema.

---

# 30. Meeting Role Assignments

Use:

```text
meeting_roles
-------------
id
meeting_id
meeting_role_type_id
user_id
created_at
updated_at
```

This represents:

```text
Meeting
    ↓
Role
    ↓
User
```

Example:

```text
Meeting #25
    ↓
TMOD
    ↓
Arun
```

---

# 31. Dynamic Prepared Speakers

Prepared speakers are dynamic.

The meeting may have:

```text
1 speaker
```

or:

```text
2 speakers
```

or:

```text
5 speakers
```

depending on the meeting.

Do not hard-code Speaker 1, Speaker 2, Speaker 3 into the database.

Use a dynamic table.

Example:

```text
meeting_speakers
----------------
id
meeting_id
user_id
slot
speech_type
project
topic
duration
notes
created_at
updated_at
```

The exact additional fields can be expanded later.

---

# 32. Speaker UI

The meeting creation page should allow:

```text
Prepared Speakers

Speaker 1
[ Select Member ]

Speaker 2
[ Select Member ]

[ + Add Speaker ]
```

The user can dynamically add or remove speakers.

Use Alpine.js for client-side interaction where appropriate and Livewire for persistence.

---

# 33. TTM Speakers

TTM speakers are also dynamic.

TTM itself is a fixed meeting role:

```text
TTM → User
```

But the people who participate/speak during TTM are dynamic.

Example:

```text
TTM:
Priya

TTM Speakers:
1. Arun
2. Kumar
3. Suresh
```

Use a dedicated structure for TTM speakers.

Example:

```text
meeting_ttm_speakers
--------------------
id
meeting_id
user_id
slot
topic
duration
notes
created_at
updated_at
```

The exact fields can be expanded based on the meeting report requirements.

---

# 34. Evaluators

Evaluators are dynamic.

The system should allow the meeting organizer to add evaluator assignments.

Example:

```text
Speaker        Evaluator

Arun       →   Priya
Kumar      →   Suresh
Meena      →   Ravi
```

Evaluators must also be selected only from users belonging to the meeting's club.

---

# 35. Evaluator Relationship

An evaluator should reference the speaker they are evaluating.

Conceptually:

```text
meeting_evaluations
-------------------
id
meeting_id
speaker_id
evaluator_user_id
notes
...
```

The exact implementation should use the appropriate speaker record relationship.

This allows:

```text
Speaker:
Arun

Evaluator:
Priya
```

and supports multiple evaluation assignments.

---

# 36. Attendance

Every meeting needs attendance tracking.

Attendance is separate from meeting-role assignments.

The system needs to know:

> Who attended the meeting?

while meeting roles answer:

> Who was assigned to perform a particular role?

These are different concepts.

---

# 37. Attendance and Role Replacement

If a person assigned to a meeting role does not attend, the assignment can be changed to another person from the **same club**.

Example:

```text
Original:

TMOD → Arun
```

Attendance:

```text
Arun → Absent
```

Then the meeting organizer changes:

```text
TMOD → Suresh
```

The replacement must come from the same club.

---

# 38. Attendance Rule

For a Chola meeting:

```text
Only Chola members
```

can be selected as:

- Attendees
- Replacement role holders
- Speakers
- TTM speakers
- Evaluators
- Fixed meeting role holders

Do not allow users from another club.

---

# 39. Attendance Table

Suggested structure:

```text
meeting_attendance
------------------
id
meeting_id
user_id
status
notes
created_at
updated_at
```

Possible statuses:

```text
present
absent
late
excused
```

The final set of statuses can be adjusted based on actual office requirements.

---

# 40. Meeting Report

The system should provide a meeting report after the meeting.

The report should bring together:

```text
Meeting information
Fixed meeting roles
Prepared speakers
TTM speakers
Evaluators
Attendance
Other meeting information
```

Example:

```text
Chola Speech Club
Meeting #25

Date:
15 September 2026

Theme:
The Art of Procrastination

Meeting Roles:

TMOD              Arun
GE                Kumar
TTM               Priya
Listening Master  Suresh
Grammarian        Meena
Ah Counter        Ravi
Timer             Karthik

Prepared Speakers:

1. Arun
2. Priya

TTM Speakers:

1. Kumar
2. Suresh

Evaluations:

Arun → Priya
Priya → Kumar

Attendance:

Present: 15
Absent: 2
Late: 1
```

---

# 41. Upcoming Meetings

Normal club members should be able to see:

```text
Upcoming Meetings
```

for their club.

They should not see upcoming meetings from other clubs.

---

# 42. Previous Meetings

Normal members should be able to view old meeting details from their club.

They should have read-only access unless their permissions allow management.

Example:

```text
Previous Meetings

Meeting #24
Meeting #23
Meeting #22
Meeting #21
```

Clicking a meeting shows its report/details.

---

# 43. Normal Member Experience

A normal member should have a simple interface.

Navigation could include:

```text
Dashboard
Upcoming Meetings
Previous Meetings
Meeting Reports
My Profile
```

The member should not see administrative navigation unless they have the appropriate permissions.

---

# 44. Club Officer Experience

A club officer such as President can have additional access depending on permissions.

Example:

```text
Dashboard
Members
Meetings
Reports
Attendance
My Profile
```

The exact capabilities should come from Spatie permissions rather than hard-coded role checks wherever possible.

---

# 45. Global User Experience

Global users should have access to multiple clubs based on their assigned clubs and permissions.

Example navigation:

```text
Dashboard

Clubs
Users
Meetings
Reports

Club Switcher
```

The current club context should affect the data shown throughout the application.

---

# 46. Global Club Switcher

For global users:

```text
[ All Clubs ▼ ]
```

Options could include:

```text
All Clubs
Chola
Chera
Pandiya
```

But only clubs the user is allowed to access should be shown.

A Super Admin can see all clubs.

---

# 47. Club Context

Implement a reusable club-context mechanism.

The application should determine:

```text
Current Club
```

from:

### Club user

```text
Authenticated user's club
```

### Global user

```text
Selected club from the global club switcher
```

### Super Admin

```text
All clubs or selected club
```

This current context should be reused throughout:

- Dashboard
- Users
- Members
- Meetings
- Attendance
- Reports
- Speakers
- Evaluators
- Club analytics

---

# 48. Do Not Put Club Selection in Meeting Creation

This is a strict UI requirement.

Do not build:

```text
Club:
[ Select Club ]
```

inside the normal meeting creation page.

Instead:

```text
Current Context:
Chola
```

determines the club automatically.

This prevents users from accidentally creating a meeting for another club.

---

# 49. Authorization Architecture

Use two dimensions:

## Permission

Determines:

> What can this user do?

Example:

```text
users.view
users.create
users.update
users.delete

meetings.view
meetings.create
meetings.update
meetings.delete

reports.view
reports.manage

clubs.view
clubs.manage
```

## Scope

Determines:

> Which clubs can this user operate on?

Club user:

```text
One club
```

Global user:

```text
Assigned clubs
```

Super Admin:

```text
All clubs
```

---

# 50. Avoid Role-Only Authorization Logic

Do not write application logic everywhere like:

```php
if ($user->role === 'President') {
    ...
}
```

Instead use permissions:

```text
$user->can(...)
```

and scope logic.

Roles should grant permissions.

Permissions should control actions.

Club membership should control data scope.

---

# 51. Data Scoping

Every club-owned model should be scoped by club.

Examples:

```text
meetings.club_id
clubs.id
user_clubs
```

When querying meetings for a club user:

```text
Only meetings belonging to the user's club.
```

When querying meetings for a global user:

```text
Only meetings belonging to clubs available in their context/assignment.
```

When querying for Super Admin:

```text
All clubs.
```

---

# 52. Security Requirement

Never rely only on UI filtering.

For example, hiding Chera from a dropdown is not sufficient.

The backend must also verify:

```text
Selected user belongs to the current club.
```

This applies to:

- Meeting roles
- Speakers
- TTM speakers
- Evaluators
- Attendance
- User creation
- User updates
- Meeting updates

A malicious request must not be able to assign a user from another club.

---

# 53. Meeting Validation

When creating a meeting:

```text
meeting.club_id
```

must come from the current context.

For every selected user:

```text
user belongs to meeting.club_id
```

must be validated.

Example:

```text
TMOD user → must belong to Chola
GE user → must belong to Chola
TTM user → must belong to Chola
Speaker → must belong to Chola
Evaluator → must belong to Chola
```

---

# 54. Duplicate Assignment Validation

The system should prevent accidental duplicate assignments where appropriate.

For example, depending on business rules:

```text
TMOD → Arun
GE → Arun
```

may or may not be allowed.

This should be configurable rather than assumed.

At minimum, prevent duplicate records for the same exact meeting role unless multiple assignments are intentionally supported.

---

# 55. Meeting Role Configuration

Meeting role types should be data-driven.

Do not hard-code every role into Blade templates.

Instead:

```text
meeting_role_types
```

contains:

```text
TMOD
GE
TTM
Listening Master
Grammarian
Ah Counter
Timer
```

The UI loops over active role types.

This allows future additions without code changes.

---

# 56. Meeting Status

Meetings can have statuses such as:

```text
Draft
Scheduled
Completed
Cancelled
```

Suggested flow:

```text
Draft
 ↓
Scheduled
 ↓
Completed
```

or:

```text
Scheduled
 ↓
Cancelled
```

A completed meeting should have its final assignments and attendance.

---

# 57. Meeting Information

Suggested meeting fields:

```text
id
club_id
meeting_number
meeting_date
theme
venue
status
notes
created_by
created_at
updated_at
```

Additional fields can be added based on actual reporting requirements.

---

# 58. Meeting Number

Meeting numbers should be unique within a club.

Example:

```text
Chola:
Meeting #1
Meeting #2
Meeting #3
```

Another club can also have:

```text
Chera:
Meeting #1
Meeting #2
Meeting #3
```

Therefore, uniqueness should be scoped to the club rather than globally.

Conceptually:

```text
unique(club_id, meeting_number)
```

---

# 59. Dashboard — Club User

A club dashboard can show:

```text
Upcoming Meeting
Next Meeting Date
Members
Recent Meetings
Attendance Summary
Recent Reports
```

Example cards:

```text
Members
24

Upcoming Meeting
Sep 15

Previous Meetings
18

Last Attendance
21 / 24
```

Use properly spaced cards.

---

# 60. Dashboard — Global User

Global dashboard can show:

```text
Total Clubs
Total Members
Upcoming Meetings
Recent Meetings
Attendance Statistics
Club-wise summaries
```

When a specific club is selected, the dashboard changes to that club.

---

# 61. UI Design Requirements

The UI should be professional and clean.

Use Tailwind CSS.

Important spacing:

```text
Page sections:
space-y-8

Cards:
p-6

Form fields:
space-y-5

Grid:
gap-6

Table:
px-6 py-4
```

These are examples rather than rigid values; consistency is more important than the exact utility.

---

# 62. Cards

Cards must have:

- Comfortable padding
- Clear headings
- Descriptions where useful
- Consistent borders
- Subtle shadows where appropriate
- Rounded corners
- Enough space between cards

Example visual structure:

```text
┌─────────────────────────────┐
│ Members                     │
│                             │
│ 24                          │
│ Active members              │
└─────────────────────────────┘
```

Do not cram text against card edges.

---

# 63. Tables

Tables should have:

- Adequate horizontal padding
- Adequate vertical padding
- Readable headers
- Proper alignment
- Action column
- Hover state where appropriate
- Responsive behavior

Avoid:

```text
Name Email Role Club Status Actions
```

with everything touching each other.

Use generous cell spacing.

---

# 64. Forms

Forms should use clear labels.

Example:

```text
Name

[                                      ]

Email

[                                      ]

Role                     Status

[ Member ▼ ]             [ Active ▼ ]
```

Use grouped fields where appropriate.

Do not create cramped forms.

---

# 65. Modals

Use Alpine.js for modal visibility/state.

Livewire handles the actual operation.

Example conceptual structure:

```text
Alpine:
open/close modal

Livewire:
save/delete/update
```

---

# 66. Dropdowns

Use Alpine.js for dropdown UI behavior where appropriate.

Server-side values and validation should remain controlled by Livewire.

---

# 67. Dynamic Meeting Form

The meeting creation page is an important Livewire + Alpine component.

Sections:

```text
Meeting Information

Meeting Roles

Prepared Speakers

TTM Speakers

Evaluators
```

The user should be able to:

```text
+ Add Speaker
+ Add TTM Speaker
+ Add Evaluation
```

and:

```text
Remove
```

entries dynamically.

Use Alpine for UI behavior where appropriate and Livewire for persistence/validation.

---

# 68. Meeting Creation UI Example

```text
Create Meeting

Meeting Information
────────────────────────────

Date
[ 15 Sep 2026 ]

Meeting Number
[ 25 ]

Theme
[ The Art of Procrastination ]

Venue
[ Conference Room ]

────────────────────────────

Meeting Roles

TMOD
[ Select Member ]

GE
[ Select Member ]

TTM
[ Select Member ]

Listening Master
[ Select Member ]

Grammarian
[ Select Member ]

Ah Counter
[ Select Member ]

Timer
[ Select Member ]

────────────────────────────

Prepared Speakers

1. [ Select Member ] [ Remove ]

[ + Add Speaker ]

────────────────────────────

TTM Speakers

1. [ Select Member ] [ Remove ]

[ + Add TTM Speaker ]

────────────────────────────

Evaluators

Speaker: [ Arun ]
Evaluator: [ Priya ]

[ + Add Evaluation ]

────────────────────────────

[ Cancel ] [ Create Meeting ]
```

No club selector.

---

# 69. Meeting Edit

Meeting editing should allow authorized users to:

- Change meeting date
- Change theme
- Change venue
- Change fixed role assignments
- Add/remove speakers
- Add/remove TTM speakers
- Change evaluator assignments
- Update notes
- Change status

All selected people must remain within the meeting's club.

---

# 70. Meeting Attendance Screen

After or during the meeting:

```text
Attendance

☑ Arun
☑ Kumar
☑ Priya
☐ Suresh
☑ Meena
```

Or use statuses:

```text
Arun       Present
Kumar      Present
Priya      Late
Suresh     Absent
Meena      Present
```

If a role holder is absent, the system should make it easy to replace their meeting assignment.

---

# 71. Role Replacement

Example:

```text
TMOD
Current:
Arun

Attendance:
Absent

[ Replace TMOD ]

Select replacement:
[ Suresh ▼ ]
```

The dropdown must only contain members of the current club.

---

# 72. Historical Meeting Reports

Users should be able to open a completed meeting.

The report should be read-friendly.

Structure:

```text
Meeting #25
Chola Speech Club
15 September 2026

Theme
The Art of Procrastination

Meeting Roles
...

Prepared Speakers
...

TTM Speakers
...

Evaluations
...

Attendance
...
```

The report should not look like a raw database screen.

---

# 73. Permissions

Suggested permission groups:

## User Management

```text
users.view
users.create
users.update
users.delete
```

## Global User Management

```text
global-users.view
global-users.create
global-users.update
global-users.delete
```

## Club Management

```text
clubs.view
clubs.create
clubs.update
clubs.delete
```

## Meeting Management

```text
meetings.view
meetings.create
meetings.update
meetings.delete
```

## Meeting Roles

```text
meeting-roles.manage
```

## Attendance

```text
attendance.view
attendance.manage
```

## Reports

```text
reports.view
reports.manage
```

## Role/Permission Management

```text
roles.view
roles.create
roles.update
roles.delete

permissions.manage
```

These names are suggestions and can be refined during implementation.

---

# 74. Navigation by Permission

Do not show every menu item to every user.

For example:

### Member

```text
Dashboard
Upcoming Meetings
Previous Meetings
Reports
Profile
```

### Club Officer/Admin

```text
Dashboard
Members
Meetings
Attendance
Reports
Profile
```

### Global Admin

```text
Dashboard
Clubs
Users
Meetings
Reports
Roles
Permissions
Settings
```

Menus should be permission-aware.

---

# 75. Database Relationship Summary

Conceptual structure:

```text
users
  │
  ├──────── user_clubs ─────── clubs
  │
  └──────── model_has_roles ── roles
                                  │
                                  └── permissions


clubs
  │
  └── meetings
          │
          ├── meeting_roles
          │       ├── meeting_role_types
          │       └── users
          │
          ├── meeting_speakers
          │       └── users
          │
          ├── meeting_ttm_speakers
          │       └── users
          │
          ├── meeting_evaluations
          │       ├── users
          │       └── speakers
          │
          └── meeting_attendance
                  └── users
```

---

# 76. Suggested Core Tables

At minimum:

```text
users
clubs
user_clubs

roles
permissions
model_has_roles
model_has_permissions
role_has_permissions

meeting_role_types

meetings
meeting_roles
meeting_speakers
meeting_ttm_speakers
meeting_evaluations
meeting_attendance
```

Additional tables can be introduced when actual requirements are finalized.

---

# 77. Do Not Over-Engineer

Do not create separate tables for:

```text
TMOD
GE
TTM
Listening Master
Grammarian
Ah Counter
Timer
```

They are all the same concept:

```text
Meeting Role Type
```

Therefore:

```text
meeting_role_types
```

handles them all.

Similarly, do not create separate fixed tables for every speaker.

Speakers are dynamic records.

---

# 78. Important Concept Separation

The project has four distinct concepts.

## A. System Role

Used for authorization.

Example:

```text
Admin
Super Admin
```

## B. Club Role

Used for a person's position in a club.

Example:

```text
President
VP Education
Member
```

## C. Meeting Role

Used for a person's duty in one specific meeting.

Example:

```text
TMOD
GE
TTM
Listening Master
Grammarian
Ah Counter
Timer
```

## D. Meeting Participant

Dynamic participation.

Example:

```text
Prepared Speaker
TTM Speaker
Evaluator
```

Do not mix these concepts.

---

# 79. Example Full User

```text
User:
Arun

System Role:
None / Member-level permissions

Club:
Chola

Club Role:
President
```

During Meeting #25:

```text
Meeting Role:
TMOD
```

During Meeting #26:

```text
Meeting Role:
GE
```

During Meeting #27:

```text
No fixed meeting role
```

This is valid.

---

# 80. Example Global User

```text
User:
Admin User

System Role:
Admin

Club Role:
None

Assigned Clubs:
Chola
Chera
Pandiya
```

The user can switch between those clubs.

The navbar might show:

```text
Speech Club | Chola ▼
```

and selecting:

```text
Chera
```

changes the application context.

---

# 81. Example Super Admin

```text
User:
System Administrator

System Role:
Super Admin

Club Role:
None

Club access:
All
```

Navbar:

```text
Speech Club | All Clubs ▼
```

The Super Admin can select any club.

---

# 82. Club User Creation Example

Logged-in:

```text
President - Chola
```

Clicks:

```text
Members → Create User
```

Form:

```text
Name
Email
Phone
Role
Password
```

The system automatically knows:

```text
club = Chola
```

The user is inserted into:

```text
users
```

and:

```text
user_clubs
```

with:

```text
user_id
Chola club_id
```

No club selection is presented.

---

# 83. Global User Creation Example

Global Admin opens:

```text
Global Users → Create
```

Form:

```text
Name
Email
Phone

Clubs:
☑ Chola
☑ Chera
☐ Pandiya

Role:
Admin
```

The system inserts:

```text
users
```

and multiple:

```text
user_clubs
```

records.

---

# 84. Meeting Creation Example

Logged-in:

```text
Arun
President
Chola
```

Clicks:

```text
Meetings → Create
```

The system determines:

```text
club = Chola
```

No club selection.

All member dropdowns query:

```text
users belonging to Chola
```

The resulting meeting:

```text
Meeting #25
club_id = Chola
```

---

# 85. Global Meeting Creation Example

Global Admin selects:

```text
Current Club:
Chola
```

Then clicks:

```text
Create Meeting
```

The system uses:

```text
current club = Chola
```

The meeting is created under Chola.

All member dropdowns contain Chola users only.

---

# 86. Backend Safety

Every action must validate the current scope.

Never trust:

```text
club_id
user_id
meeting_id
```

from the frontend without checking authorization.

For example, if a Chola user attempts to assign a Chera user to a Chola meeting, reject the operation.

---

# 87. Soft Deletes

Use soft deletes where appropriate, especially for:

```text
users
clubs
```

Do not physically delete important historical meeting data unless explicitly required.

Meeting reports are historical records and should remain stable.

---

# 88. Historical Data Integrity

If a user later changes their club role, old meetings must not change.

Example:

```text
Meeting #20
TMOD → Arun
```

If Arun later becomes:

```text
VP Education
```

Meeting #20 should still show:

```text
TMOD → Arun
```

Historical meeting data must remain intact.

---

# 89. Role Changes

A user's club position can change over time.

Example:

```text
2026:
Arun → Member

Later:
Arun → President
```

The current user role determines current access.

Historical meetings should continue to show the meeting assignments that existed at that time.

---

# 90. Current Club vs Historical Club

Meeting records should permanently store their `club_id`.

Do not derive the meeting's club from the current user's club.

This is important because users may later change clubs.

Example:

```text
Meeting #10
club_id = Chola
```

must remain Chola even if the meeting creator later joins Chera.

---

# 91. Responsive Design

The application must work well on:

- Desktop
- Laptop
- Tablet
- Mobile

The sidebar should collapse on smaller screens.

Use Alpine.js for responsive menu state.

Tables should have responsive overflow or an appropriate mobile presentation.

---

# 92. Accessibility

Use:

- Proper labels
- Keyboard-friendly controls
- Focus states
- Semantic buttons
- Accessible modal behavior
- Accessible dropdown behavior
- Sufficient contrast
- Clear validation messages

Do not rely only on color to communicate status.

---

# 93. Validation

All forms must have server-side validation.

Validation messages should be displayed near the relevant fields.

Examples:

```text
The meeting date is required.

The selected TMOD must belong to this club.

The selected evaluator must belong to this club.
```

---

# 94. Loading States

Livewire actions should have appropriate loading indicators.

Examples:

```text
Creating meeting...
Saving attendance...
Updating role...
Deleting member...
```

Use Alpine.js for visual interaction where useful.

---

# 95. Confirmation Dialogs

Destructive actions such as:

```text
Delete user
Delete meeting
Remove speaker
Remove club
```

should have confirmation.

Use Alpine.js for confirmation/modal behavior and Livewire for the actual action.

---

# 96. Search and Filtering

Large lists should support:

- Search
- Filtering
- Pagination

User list filters:

```text
Search
Club
Role
Status
```

Meeting list filters:

```text
Date
Status
Club
Meeting number
```

Club users should only see filters applicable to their scope.

---

# 97. Global User List

Global users with appropriate permission can see:

```text
All Users
```

with:

```text
Name
Email
Club(s)
Role
Status
Created At
Actions
```

A global user assigned to multiple clubs should show multiple clubs clearly.

Example:

```text
Meiyarasan
Admin
Chola, Chera, Pandiya
Active
```

---

# 98. Club Member List

A club-scoped user sees:

```text
Chola Members
```

only.

Columns:

```text
Name
Email
Club Role
Status
Joined
Actions
```

No other club members should appear.

---

# 99. Meeting List

Club-scoped:

```text
Chola Meetings
```

Global:

```text
All Meetings
```

or:

```text
Current Club Meetings
```

depending on the current context.

---

# 100. Meeting Detail

Meeting detail should show:

```text
Meeting Number
Date
Theme
Status

Fixed Roles

Prepared Speakers

TTM Speakers

Evaluators

Attendance

Notes
```

Use clear sections/cards with spacing.

---

# 101. Meeting Report UX

The final report should be visually structured rather than just a table dump.

Suggested sections:

```text
Meeting Header

Meeting Information

Meeting Roles

Prepared Speeches

TTM Session

Evaluations

Attendance

Notes
```

---

# 102. Performance

Use Eloquent relationships carefully.

Avoid N+1 queries.

Use eager loading where appropriate:

```text
Meeting
  → roles
  → speakers
  → TTM speakers
  → evaluations
  → attendance
```

Paginate large datasets.

Do not load every club/user unnecessarily.

For dropdowns, query only users belonging to the current club.

---

# 103. Database Indexing

Important indexes should include:

```text
user_clubs.user_id
user_clubs.club_id

meetings.club_id
meetings.meeting_date

meeting_roles.meeting_id
meeting_roles.user_id

meeting_speakers.meeting_id
meeting_speakers.user_id

meeting_ttm_speakers.meeting_id
meeting_ttm_speakers.user_id

meeting_attendance.meeting_id
meeting_attendance.user_id
```

Use composite indexes where query patterns justify them.

---

# 104. Unique Constraints

Potential unique constraints:

```text
user_clubs:
unique(user_id, club_id)

meetings:
unique(club_id, meeting_number)
```

For fixed meeting roles, if exactly one person is allowed per role:

```text
unique(meeting_id, meeting_role_type_id)
```

Do not use that constraint for dynamic speakers.

---

# 105. Dynamic Speaker Ordering

Use a `slot` or `sort_order`.

Example:

```text
Speaker 1
Speaker 2
Speaker 3
```

Database:

```text
slot = 1
slot = 2
slot = 3
```

This allows the meeting report to preserve the intended order.

---

# 106. Dynamic TTM Speaker Ordering

Same approach:

```text
TTM Speaker 1
TTM Speaker 2
TTM Speaker 3
```

Use:

```text
slot
```

or:

```text
sort_order
```

---

# 107. Meeting Role Ordering

Use:

```text
meeting_role_types.sort_order
```

so the meeting creation/report UI can consistently display:

```text
TMOD
GE
TTM
Listening Master
Grammarian
Ah Counter
Timer
```

---

# 108. Configurable Meeting Roles

Administrators should eventually be able to add another fixed meeting role.

Example:

```text
Role:
Word Master
```

Once active, it appears in meeting creation.

No migration should be required.

---

# 109. Authentication

The application requires login.

Unauthenticated users should be redirected to login.

After login:

```text
Determine user
↓
Determine system role
↓
Determine club role/global scope
↓
Determine accessible clubs
↓
Set current club context
↓
Load dashboard
```

---

# 110. Post-Login Behavior

### Club user

Immediately enter their club:

```text
Chola Club
```

No club selector is necessary.

### Global user

If they have multiple clubs:

```text
Show global context
```

They can select:

```text
All Clubs
Chola
Chera
Pandiya
```

### Global user with one assigned club

The system can automatically use that club as the current context, while still treating the user as global.

---

# 111. No Cross-Club Leakage

This is one of the most important security requirements.

A Chola user must never be able to access:

```text
/chera/meetings
```

or manipulate Chera data simply by changing an ID in a URL/request.

Authorization must be enforced server-side.

---

# 112. URL Design

Routes can be designed around resources rather than exposing unnecessary club IDs everywhere.

For club-scoped users:

```text
/meetings
/meetings/create
/meetings/{meeting}
/members
/reports
```

The application gets the club from context.

Global users can use the current club context.

Avoid making the user manually type or manipulate `club_id`.

---

# 113. Service/Domain Layer

As the project grows, consider extracting scope logic into dedicated services.

Potential concepts:

```text
ClubContext
ClubAccessService
MeetingService
MeetingAssignmentService
AttendanceService
```

This prevents controllers/Livewire components from becoming too large.

---

# 114. Livewire Component Organization

Suggested components:

```text
Dashboard
Users
    UserIndex
    UserCreate
    UserEdit

GlobalUsers
    GlobalUserIndex
    GlobalUserCreate
    GlobalUserEdit

Clubs
    ClubIndex
    ClubCreate
    ClubEdit
    ClubRoleManager

Meetings
    MeetingIndex
    MeetingCreate
    MeetingEdit
    MeetingShow
    MeetingAttendance
    MeetingReport

Roles
    RoleIndex
    RoleCreate
    RoleEdit

Permissions
    PermissionIndex
```

Names can be adjusted according to the final Laravel project structure.

---

# 115. Alpine Components

Use Alpine for:

```text
Sidebar
Dropdown
Modal
Confirmation
Tabs
Collapsible sections
Mobile menu
Club switcher UI
Dynamic meeting UI interactions
```

Do not introduce another frontend framework.

---

# 116. No React/Vue Requirement

Do not use:

- React
- Vue
- jQuery-heavy architecture
- Separate SPA architecture

The application should remain:

```text
Laravel
+
Livewire
+
Alpine.js
+
Tailwind
```

---

# 117. Styling Philosophy

The application should feel like a modern professional internal management platform.

Use:

- Clean typography
- White/neutral cards
- Subtle borders
- Consistent radius
- Moderate shadows
- Clear primary actions
- Good whitespace
- Responsive layouts

Do not over-design.

Do not use excessive gradients.

Do not cram content.

---

# 118. Spacing Philosophy

Spacing is a project-wide requirement.

Cards:

```text
p-6
```

Sections:

```text
space-y-8
```

Form groups:

```text
space-y-5
```

Grid:

```text
gap-6
```

Tables:

```text
px-6 py-4
```

These are starting conventions and can be adjusted while maintaining the same visual density.

---

# 119. Empty States

Every list should have a useful empty state.

Example:

```text
No upcoming meetings

There are no meetings scheduled for your club yet.

[ Create Meeting ]
```

Do not show blank tables.

---

# 120. Error States

Show clear messages for:

- Unauthorized access
- Invalid club context
- User not belonging to club
- Meeting not found
- Meeting from another club
- Invalid role assignment
- Invalid evaluator assignment

---

# 121. Auditability

Consider tracking:

```text
created_by
updated_by
```

for important entities.

Especially:

```text
meetings
meeting_roles
meeting reports
```

This can be expanded later into a complete audit log.

---

# 122. Future Extensibility

The architecture should make it possible to add:

- Speech evaluation forms
- Awards
- Member achievements
- Attendance statistics
- Club performance
- Meeting analytics
- Member participation history
- Speech history
- Evaluation history
- Notifications
- Email reminders
- Calendar integration
- Export to PDF
- Monthly/annual reports

without redesigning the core architecture.

---

# 123. Important Business Rules Summary

## Users

```text
One users table.
```

## Club membership

```text
user_clubs pivot.
```

## Club user

```text
Restricted to their club.
```

## Global user

```text
Can be assigned multiple clubs.
```

## Super Admin

```text
Global access.
```

## Club roles

```text
President
VPE
VPM
VPPR
Secretary
Treasurer
SAA
Member
etc.
```

## Meeting roles

```text
TMOD
GE
TTM
Listening Master
Grammarian
Ah Counter
Timer
```

## Dynamic

```text
Prepared Speakers
TTM Speakers
Evaluators
```

## Meeting attendance

```text
Track users attending the meeting.
```

## Replacement

```text
Absent role holder can be replaced by a member from the SAME club.
```

## Meeting club

```text
Automatically determined from current club context.
```

## Meeting user dropdowns

```text
Only users from the meeting's club.
```

---

# 124. Critical Distinction Table

| Concept | Purpose | Example |
|---|---|---|
| System Role | Application authorization | Admin |
| Club Role | User's position in club | President |
| Club Membership | Which clubs user belongs to | Chola, Chera |
| Meeting Role | Duty during one meeting | TMOD |
| Speaker | Dynamic meeting participant | Arun |
| TTM Speaker | Dynamic TTM participant | Kumar |
| Evaluator | Person evaluating a speaker | Priya |
| Attendance | Whether user attended | Present |

These concepts must not be merged.

---

# 125. Example Complete Scenario

Assume the system contains:

```text
Clubs:

Chola
Chera
Pandiya
```

Users:

```text
Arun
Club: Chola
Club Role: President

Kumar
Club: Chola
Club Role: Member

Priya
Club: Chola
Club Role: VPE

Ravi
Club: Chera
Club Role: President

Admin User
System Role: Admin
Clubs: Chola, Chera, Pandiya
```

Arun logs in.

The navbar:

```text
Speech Club | Chola Club
```

Arun creates a meeting.

The system automatically sets:

```text
club = Chola
```

The member dropdowns contain:

```text
Arun
Kumar
Priya
```

They do not contain:

```text
Ravi
```

Meeting assignments:

```text
TMOD → Arun
GE → Kumar
TTM → Priya
Listening Master → Kumar
Grammarian → Priya
Ah Counter → Arun
Timer → Kumar
```

Prepared speakers:

```text
Arun
Priya
```

TTM speakers:

```text
Kumar
```

Evaluators:

```text
Arun → Priya
```

Attendance:

```text
Arun → Present
Kumar → Present
Priya → Present
```

Later Arun is absent.

The organizer changes:

```text
TMOD → Kumar
```

Only Chola members are available for replacement.

---

# 126. Global Admin Scenario

Admin logs in.

They have:

```text
Chola
Chera
Pandiya
```

They see:

```text
Speech Club | All Clubs
```

They can select:

```text
Chola
```

Now:

```text
Dashboard
Members
Meetings
Reports
```

show Chola data.

They switch to:

```text
Chera
```

and the same modules show Chera data.

When creating a meeting while Chola is active:

```text
meeting.club_id = Chola
```

and only Chola members are available for assignments.

---

# 127. Development Principles

Claude should follow these principles while implementing:

1. Do not invent unnecessary tables.
2. Do not mix system roles with meeting roles.
3. Do not put meeting roles into Spatie.
4. Do not put speakers into fixed role tables.
5. Do not put evaluators into fixed role tables.
6. Do not require club selection during normal club-user meeting creation.
7. Always derive the club from current context.
8. Always filter meeting participants by the meeting's club.
9. Always enforce club scope server-side.
10. Never trust frontend club/user IDs.
11. Use Livewire 4 for server/reactive behavior.
12. Use Alpine.js for client-side interactions.
13. Use Tailwind CSS.
14. Maintain generous spacing.
15. Keep cards and tables visually comfortable.
16. Build reusable components.
17. Keep authorization separate from data scope.
18. Preserve historical meeting data.
19. Avoid N+1 queries.
20. Use proper database indexes.
21. Keep the architecture extensible.
22. Do not introduce React/Vue unless explicitly requested.

---

# 128. Recommended Implementation Order

Implement the project in this order.

## Phase 1 — Foundation

- Laravel 13 setup
- MySQL
- Authentication
- Livewire 4
- Alpine.js
- Tailwind
- Spatie Permission
- Base layout
- Navigation
- Theme/UI foundation

## Phase 2 — Clubs

- Clubs table
- Club CRUD
- Club status
- Club role configuration

## Phase 3 — Users

- User model
- User-club pivot
- Club user creation
- Global user creation
- User listing
- User editing
- User status
- User permissions

## Phase 4 — Roles & Permissions

- Spatie roles
- Permissions
- Club role identification
- Role/permission management
- Scope handling

## Phase 5 — Club Context

- Club context service
- Global club switcher
- Club-scoped navigation
- Global navigation
- Scope middleware/policies

## Phase 6 — Meetings

- Meeting model
- Meeting CRUD
- Meeting number
- Date
- Theme
- Venue
- Status
- Current-club creation

## Phase 7 — Meeting Roles

- Meeting role types
- Fixed role assignments
- TMOD
- GE
- TTM
- Listening Master
- Grammarian
- Ah Counter
- Timer

## Phase 8 — Dynamic Meeting Participants

- Prepared speakers
- TTM speakers
- Evaluators
- Dynamic Alpine/Livewire UI

## Phase 9 — Attendance

- Attendance
- Attendance statuses
- Role replacement
- Same-club replacement validation

## Phase 10 — Reports

- Meeting detail
- Meeting report
- Historical meetings
- Upcoming meetings

## Phase 11 — Dashboards

- Club dashboard
- Global dashboard
- Statistics

## Phase 12 — Polish

- Responsive design
- Loading states
- Empty states
- Error states
- Accessibility
- Performance
- Security
- Tests

---

# 129. Testing Requirements

Write tests for:

## Authentication

- Guest cannot access dashboard.
- Authenticated user can access dashboard.

## Club scope

- Club user sees only their club.
- Club user cannot access another club.
- Global user can access assigned clubs.
- Global user cannot access unassigned clubs.
- Super Admin can access all clubs.

## User creation

- Club user creates users only in their own club.
- Global user can assign multiple clubs.
- User cannot receive unauthorized club membership.

## Meetings

- Club user creates meeting in their own club.
- No club selector is required.
- Meeting automatically receives current club.
- Global user creates meeting within selected club.
- Users from another club cannot be assigned.

## Meeting roles

- Fixed roles can be assigned.
- Unauthorized users cannot modify meeting roles.

## Speakers

- Speakers are dynamic.
- Multiple speakers can be added.
- Speaker must belong to meeting club.

## TTM speakers

- Dynamic TTM speakers.
- TTM speakers must belong to meeting club.

## Evaluators

- Evaluators can be assigned dynamically.
- Evaluator must belong to meeting club.
- Evaluation points to correct speaker.

## Attendance

- Attendance can be recorded.
- Absent role holder can be replaced.
- Replacement must belong to same club.

## Historical data

- Existing meetings remain associated with their original club.
- User role changes do not modify historical meeting assignments.

---

# 130. Final Architecture

The final architecture should conceptually look like this:

```text
                         SPEECH CLUB
                              │
                    ┌─────────┴─────────┐
                    │                   │
              GLOBAL USERS         CLUB USERS
                    │                   │
             Multiple Clubs        One Club
                    │                   │
                    └─────────┬─────────┘
                              │
                            USERS
                              │
                 ┌────────────┼────────────┐
                 │            │            │
             Spatie       User Clubs    Profile
              Roles
                 │
          Authorization
                 │
                 ▼
              CLUBS
                 │
                 ▼
             MEETINGS
                 │
        ┌────────┼───────────┬──────────────┐
        │        │           │              │
        ▼        ▼           ▼              ▼
     Fixed    Speakers    TTM Speakers   Evaluators
     Roles       │           │              │
        │        └───────────┴──────────────┘
        │
        ▼
      USERS

              MEETINGS
                 │
                 ▼
             ATTENDANCE
```

---

# 131. Final Mental Model

The entire application should be understood using this simple model:

```text
USER
│
├── SYSTEM ROLE
│      ↓
│   What can I do?
│
├── CLUB MEMBERSHIP
│      ↓
│   Which clubs can I access?
│
└── CLUB ROLE
       ↓
    What is my position in that club?
```

Then:

```text
MEETING
│
├── CLUB
│
├── FIXED MEETING ROLES
│      ├── TMOD
│      ├── GE
│      ├── TTM
│      ├── Listening Master
│      ├── Grammarian
│      ├── Ah Counter
│      └── Timer
│
├── DYNAMIC SPEAKERS
│
├── DYNAMIC TTM SPEAKERS
│
├── DYNAMIC EVALUATORS
│
└── ATTENDANCE
```

And the most important rule:

```text
CURRENT CLUB CONTEXT
        ↓
MEETING
        ↓
ONLY USERS FROM THAT CLUB
```

This rule must be enforced both in the UI and on the backend.

---

# 132. Final Implementation Goal

The finished application should feel like a **professional internal Speech Club management platform**, not a generic CRUD application.

A normal member should be able to log in and immediately understand:

```text
This is my club.
These are my upcoming meetings.
These are my previous meetings.
These are the meeting reports.
```

A club officer should be able to:

```text
Manage members
Create meetings
Assign meeting roles
Add speakers
Add TTM speakers
Assign evaluators
Record attendance
Replace absent role holders
Finalize meeting reports
```

A global user should be able to:

```text
Manage multiple assigned clubs
Switch club context
Manage users
Manage meetings
View reports
```

A Super Admin should be able to:

```text
Manage the entire Speech Club system.
```

The architecture must remain:

```text
Laravel 13
+
Livewire 4
+
Alpine.js
+
Tailwind CSS
+
MySQL
+
Spatie Laravel Permission
```

with **strong club-level data isolation, clean role separation, dynamic meeting participation, and a spacious professional UI**.