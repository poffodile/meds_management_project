/**
 * frontend4 — wiring check.
 *
 * This was the landing page while frontend4 was a scaffold. Today took that job
 * at /frontend4; this stayed at /frontend4/start as what it always actually
 * was — proof that the route, the bundle and the scoped stylesheet are wired
 * up, which is what you want when something stops rendering.
 */

import React from 'react';
import { Head } from '@inertiajs/react';
import F4Shell from '@frontend4/components/F4Shell';
import { Status } from '@frontend4/components/F4Atoms';

export default function Home({ buildLabel, can = [], roleLabel = null, accessContext = null }) {
    return (
        <F4Shell
            title="Frontend 4"
            summary="A fourth front end, running beside frontends 1, 2 and 3 in the same application."
            placeSub={buildLabel}
            width="narrow"
            can={can}
            roleLabel={roleLabel}
            accessContext={accessContext}
        >
            <Head title="Frontend 4" />

            <div className="f4-stack">
                <section className="f4-card">
                    <h2>
                        Isolation{' '}
                        <Status status="given" label="Verified by this page" variant="badge" />
                    </h2>
                    <p>
                        If this page is styled, the scoped stylesheet loaded. Every rule
                        frontend4 owns is written under <code>.f4-root</code>, so it cannot
                        reach another front end — and no global stylesheet is loaded here,
                        so nothing can reach in.
                    </p>
                    <ul className="f4-list">
                        <li>Routes — <code>/frontend4</code> (Today), <code>/frontend4/start</code> (this page)</li>
                        <li>Root view — <code>resources/views/f4.blade.php</code></li>
                        <li>Entry — <code>resources/js/f4.jsx</code></li>
                        <li>Pages — <code>resources/js/F4Pages/</code></li>
                        <li>Styles — <code>frontend4/f4.css</code> only</li>
                    </ul>
                </section>

                <section className="f4-card">
                    <h2>Adding a screen</h2>
                    <p>
                        Drop a component in <code>resources/js/F4Pages/</code>, render it from
                        a controller extending <code>F4Controller</code>, and add its route
                        beside this one. Keep new styles in <code>f4.css</code> under
                        <code> .f4-root</code>; if a rule will not go there, that is the signal
                        it is about to leak.
                    </p>
                    <div className="f4-actions">
                        <a className="f4-btn" href="/frontend4">Go to Today</a>
                        <a className="f4-btn" data-variant="quiet" href="/">Back to the main app</a>
                    </div>
                </section>
            </div>
        </F4Shell>
    );
}
