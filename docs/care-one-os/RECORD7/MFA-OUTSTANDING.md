# Record7 security verification — what is done, and what is not

**Status: the POLICY is built and enforced. The VERIFICATION ITSELF is a
prototype. Record7 is not ready for production sign-in.**

Nothing in this document should be read as "MFA is finished".

## What is built and working

The decision about **when** to demand a second factor is real, enforced on the
server, and covered by tests:

- first sign-in on an account
- a device that has not been seen before
- a shared device, which is never remembered for one person
- a device whose trust has expired
- after a password reset
- after recent failed attempts, or a lock
- any account with elevated access, decided from permissions and access type

And, equally deliberately, it is **not** demanded on every screen, or again
inside a session, or again on a trusted personal device until that trust runs
out.

Also built: recovery codes (hashed, one-time, reissuable), device records that
store only a hash of the device signature, organisation-configurable trust
duration, and administrator revocation of a trusted device.

## What is NOT built

**No real verification method is integrated.** There is no:

- authenticator app (TOTP) — no secret generation, no QR provisioning, no
  time-window validation, no drift tolerance
- passkey or security key — no WebAuthn registration, no attestation, no
  assertion verification
- email delivery of a one-time code
- SMS delivery of a one-time code

`record7_mfa_methods` carries the columns these will need, and the fixture
contains rows describing them, but nothing reads a real secret or verifies a
real credential.

## What actually happens today

`AuthenticationService::verifyCode()` accepts exactly two things:

1. A **fixed development code**, which requires all of: a non-production
   environment, `RECORD7_ALLOW_PROTOTYPE_MFA=true`, and
   `RECORD7_PROTOTYPE_MFA_CODE` set to a value.
2. One of the person's **recovery codes**.

In production the first is impossible — the environment check alone rules it
out — so unless recovery codes have been issued, **verification refuses
everything**. That is the correct behaviour for an unfinished control: it fails
closed rather than waving people through.

## Before production

1. Integrate at least one real method. TOTP is the smaller piece; passkeys are
   the better answer for shared care settings because there is no code to read
   aloud or write down.
2. Decide the delivery fallback and who may trigger it, and rate limit it.
3. Issue recovery codes at activation rather than on request.
4. Give administrators a screen for methods and devices. The service methods
   exist; there is no interface yet.
5. Remove `RECORD7_ALLOW_PROTOTYPE_MFA` from every environment file.
6. Have the whole flow reviewed against the DTAC technical-security criterion.

## Related

- `app/Services/Record7/VerificationPolicy.php` — when to ask
- `app/Services/Record7/AuthenticationService.php` — what is accepted
- `tests/Feature/Record7/Record7VerificationPolicyTest.php` — the guarantees
