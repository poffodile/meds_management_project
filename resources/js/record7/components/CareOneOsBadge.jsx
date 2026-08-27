import React from 'react';
import logoLight from '../assets/careoneos-light.png';
import logoDark from '../assets/careoneos-dark.png';

/**
 * "Part of Care One OS."
 *
 * The same software is RECORD7 when it stands alone and Care One OS when it is
 * the module inside Omega's wider platform. This is the endorsement that ties
 * the two together, and it is deliberately quiet: Record7 is the name on the
 * door of this screen, and Care One OS is the house it belongs to.
 *
 * Two artwork files because the wordmark is flat colour — white on a dark
 * ground, navy on a light one. `tone` picks; there is no automatic guess,
 * because the caller knows what it is sitting on and a wrong guess makes the
 * mark disappear.
 *
 * The files are Record7's OWN copies under resources/js/record7/assets. They
 * are the same artwork the other front ends use, but importing across the
 * @frontend alias would make Record7 depend on another front end's folder, and
 * the whole point of Record7 is that it does not.
 */
export default function CareOneOsBadge({ tone = 'onDark', label = 'Part of' }) {
    return (
        <span className="r7-careone">
            {label ? <span className="r7-careone__label">{label}</span> : null}
            <img
                className="r7-careone__mark"
                src={tone === 'onDark' ? logoLight : logoDark}
                alt="Care One OS"
            />
        </span>
    );
}
