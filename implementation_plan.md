# Implementation Plan: Concussion Recovery Clearance Tracker (Laravel)

Build a full Laravel web application for the hackathon project: **Concussion Recovery Clearance Tracker** — a multi-stakeholder recovery coordination and clearance platform connecting students/parents, schools, and healthcare providers (doctors) with AI-assisted symptom logging, CDC HEADS UP recovery progression, one-time secure approval links, and a modular multi-provider AI engine.

## User Review Required

> [!IMPORTANT]
> - **Primary Keys**: All database tables use UUIDs (`Illuminate\Database\Eloquent\Concerns\HasUuids`).
> - **Patient/Parent Demo Authentication & Strict Isolation**: Patients/parents log in via a demo magic link flow. `EnsurePatientAuthenticated` middleware guarantees that an authenticated patient can **ONLY** access their own passport. If Patient A navigates to `/patients/{patient_B_uuid}/passport`, access is strictly denied (403 Forbidden / redirect with security notice).
> - **Strict Tokenized Approvals (No Passport Bypass)**: There is **NO** approve/reject button on the passport view itself. The passport only displays the simulated one-time approval URLs for testing convenience. The actual decision action occurs exclusively on `/approve/{token}`.
> - **Decoupled Multi-Provider AI Architecture**: AI calls are abstracted via an `AiCompletionProvider` contract, allowing seamless switching between Gemini (`gemma-4-26b-a4b-it`) and any OpenAI-compatible API (Groq, OpenRouter, Together, OpenAI, DeepSeek, etc.) via `.env` configuration without changing business logic.
> - **Safety-First AI Pipeline**: AI never clears patients. All clearance is strictly decided by doctors/schools/parents through one-time approval links. A deterministic keyword safety check overrides false-negative AI evaluations for clinical danger signs.

## Proposed Changes

### Project Scaffolding & Setup

- Create/scaffold the Laravel application in the workspace root directory.
- Configure `.env` and `.env.example` with SQLite database setup, AI multi-provider configuration, and app keys.

---

### Database & Migrations

#### [NEW] `database/migrations/xxxx_create_patients_table.php`
- `id` (UUID PK), `name` (string), `injury_type` (string), `injury_date` (date), `current_stage` (integer default 1), `activity_step` (integer nullable, for stage 3), `parent_email` (string), `login_token` (string 64 nullable), `login_token_expires_at` (timestamp nullable), `timestamps`.

#### [NEW] `database/migrations/xxxx_create_symptom_reports_table.php`
- `id` (UUID PK), `patient_id` (foreignUuid -> patients on delete cascade), `report_text` (text), `ai_severity` (string nullable: `mild|moderate|severe`), `ai_red_flag` (boolean default false), `ai_safety_override` (boolean default false), `extracted_symptoms` (json nullable), `reported_at` (timestamp), `timestamps`.

#### [NEW] `database/migrations/xxxx_create_approval_links_table.php`
- `id` (UUID PK), `patient_id` (foreignUuid -> patients on delete cascade), `for_stage` (integer), `approver_role` (string: `doctor|school|parent`), `approver_name` (string), `token` (string 64 unique), `expires_at` (timestamp), `is_used` (boolean default false), `timestamps`.

#### [NEW] `database/migrations/xxxx_create_approval_records_table.php`
- `id` (UUID PK), `patient_id` (foreignUuid -> patients on delete cascade), `stage` (integer), `approver_role` (string), `decision` (string: `approved|rejected`), `ai_recommendation` (text nullable), `decided_at` (timestamp), `timestamps`.

---

### Models & Clinical Configuration

#### [NEW] `app/Models/Patient.php`, `SymptomReport.php`, `ApprovalLink.php`, `ApprovalRecord.php`
- Eloquent models using `HasUuids` and standard relationships (`hasMany` / `belongsTo`).

#### [NEW] `config/recovery_restrictions.php`
- Static restriction rules mapping `current_stage` and `activity_step` to restriction codes (`complete_rest`, `no_screen_time`, `no_contact_sports`, etc.).

---

### AI Architecture & Providers

#### [NEW] `app/Contracts/AiCompletionProvider.php`
- Interface defining `complete(string $prompt): string`.

#### [NEW] `app/Services/AiProviders/GeminiProvider.php`
- Implements `AiCompletionProvider`.
- Calls Google AI Studio API (`https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent`) with `config('services.ai.gemini.key')`.
- Extracts text from `candidates[0].content.parts[0].text`.

#### [NEW] `app/Services/AiProviders/OpenAiCompatibleProvider.php`
- Implements `AiCompletionProvider`.
- Generic provider for OpenAI, Groq, OpenRouter, Together AI, DeepSeek, etc.
- Posts to `{base_url}/chat/completions` with bearer token auth and `choices[0].message.content` extraction.

#### [NEW] `config/services.php` update
- Adds `ai` configuration block supporting `provider => env('AI_PROVIDER', 'gemini')`, `gemini` settings, and `openai_compatible` settings.

#### [NEW] `app/Providers/AppServiceProvider.php`
- Binds `AiCompletionProvider` interface in `register()` to `GeminiProvider` or `OpenAiCompatibleProvider` dynamically based on configuration.

#### [NEW] `app/Services/SymptomAnalyzer.php`
- Injects `AiCompletionProvider` via constructor.
- `extractSymptoms()` and `generateSummary()` construct prompts and invoke `$this->provider->complete($prompt)`.
- Preserves robust `extractJson()` parsing (code fence stripping, regex substring extraction, and fail-safe fallback to `moderate` + `red_flag: true`).
- Preserves deterministic `applyKeywordSafetyCheck()` (10–14 bilingual red-flag keywords overriding false negatives).

---

### Authentication, Middleware & Traits

#### [NEW] `app/Http/Controllers/AuthController.php`
- `showLoginForm()`: Displays the demo login screen with quick-select demo patient cards.
- `sendMagicLink(Request $request)`: Validates email, creates single-use token, logs to `storage/logs/laravel.log`, and flashes token link for instant demo testing.
- `verifyMagicLink(string $token)`: Validates token, sets session `authenticated_patient_id`, clears token, and redirects to passport.
- `logout()`: Clears session and redirects to login.

#### [NEW] `app/Http/Middleware/EnsurePatientAuthenticated.php`
- Middleware protecting `/patients/{patient}/passport`.
- **Cross-Patient Isolation**: Verifies that `session('authenticated_patient_id')` is present **AND** strictly matches the `{patient}` route parameter (e.g. `$request->route('patient')->id === session('authenticated_patient_id')`). If unauthenticated, redirects to `/login`. If authenticated as Patient A but attempting to view Patient B, denies access with `403 Forbidden` / unauthorized response.

#### [NEW] `app/Http/Middleware/SetLocale.php`
- Reads session locale (`en` / `id`), sets `App::setLocale()`.

#### [NEW] `app/Traits/BuildsRecoveryContext.php`
- Shared helper providing milestone status, 7-day trend, active restrictions, and approver links.

---

### Controllers & Routing

#### [NEW] `app/Http/Controllers/SymptomReportController.php`
- Handles symptom report intake, AI extraction via `SymptomAnalyzer`, Stage 3 `activity_step` (+1 / -1), and milestone auto-downgrade logic.

#### [NEW] `app/Http/Controllers/PassportController.php`
- Renders the Recovery Passport dashboard for the authenticated patient.

#### [NEW] `app/Http/Controllers/ApprovalController.php`
- `show(string $token)`: Validates token and expiry, checks Stage 4 Step 6 prerequisite gate, displays full recovery context for the approver.
- `decide(Request $request, string $token)`: Records decision, verifies if all approvers for stage agreed, and advances milestone.

#### [NEW] `routes/web.php`
- Configures routes for auth, passport (middleware-guarded), symptom intake, one-time approvals, and language switching.

---

### Localization & Views

#### [NEW] `lang/en/app.php` & `lang/id/app.php`
- Full bilingual dictionary for milestones, activity steps, UI components, restrictions, and disclaimers.

#### [NEW] `resources/views/`
- `layouts/app.blade.php`: High-contrast, calm, clinical layout with navbar, language switcher, and auth state.
- `auth/login.blade.php`: Demo magic link login screen.
- `partials/ai-disclaimer.blade.php`: Standard AI assistance disclaimer.
- `partials/language-switcher.blade.php`: Language dropdown.
- `passport/show.blade.php`: Recovery Passport dashboard with milestone progression, 6-step physical activity dots, 7-day severity trend chart, active restrictions badges, approver status cards with simulated links, and symptom intake modal.
- `approval/show.blade.php`: Dedicated approval portal with complete patient context and official Approve/Reject actions.

---

### Seeders & Documentation

#### [NEW] `database/seeders/DatabaseSeeder.php`
- Seeds 3 demo patients (Alex Rivera - Stage 1, Jordan Taylor - Stage 2, Maya Chen - Stage 3 Step 3) with realistic symptom histories and ready-to-test approval links.

#### [NEW] `README.md`
- Problem statement, Clinical Basis (CDC HEADS UP + PedsConcussion), Multi-Provider AI Architecture, Safety Guardrails, Demo Authentication guide, and Prototype Disclaimer.

---

## Verification Plan

### Automated Tests & CLI (`tests/Feature/` & `tests/Unit/`)
- **Cross-Patient Authorization Isolation Test**: Verify that when Patient A is authenticated in the session, attempting to access `GET /patients/{patient_B_uuid}/passport` returns **403 Forbidden**, preventing unauthorized access to other patients' sensitive recovery data.
- **Unauthenticated Passport Access Test**: Verify that unauthenticated requests to `GET /patients/{patient}/passport` redirect to `/login`.
- **Multi-Party Approval Consensus Test**: Verify `patients.current_stage` does **NOT** advance when only some (not all) required `approval_links` for that stage are approved (e.g., Doctor approved, School pending).
- **One-Time Link Security & Replay Prevention Test**: Verify `GET /approve/{token}` returns **404** when `is_used === true`, even if the token is otherwise valid and unexpired.
- **Stage 4 Step 6 Gate Test**: Verify `GET /approve/{token}` for Stage 4 returns **403 Forbidden** if `activity_step` has not reached 6.
- **Magic Link Authentication Flow Test**: Verify login token generation, validation, session setting, single-use token invalidation, and passport redirect.
- **AI Symptom Intake & Auto-Downgrade Test**: Verify stage 3 `activity_step` increment on mild symptoms, decrement on red flags, and milestone downgrade on recurrent red flags.
- **Keyword Safety Override Unit Test**: Verify deterministic safety keywords trigger `ai_safety_override: true` and `ai_red_flag: true` even when AI evaluation is false negative.
- **AI Multi-Provider Test**: Verify `AiCompletionProvider` contract binding for Gemini and OpenAI-compatible providers.

### Manual Verification
- Test magic link login with demo emails and cross-patient isolation.
- Test symptom report submission with mild, moderate, and severe/red-flag inputs (both EN & ID).
- Test switching AI provider in `.env` between Gemini and OpenAI-compatible endpoints.
- Verify keyword safety override triggers when danger words are used.
- Test opening one-time approval links in a separate browser tab to approve/reject milestones.
- Verify language switching between English and Indonesian seamlessly translates all UI text.
