# Record7 MFA — what exists, so it can be kept or dropped later

**Status: paused. Nothing below is production-ready, and none of it is
committed.** This is an inventory for a later decision about what stays as
scaffolding and what goes.

The verification step is now **optional and off by default**, so the normal
test journey is:

> organisation → username and password → house → Today

---

## The switch

`config/record7.php` → `record7.mfa.mode`, from `RECORD7_MFA_MODE`.

| Mode | What happens |
|---|---|
| **`off`** *(default, and what `.env` is set to)* | The step is not in the journey at all |
| `test` | The screen appears, accepts `246810`, under a "not real security" label |
| `production` | A real provider is required; none exists, so it refuses everything |

**The production environment forces `production` whatever the config says**, so
`off` can never disable verification on a live system. An unrecognised value
falls back to `off`.

---

## Files added

### Core — needed for the step to exist at all

| File | Lines | Keep? |
|---|---|---|
| `app/Services/Record7/VerificationPolicy.php` | ~430 | The decision engine. Most of it |
| `app/Http/Middleware/Record7/PendingVerification.php` | 48 | Yes — holds partial authentication |
| `resources/js/R7Pages/Auth/Verify.jsx` | ~105 | Yes — the screen itself |
| `resources/js/record7/components/CodeInput.jsx` | ~45 | Yes — reusable |

### Policy — built beyond the current requirement

| File | What for |
|---|---|
| `app/Models/Record7/TrustedDevice.php` | Device trust and revocation |
| `app/Models/Record7/RecoveryCode.php` | Recovery codes |
| `app/Models/Record7/VerificationEvent.php` | Why a challenge was made or skipped |

### Changed, not new

- `app/Services/Record7/AuthenticationService.php` — `verificationMode()`,
  `verificationStepEnabled()`, `hasRealVerificationProvider()`,
  `prototypeCode()`, `verifyCode()`, `issueRecoveryCodes()`,
  `consumeRecoveryCode()`
- `app/Http/Controllers/Record7/SignInController.php` — the verify actions and
  the branch that skips them
- `app/Models/Record7/User.php` — three relationships
- `config/record7.php` — the `mfa` block
- `.env`, `phpunit.xml` — mode and code

---

## Tables added

Two migrations, both on the `record7` connection.

`2026_08_25_000002_create_record7_verification_policy.php`

| Table | Rows today | Needed for the simple journey? |
|---|---|---|
| `record7_verification_events` | written on every sign-in | Useful audit; small |
| `record7_recovery_codes` | 0 | No |
| `record7_trusted_devices` | written when the step runs | No |
| *(columns on `record7_mfa_methods`)* | `secret`, `credential_reference`, `failed_attempts` | Only for real providers |

`2026_08_25_000003_add_record7_device_trust_policy.php`

| Change | Needed for the simple journey? |
|---|---|
| `record7_organisations.trusted_device_days` | No |
| `record7_organisations.device_trust_enabled` | No |
| `record7_trusted_devices.revoked_by_user_id`, `revoked_at`, `revoked_reason` | No |

**Nothing here is referenced by the organisation, credentials, house or Today
screens.** Dropping both migrations would leave the simple journey working; it
would break the two MFA test suites, which would be removed with them.

---

## Tests added

| Suite | Tests | Covers |
|---|---|---|
| `Record7VerificationModeTest` | 10 | The three modes, and that `off` is impossible in production |
| `Record7VerificationPolicyTest` | 31 | Elevation, device trust, revocation, recovery codes |

Existing suites run in `test` mode via `phpunit.xml`, so they still exercise the
full journey including verification.

---

## What is NOT built

No authenticator app, no passkey or WebAuthn, no email delivery, no SMS. Today
`verifyCode()` accepts only the fixed development code or a recovery code, and
in production neither is available. See `MFA-OUTSTANDING.md`.

---

## If the decision later is to trim

**Smallest safe removal**, leaving the simple journey untouched:

1. Drop `2026_08_25_000003` entirely.
2. From `2026_08_25_000002`, keep `record7_verification_events` and drop the
   other two tables and the `record7_mfa_methods` columns.
3. Delete `TrustedDevice`, `RecoveryCode`, and the trust and recovery sections
   of `VerificationPolicy` and `AuthenticationService`.
4. Delete `Record7VerificationPolicyTest`; keep `Record7VerificationModeTest`.

**Keep everything** if real MFA is coming at the security and deployment stage —
the decision logic is the part that takes thought, and it is written and tested.
The providers are the mechanical part.
