# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Stack
PHP / Laravel (Standard Laragon web application stack)

## Users
- **Healthcare Providers (Doctors)**: Oversee medical evaluations, define milestone and stage transitions, evaluate daily AI-assisted symptom reports, and grant official clinical clearance.
- **School Staff**: Coordinate Return-to-Learn accommodations, monitor academic recovery status, and approve the School Recovery milestone.
- **Students/Athletes & Parents**: Log daily symptom reports, track recovery trajectory via the Recovery Passport, view current restrictions, and receive approval status updates.

## Product Purpose
Provide a trustworthy, structured, and collaborative platform for managing concussion recovery protocols from initial injury through multi-stage Return-to-Learn (RTL) and Return-to-Play (RTP) progressions up to final certified medical clearance. Success means zero premature returns to contact, strict adherence to medical safety protocols, and complete transparency among doctors, schools, parents, and students.

## Positioning
An evidence-backed, multi-stakeholder clearance workflow combining rigorous clinical stage-gating (preventing progression while symptomatic) with real-time, role-based visibility and transparent, timestamped digital approvals.

## Operating Context
- 4-milestone recovery workflow (Initial Assessment -> School Recovery -> Graduated Physical Activity -> Final Clearance), with a 6-step sub-progression inside Graduated Physical Activity based on CDC HEADS UP's 6-Step Return to Play Progression: Step 1 Back to regular activities, Step 2 Light aerobic activity, Step 3 Moderate activity, Step 4 Heavy non-contact activity, Step 5 Practice & full contact, Step 6 Competition.
- School accommodations based on CDC HEADS UP "Returning to School" guidance.
- Daily free-text symptom reports analyzed via a 2-stage AI pipeline plus a keyword-based safety layer.
- Approval via one-time secure links per stage/role (doctor, school, parent) — no full account system for approvers.

## Capabilities and Constraints
- Role-based access via one-time secure links (Doctor, School Staff) and full login (Student/Parent) — no unified admin/coach dashboard.
- Incident intake & injury logging (injury date, mechanism, initial evaluation, baseline comparisons).
- Daily symptom tracking & progress visualization with severity scoring trends over time.
- Stage progression validation rules (minimum 24 hours symptom-free before advancing; immediate rollback and rest recommendation upon symptom recurrence).
- Digital approval records with decision, timestamp, and approver role, providing a full audit trail.
- PHP/Laravel backend architecture running within Laragon local environment.

## Brand Commitments
- Clinical, authoritative, calm, and high-trust aesthetic and tone.
- Unambiguous status indicators (e.g., Cleared, In Protocol: Stage 1–5, Withheld / Flagged).
- Accessible design optimized for users experiencing post-concussion symptoms (low eye-strain, soft balanced contrast, clear typography, reduced motion).

## Evidence on Hand
- CDC HEADS UP: Returning to Sports (6-Step Return to Play Progression) and Returning to School guidance.
- PedsConcussion Living Guideline for Pediatric Concussion (child/adolescent-specific grounding).

## Product Principles
1. **Safety First (Zero Premature Clearance)**: The system strictly enforces protocol stage prerequisites; stage progression cannot be bypassed without documented clinical sign-off and symptom clearance.
2. **Role Clarity & Actionable Simplicity**: Each stakeholder (doctor, school staff, parent/student) sees exactly what is relevant to their responsibilities with zero ambiguity on current clearance status.
3. **Cognitive Accessibility**: Interfaces must respect concussed users experiencing light/visual sensitivity by maintaining high legibility, comfortable spacing, and avoiding visual noise or disorienting animations.
4. **Auditability & Traceability**: Every status change, symptom entry, and clearance signature is timestamped and recorded for compliance and athletic safety records.

## Accessibility & Inclusion
- WCAG 2.1 AA compliance with special consideration for post-concussion light/glare sensitivity (soft contrast themes, clean typography, comfortable spacing).
- Responsive mobile & desktop access for quick reviews by doctors/school staff and at-home symptom logging by students/parents.
