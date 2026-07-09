import { useState, createContext, useContext } from 'react';
import {
    AppShell as MantineAppShell, Group, Text, Burger, ScrollArea, Box,
    ActionIcon, UnstyledButton, Menu, Switch, Collapse, Stack, Anchor, useMantineColorScheme, useComputedColorScheme,
} from '@mantine/core';
import { useDisclosure } from '@mantine/hooks';
import { usePage, Link } from '@inertiajs/react';
import {
    IconLayoutDashboard, IconUsers, IconPill, IconCalendarEvent, IconChartBar,
    IconFileText, IconSettings, IconBell, IconChevronDown, IconMoon, IconArrowLeft,
    IconClipboardHeart, IconShieldLock, IconAlertTriangle, IconBox, IconSparkles, IconCopy,
} from '@tabler/icons-react';
import { RoleContext } from '@frontend/lib/role';
import BrandLogo from '@frontend/components/BrandLogo';
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
        ],
    },
    {
        // Trial / superseded variants — parked here (not deleted) so the main menu stays clean.
        group: 'Duplicates', icon: IconCopy, children: [
            { label: 'Med round (new)', icon: IconClipboardHeart, href: '/frontend2/medication-round-v2' },
            { label: 'Med round (split)', icon: IconClipboardHeart, href: '/frontend2/medication-round-split' },
            { label: 'Med round (split B)', icon: IconClipboardHeart, href: '/frontend2/medication-round-split-b' },
            { label: 'Med round (split C)', icon: IconClipboardHeart, href: '/frontend2/medication-round-split-c' },
            { label: 'Missed doses (new)', icon: IconAlertTriangle, href: '/frontend2/missed-doses-b' },
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
    return <Box component={Link} href={item.href} style={{ textDecoration: 'none', display: 'block' }}>{inner}</Box>;
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
    const role = props?.auth?.user?.role ?? 'carer';
    const userName = props?.auth?.user?.name ?? 'User';
    const home = props?.home; // shown as a chip in the header when the page provides it
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
                            {home && (
                                <Group gap={8} wrap="nowrap" visibleFrom="sm">
                                    <Text fz={13.5} fw={700} c="light-dark(#13233F, var(--mantine-color-gray-1))">{home}</Text>
                                    <IconChevronDown size={14} stroke={2.4} color="#9aa4ae" />
                                </Group>
                            )}
                            <Text fz={13.5} fw={700} c="#7a8590" visibleFrom="sm">EN</Text>
                            {/* Bell — boxed (white, rounded, shadow) with an orange unread dot, per the mockup. */}
                            <ActionIcon variant="default" size={38} radius={11} pos="relative"
                                style={{ background: 'light-dark(#fff, var(--mantine-color-dark-6))', boxShadow: '0 2px 6px rgba(20,50,80,0.06)' }}>
                                <IconBell size={18} stroke={1.8} color="#5F6B76" />
                                <Box pos="absolute" top={8} right={9} w={8} h={8} style={{ background: '#F58321', borderRadius: '50%', border: '1.5px solid #fff' }} />
                            </ActionIcon>
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
                    {children}
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
