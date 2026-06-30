import { useState } from 'react';
import {
    AppShell as MantineAppShell, Group, Text, Burger, ScrollArea, Avatar, Badge,
    SegmentedControl, ActionIcon, UnstyledButton, Box, Switch, Menu, Divider,
    Collapse, Stack, useMantineColorScheme,
} from '@mantine/core';
import { useDisclosure } from '@mantine/hooks';
import { usePage, Link } from '@inertiajs/react';
import {
    IconHome, IconNotebook, IconClock, IconShieldLock, IconBox, IconAlertTriangle, IconPill, IconFlask,
    IconArrowsLeftRight, IconLayoutGrid, IconCalendar, IconCalendarStats, IconUsers,
    IconUserCircle, IconSend, IconReportAnalytics, IconMessage, IconBell, IconMoon,
    IconChevronDown, IconChevronLeft,
} from '@tabler/icons-react';
import { RoleContext } from '@frontend/lib/role';
import BrandLogo from '@frontend/components/BrandLogo';
import AppFooter from '@frontend/components/AppFooter';
import classes from './AppShell.module.css';

// The whole left-nav, in render order. `section` rows are the grey group labels.
// Only the medication items have real routes today; the rest are placeholders (#).
const NAV = [
    { label: 'Home', icon: IconHome, href: '#' },
    { label: 'Daily Log', icon: IconNotebook, href: '#' },
    {
        // Demo flow — the four warm/editorial "4.1" pages, in click-through order.
        group: 'Demo · 4.1', icon: IconLayoutGrid, children: [
            { label: '1 · Medication Round', icon: IconClock, href: '/medication/medication-round-4' },
            { label: '2 · Medication Stock', icon: IconBox, href: '/medication/stock-4-1' },
            { label: '3 · Controlled Drugs', icon: IconShieldLock, href: '/medication/controlled-drugs-4-1' },
            { label: '4 · Missed Doses', icon: IconAlertTriangle, href: '/medication/missed-doses-4-1' },
        ],
    },
    {
        group: 'Medication', icon: IconPill, children: [
            // The primary Medication Round is now the polished Lab 1.4.2 build.
            { label: 'Medication Round', icon: IconClock, href: '/medication/medication-round-lab1-4-2' },
            // Editorial/warm style: serif, progress donut, schedule timeline.
            { label: 'Medication Round 4', icon: IconLayoutGrid, href: '/medication/medication-round-4' },
            // All the other trial versions tucked into one collapsible dropdown.
            {
                label: 'Round Versions', icon: IconFlask, children: [
                    { label: 'Original (React)', href: '/medication/medication-round-react' },
                    { label: 'Lab', href: '/medication/medication-round-lab' },
                    { label: 'Lab 1.1', href: '/medication/medication-round-lab1-1' },
                    { label: 'Lab 1.2', href: '/medication/medication-round-lab1-2' },
                    { label: 'Lab 1.3', href: '/medication/medication-round-lab1-3' },
                    { label: 'Lab 1.4', href: '/medication/medication-round-lab1-4' },
                    { label: 'Lab 1.4.1', href: '/medication/medication-round-lab1-4-1' },
                    { label: 'Lab 1.4.3', href: '/medication/medication-round-lab1-4-3' },
                    { label: 'Lab 2', href: '/medication/medication-round-lab2' },
                ],
            },
            { label: 'Medication Stock', icon: IconBox, href: '/medication/stock-react' },
            { label: 'Medication Stock (Lab)', icon: IconFlask, href: '/medication/stock-react-lab' },
            { label: 'Medication Stock (Lab 2)', icon: IconFlask, href: '/medication/stock-react-lab-2' },
            // Editorial/warm style matching Medication Round 4 (stock counterpart).
            { label: 'Medication Stock 4.1', icon: IconBox, href: '/medication/stock-4-1' },
            { label: 'Controlled Drugs', icon: IconShieldLock, href: '/medication/controlled-drugs-react' },
            // Editorial/warm style matching Medication Round 4.
            { label: 'Controlled Drugs 4.1', icon: IconShieldLock, href: '/medication/controlled-drugs-4-1' },
            { label: 'Missed Doses', icon: IconAlertTriangle, href: '/medication/missed-doses-react' },
            // Editorial/warm style matching Medication Round 4.
            { label: 'Missed Doses 4.1', icon: IconAlertTriangle, href: '/medication/missed-doses-4-1' },
        ],
    },
    { label: 'Shift Handover', icon: IconArrowsLeftRight, href: '/medication/shift-handover-react' },
    { section: 'Domiciliary Care' },
    { label: 'Dom Care Dashboard', icon: IconLayoutGrid, href: '#' },
    { label: 'Visit Schedule', icon: IconCalendar, href: '#' },
    { label: 'Carer Availability', icon: IconCalendarStats, href: '#' },
    { divider: true },
    { label: 'Carers', icon: IconUsers, href: '#' },
    { label: 'Clients', icon: IconUserCircle, href: '#' },
    { label: 'Runs', icon: IconSend, href: '#' },
    { label: 'Reports', icon: IconReportAnalytics, href: '#', managerOnly: true },
    { label: 'Communications', icon: IconMessage, href: '#' },
];

function NavItem({ item, active, collapsed }) {
    const Icon = item.icon;
    const disabled = item.href === '#';
    const inner = (
        <Group className={disabled ? undefined : classes.navRow} data-active={active || undefined}
            gap="sm" wrap="nowrap" px="sm" py={9} justify={collapsed ? 'center' : 'flex-start'} style={{
                color: active ? 'var(--mantine-color-brandTeal-light-color)' : 'light-dark(var(--mantine-color-gray-7), var(--mantine-color-gray-4))',
                opacity: disabled ? 0.55 : 1,
                cursor: disabled ? 'default' : 'pointer',
            }}>
            <Icon size={20} stroke={1.6} color={active ? 'var(--mantine-color-brandTeal-light-color)' : 'var(--mantine-color-brandTeal-6)'} />
            {!collapsed && <Text className={classes.navLabel} size="sm" fw={active ? 700 : 500}>{item.label}</Text>}
        </Group>
    );
    if (disabled) return <Box mb={2} title={collapsed ? item.label : 'Coming soon'}>{inner}</Box>;
    return <Box component={Link} href={item.href} mb={2} style={{ textDecoration: 'none', display: 'block' }}>{inner}</Box>;
}

// A sub-nav row: a small dot + a smaller icon + label (the medication children).
function SubNavItem({ item, active }) {
    const Icon = item.icon;
    return (
        <Box component={Link} href={item.href} style={{ textDecoration: 'none', display: 'block' }}>
            <Group className={classes.navRow} data-active={active || undefined}
                gap="xs" wrap="nowrap" px="sm" py={7} style={{
                    color: active ? 'var(--mantine-color-brandTeal-light-color)' : 'light-dark(var(--mantine-color-gray-6), var(--mantine-color-gray-5))',
                }}>
                <Box w={5} h={5} style={{ borderRadius: '50%', flexShrink: 0, background: active ? 'var(--mantine-color-brandTeal-light-color)' : 'var(--mantine-color-gray-4)' }} />
                {Icon && <Icon size={15} stroke={1.6} color={active ? 'var(--mantine-color-brandTeal-light-color)' : 'var(--mantine-color-brandTeal-6)'} />}
                <Text className={classes.navLabel} size="xs" fw={active ? 700 : 500}>{item.label}</Text>
            </Group>
        </Box>
    );
}

// A nested collapsible inside a NavGroup (e.g. "Round Versions"), holding SubNavItems.
// Starts collapsed so the version links stay out of the way until opened.
function SubNavGroup({ item, path }) {
    const Icon = item.icon;
    const childActive = item.children.some((c) => path === c.href);
    const [open, setOpen] = useState(false);
    return (
        <Box>
            <UnstyledButton onClick={() => setOpen((o) => !o)} w="100%">
                <Group className={classes.navRow}
                    gap="xs" wrap="nowrap" px="sm" py={7} style={{
                        color: childActive ? 'var(--mantine-color-brandTeal-light-color)' : 'light-dark(var(--mantine-color-gray-6), var(--mantine-color-gray-5))',
                    }}>
                    <Box w={5} h={5} style={{ borderRadius: '50%', flexShrink: 0, background: childActive ? 'var(--mantine-color-brandTeal-light-color)' : 'var(--mantine-color-gray-4)' }} />
                    {Icon && <Icon size={15} stroke={1.6} color={childActive ? 'var(--mantine-color-brandTeal-light-color)' : 'var(--mantine-color-brandTeal-6)'} />}
                    <Text className={classes.navLabel} size="xs" fw={childActive ? 600 : 500} style={{ flex: 1 }}>{item.label}</Text>
                    <IconChevronDown size={13} stroke={1.6} style={{ transform: open ? 'rotate(180deg)' : 'none', transition: 'transform .15s' }} />
                </Group>
            </UnstyledButton>
            <Collapse in={open}>
                <Stack gap={2} mt={2} pl="md">
                    {item.children.map((c) => <SubNavItem key={c.href} item={c} active={path === c.href} />)}
                </Stack>
            </Collapse>
        </Box>
    );
}

// A collapsible parent (e.g. Medication) with a chevron, holding SubNavItems / SubNavGroups.
function NavGroup({ item, url, mini }) {
    const Icon = item.icon;
    const path = url.split('?')[0];
    const childActive = item.children.some((c) => (c.children ? c.children.some((g) => path === g.href) : path === c.href));
    const [open, setOpen] = useState(childActive);
    if (mini) {
        return (
            <Box mb={2} title={item.group}>
                <Group className={classes.navRow} data-active={childActive || undefined}
                    justify="center" wrap="nowrap" py={9} style={{
                        color: childActive ? 'var(--mantine-color-brandTeal-light-color)' : 'light-dark(var(--mantine-color-gray-7), var(--mantine-color-gray-4))',
                    }}>
                    <Icon size={20} stroke={1.6} color={childActive ? 'var(--mantine-color-brandTeal-light-color)' : 'var(--mantine-color-brandTeal-6)'} />
                </Group>
            </Box>
        );
    }
    return (
        <Box mb={2}>
            <UnstyledButton onClick={() => setOpen((o) => !o)} w="100%">
                <Group className={classes.navRow}
                    gap="sm" wrap="nowrap" px="sm" py={9} style={{
                        color: childActive ? 'var(--mantine-color-brandTeal-light-color)' : 'light-dark(var(--mantine-color-gray-7), var(--mantine-color-gray-4))',
                    }}>
                    <Icon size={20} stroke={1.6} color={childActive ? 'var(--mantine-color-brandTeal-light-color)' : 'var(--mantine-color-brandTeal-6)'} />
                    <Text className={classes.navLabel} size="sm" fw={childActive ? 600 : 500} style={{ flex: 1 }}>{item.group}</Text>
                    <IconChevronDown size={15} stroke={1.6} style={{ transform: open ? 'rotate(180deg)' : 'none', transition: 'transform .15s' }} />
                </Group>
            </UnstyledButton>
            <Collapse in={open}>
                <Stack gap={2} mt={2} pl="lg">
                    {item.children.map((c) => c.children
                        ? <SubNavGroup key={c.label} item={c} path={path} />
                        : <SubNavItem key={c.href} item={c} active={path === c.href} />)}
                </Stack>
            </Collapse>
        </Box>
    );
}

export default function AppShell({ children }) {
    const [mobileOpened, { toggle: toggleMobile }] = useDisclosure();
    const [railHover, setRailHover] = useState(false); // sidebar is an icon rail; expands to the full menu on hover
    const mini = !railHover;
    const { props, url } = usePage();
    const realRole = props?.auth?.user?.role ?? 'carer';
    const userName = props?.auth?.user?.name ?? 'User';
    const [previewRole, setPreviewRole] = useState(realRole);
    const { colorScheme, toggleColorScheme } = useMantineColorScheme();

    const isManager = previewRole === 'manager';
    const roleLabel = isManager ? 'Care Manager' : 'Carer';

    return (
        <RoleContext.Provider value={previewRole}>
            <MantineAppShell
                header={{ height: 64 }}
                navbar={{ width: 76, breakpoint: 'sm', collapsed: { mobile: !mobileOpened, desktop: false } }}
                padding="lg"
            >
                {/* ---- Header — full-width white bar with the hamburger + logo ---- */}
                <MantineAppShell.Header style={{ background: 'light-dark(#ffffff, var(--mantine-color-dark-7))', borderBottom: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))' }}>
                    <Group h="100%" px="lg" justify="space-between" wrap="nowrap">
                        <Group gap="sm" wrap="nowrap">
                            <Burger opened={mobileOpened} onClick={toggleMobile} hiddenFrom="sm" size="sm" />
                            <BrandLogo tone="auto" height={24} />
                        </Group>

                        <Group gap="md" wrap="nowrap">
                            <Group gap={6} visibleFrom="md">
                                <Text size="xs" c="dimmed">Preview:</Text>
                                <SegmentedControl
                                    size="xs"
                                    value={previewRole}
                                    onChange={setPreviewRole}
                                    data={[{ label: 'Manager', value: 'manager' }, { label: 'Carer', value: 'carer' }]}
                                />
                            </Group>

                            <ActionIcon variant="subtle" color="gray" radius="xl" size="lg" pos="relative">
                                <IconBell size={20} stroke={1.6} />
                                <Box pos="absolute" top={10} right={11} w={8} h={8} style={{ background: 'var(--mantine-color-indigo-4)', borderRadius: '50%' }} />
                            </ActionIcon>

                            <Menu position="bottom-end" withArrow width={210}>
                                <Menu.Target>
                                    <UnstyledButton>
                                        <Group gap="sm" wrap="nowrap">
                                            <Avatar color="indigo" radius="xl" size={36}>{userName.charAt(0).toUpperCase()}</Avatar>
                                            <Box visibleFrom="sm" style={{ lineHeight: 1.1 }}>
                                                <Text size="sm" fw={600}>{userName}</Text>
                                                <Text size="xs" c="dimmed">{roleLabel}</Text>
                                            </Box>
                                            <IconChevronDown size={16} stroke={1.6} color="light-dark(var(--mantine-color-gray-7), var(--mantine-color-gray-4))" />
                                        </Group>
                                    </UnstyledButton>
                                </Menu.Target>
                                <Menu.Dropdown>
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

                {/* ---- Sidebar — icon rail under the header; expands to the full menu on hover ---- */}
                <MantineAppShell.Navbar
                    onMouseEnter={() => setRailHover(true)}
                    onMouseLeave={() => setRailHover(false)}
                    style={{
                        // The layout always reserves 76px (collapsed). On hover the rail expands
                        // to 264px and *overlays* the page (higher z-index) instead of pushing it,
                        // so the page content never gets squeezed/clipped.
                        width: railHover ? 264 : 76,
                        zIndex: railHover ? 201 : undefined,
                        background: 'light-dark(#ffffff, var(--mantine-color-dark-7))',
                        borderRight: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))',
                        boxShadow: railHover ? '4px 0 24px rgba(16,24,40,0.10)' : 'none',
                        transition: 'width 400ms cubic-bezier(0.22, 1, 0.36, 1), box-shadow 300ms ease',
                        overflow: 'hidden',
                    }}
                >
                    <MantineAppShell.Section grow component={ScrollArea} px="sm" pt="md" pb="md">
                        {NAV
                            .filter((i) => !i.managerOnly || isManager)
                            .map((item, idx) => {
                                if (item.divider) return <Divider key={`d${idx}`} my="sm" color="light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))" />;
                                if (item.section) {
                                    return mini
                                        ? <Divider key={`s${idx}`} my="sm" color="light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))" />
                                        : (
                                            <Text key={`s${idx}`} size="xs" fw={700} c="dimmed" tt="uppercase"
                                                px="sm" mt="md" mb={6} style={{ letterSpacing: 0.6 }}>
                                                {item.section}
                                            </Text>
                                        );
                                }
                                if (item.group) return <NavGroup key={item.group} item={item} url={url} mini={mini} />;
                                return <NavItem key={item.label} item={item} active={item.href !== '#' && url.startsWith(item.href)} collapsed={mini} />;
                            })}
                    </MantineAppShell.Section>
                </MantineAppShell.Navbar>

                <MantineAppShell.Main style={{
                    display: 'flex', flexDirection: 'column', minHeight: '100dvh',
                    transition: 'padding 400ms cubic-bezier(0.22, 1, 0.36, 1)',
                }}>
                    <Box style={{ flex: 1, minWidth: 0 }}>{children}</Box>
                    <AppFooter />
                </MantineAppShell.Main>
            </MantineAppShell>
        </RoleContext.Provider>
    );
}
