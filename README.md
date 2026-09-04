# Concussion Recovery Clearance Tracker

> **"A student shouldn't have to carry their recovery paperwork between a doctor, school, and sports team."**

Concussion Recovery Clearance Tracker is a multi-stakeholder recovery coordination and clearance platform designed for pediatric and adolescent student-athletes. It creates a unified, transparent, and auditable bridge connecting **Students & Parents**, **Schools**, and **Healthcare Providers (Doctors)** throughout the entire recovery lifecycle assisted by AI for symptom analysis while strictly preserving human-in-the-loop medical authority.

---

## 🏥 Clinical Basis & Progression Framework

The recovery workflow is structured around **4 core administrative milestones**, grounded directly in established pediatric concussion consensus protocols:

1. **Milestone 1 — Initial Assessment**:
   * *Authority*: Healthcare Provider (Doctor).
   * *Scope*: Clinical diagnosis, baseline cognitive/physical rest prescription, and baseline intake.
2. **Milestone 2 — School Recovery**:
   * *Authority*: School Staff (Nurses, Academic Counselors, Principals).
   * *Scope*: Return-to-Learn (RTL) classroom accommodations based on **CDC HEADS UP "Returning to School"** guidance (screen time limits, shortened days, rest breaks).
3. **Milestone 3 — Graduated Physical Activity**:
   * *Authority*: Healthcare Provider (Doctor / Athletic Trainer).
   * *Scope*: Multi-step Return-to-Play protocol following **CDC HEADS UP "6-Step Return to Play Progression"**:
     * **Step 1**: Back to regular daily activities (light walking).
     * **Step 2**: Light aerobic activity (stationary bike, jogging).
     * **Step 3**: Moderate activity (movement drills, light weightlifting).
     * **Step 4**: Heavy non-contact physical training (sprinting, high-intensity drills).
     * **Step 5**: Full contact practice drills (after clinical review).
     * **Step 6**: Full return to sport competition.
   * *Step Safety Mechanism*: `activity_step` advances one step at a time only when symptom-free. If symptoms flare or red flags appear, the step drops by 1 (or reverts to Milestone 2 if already at Step 1), preventing premature return to contact.
4. **Milestone 4 — Final Clearance**:
   * *Authority*: Multidisciplinary consensus (**Doctor + School + Parents**).
   * *Gating Prerequisite*: Can only be submitted for review once Step 6 of Graduated Physical Activity has been successfully completed.

*Additional Grounding: Informed by the pediatric-specific recovery principles of the **PedsConcussion Living Guideline for Pediatric Concussion**.*

---

## 🤖 AI Architecture, Data Handling & Safety Guardrails

### 1. Privacy-First Data Handling
* **Zero Patient PII in AI Prompts**: Only de-identified free-text symptom descriptions and temporal severity scores are sent to the AI model. Patient names, contact details, and identifying records remain strictly isolated in the database.

### 2. Multi-Provider AI Engine (`AiCompletionProvider`)
The application features a decoupled, modular AI architecture configured via `.env`:
* **Default Provider (`gemini`)**: Connects to Google AI Studio using the `gemma-4-26b-a4b-it` model.
* **OpenAI-Compatible Provider (`openai_compatible`)**: Seamlessly connects to any OpenAI-compatible API (Groq, OpenRouter, Together AI, Mistral, DeepSeek, OpenAI) by updating environment variables:
```env
AI_PROVIDER=openai_compatible
AI_API_KEY=your_api_key
AI_BASE_URL=https://api.groq.com/openai/v1  # or https://openrouter.ai/api/v1
AI_MODEL=llama-3.3-70b-versatile
```

### 3. Deterministic Keyword Safety Layer (Fail-Safe Guardrail)
* Even the most advanced LLMs can produce false negatives. As an added defensive layer, a deterministic, case-insensitive keyword matcher (`RED_FLAG_KEYWORDS`) scans all intake text for danger signals in both English and Indonesian (e.g. *repeated vomiting / muntah berulang*, *loss of consciousness / pingsan*, *severe confusion / kebingungan parah*, *seizure / kejang*, *sudden severe headache*).
* If a danger sign is matched, the system automatically overrides the AI output (`ai_safety_override: true`, `ai_red_flag: true`, severity `severe`), alerting approvers and triggering clinical setbacks.
* If AI parsing fails, the system fails safe to `moderate` severity and `red_flag: true`.

### 4. Strict Human Decision Authority
* **AI Never Clears Patients**: The AI pipeline only tracks, summarizes, flags, and reminds. It is programmatically forbidden from concluding "cured", "cleared", or "safe to advance".
* All milestone transitions require deliberate, timestamped decisions from authorized stakeholders via secure, single-use approval links.

### 5. Technical Limitations
* AI outputs are assistive summaries only and have not been validated as medical diagnostic software. Red-flag detection cannot substitute for professional clinical judgment.

---

## 🔐 Authentication, Approvals & Governance

* **Student / Parent Access**: Log in via demo magic link (enter parent email -> single-use login token generated -> verified -> session established). Protected by `EnsurePatientAuthenticated` middleware with strict cross-patient isolation (Patient A cannot view Patient B's records).
* **Approver Access (Doctors & School Staff)**: One-time tokenized URLs (`/approve/{token}`). No bulky account registration required for external clinicians or busy school nurses.
* **Replay & Consensus Protection**: Single-use tokens are consumed immediately upon submission (`is_used: true`). Multi-approver stages (e.g. Doctor + School) only advance when **100%** of required parties approve with zero rejections.
* **Clinical Rejection / Hold Notes Requirement**: When an approver decides to reject or hold a milestone, clinical notes explaining why are **conditionally required by validation** (`required_if:decision,rejected`). These notes are displayed directly in the patient's Recovery Passport and confirmation screen so parents and providers know what is required next.
* **Bilingual Localization (EN & ID)**: Full internationalization support with an instant toggle in the navbar. Clinical status terms maintain fixed schema identifiers while UI copy and guidance are available in English and Bahasa Indonesia.
* **In-App Demo Guide**: An interactive "💡 Guide & Demo Flow" modal accessible directly from the top navigation bar provides a 3-minute presentation cheat sheet and architectural walkthrough.

---

## 🚀 Quick Start & Local Setup

### Requirements
* PHP 8.2+
* Node.js & npm (for Tailwind CSS v4 asset compilation)
* Composer
* SQLite or MySQL

### 1. Installation
```bash
git clone <repository-url>
cd concussion_recovery_clearance_tracker
composer install
npm install
cp .env.example .env
php artisan key:generate
```

### 2. Build Frontend Assets
```bash
npm run build
# Or run hot-reloading dev server:
npm run dev
```

### 3. Configure AI Provider (Optional for AI symptom extraction)
Add your Gemini or Groq/OpenAI API key to `.env`:
```env
AI_PROVIDER=gemini
GEMMA_API_KEY=your_gemini_api_key
```

### 4. Migrate and Seed Demo Data
```bash
php artisan migrate:fresh --seed
```

### 5. Run Development Server
```bash
php artisan serve
```
Open `http://127.0.0.1:8000` in your browser.

> 💡 **Cloud Deployments & Quick Reset**:
> For cloud environments without terminal shell access (e.g., Render free tier), visiting `/reset-database` (or clicking the *Reset Demo Database* button on the login screen) executes `migrate:fresh --seed` automatically to restore all 3 demo accounts to their clean initial state.

---

## 🧪 Demo Patient Accounts Available Out of the Box

The database seeder pre-populates 3 realistic clinical recovery scenarios accessible from the quick-login cards:

| Student Name | Parent Email | Current Milestone | Recovery Scenario & Testing Tips |
| :--- | :--- | :--- | :--- |
| **Maya Chen** | `maya.chen@example.com` | **Milestone 3 (Step 3/6)** | Improving soccer forward with 7 days of logs. <br>⚠️ **CDC Safety Gate Demo**: Attempting to open her Final Clearance review links prematurely triggers an intentional **403 Access Restricted** safety page because CDC protocol forbids clearance before Step 6. Log 3 mild daily reports to advance her to Step 6 and unlock the portals! |
| **Alex Rivera** | `alex.rivera@example.com` | **Milestone 1 (Initial)** | Fresh injury (day 3), complete cognitive rest with Doctor link for Milestone 2. <br>💡 **AI Demo**: Click *Log Daily Symptoms* to test free-text extraction, layperson summaries, and Red Flag safety auto-downgrades. |
| **Jordan Taylor** | `jordan.taylor@example.com` | **Milestone 2 (School)** | Track athlete attending classes under RTL accommodations, with Doctor & School links for Milestone 3. <br>💡 **Approvals Demo**: Scroll down to open the Doctor and School review portals to test multi-party sign-off. |

---

## 🛡️ Automated Verification Suite

The platform includes an automated test suite covering security boundaries, approval workflows, clinical step gating, and AI guardrails:

```bash
php artisan test
```

* **Authentication & Cross-Patient Isolation**: 8 tests
* **Approval Flow, Consensus & Rejection Governance**: 18 tests
* **Symptom Intake & Clinical Step Auto-Downgrade**: 5 tests
* **Keyword Safety Guardrails & Fail-Safe Parsing**: 5 tests
* **AI Provider Binding & Modular Swapping**: 2 tests
* **Bilingual Localization (EN / ID)**: 2 tests
* **40 total tests, 143 assertions, 100% passing.**

---

## ⚠️ Prototype Medical Disclaimer

*This application is a software prototype created for hackathon demonstration and research purposes. It is not a certified medical device and is not intended to provide clinical diagnoses, treat medical conditions, or replace professional medical advice. Always consult a licensed healthcare professional for any medical concerns regarding head injuries or concussions.*
