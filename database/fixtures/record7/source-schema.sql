PRAGMA foreign_keys = ON;

CREATE TABLE schema_versions (
    version TEXT PRIMARY KEY,
    applied_at_utc TEXT NOT NULL
);

CREATE TABLE organisations (
    id TEXT PRIMARY KEY,
    legal_name TEXT NOT NULL,
    display_name TEXT NOT NULL,
    name_normalised TEXT NOT NULL UNIQUE,
    status TEXT NOT NULL CHECK (status IN ('active', 'suspended', 'inactive')),
    created_at_utc TEXT NOT NULL
);

CREATE TABLE services (
    id TEXT PRIMARY KEY,
    organisation_id TEXT NOT NULL REFERENCES organisations(id),
    name TEXT NOT NULL,
    service_type TEXT NOT NULL,
    town TEXT,
    status TEXT NOT NULL CHECK (status IN ('active', 'suspended', 'inactive')),
    created_at_utc TEXT NOT NULL,
    UNIQUE (organisation_id, name)
);

CREATE TABLE roles (
    id TEXT PRIMARY KEY,
    code TEXT NOT NULL UNIQUE,
    name TEXT NOT NULL UNIQUE,
    description TEXT NOT NULL,
    privilege_level INTEGER NOT NULL CHECK (privilege_level BETWEEN 0 AND 100)
);

CREATE TABLE permissions (
    id TEXT PRIMARY KEY,
    code TEXT NOT NULL UNIQUE,
    name TEXT NOT NULL,
    description TEXT NOT NULL,
    is_sensitive INTEGER NOT NULL DEFAULT 0 CHECK (is_sensitive IN (0, 1))
);

CREATE TABLE role_permissions (
    role_id TEXT NOT NULL REFERENCES roles(id),
    permission_id TEXT NOT NULL REFERENCES permissions(id),
    PRIMARY KEY (role_id, permission_id)
);

CREATE TABLE users (
    id TEXT PRIMARY KEY,
    organisation_id TEXT NOT NULL REFERENCES organisations(id),
    full_name TEXT NOT NULL,
    preferred_name TEXT,
    username TEXT NOT NULL,
    work_email TEXT,
    employee_reference TEXT,
    password_hash TEXT NOT NULL,
    account_status TEXT NOT NULL CHECK (
        account_status IN ('invited', 'active', 'security_locked', 'suspended', 'inactive', 'access_expired')
    ),
    employment_type TEXT NOT NULL CHECK (
        employment_type IN ('permanent', 'temporary', 'agency', 'contractor', 'external')
    ),
    access_starts_at_utc TEXT,
    access_ends_at_utc TEXT,
    last_signed_in_at_utc TEXT,
    created_at_utc TEXT NOT NULL,
    updated_at_utc TEXT NOT NULL,
    UNIQUE (organisation_id, username),
    UNIQUE (organisation_id, work_email)
);

CREATE TABLE user_roles (
    id TEXT PRIMARY KEY,
    user_id TEXT NOT NULL REFERENCES users(id),
    role_id TEXT NOT NULL REFERENCES roles(id),
    service_id TEXT REFERENCES services(id),
    starts_at_utc TEXT,
    ends_at_utc TEXT,
    granted_by_user_id TEXT REFERENCES users(id),
    granted_at_utc TEXT NOT NULL
);

CREATE TABLE user_service_access (
    id TEXT PRIMARY KEY,
    user_id TEXT NOT NULL REFERENCES users(id),
    service_id TEXT NOT NULL REFERENCES services(id),
    access_type TEXT NOT NULL CHECK (access_type IN ('standard', 'manager', 'oversight', 'read_only', 'temporary')),
    status TEXT NOT NULL CHECK (status IN ('active', 'suspended', 'expired', 'revoked')),
    starts_at_utc TEXT,
    ends_at_utc TEXT,
    granted_by_user_id TEXT REFERENCES users(id),
    granted_at_utc TEXT NOT NULL,
    UNIQUE (user_id, service_id)
);

CREATE TABLE user_permissions (
    id TEXT PRIMARY KEY,
    user_id TEXT NOT NULL REFERENCES users(id),
    permission_id TEXT NOT NULL REFERENCES permissions(id),
    service_id TEXT REFERENCES services(id),
    effect TEXT NOT NULL CHECK (effect IN ('allow', 'deny')),
    status TEXT NOT NULL CHECK (status IN ('active', 'suspended', 'expired', 'revoked')),
    starts_at_utc TEXT,
    ends_at_utc TEXT,
    reason TEXT NOT NULL,
    granted_by_user_id TEXT REFERENCES users(id),
    granted_at_utc TEXT NOT NULL
);

CREATE TABLE competency_types (
    id TEXT PRIMARY KEY,
    code TEXT NOT NULL UNIQUE,
    name TEXT NOT NULL,
    description TEXT NOT NULL,
    default_review_months INTEGER
);

CREATE TABLE user_competencies (
    id TEXT PRIMARY KEY,
    user_id TEXT NOT NULL REFERENCES users(id),
    competency_type_id TEXT NOT NULL REFERENCES competency_types(id),
    service_id TEXT REFERENCES services(id),
    status TEXT NOT NULL CHECK (
        status IN ('current', 'review_due', 'expired', 'suspended', 'not_assessed', 'not_required')
    ),
    assessed_at_utc TEXT,
    review_due_at_utc TEXT,
    assessed_by_user_id TEXT REFERENCES users(id),
    evidence_reference TEXT,
    notes TEXT
);

CREATE TABLE mfa_methods (
    id TEXT PRIMARY KEY,
    user_id TEXT NOT NULL REFERENCES users(id),
    method_type TEXT NOT NULL CHECK (
        method_type IN ('passkey', 'security_key', 'authenticator_app', 'hardware_otp', 'work_email', 'sms')
    ),
    label TEXT NOT NULL,
    is_primary INTEGER NOT NULL DEFAULT 0 CHECK (is_primary IN (0, 1)),
    status TEXT NOT NULL CHECK (status IN ('pending', 'active', 'revoked', 'lost')),
    registered_at_utc TEXT,
    last_verified_at_utc TEXT
);

CREATE TABLE account_invitations (
    id TEXT PRIMARY KEY,
    user_id TEXT NOT NULL REFERENCES users(id),
    token_hash TEXT NOT NULL,
    status TEXT NOT NULL CHECK (status IN ('pending', 'used', 'expired', 'cancelled')),
    expires_at_utc TEXT NOT NULL,
    sent_at_utc TEXT NOT NULL,
    used_at_utc TEXT,
    created_by_user_id TEXT REFERENCES users(id)
);

CREATE TABLE password_reset_requests (
    id TEXT PRIMARY KEY,
    user_id TEXT NOT NULL REFERENCES users(id),
    token_hash TEXT NOT NULL,
    status TEXT NOT NULL CHECK (status IN ('pending', 'used', 'expired', 'cancelled')),
    requested_at_utc TEXT NOT NULL,
    expires_at_utc TEXT NOT NULL,
    completed_at_utc TEXT
);

CREATE TABLE login_sessions (
    id TEXT PRIMARY KEY,
    user_id TEXT NOT NULL REFERENCES users(id),
    organisation_id TEXT NOT NULL REFERENCES organisations(id),
    active_service_id TEXT REFERENCES services(id),
    status TEXT NOT NULL CHECK (status IN ('active', 'locked', 'signed_out', 'expired', 'revoked')),
    device_reference TEXT NOT NULL,
    started_at_utc TEXT NOT NULL,
    last_activity_at_utc TEXT NOT NULL,
    locked_at_utc TEXT,
    ended_at_utc TEXT
);

CREATE TABLE access_audit_events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    event_uuid TEXT NOT NULL UNIQUE,
    organisation_id TEXT NOT NULL REFERENCES organisations(id),
    service_id TEXT REFERENCES services(id),
    user_id TEXT REFERENCES users(id),
    staff_name_at_time TEXT,
    role_name_at_time TEXT,
    event_type TEXT NOT NULL,
    event_result TEXT NOT NULL CHECK (event_result IN ('success', 'failure', 'denied', 'warning', 'information')),
    event_time_utc TEXT NOT NULL,
    session_id TEXT REFERENCES login_sessions(id),
    device_reference TEXT,
    authorised_by_user_id TEXT REFERENCES users(id),
    reason TEXT,
    risk_level TEXT NOT NULL DEFAULT 'none' CHECK (risk_level IN ('none', 'low', 'medium', 'high', 'critical')),
    previous_event_uuid TEXT,
    metadata_json TEXT
);

CREATE INDEX idx_services_org_status ON services (organisation_id, status);
CREATE INDEX idx_users_org_username ON users (organisation_id, username);
CREATE INDEX idx_users_org_status ON users (organisation_id, account_status);
CREATE INDEX idx_user_access_user_status ON user_service_access (user_id, status);
CREATE INDEX idx_user_access_service_status ON user_service_access (service_id, status);
CREATE INDEX idx_user_permissions_user_status ON user_permissions (user_id, status);
CREATE INDEX idx_user_competencies_user_status ON user_competencies (user_id, status);
CREATE INDEX idx_audit_org_time ON access_audit_events (organisation_id, event_time_utc DESC);
CREATE INDEX idx_audit_service_time ON access_audit_events (service_id, event_time_utc DESC);
CREATE INDEX idx_audit_user_time ON access_audit_events (user_id, event_time_utc DESC);
CREATE INDEX idx_audit_type_result ON access_audit_events (event_type, event_result);

CREATE TRIGGER access_audit_events_no_update
BEFORE UPDATE ON access_audit_events
BEGIN
    SELECT RAISE(ABORT, 'access audit events are append-only');
END;

CREATE TRIGGER access_audit_events_no_delete
BEFORE DELETE ON access_audit_events
BEGIN
    SELECT RAISE(ABORT, 'access audit events are append-only');
END;
