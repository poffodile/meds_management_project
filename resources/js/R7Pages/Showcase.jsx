import React, { useState } from 'react';
import Mark from '@record7/components/Mark.jsx';
import ThemeToggle from '@record7/components/ThemeToggle.jsx';
import PageHeading from '@record7/components/PageHeading.jsx';
import SectionHeading from '@record7/components/SectionHeading.jsx';
import Button from '@record7/components/Button.jsx';
import TextLink from '@record7/components/TextLink.jsx';
import Field from '@record7/components/Field.jsx';
import PasswordField from '@record7/components/PasswordField.jsx';
import CodeInput from '@record7/components/CodeInput.jsx';
import Notice from '@record7/components/Notice.jsx';
import StatusLabel from '@record7/components/StatusLabel.jsx';
import SafetyWarning from '@record7/components/SafetyWarning.jsx';
import ConfirmPanel from '@record7/components/ConfirmPanel.jsx';
import StateBlock from '@record7/components/StateBlock.jsx';
import HouseRow from '@record7/components/HouseRow.jsx';
import PersonIdentity from '@record7/components/PersonIdentity.jsx';
import MedicineIdentity from '@record7/components/MedicineIdentity.jsx';
import AppNav from '@record7/components/AppNav.jsx';
import useProduct from '@record7/useProduct.js';

/**
 * Every Record7 component and its important states, on one page.
 *
 * Development only. This is where the design system is reviewed as a system:
 * change one value in r7-tokens.css and the effect shows here at once, across
 * everything, rather than being discovered a screen at a time.
 *
 * All data below is invented and inert. Nothing here reads the database.
 */

const COLOUR_TOKENS = [
    '--r7-colour-primary', '--r7-colour-accent', '--r7-colour-accent-soft',
    '--r7-surface-page', '--r7-surface-raised', '--r7-surface-sunken',
    '--r7-surface-solid', '--r7-border-subtle', '--r7-border-strong',
    '--r7-state-success', '--r7-state-warning', '--r7-state-error',
    '--r7-state-info', '--r7-state-danger',
];

const TYPE_SPECIMENS = [
    ['r7-display', 'Display — Sora Bold'],
    ['r7-title', 'Title — Sora Semibold'],
    ['r7-heading', 'Heading — Sora Semibold'],
    ['r7-subhead', 'Subhead — Sora Medium'],
    ['r7-lede', 'Lede — Outfit Light, for the sentence under a heading'],
    ['r7-body', 'Body — Outfit Regular, the size a medicines round is read at'],
    ['r7-small', 'Small — Outfit, supporting detail'],
    ['r7-xs', 'Extra small — Outfit, timestamps and legal text'],
];

const HOUSES = [
    { id: 1, name: 'Oakwood House', type: 'Supported Living', town: 'Liverpool', accessType: 'standard' },
    { id: 2, name: 'Rosewood House', type: 'Supported Living', town: 'Liverpool', accessType: 'read_only' },
    { id: 3, name: 'Meadow View', type: 'Residential Care', town: 'Liverpool', accessType: 'temporary' },
];

const NAV = [
    { key: 'today', label: 'Today', href: '#', current: true },
    { key: 'people', label: 'People', href: '#' },
    { key: 'rounds', label: 'Rounds', href: '#' },
    { key: 'audit', label: 'Audit', href: '#' },
];

export default function Showcase({ environment }) {
    const product = useProduct();

    const [text, setText] = useState('Oakwood House');
    const [empty, setEmpty] = useState('');
    const [secret, setSecret] = useState('a-fictional-password');
    const [code, setCode] = useState('2468');

    return (
        <div className="r7-auth">
            <header className="r7-auth__head">
                <Mark productName={product.name} strapline="Component showcase" />
                <ThemeToggle />
            </header>

            <main className="r7-auth__body">
                <div style={{ width: '100%', maxWidth: 'var(--r7-width-app)' }}>
                    <PageHeading
                        eyebrow={`Development only — ${environment}`}
                        title="Record7 design system"
                        lede="Every component and its important states. Change a value in r7-tokens.css and everything here changes with it. This page is never registered in production."
                    />

                    <div className="r7-showcase">

                        <Group title="Colour" note={`${COLOUR_TOKENS.length} tokens`}>
                            <div className="r7-swatches">
                                {COLOUR_TOKENS.map((token) => (
                                    <div className="r7-swatch" key={token}>
                                        <span
                                            className="r7-swatch__chip"
                                            style={{ background: `var(${token})` }}
                                        />
                                        <span className="r7-swatch__name">{token}</span>
                                    </div>
                                ))}
                            </div>
                        </Group>

                        <Group title="Typography" note="Sora and Outfit">
                            <div className="r7-showcase__demo r7-showcase__demo--stack">
                                {TYPE_SPECIMENS.map(([className, label]) => (
                                    <p className={className} key={className}>{label}</p>
                                ))}
                            </div>
                        </Group>

                        <Group title="Branding">
                            <div className="r7-showcase__demo">
                                <Mark productName={product.name} />
                                <Mark productName={product.name} strapline={product.strapline} />
                                <div className="r7-mark--large"><Mark productName={product.name} /></div>
                            </div>
                        </Group>

                        <Group title="Buttons" note="One component, five variants">
                            <div className="r7-showcase__demo">
                                <Button variant="primary">Primary</Button>
                                <Button variant="secondary">Secondary</Button>
                                <Button variant="quiet">Quiet</Button>
                                <Button variant="warning">Warning</Button>
                                <Button variant="dangerous">Dangerous</Button>
                                <Button variant="bare">Bare</Button>
                            </div>
                            <div className="r7-showcase__demo">
                                <Button variant="primary" busy busyLabel="Working">Busy</Button>
                                <Button variant="primary" disabled>Disabled</Button>
                                <Button variant="quiet" size="small">Small</Button>
                                <TextLink href="#">A text link</TextLink>
                                <TextLink href="#" quiet>A quiet link</TextLink>
                            </div>
                            <div className="r7-showcase__demo r7-showcase__demo--stack">
                                <Button variant="primary" block>Block, as on a phone</Button>
                            </div>
                        </Group>

                        <Group title="Fields and validation">
                            <div className="r7-showcase__demo r7-showcase__demo--stack">
                                <Field label="Organisation" value={text} onChange={setText} />
                                <Field
                                    label="Organisation"
                                    hint="Capital letters and extra spaces do not matter."
                                    value={empty}
                                    onChange={setEmpty}
                                />
                                <Field
                                    label="Organisation"
                                    value={empty}
                                    onChange={setEmpty}
                                    error="Enter the organisation name your manager gave you."
                                />
                                <Field label="Disabled" value="Not editable" onChange={() => {}} disabled />
                                <PasswordField label="Password" value={secret} onChange={setSecret} />
                                <PasswordField
                                    label="Password"
                                    value={secret}
                                    onChange={setSecret}
                                    error="Your password does not match this username."
                                />
                                <CodeInput
                                    label="Six-digit code"
                                    hint="Digits only. It expires after five minutes."
                                    value={code}
                                    onChange={setCode}
                                />
                                <CodeInput
                                    label="Six-digit code"
                                    value={code}
                                    onChange={setCode}
                                    error="That code was not correct."
                                />
                            </div>
                        </Group>

                        <Group title="Notices" note="Inline, never a pop-up">
                            <div className="r7-showcase__demo r7-showcase__demo--stack">
                                <Notice tone="success">Your password has been changed. Please sign in.</Notice>
                                <Notice tone="warning">Your access to this house is temporary and ends on 5 September 2026.</Notice>
                                <Notice tone="error">We could not sign you in with those details.</Notice>
                                <Notice tone="info">Your access to this house is review only.</Notice>
                                <Notice tone="warning" title="Test environment">This environment accepts the fixed code 246810.</Notice>
                            </div>
                        </Group>

                        <Group title="Status labels" note="Always a word, never colour alone">
                            <div className="r7-showcase__demo">
                                <StatusLabel tone="success">Allowed</StatusLabel>
                                <StatusLabel tone="warning">Review due</StatusLabel>
                                <StatusLabel tone="error">Refused</StatusLabel>
                                <StatusLabel tone="info">Read only</StatusLabel>
                                <StatusLabel tone="neutral">Not assessed</StatusLabel>
                            </div>
                        </Group>

                        <Group title="Safety warning" note="Louder than an error, on purpose">
                            <div className="r7-showcase__demo r7-showcase__demo--stack">
                                <SafetyWarning title="This person has a recorded allergy to penicillin">
                                    Check the medicine against the allergy record before you continue.
                                    Recorded 14 January 2026 by a fictional prescriber.
                                </SafetyWarning>
                            </div>
                        </Group>

                        <Group title="Confirmation panel">
                            <div className="r7-showcase__demo r7-showcase__demo--stack">
                                <ConfirmPanel
                                    title="Record this administration?"
                                    facts={[
                                        { label: 'Person', value: 'A Fictional Person' },
                                        { label: 'Medicine', value: 'Amlodipine 5mg tablets' },
                                        { label: 'Dose', value: 'One tablet' },
                                        { label: 'House', value: 'Oakwood House' },
                                    ]}
                                    actions={
                                        <>
                                            <Button variant="primary">Yes, record it</Button>
                                            <Button variant="quiet">Go back</Button>
                                        </>
                                    }
                                >
                                    This cannot be deleted afterwards. A mistake is corrected by
                                    adding a correction, which keeps both entries.
                                </ConfirmPanel>
                            </div>
                        </Group>

                        <Group title="Identity">
                            <div className="r7-showcase__demo r7-showcase__demo--stack">
                                <PersonIdentity
                                    name="A Fictional Person"
                                    details={['Room 4', 'Born 3 March 1948', 'NHS number withheld']}
                                    status={<StatusLabel tone="warning">Allergy recorded</StatusLabel>}
                                />
                                <PersonIdentity
                                    name="Another Fictional Person"
                                    details={['Support Worker', 'Oakwood House']}
                                />
                                <MedicineIdentity
                                    name="Amlodipine"
                                    strength="5mg"
                                    form="tablets"
                                    code="dm+d 319938001"
                                />
                                <MedicineIdentity
                                    name="Morphine sulfate"
                                    strength="10mg/5ml"
                                    form="oral solution"
                                    code="dm+d 322280009"
                                    controlled
                                />
                            </div>
                        </Group>

                        <Group title="House selection">
                            <div className="r7-showcase__demo r7-showcase__demo--stack">
                                <ul className="r7-houses">
                                    {HOUSES.map((house) => (
                                        <li key={house.id}>
                                            <HouseRow
                                                house={house}
                                                current={house.id === 1}
                                                onChoose={() => {}}
                                            />
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        </Group>

                        <Group title="Screen states" note="Loading, empty, offline, restricted, error">
                            <div className="r7-showcase__demo r7-showcase__demo--stack">
                                <StateBlock state="loading" title="Loading the round" />
                                <StateBlock state="empty" title="Nothing due right now">
                                    The next medicines round for this house opens at 12:00.
                                </StateBlock>
                                <StateBlock state="offline" title="You are working offline">
                                    What you record is kept on this device and will be filed when
                                    the connection returns.
                                </StateBlock>
                                <StateBlock state="restricted" title="Not available to you">
                                    Your access to this house is review only, so you cannot record
                                    or change anything here.
                                </StateBlock>
                                <StateBlock
                                    state="error"
                                    title="We could not load this screen"
                                    action={{ label: 'Try again', onClick: () => {} }}
                                >
                                    Nothing has been recorded. Try again, and tell your manager if
                                    it keeps happening.
                                </StateBlock>
                            </div>
                        </Group>

                        <Group title="Navigation" note="Sidebar on desktop, bottom bar on a phone">
                            <div className="r7-showcase__demo">
                                <div style={{ width: 'var(--r7-width-sidebar)' }}>
                                    <AppNav items={NAV} variant="sidebar" />
                                </div>
                            </div>
                            <p className="r7-small r7-muted">
                                The bottom bar is fixed to the viewport, so it appears at the foot
                                of this page below 960 pixels rather than inside this box.
                            </p>
                        </Group>

                        <Group title="Panels and lists">
                            <div className="r7-showcase__demo r7-showcase__demo--stack">
                                <section className="r7-panel">
                                    <header className="r7-panel__head">
                                        <h3 className="r7-heading">A panel</h3>
                                        <span className="r7-label">With a note</span>
                                    </header>
                                    <div className="r7-panel__body">
                                        <ul className="r7-list">
                                            <li className="r7-list__row">
                                                <span className="r7-list__body">
                                                    <span className="r7-strong">Administer medication</span>
                                                    <span className="r7-code">administer_medication</span>
                                                </span>
                                                <StatusLabel tone="success">Allowed</StatusLabel>
                                            </li>
                                            <li className="r7-list__row">
                                                <span className="r7-list__body">
                                                    <span className="r7-strong">Manage controlled drugs</span>
                                                    <span className="r7-list__why">Competency for this action is not in place.</span>
                                                </span>
                                                <StatusLabel tone="error">Refused</StatusLabel>
                                            </li>
                                        </ul>
                                    </div>
                                </section>
                            </div>
                        </Group>

                    </div>
                </div>
            </main>

            <footer className="r7-auth__foot">
                <span>{product.seventhRight}</span>
                <span>Development only. This page is not registered in production.</span>
            </footer>
        </div>
    );
}

function Group({ title, note = null, children }) {
    return (
        <section className="r7-showcase__group">
            <SectionHeading title={title} note={note} level={2} />
            {children}
        </section>
    );
}
