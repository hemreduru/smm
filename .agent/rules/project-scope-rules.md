---
trigger: always_on
---

# Full System Specification (Laravel 12 / PHP 8.5)  
Instruction set for an AI that will implement the whole project end-to-end.

---

## 0) Product Summary
Build a multi-tenant web application that:
- Manages multiple social media accounts per user (Instagram, TikTok, YouTube Shorts)
- Allows linking accounts into “Account Groups” so one content publish targets multiple platforms at once
- Controls n8n workflows from the app (trigger, enable/disable, scheduling, execution logs)
- Shows recent posts per selected account/group and displays as many stats/insights as the APIs allow
- Supports multiple app users; each user’s tokens, API keys, and n8n settings are isolated
- Provides observability (logs, executions, retries, failures) for publishing and analytics sync

---

## 1) Non-Negotiable Global Rules
- **Bilingual application (TR/EN)**
  - All UI texts, confirmations, validation messages, notifications: TR + EN
- **Centralized notification system**
  - SweetAlert2 + Toastr must be integrated once globally (master layout)
- **Centralized Result return types**
  - Every user-facing operation must return one of:
    - `SuccessResult`
    - `FailResult`
    - `ServerErrorResult` (or equivalent)
  - No ad-hoc flashing/redirect messages scattered in controllers/views
- **Centralized confirmation handling**
  - All “confirm required” actions (delete, destructive actions, sensitive triggers) use a global Swal2 system
  - Confirmation should be declarative via HTML attributes (no per-page custom JS)
- **Logging policy**
  - Every store/update/delete must log **text-only** logs (no arrays)
  - Each log includes:
    - operation tag
    - user id
  - Example: `Log::info('TriggerN8N: User: 1 triggered n8n');`
- **Request validation policy**
  - Every store/update/delete must use a dedicated **Custom Request Class**
  - Validation failures must return `FailResult` and show as Toastr via centralized listener
- **DB transactions policy**
  - Default approach for write operations:
    - `DB::beginTransaction();`
    - `DB::commit();`
    - `DB::rollback();`
  - Do not use `DB::transaction(function(){})` style
- **Controllers must be minimal**
  - Controllers only: accept request, call service, return result/response
  - Business logic must live in services/repositories
- **Common logic**
  - Module-internal shared logic: Service layer (or Repository, choose consistently)
  - Cross-module utilities: Helper system
- **HTTP/AJAX**
  - If JS must call routes: use **Axios** (always)
- **Tables**
  - If tabular data is needed: use **Yajra DataTables** (always)

---

## 2) Architecture Overview
### Layers
- **HTTP Layer**
  - Controllers (thin)
  - Form Requests (validation + authorize)
  - Resources/Transformers (API responses)
- **Application Layer**
  - Services: orchestration + business rules
  - DTOs: enforce consistent data passing
  - Result classes: standardized returns
- **Domain Layer**
  - Entities/Models (Eloquent)
  - Enums (statuses, platforms, roles)
- **Infrastructure Layer**
  - Repositories (interfaces + Eloquent)
  - External API Clients (IG/TikTok/YT, n8n)
  - Queue Jobs, Schedulers, Webhooks

---

## 3) Tenancy / User Isolation
- Each user operates inside a **Workspace** context
- Tokens / keys / platform accounts / workflows are always tied to a workspace/user scope
- Never allow one user to access another user's platform tokens or n8n executions
- Use policies/guards everywhere to enforce workspace scoping

---

## 4) Core Modules (Behavior Spec)

## 4.1 Accounts & Linking (Account Groups)
### Requirements
- User can connect multiple accounts per platform (e.g., 5 Instagram, 3 TikTok, etc.)
- User can create **Account Groups**:
  - Example: Group “A” links one Instagram + one TikTok + one YouTube Shorts account
- When publishing content to a group, the system must publish to all linked platform accounts

### Must Support
- Connect/disconnect platform accounts
- Token health checks + refresh handling
- Group membership management

---

## 4.2 Content Lifecycle
### States
- `draft → approved → scheduled → publishing → published`
- `failed` state with retry support

### Requirements
- Content item stores:
  - video asset reference
  - caption + hashtags
  - target group(s) or direct accounts
  - schedule time
- Publishing creates deliveries per platform (one per linked account)
- Each delivery has execution tracking, status, and error details (safe for UI display)

---

## 4.3 n8n Orchestration
### Requirements
- From the app, the user can:
  - trigger workflow “Run now”
  - enable/disable workflow
  - configure schedule (times/day + specific hours + timezone)
  - view last executions and statuses
- Scheduling:
  - The app must support schedule definition and enforcement
  - Scheduling can be implemented via:
    - App-level scheduler calling n8n execute endpoints
    - OR n8n schedule nodes controlled via API
  - Choose the approach that is most stable and fully manageable from the app UI

### n8n Callback
- n8n must send results back to app via webhook:
  - generated content assets
  - AI output payloads
  - status of processing
- The app must validate webhook signatures/secret

---

## 4.4 Analytics / Stats Sync
### Requirements
- UI shows:
  - recent posts for selected account/group
  - best-effort stats (views, likes, comments, engagement)
- Analytics must run on a schedule (daily or more frequent depending on API limits)
- Handle partial availability:
  - if a platform doesn’t provide a metric, show “N/A” gracefully
- Maintain execution logs and retries for sync jobs

---

## 5) UI System Specification (High Level)
### Pages
- Auth (login/register/forgot)
- Workspace switch + user management (invite, roles)
- Dashboard (overview KPIs)
- Connected Accounts
- Account Groups
- Content Board (kanban)
- Content Detail (preview, edit, schedule, publish)
- Publish Logs / Failures
- Analytics (overview + post detail)
- Automation (n8n workflows list/detail/schedule)
- Settings (API keys, security, audit/log view)

### UI Component Policy
- Bootstrap-based, but fully customized via SCSS variables
- Component approach (Blade components or equivalent)
- All confirmation actions use declarative HTML attributes for Swal2 binding
- All notifications are routed through Result objects and handled centrally

---

## 6) Centralized Swal2 + Toastr (Critical Implementation Spec)
### Global Integration
- Master layout includes:
  - Swal2
  - Toastr
  - One global JS module that:
    - reads session/result payload
    - shows toastr based on result type
    - binds Swal2 confirmations to elements via HTML attributes

### Result System
- Create standardized result classes:
  - `SuccessResult(message, redirect?, data?)`
  - `FailResult(message, redirect?, errors?)`
  - `ServerErrorResult(message, redirect?)`
- All user-visible messages come from these
- Controllers return these consistently (redirect with flash payload or JSON response)

### Declarative Confirmation
- Any action requiring confirmation uses attributes like:
  - `data-confirm="true"`
  - `data-confirm-title="..."`
  - `data-confirm-text="..."`
  - `data-confirm-button="..."`
  - `data-confirm-method="delete"`
- No custom per-page JS for confirmations

---

## 7) Validation Policy (Custom Requests Everywhere)
- For every store/update/delete:
  - Create a dedicated Request class
- Validation failures return `FailResult` with a localized message
- UI displays errors using Toastr (global handler)
- Authorization rules are enforced inside Requests or Policies (prefer Requests for action-level checks)

---

## 8) Transaction + Error Handling Policy
- Every write operation must:
  - start transaction
  - execute repo/service writes
  - commit
  - rollback on any exception
- Every caught exception must:
  - log a text-only message with tag + user id
  - return `ServerErrorResult` (localized)
- Controllers must not contain transaction logic; services handle it

---

## 9) Repository Pattern Rules
- Use interfaces + Eloquent implementations
- Repositories handle:
  - query building
  - persistence
- Services handle:
  - orchestration across repositories
  - external API calls
  - transactions + business logic

---

## 10) External API Clients (Platform Connectors)
- Implement one client per platform:
  - Instagram client
  - TikTok client
  - YouTube client
- Each exposes a consistent interface:
  - publish content
  - fetch recent posts
  - fetch insights
  - refresh token
- All HTTP done with a shared HTTP client wrapper (timeouts, retry rules, error normalization)

---

## 11) Async Jobs / Queues
- Publish runs via queues (never block UI)
- Analytics sync runs via scheduler + queues
- Jobs must:
  - be idempotent
  - log with tag + user id
  - record execution outcome for UI

---

## 12) DataTables Standard (Yajra)
- Any listing tables in UI should be built using Yajra DataTables:
  - server-side paging
  - filters: status, platform, workspace, date range
- Axios used for any required JS-driven calls

---

## 13) Localization (TR/EN)
- Use Laravel localization files (PHP arrays)
- All:
  - UI strings
  - validation messages
  - toastr messages
  - swal titles/text/buttons
  must be localized

---

## 14) Quality Requirements
- PSR-12
- Service methods are small and focused
- Controllers extremely short
- No duplicated JS toast/confirm logic in pages
- Robust logging for all write paths
- Strong authorization boundaries between users/workspaces

---

## 15) Deliverables (What the AI must produce)
- Laravel 12 project scaffold (auth, tenancy baseline)
- Modules implementation:
  - accounts, groups, content, publish, analytics, n8n orchestration
- Centralized Result system
- Centralized Swal2 + Toastr integration
- Full TR/EN localization coverage
- Yajra DataTables for list pages
- Axios integration for dynamic actions
- Queue + scheduler setup for publishing and stats sync
- Clear admin/editor/viewer role behavior

---
