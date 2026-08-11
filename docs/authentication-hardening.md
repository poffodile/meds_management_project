# Authentication hardening

This change applies to the `care-one-integration` branch only.

## What changed

- Staff, administrator and service-user login attempts are throttled.
- Five failed attempts lock an account for 15 minutes by default.
- Inactive and soft-deleted accounts cannot sign in.
- Successful web logins regenerate the session identifier.
- Web logout is POST-only and invalidates the full session.
- Mobile login issues a 12-hour Laravel Sanctum bearer token.
- Mobile logout revokes the current token.
- Password setup and reset links now use random, hashed, one-use tokens.
- Password links expire after 30 minutes by default.
- Password-reset responses do not reveal whether an account exists.
- New passwords require at least 12 characters, mixed case, a number and a symbol.
- Legacy administrator MD5 hashes are upgraded to Laravel hashes after a valid login.
- Authentication events are written to an append-only event table.
- Session data is encrypted and expires after 30 idle minutes by default.

## Deployment order

1. Back up the production database and confirm the restore procedure.
2. Deploy to a non-production environment first.
3. Run `php artisan migrate --force`.
4. Set `SESSION_SECURE_COOKIE=true` when HTTPS is enabled.
5. Confirm that the mail configuration uses a company-controlled sender.
6. Test staff, agent, administrator and service-user password reset journeys.
7. Update mobile clients to store the returned bearer token securely and send it in the `Authorization` header.
8. Verify the mobile logout endpoint revokes the token.
9. Review `authentication_events` for successful, failed and locked-out attempts.

## Environment settings

```dotenv
SESSION_LIFETIME=30
SESSION_EXPIRE_ON_CLOSE=true
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
AUTH_MAX_ATTEMPTS=5
AUTH_DECAY_SECONDS=900
AUTH_LOCKOUT_MINUTES=15
AUTH_PASSWORD_TOKEN_MINUTES=30
```

Do not deploy production API keys, passwords or database credentials in the repository.

## Remaining rollout decision

The application upgrades a valid legacy MD5 administrator password to Laravel's current password hash automatically. Before production launch, administrators whose accounts still use a legacy hash should also complete a managed password-reset campaign so old weak passwords are not retained.
