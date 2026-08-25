import React from 'react';

/**
 * A last look before something that cannot be taken back.
 *
 * The facts being confirmed are shown again, in a sunken block, so the person
 * checks the record rather than their memory of it. The confirming button
 * takes a variant so a dangerous action does not look like an ordinary one.
 */
export default function ConfirmPanel({ title, facts = [], children, actions = null }) {
    return (
        <section className="r7-confirm">
            <h2 className="r7-heading">{title}</h2>

            {facts.length ? (
                <dl className="r7-confirm__facts">
                    {facts.map((fact) => (
                        <div className="r7-def" key={fact.label}>
                            <dt className="r7-label">{fact.label}</dt>
                            <dd className="r7-def__value">{fact.value}</dd>
                        </div>
                    ))}
                </dl>
            ) : null}

            {children ? <div className="r7-small r7-muted r7-measure">{children}</div> : null}
            {actions ? <div className="r7-btn-row">{actions}</div> : null}
        </section>
    );
}
