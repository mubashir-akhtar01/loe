# LOE Hub Technical Overview Narrative Plan

## Audience

- product leadership
- engineering
- delivery operations
- internal stakeholders reviewing MVP readiness

## Objective

Explain the currently implemented LOE Hub product in a technical but presentation-friendly way, with emphasis on rules, controls, lifecycle, and platform architecture.

## Narrative Arc

1. Define what the product is and what operating problem it solves.
2. Show how admin and employee responsibilities are separated.
3. Make the core thresholds and policy rules explicit.
4. Walk through the monthly reporting lifecycle.
5. Explain the calculation and alert engine.
6. Show how reminders, overdue handling, and closure automation work.
7. Summarize the data model and control surfaces.
8. Close with delivery status, current constraints, and next-phase boundaries.

## Slide List

1. LOE Hub Technical Baseline
2. Two-panel operating model
3. Core thresholds and reporting guardrails
4. Monthly report lifecycle
5. Calculation logic and variance handling
6. Automation and alert policies
7. Data model and control surfaces
8. Current implementation status

## Source Plan

- application services under `app/Services/LOE`
- Filament panel providers under `app/Providers/Filament`
- models and enums under `app/Models` and `app/`
- routes and scheduler definitions under `routes/`
- exported technical summary in `docs/LOE-technical-document.md`

## Visual System

- warm navy and amber as the primary identity
- structured cards, compact metric panels, and executive-brief layout spacing
- clean typography with strong headline contrast and restrained secondary text
- no screenshots required; the deck relies on designed shapes and dense information hierarchy

## Asset Needs

- no external imagery required for this version
- design relies on native PowerPoint shapes, panels, badges, and decorative geometry

## Editability Plan

- all visible slide text will remain editable PowerPoint text
- structure will use native shapes and layout geometry
- speaker notes will carry source and presenter context
