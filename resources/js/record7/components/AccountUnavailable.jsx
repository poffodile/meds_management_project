import React, { useState } from 'react';
import Button from './Button.jsx';

/**
 * The credentials were right, but the account cannot be used.
 *
 * WHY THIS SCREEN EXISTS
 * Telling someone their password was wrong when it was not is a lie, and it
 * sends them round a password-reset loop that cannot fix anything. Somebody
 * whose access has been withdrawn deserves to be told plainly, and pointed at
 * the person who can actually help.
 *
 * WHAT IT DELIBERATELY DOES NOT SAY
 * Not the house they were assigned to, not their role, not what they could do,
 * and not the word "suspended". The exact reason is recorded privately in the
 * access audit, where a manager can read it. This screen carries no facts about
 * the person at all — it takes no props describing them, so there is nothing
 * here to leak even by mistake.
 */
export default function AccountUnavailable({ signInUrl, supportUrl = null }) {
    const [showHelp, setShowHelp] = useState(false);

    return (
        <>
            <p className="r7-measure">
                Your account is not currently available. Please contact your manager or
                organisation administrator for help.
            </p>

            {showHelp ? (
                <div className="r7-confirm__facts">
                    <p className="r7-small">
                        Your manager or organisation administrator can restore access to your
                        account. Record7 support cannot change who has access to an organisation.
                    </p>
                </div>
            ) : null}

            <div className="r7-btn-row">
                <Button variant="primary" onClick={() => { window.location.href = signInUrl; }}>
                    Return to sign in
                </Button>

                {supportUrl ? (
                    <Button variant="quiet" onClick={() => { window.location.href = supportUrl; }}>
                        Contact support
                    </Button>
                ) : (
                    <Button
                        variant="quiet"
                        onClick={() => setShowHelp((open) => !open)}
                        aria-expanded={showHelp}
                    >
                        Contact support
                    </Button>
                )}
            </div>
        </>
    );
}
