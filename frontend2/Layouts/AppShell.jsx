import { useState } from 'react';
import {
    AppShell as MantineAppShell, Group, Text, Burger, ScrollArea, Box,
    ActionIcon, UnstyledButton, Menu, Switch, Collapse, Stack, useMantineColorScheme,
} from '@mantine/core';
import { useDisclosure } from '@mantine/hooks';
import { usePage, Link } from '@inertiajs/react';
import {
    IconLayoutDashboard, IconUsers, IconPill, IconCalendarEvent, IconChartBar,
    IconFileText, IconSettings, IconBell, IconChevronDown, IconMoon, IconArrowLeft,
    IconClipboardHeart, IconShieldLock, IconAlertTriangle, IconBox, IconSparkles,
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
const CANVAS = 'light-dark(#EEF1F8, var(--mantine-color-dark-8))';

const NAV = [
    { label: 'Dashboard', icon: IconLayoutDashboard, href: '/frontend2', exact: true },
    { label: 'Residents', icon: IconUsers, href: '/frontend2/residents' },
    {
        // Collapsible parent — everything medication lives inside here.
        group: 'Medication', icon: IconPill, children: [
            { label: 'Medication round', icon: IconClipboardHeart, href: '/frontend2/medication-round' },
            { label: 'Med round (new)', icon: IconClipboardHeart, href: '/frontend2/medication-round-v2' },
            { label: 'Med round (split)', icon: IconClipboardHeart, href: '/frontend2/medication-round-split' },
            { label: 'Medications', icon: IconPill, href: '/frontend2/medications' },
            { label: 'Missed doses', icon: IconAlertTriangle, href: '/frontend2/missed-doses' },
            { label: 'Controlled drugs', icon: IconShieldLock, href: '/frontend2/controlled-drugs' },
            { label: 'Stock', icon: IconBox, href: '/frontend2/stock' },
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
    const disabled = item.href === '#';
    const color = active ? ACCENT : 'rgba(255,255,255,0.88)';
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
    const disabled = item.href === '#';
    const color = active ? ACCENT : 'rgba(255,255,255,0.82)';
    const inner = (
        <Group className={disabled ? undefined : classes.navRow} data-active={active || undefined}
            gap="xs" wrap="nowrap" px="sm" py={8} mb={2} style={{
                color, opacity: disabled ? 0.6 : 1, cursor: disabled ? 'default' : 'pointer',
            }}>
            <Box w={5} h={5} style={{ borderRadius: '50%', flexShrink: 0, background: active ? ACCENT : 'rgba(255,255,255,0.5)' }} />
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
    const [open, setOpen] = useState(childActive);
    const color = childActive ? '#fff' : 'rgba(255,255,255,0.88)';
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

export default function AppShell({ children, title }) {
    const [mobileOpened, { toggle: toggleMobile }] = useDisclosure();
    const { props, url } = usePage();
    const role = props?.auth?.user?.role ?? 'carer';
    const userName = props?.auth?.user?.name ?? 'User';
    const home = props?.home; // shown as a chip in the header when the page provides it
    const { colorScheme, toggleColorScheme } = useMantineColorScheme();
    const path = url.split('?')[0];
    const isActive = (item) => item.href !== '#' && (item.exact ? path === item.href : path.startsWith(item.href));

    return (
        <RoleContext.Provider value={role}>
            <MantineAppShell
                layout="alt"
                header={{ height: 68 }}
                navbar={{ width: 236, breakpoint: 'sm', collapsed: { mobile: !mobileOpened } }}
                padding="lg"
            >
                {/* Sidebar — full-height gradient rail (logo → nav → bottom card). */}
                <MantineAppShell.Navbar style={{ background: GRAD, border: 'none' }}>
                    <MantineAppShell.Section px="md" pt="lg" pb="xs">
                        <BrandLogo tone="onDark" height={30} />
                    </MantineAppShell.Section>

                    <MantineAppShell.Section grow component={ScrollArea} px="sm" pt="md">
                        {NAV.map((item) => item.group
                            ? <NavGroup key={item.group} item={item} path={path} />
                            : <NavItem key={item.label} item={item} active={isActive(item)} />)}
                    </MantineAppShell.Section>

                    {/* Bottom — Care Copilot AI card (from the mockup) + a temp back link. */}
                    <MantineAppShell.Section px="sm" pb="md">
                        <Box p="md" style={{ borderRadius: 16, background: 'rgba(255,255,255,0.10)', border: '1px solid rgba(255,255,255,0.16)' }}>
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
                            style={{ display: 'flex', alignItems: 'center', gap: 6, justifyContent: 'center', width: '100%', color: 'rgba(255,255,255,0.7)', fontSize: 12, fontWeight: 600 }}>
                            <IconArrowLeft size={14} /> Back to main app (temp)
                        </UnstyledButton>
                    </MantineAppShell.Section>
                </MantineAppShell.Navbar>

                {/* Title bar — white bar over the content only (layout="alt"). */}
                <MantineAppShell.Header style={{ background: 'light-dark(#ffffff, var(--mantine-color-dark-7))', borderBottom: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))' }}>
                    <Group h="100%" pl="lg" pr={30} justify="space-between" wrap="nowrap">
                        <Group gap="sm" wrap="nowrap">
                            <Burger opened={mobileOpened} onClick={toggleMobile} hiddenFrom="sm" size="sm" />
                            <Text fw={800} fz={22} c="light-dark(var(--mantine-color-gray-9), var(--mantine-color-gray-1))">{title ?? 'CareOne'}</Text>
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
            </MantineAppShell>
        </RoleContext.Provider>
    );
}
