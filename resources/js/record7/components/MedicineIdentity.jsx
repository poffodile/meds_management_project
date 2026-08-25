import React from 'react';

/**
 * What a medicine is.
 *
 * Three separate lines on purpose: the name, then the strength and form, then
 * the coded identifier. Run together they can be misread — "Amlodipine 5mg
 * tablets 10mg" is a real hazard — so they are given their own elements and
 * their own weight.
 *
 * The dm+d code is shown because a name alone does not identify a product.
 */
export default function MedicineIdentity({ name, strength = null, form = null, code = null, controlled = false }) {
    return (
        <div className="r7-medicine">
            <span className="r7-medicine__name">{name}</span>

            {strength || form ? (
                <span className="r7-medicine__form">
                    {[strength, form].filter(Boolean).join(' ')}
                </span>
            ) : null}

            <span className="r7-medicine__meta">
                {code ? <span className="r7-code">{code}</span> : null}
                {controlled ? <span className="r7-status r7-status--warning">Controlled drug</span> : null}
            </span>
        </div>
    );
}
