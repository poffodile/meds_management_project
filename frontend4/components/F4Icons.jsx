/**
 * frontend4 icons — one family, one stroke, one size.
 *
 * WHY TABLER RATHER THAN LUCIDE
 * The visual specification asks for "one consistent line-icon family, such as
 * Lucide" — rounded line endings, ~1.75–2px stroke, simple shapes, no mixing of
 * filled and outlined systems. `@tabler/icons-react` is already a dependency of
 * this project and is the same style of family: 24×24, 2px stroke, round caps
 * and joins. Using it means no new shared dependency, and the icons tree-shake
 * into frontend4's bundle only — nothing changes for the other three front ends.
 *
 * Import icons from HERE, not from '@tabler/icons-react' directly, so the whole
 * front end has one place that decides stroke, size and naming. If the family
 * ever changes, it changes once.
 *
 * ACCESSIBILITY
 * Every icon here is decorative by default (`aria-hidden`). An icon that carries
 * meaning on its own must be given a label by its caller — the specification is
 * explicit that icons need accessible labels when their meaning is not obvious.
 */

import React from 'react';
import {
    IconAlertTriangle,
    IconCalendarTime,
    IconChartBar,
    IconCheck,
    IconChevronRight,
    IconClipboardList,
    IconClock,
    IconDots,
    IconFileText,
    IconFilter,
    IconMessage,
    IconPackage,
    IconPill,
    IconScan,
    IconSearch,
    IconSettings,
    IconShieldCheck,
    IconUser,
    IconUsers,
    IconUsersGroup,
    IconWifiOff,
    IconX,
} from '@tabler/icons-react';

/** The house stroke and size. Do not set these per call site. */
const BASE = { stroke: 1.75, size: 18, 'aria-hidden': true };

/** Wrap one Tabler icon so every use gets the same stroke, size and defaults. */
function icon(Component) {
    return function F4Icon({ size, label, ...rest }) {
        return (
            <Component
                {...BASE}
                {...rest}
                size={size || BASE.size}
                // An icon with a label is meaningful; without one it is decoration.
                aria-hidden={label ? undefined : true}
                aria-label={label}
                role={label ? 'img' : undefined}
            />
        );
    };
}

/* Navigation and shell */
export const Today      = icon(IconClipboardList);
export const People     = icon(IconUsers);
export const Medicines  = icon(IconPill);
export const Operations = icon(IconPackage);
export const Assurance  = icon(IconChartBar);
export const Settings   = icon(IconSettings);
export const Round      = icon(IconCalendarTime);
export const More       = icon(IconDots);

/* Actions and controls */
export const Search   = icon(IconSearch);
export const Filter   = icon(IconFilter);
export const Scan     = icon(IconScan);
export const Next     = icon(IconChevronRight);
export const Close    = icon(IconX);

/* Status and meaning */
export const Given    = icon(IconCheck);
export const Alert    = icon(IconAlertTriangle);
export const Time     = icon(IconClock);
export const Witness  = icon(IconUsersGroup);
export const Person   = icon(IconUser);
export const Shield   = icon(IconShieldCheck);
export const Note     = icon(IconFileText);
export const Message  = icon(IconMessage);
export const Offline  = icon(IconWifiOff);

export default {
    Today, People, Medicines, Operations, Assurance, Settings, Round, More,
    Search, Filter, Scan, Next, Close,
    Given, Alert, Time, Witness, Person, Shield, Note, Message, Offline,
};
