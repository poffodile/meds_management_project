# Frontend 4 authentication isolation

Care One OS uses a separate authentication boundary under `/frontend4`.

## Isolation contract

- The existing `/login`, `/logout`, `/admin/login` and API authentication routes are unchanged.
- Frontend 4 uses the `frontend4` session guard and `Frontend4User` provider model.
- Its session values are namespaced under `frontend4.*`.
- Its selected service is exposed to shared medication queries only while a Frontend 4 request is running, then the legacy session value is restored.
- Frontend 4 credentials, lockouts, password tokens and authentication events have dedicated tables.
- A Frontend 4 password reset never changes `user.password`, so it cannot change a legacy login.
- When a Frontend 4 credential row is first needed, it starts from the existing one-way password hash. No plaintext password is copied or stored.
- Frontend 4 authentication events are append-only at model level.

## Routes

- `GET /frontend4/login`
- `POST /frontend4/login/organisation`
- `POST /frontend4/login`
- `GET|POST /frontend4/select-service`
- `POST /frontend4/logout`
- `GET|POST /frontend4/forgot-password`
- `GET /frontend4/reset-password/{token}`
- `POST /frontend4/reset-password`

The login is a server-controlled three-step flow: organisation, username/password, then service. Service names are never returned before password verification. An account with one active service is routed into it automatically; an account with several active services uses the post-authentication picker. An account with no active service is signed back out and told to contact its manager.

`frontend4.identity` protects service selection and logout after the password has been verified. Every clinical Frontend 4 route remains behind `frontend4.auth`, which additionally requires and revalidates a selected service. A password-verified user without a service is redirected to `/frontend4/select-service`, never admitted to clinical pages.

## Deployment

Run the migration before enabling the Frontend 4 login:

```bash
php artisan migrate
```

The migration creates only:

- `frontend4_credentials`
- `frontend4_password_tokens`
- `frontend4_authentication_events`

It does not alter the legacy `user`, `admin` or `service_user` tables.

Optional Frontend 4-only environment settings:

```dotenv
FRONTEND4_AUTH_MAX_ATTEMPTS=5
FRONTEND4_AUTH_DECAY_SECONDS=900
FRONTEND4_AUTH_LOCKOUT_MINUTES=15
FRONTEND4_PASSWORD_TOKEN_MINUTES=30
FRONTEND4_IDLE_MINUTES=30
```
