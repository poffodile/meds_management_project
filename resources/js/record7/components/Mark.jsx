import React from 'react';

/**
 * The Record7 mark: a midnight tile carrying the seven, beside the wordmark.
 *
 * Drawn in CSS so it stays crisp and follows the theme. The product name comes
 * from the server because the same software ships under more than one name.
 */
export default function Mark({ productName = 'Record7', strapline = null }) {
    const match = /^(.*?)(\d+)$/.exec(productName);

    return (
        <div className="r7-mark">
            <span className="r7-mark__glyph" aria-hidden="true">7</span>
            <span className="r7-mark__text">
                <span className="r7-mark__word">
                    {match ? (<>{match[1]}<em>{match[2]}</em></>) : productName}
                </span>
                {strapline ? <span className="r7-label">{strapline}</span> : null}
            </span>
        </div>
    );
}
