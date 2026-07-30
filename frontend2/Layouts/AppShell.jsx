import { useState, createContext, useContext } from 'react';
import {
    AppShell as MantineAppShell, Group, Text, Burger, ScrollArea, Box,
    ActionIcon, UnstyledButton, Menu, Switch, Collapse, Stack, Anchor, SegmentedControl, Tooltip, ThemeIcon, useMantineColorScheme, useComputedColorScheme,
} from '@mantine/core';
import { useDisclosure } from '@mantine/hooks';
import { usePage, Link, router } from '@inertiajs/react';
import { IconCheck as IconCheckMark, IconBuilding } from '@tabler/icons-react';
import {
    IconLayoutDashboard, IconUsers, IconPill, IconCalendarEvent, IconChartBar,
    IconFileText, IconSettings, IconBell, IconChevronDown, IconMoon, IconArrowLeft,
    IconClipboardHeart, IconShieldLock, IconAlertTriangle, IconBox, IconSparkles, IconCopy, IconEye,
} from '@tabler/icons-react';
import { RoleContext, useRealRole, useViewAs, setViewAs } from '@frontend/lib/role';
import BrandLogo from '@frontend/components/BrandLogo';
import ErrorBoundary from '@frontend/components/ErrorBoundary';
import classes from './AppShell.module.css';

// ── Frontend2 — "CLINIK"-style shell ─────────────────────────────────────────
// A second app shell (separate sidebar) modelled on the clinic dashboard mockup:
// a full-height blue→indigo gradient sidebar, a light lavender canvas and a white
// title bar over the content. Same app/login as the main frontend; pages opt in
// by wrapping themselves in this <AppShell title="…">.
const ACCENT = '#4C6FFF';
// Navy brand gradient (base is the official Care One OS navy #13233F) — a deep,
// slightly-blue top easing into the brand navy at the bottom.
const GRAD = 'linear-gradient(180deg, #1E355F 0%, #13233F 62%, #0F1C33 100%)';
const CANVAS = 'light-dark(#ECEFF4, #131E33)';

// ── Sidebar look ─────────────────────────────────────────────────────────────
// 'navy'  → deep navy gradient rail, light nav text (original brand look)
// 'warm'  → soft warm-white rail with a warm border (light, on cream theme)
// 'cream' → same cream as the page, separated only by a subtle divider (seamless)
const SIDEBAR_MODE = 'navy';
const LIGHT_SIDEBAR = SIDEBAR_MODE !== 'navy';
const SIDEBAR_BG = SIDEBAR_MODE === 'navy'
    ? GRAD
    : SIDEBAR_MODE === 'warm'
        ? 'light-dark(#FBF8F1, var(--mantine-color-dark-7))'
        : 'light-dark(#ECEFF4, #182740)';
const SIDEBAR_BORDER = SIDEBAR_MODE === 'navy'
    ? 'none'
    : '1px solid light-dark(#DCE2EC, var(--mantine-color-dark-4))';
const NAV_TXT = LIGHT_SIDEBAR ? '#4A5568' : 'rgba(255,255,255,0.88)';
const NAV_TXT_SUB = LIGHT_SIDEBAR ? '#5A6678' : 'rgba(255,255,255,0.82)';
const NAV_DOT = LIGHT_SIDEBAR ? '#C2CAD6' : 'rgba(255,255,255,0.5)';
const NAV_GROUP_ACTIVE = LIGHT_SIDEBAR ? '#13233F' : '#fff';

// Nav colours are provided reactively so the sidebar can flip light↔dark with the
// colour scheme (dark-mode "swap": light rail on a dark body). Default = navy rail.
const NAV_TONE_NAVY = { txt: 'rgba(255,255,255,0.88)', sub: 'rgba(255,255,255,0.82)', dot: 'rgba(255,255,255,0.5)', groupActive: '#fff' };
const NAV_TONE_LIGHT = { txt: '#20304A', sub: '#3A4A66', dot: '#8B98AD', groupActive: '#13233F' };
const NavToneContext = createContext(NAV_TONE_NAVY);

const NAV = [
    { label: 'Dashboard', icon: IconLayoutDashboard, href: '/frontend2', exact: true },
    { label: 'Residents', icon: IconUsers, href: '/frontend2/residents' },
    {
        // Collapsible parent — everything medication lives inside here.
        group: 'Medication', icon: IconPill, children: [
            { label: 'Medication round', icon: IconClipboardHeart, href: '/frontend2/medication-round' },
            { label: 'Medications', icon: IconPill, href: '/frontend2/medications' },
            { label: 'Missed doses', icon: IconAlertTriangle, href: '/frontend2/missed-doses' },
            { label: 'Controlled drugs', icon: IconShieldLock, href: '/frontend2/controlled-drugs' },
            { label: 'Stock', icon: IconBox, href: '/frontend2/stock' },
            { label: 'Stock 2', icon: IconBox, href: '/frontend2/stock-2' },
        ],
    },
    {
        // Every trial / superseded round variant lives here, in ONE collapsible menu —
        // parked, not deleted (owner instruction 2026-07-23). These older pages still
        // render "Sleeping"/"Withheld" as given; the write path underneath them is the
        // corrected shared one, so the DATA they record is right, only their display is
        // stale. Kept reachable for reference; not for daily use.
        group: 'Duplicates', icon: IconCopy, children: [
            // Frontend 2 variants
            { label: 'Med round (new)', icon: IconClipboardHeart, href: '/frontend2/medication-round-v2' },
            { label: 'Med round (split)', icon: IconClipboardHeart, href: '/frontend2/medication-round-split' },
            { label: 'Med round (split B)', icon: IconClipboardHeart, href: '/frontend2/medication-round-split-b' },
            { label: 'Med round (split C)', icon: IconClipboardHeart, href: '/frontend2/medication-round-split-c' },
            { label: 'Missed doses (new)', icon: IconAlertTriangle, href: '/frontend2/missed-doses-b' },
            // Frontend 1 experimental variants (open in the old shell)
            { label: 'F1 round (react)', icon: IconClipboardHeart, href: '/medication/medication-round-react' },
            { label: 'F1 round (lab)', icon: IconClipboardHeart, href: '/medication/medication-round-lab' },
            { label: 'F1 round (lab 2)', icon: IconClipboardHeart, href: '/medication/medication-round-lab2' },
            { label: 'F1 round (lab 1.1)', icon: IconClipboardHeart, href: '/medication/medication-round-lab1-1' },
            { label: 'F1 round (lab 1.2)', icon: IconClipboardHeart, href: '/medication/medication-round-lab1-2' },
            { label: 'F1 round (lab 1.3)', icon: IconClipboardHeart, href: '/medication/medication-round-lab1-3' },
            { label: 'F1 round (lab 1.4)', icon: IconClipboardHeart, href: '/medication/medication-round-lab1-4' },
            { label: 'F1 round (lab 1.4.1)', icon: IconClipboardHeart, href: '/medication/medication-round-lab1-4-1' },
            { label: 'F1 round (lab 1.4.2)', icon: IconClipboardHeart, href: '/medication/medication-round-lab1-4-2' },
            { label: 'F1 round (lab 1.4.3)', icon: IconClipboardHeart, href: '/medication/medication-round-lab1-4-3' },
            { label: 'F1 round (v4)', icon: IconClipboardHeart, href: '/medication/medication-round-4' },
            { label: 'F1 round (v4.2)', icon: IconClipboardHeart, href: '/medication/medication-round-4-2' },
        ],
    },
    {
        // Second meds area — placeholder pages, to iterate on separately.
        group: 'Medication 2', icon: IconPill, children: [
            { label: 'Medication round', icon: IconClipboardHeart, href: '/frontend2/medication-2/round' },
            { label: 'Medications', icon: IconPill, href: '/frontend2/medication-2/medications' },
            { label: 'Missed doses', icon: IconAlertTriangle, href: '/frontend2/medication-2/missed-doses' },
            { label: 'Controlled drugs', icon: IconShieldLock, href: '/frontend2/medication-2/controlled-drugs' },
            { label: 'Stock', icon: IconBox, href: '/frontend2/medication-2/stock' },
        ],
    },
    { label: 'Scheduled visits', icon: IconCalendarEvent, href: '#' },
    { label: 'Statistics', icon: IconChartBar, href: '#' },
    { label: 'Reports', icon: IconFileText, href: '#' },
    { label: 'Settings', icon: IconSettings, href: '#' },
];

const childActiveFor = (item, path) => item.children.some((c) => c.href !== '#' && (c.exact ? path === c.href : path.startsWith(c.href)));

function NavItem({ item, active }) {
    const Icon = item.icon;
    const tone = useContext(NavToneContext);
    const disabled = item.href === '#';
    const color = active ? ACCENT : tone.txt;
    const inner = (
        <Group className={disabled ? undefined : classes.navRow} data-active={active || undefined}
            gap="sm" wrap="nowrap" px="sm" py={10} mb={4} style={{
                color, opacity: disabled ? 0.6 : 1, cursor: disabled ? 'default' : 'pointer',
            }}>
            <Icon size={20} stroke={1.7} color={color} />
            <Text className={classes.navLabel} size="sm" fw={active ? 700 : 500}>{item.label}</Text>
        </Group>
    );
    if (disabled) return <Box title="Coming soon">{inner}</Box>;
    // Frontend 1 pages (the parked /medication/* duplicates) are old-shell Blade pages,
    // not Inertia pages. An Inertia <Link> would XHR them and get non-Inertia HTML back;
    // a plain anchor does the correct full-page navigation into the old shell.
    if (!item.href.startsWith('/frontend2')) {
        return <Box component="a" href={item.href} style={{ textDecoration: 'none', display: 'block' }}>{inner}</Box>;
    }
    return <Box component={Link} href={item.href} style={{ textDecoration: 'none', display: 'block' }}>{inner}</Box>;
}

// A sub-nav row inside a collapsible group (dot + icon + label).
function SubNavItem({ item, active }) {
    const Icon = item.icon;
    const tone = useContext(NavToneContext);
    const disabled = item.href === '#';
    const color = active ? ACCENT : tone.sub;
    const inner = (
        <Group className={disabled ? undefined : classes.navRow} data-active={active || undefined}
            gap="xs" wrap="nowrap" px="sm" py={8} mb={2} style={{
                color, opacity: disabled ? 0.6 : 1, cursor: disabled ? 'default' : 'pointer',
            }}>
            <Box w={5} h={5} style={{ borderRadius: '50%', flexShrink: 0, background: active ? ACCENT : tone.dot }} />
            {Icon && <Icon size={16} stroke={1.7} color={color} />}
            <Text className={classes.navLabel} size="sm" fw={active ? 700 : 500}>{item.label}</Text>
        </Group>
    );
    if (disabled) return <Box title="Coming soon">{inner}</Box>;
    // Frontend 1 pages (the parked /medication/* duplicates) are old-shell Blade pages,
    // not Inertia pages. An Inertia <Link> would XHR them and get non-Inertia HTML back;
    // a plain anchor does the correct full-page navigation into the old shell.
    if (!item.href.startsWith('/frontend2')) {
        return <Box component="a" href={item.href} style={{ textDecoration: 'none', display: 'block' }}>{inner}</Box>;
    }
    return <Box component={Link} href={item.href} style={{ textDecoration: 'none', display: 'block' }}>{inner}</Box>;
}

/**
 * Header home-switcher (CR-06). Shows the active home. When the manager has more than
 * one home, it becomes a menu to switch between them; the chosen home drives every
 * medication/resident query server-side. With a single home it is a plain label.
 * `homes` is null for single-home users (the server withholds it), so the menu only
 * appears where it's meaningful.
 */
function HomeSwitcher({ home, homes }) {
    const list = homes?.list ?? [];
    const activeId = homes?.active;
    const activeName = list.find((h) => h.id === activeId)?.name ?? home;

    if (!homes || list.length < 2) {
        // Single home (or none) — the static label the header always had.
        return activeName ? (
            <Group gap={8} wrap="nowrap" visibleFrom="sm">
                <Text fz={13.5} fw={700} c="light-dark(#13233F, var(--mantine-color-gray-1))">{activeName}</Text>
            </Group>
        ) : null;
    }

    const switchTo = (id) => {
        if (id === activeId) return;
        router.post('/frontend2/switch-home', { home_id: id }, { preserveScroll: true });
    };

    return (
        <Menu position="bottom-start" withArrow width={240} shadow="md">
            <Menu.Target>
                <UnstyledButton visibleFrom="sm" style={{ display: 'inline-flex', alignItems: 'center', gap: 7, padding: '5px 10px', borderRadius: 999, border: '1px solid light-dark(#CBD5E4, rgba(255,255,255,0.14))', background: 'light-dark(#E4E9F1, var(--mantine-color-dark-7))' }}>
                    <IconBuilding size={15} stroke={1.9} color="#7A8697" />
                    <Text fz={13.5} fw={700} c="light-dark(#13233F, var(--mantine-color-gray-1))">{activeName ?? 'Select home'}</Text>
                    <IconChevronDown size={14} stroke={2.4} color="#9aa4ae" />
                </UnstyledButton>
            </Menu.Target>
            <Menu.Dropdown>
                <Menu.Label>Switch home ({list.length})</Menu.Label>
                {list.map((h) => (
                    <Menu.Item key={h.id} onClick={() => switchTo(h.id)}
                        leftSection={<IconBuilding size={15} stroke={1.8} />}
                        rightSection={h.id === activeId ? <IconCheckMark size={15} stroke={2.4} color="#1F9E93" /> : null}
                        fw={h.id === activeId ? 700 : 500}>
                        {h.name}
                    </Menu.Item>
                ))}
            </Menu.Dropdown>
        </Menu>
    );
}

// Collapsible group (e.g. Medication) — opens automatically when a child is active.
function NavGroup({ item, path }) {
    const Icon = item.icon;
    const isChildActive = (c) => c.href !== '#' && (c.exact ? path === c.href : path.startsWith(c.href));
    const childActive = childActiveFor(item, path);
    const tone = useContext(NavToneContext);
    const [open, setOpen] = useState(childActive);
    const color = childActive ? tone.groupActive : tone.txt;
    return (
        <Box mb={4}>
            <UnstyledButton onClick={() => setOpen((o) => !o)} w="100%">
                <Group className={classes.navRow} gap="sm" wrap="nowrap" px="sm" py={10} style={{ color }}>
                    <Icon size={20} stroke={1.7} color={color} />
                    <Text className={classes.navLabel} size="sm" fw={childActive ? 700 : 500} style={{ flex: 1 }}>{item.group}</Text>
                    <IconChevronDown size={15} stroke={1.8} style={{ transform: open ? 'rotate(180deg)' : 'none', transition: 'transform .15s' }} />
                </Group>
            </UnstyledButton>
            <Collapse in={open}>
                <Stack gap={2} mt={2} pl="md">
                    {item.children.map((c) => <SubNavItem key={c.label} item={c} active={isChildActive(c)} />)}
                </Stack>
            </Collapse>
        </Box>
    );
}

export default function AppShell({ children, title, section }) {
    const [mobileOpened, { toggle: toggleMobile }] = useDisclosure();
    const { props, url } = usePage();
    // Real role from the server; managers can PREVIEW the carer view (view-only, persisted,
    // shared app-wide via the role store so every page re-renders on toggle).
    const realRole = useRealRole();
    const isManager = realRole === 'manager';
    const viewAs = useViewAs();
    const applyViewAs = (v) => setViewAs(v);
    // Effective role handed to the whole app. Non-managers can never elevate.
    const role = isManager ? viewAs : realRole;
    const previewingCarer = isManager && viewAs === 'carer';
    const userName = props?.auth?.user?.name ?? 'User';
    const home = props?.home; // shown as a chip in the header when the page provides it
    const homes = props?.homes; // {active, list:[{id,name}]} when the user has >1 home (CR-06)
    const witnessPending = Number(props?.witnessPending ?? 0); // CD signatures awaiting this user (A2)
    const { colorScheme, toggleColorScheme } = useMantineColorScheme();
    const computedScheme = useComputedColorScheme('light');
    const dark = computedScheme === 'dark';
    // Dark-mode idea — sidebar & body swap: light mode = navy rail + light body;
    // dark mode = light rail + dark body (inverts the two).
    const lightSidebar = dark;
    const tone = lightSidebar ? NAV_TONE_LIGHT : NAV_TONE_NAVY;
    // Light rail (dark mode): soft top-lit gradient + a crisp seam and a shadow cast onto
    // the dark body, so the two zones read as deliberately layered, not glued together.
    const sidebarBg = lightSidebar ? 'linear-gradient(180deg, #F6F8FC 0%, #E7ECF4 100%)' : GRAD;
    const sidebarBorder = lightSidebar ? '1px solid #D6DDEA' : 'none';
    const sidebarShadow = lightSidebar ? '5px 0 22px rgba(6,13,28,0.45)' : 'none';
    const path = url.split('?')[0];
    const isActive = (item) => item.href !== '#' && (item.exact ? path === item.href : path.startsWith(item.href));

    return (
        <RoleContext.Provider value={role}>
            <MantineAppShell
                layout="alt"
                header={{ height: 68 }}
                navbar={{ width: 236, breakpoint: 'sm', collapsed: { mobile: !mobileOpened } }}
                footer={{ height: 40 }}
                padding="lg"
            >
                {/* Sidebar — navy rail (light mode) / light rail (dark mode swap). */}
                <MantineAppShell.Navbar className={lightSidebar ? classes.lightNav : undefined} style={{ background: sidebarBg, borderRight: sidebarBorder, boxShadow: sidebarShadow, zIndex: 3 }}>
                  <NavToneContext.Provider value={tone}>
                    <MantineAppShell.Section px="md" pt="lg" pb="xs">
                        <BrandLogo tone={lightSidebar ? 'onLight' : 'onDark'} height={30} />
                    </MantineAppShell.Section>

                    <MantineAppShell.Section grow component={ScrollArea} px="sm" pt="md">
                        {NAV.map((item) => item.group
                            ? <NavGroup key={item.group} item={item} path={path} />
                            : <NavItem key={item.label} item={item} active={isActive(item)} />)}
                    </MantineAppShell.Section>

                    {/* Bottom — Care Copilot AI card (from the mockup) + a temp back link. */}
                    <MantineAppShell.Section px="sm" pb="md">
                        <Box p="md" style={{ borderRadius: 16, background: lightSidebar ? GRAD : 'rgba(255,255,255,0.10)', border: lightSidebar ? 'none' : '1px solid rgba(255,255,255,0.16)' }}>
                            <Group gap={7} wrap="nowrap" mb={4}>
                                <IconSparkles size={16} color="#8FE3D4" />
                                <Text c="#fff" fw={800} fz="sm" lh={1.2}>Care Copilot</Text>
                            </Group>
                            <Text c="rgba(255,255,255,0.72)" fz={11} lh={1.35}>Draft care plans and spot risks in minutes, not hours.</Text>
                            <UnstyledButton component="a" href="#" mt="sm"
                                style={{ display: 'block', textAlign: 'center', width: '100%', background: '#5FA8A0', color: '#fff', borderRadius: 10, padding: '9px 10px', fontWeight: 700, fontSize: 12, letterSpacing: 0.4 }}>
                                TRY COPILOT
                            </UnstyledButton>
                        </Box>
                        {/* TEMP — remove later. Quick jump back to the main frontend. */}
                        <UnstyledButton component={Link} href="/medication/medication-round-4" mt="sm"
                            style={{ display: 'flex', alignItems: 'center', gap: 6, justifyContent: 'center', width: '100%', color: lightSidebar ? '#6B7684' : 'rgba(255,255,255,0.7)', fontSize: 12, fontWeight: 600 }}>
                            <IconArrowLeft size={14} /> Back to main app (temp)
                        </UnstyledButton>
                    </MantineAppShell.Section>
                  </NavToneContext.Provider>
                </MantineAppShell.Navbar>

                {/* Title bar — white bar over the content only (layout="alt"). */}
                <MantineAppShell.Header style={{ background: 'light-dark(#ECEFF4, #182740)', borderBottom: '1px solid light-dark(#DCE2EC, var(--mantine-color-dark-4))' }}>
                    <Group h="100%" pl="lg" pr={30} justify="space-between" wrap="nowrap">
                        <Group gap="sm" wrap="nowrap">
                            <Burger opened={mobileOpened} onClick={toggleMobile} hiddenFrom="sm" size="sm" />
                            {section ? (
                                <Group gap={9} wrap="nowrap" align="center">
                                    <Text fz={15} fw={500} c="light-dark(#8391A6, #8A97AC)">{section}</Text>
                                    <Text fz={15} fw={400} c="light-dark(#B4BECC, #667085)">›</Text>
                                    <Text fz={15} fw={700} c="light-dark(#2F4063, #E4E8EF)">{title}</Text>
                                </Group>
                            ) : (
                                <Text fw={800} fz={22} c="light-dark(var(--mantine-color-gray-9), var(--mantine-color-gray-1))">{title ?? 'CareOne'}</Text>
                            )}
                        </Group>
                        <Group gap={20} wrap="nowrap">
                            <HomeSwitcher home={home} homes={homes} />
                            {isManager && (
                                <Tooltip label="Preview the app as a carer sees it — view only, your real access is unchanged" withArrow openDelay={300} multiline w={240}>
                                    <Box visibleFrom="sm" style={{ display: 'inline-flex', alignItems: 'center', gap: 4, padding: '3px 4px 3px 3px', borderRadius: 999, background: 'light-dark(#E4E9F1, var(--mantine-color-dark-7))', border: '1px solid light-dark(#CBD5E4, rgba(255,255,255,0.14))', boxShadow: 'inset 0 1px 2px light-dark(rgba(19,35,63,0.10), rgba(0,0,0,0.35))' }}>
                                        {/* Leading "viewing as" eye — makes it obvious this is a view switch. */}
                                        <Box style={{ display: 'inline-flex', alignItems: 'center', justifyContent: 'center', width: 26, height: 26, borderRadius: '50%', flexShrink: 0, color: previewingCarer ? '#8A6D3B' : 'light-dark(#7A8697, #8A97AC)' }}>
                                            <IconEye size={16} stroke={1.9} />
                                        </Box>
                                        {[{ k: 'manager', l: 'Manager', c: '#1C325A' }, { k: 'carer', l: 'Carer', c: '#8A6D3B' }].map((o) => {
                                            const on = viewAs === o.k;
                                            return (
                                                <Box key={o.k} component="button" type="button" onClick={() => applyViewAs(o.k)}
                                                    onMouseEnter={(e) => { if (!on) e.currentTarget.style.background = 'light-dark(#FFFFFF, rgba(255,255,255,0.12))'; }}
                                                    onMouseLeave={(e) => { if (!on) e.currentTarget.style.background = 'light-dark(#F7F9FC, rgba(255,255,255,0.07))'; }}
                                                    style={{
                                                        border: 'none', cursor: 'pointer', borderRadius: 999, padding: '5px 16px', fontSize: 12.5, fontWeight: 700, whiteSpace: 'nowrap',
                                                        background: on ? o.c : 'light-dark(#F7F9FC, rgba(255,255,255,0.07))',
                                                        color: on ? '#FFFFFF' : 'light-dark(#44536B, #C3CDDD)',
                                                        boxShadow: on ? '0 3px 8px -3px rgba(19,35,63,0.55)' : 'inset 0 0 0 1px light-dark(rgba(19,35,63,0.06), rgba(255,255,255,0.06))',
                                                        transition: 'background .16s ease, color .16s ease',
                                                    }}>{o.l}</Box>
                                            );
                                        })}
                                    </Box>
                                </Tooltip>
                            )}
                            <Text fz={13.5} fw={700} c="#7a8590" visibleFrom="sm">EN</Text>
                            {/* Bell — links to "signatures awaiting you" (A2); shows a live count
                                of controlled-drug witness confirmations pending this user. */}
                            <Tooltip label={witnessPending > 0 ? `${witnessPending} controlled-drug signature${witnessPending === 1 ? '' : 's'} awaiting you` : 'No signatures awaiting you'} withArrow openDelay={300}>
                                <ActionIcon component={Link} href="/frontend2/medication-2/witness-confirmations"
                                    variant="default" size={38} radius={11} pos="relative" aria-label="Signatures awaiting you"
                                    style={{ background: 'light-dark(#fff, var(--mantine-color-dark-6))', boxShadow: '0 2px 6px rgba(20,50,80,0.06)' }}>
                                    <IconBell size={18} stroke={1.8} color="#5F6B76" />
                                    {witnessPending > 0 && (
                                        <Box pos="absolute" top={-4} right={-4} miw={17} h={17} px={4}
                                            style={{ background: '#F58321', borderRadius: 999, border: '1.5px solid #fff', color: '#fff', fontSize: 10.5, fontWeight: 800, lineHeight: '14px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontVariantNumeric: 'tabular-nums' }}>
                                            {witnessPending > 9 ? '9+' : witnessPending}
                                        </Box>
                                    )}
                                </ActionIcon>
                            </Tooltip>
                            <Menu position="bottom-end" withArrow width={210}>
                                <Menu.Target>
                                    {/* Bare "P" badge (mockup), but still opens the dropdown. */}
                                    <UnstyledButton aria-label="Account menu">
                                        <Box style={{ width: 38, height: 38, borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'rgba(58,124,165,0.16)', color: '#3A7CA5', fontSize: 13, fontWeight: 700 }}>
                                            {userName.charAt(0).toUpperCase()}
                                        </Box>
                                    </UnstyledButton>
                                </Menu.Target>
                                <Menu.Dropdown>
                                    <Box px="sm" pt={8} pb={4} style={{ lineHeight: 1.2 }}>
                                        <Text size="sm" fw={700}>{userName}</Text>
                                        <Text size="xs" c="dimmed">CareOne</Text>
                                    </Box>
                                    <Menu.Divider />
                                    <Box px="sm" py={6}>
                                        <Group justify="space-between">
                                            <Group gap={8}><IconMoon size={16} stroke={1.6} /><Text size="sm">Dark mode</Text></Group>
                                            <Switch size="sm" checked={colorScheme === 'dark'} onChange={() => toggleColorScheme()} />
                                        </Group>
                                    </Box>
                                    <Menu.Divider />
                                    <Menu.Item component="a" href="/logout">Log out</Menu.Item>
                                </Menu.Dropdown>
                            </Menu>
                        </Group>
                    </Group>
                </MantineAppShell.Header>

                <MantineAppShell.Main style={{ background: CANVAS, minHeight: '100dvh' }}>
                    {previewingCarer && (
                        <Group justify="center" gap={10} wrap="nowrap" mb="md" px="md" py={8}
                            style={{ background: 'light-dark(#FBEEDD, rgba(185,102,15,0.16))', border: '1px solid light-dark(#F0D3A8, rgba(185,102,15,0.35))', borderRadius: 12 }}>
                            <IconEye size={15} color="#B9660F" />
                            <Text fz={12.5} fw={700} c="light-dark(#8A4E0B, #E0A96A)">Previewing as a carer — manager-only controls are hidden.</Text>
                            <UnstyledButton onClick={() => applyViewAs('manager')} style={{ fontSize: 12.5, fontWeight: 800, color: '#1F9E93' }}>Back to manager view</UnstyledButton>
                        </Group>
                    )}
                    {/* Keyed by url so a caught error clears on navigation instead of sticking. */}
                    <ErrorBoundary key={url}>{children}</ErrorBoundary>
                </MantineAppShell.Main>

                {/* Slim generic footer — copyright + version, quick links, and a system-status pill. */}
                <MantineAppShell.Footer style={{ background: 'light-dark(#ECEFF4, #182740)', borderTop: '1px solid light-dark(#DCE2EC, var(--mantine-color-dark-4))' }}>
                    <Group h="100%" px="lg" justify="space-between" wrap="nowrap" gap="sm">
                        <Group gap={7} wrap="nowrap" style={{ minWidth: 0 }}>
                            <Text fz={12} fw={700} c="light-dark(#48576E, #A9B6CB)" truncate>© 2026 Care One OS</Text>
                            <Text fz={12} fw={500} c="light-dark(#8A97AC, #7C899E)" visibleFrom="xs">· v0.9 beta</Text>
                        </Group>
                        <Group gap={22} wrap="nowrap" visibleFrom="sm">
                            <Anchor href="#" fz={12} fw={600} c="light-dark(#586A85, #9AA8BE)" underline="never">Support</Anchor>
                            <Anchor href="#" fz={12} fw={600} c="light-dark(#586A85, #9AA8BE)" underline="never">Privacy</Anchor>
                            <Anchor href="#" fz={12} fw={600} c="light-dark(#586A85, #9AA8BE)" underline="never">Terms</Anchor>
                        </Group>
                        <Group gap={7} wrap="nowrap">
                            <Box w={7} h={7} style={{ borderRadius: '50%', background: '#1F9E93', boxShadow: '0 0 0 3px rgba(31,158,147,0.18)' }} />
                            <Text fz={12} fw={600} c="light-dark(#48576E, #A9B6CB)" truncate>All systems operational</Text>
                        </Group>
                    </Group>
                </MantineAppShell.Footer>
            </MantineAppShell>
        </RoleContext.Provider>
    );
}
