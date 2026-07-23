---
description: Run a focused clinical-safety + regulatory review of a Care One OS page and update the clinical hazard log.
argument-hint: <page name, e.g. "Medication Round">
---

Run a focused **clinical safety and regulatory** review of the **$ARGUMENTS** page.

1. Read `docs/care-one-os/CLINICAL-HAZARD-LOG.md`, `MEDICATION-WORKFLOW.md`, `STANDARDS-REGISTER.md`.
2. Use the **clinical-safety-reviewer** agent: identify hazards (wrong resident, wrong/discontinued medicine, wrong dose, missed-dose not escalated, double administration, CD without witness, misplaced trust in barcode/GP import, lost/altered records, unsafe recovery); rate provisional risk (use the agreed matrix — do not invent thresholds); define controls, residual risk and safety requirements; update the hazard log. Apply DCB0129 (manufacturer) thinking and note DCB0160 risks to transfer to deploying organisations.
3. Use the **uk-compliance-reviewer** agent for the regulatory angle (CQC Reg 12/17, Human Medicines Regs 2012, Misuse of Drugs Regs 2001, MCA 2005, NICE SC1) — requirement + source + force + evidence (file:line) + status.
4. Cross-check controls against the real code.

Return: hazard rows added/updated, safety requirements (Critical/Important/Optional), regulatory findings, and **explicitly** the decisions that require a **Clinical Safety Officer / pharmacist / medication lead**. Do not claim the page is clinically safe or compliant — say what a qualified human must sign off. No code changes in this command.
