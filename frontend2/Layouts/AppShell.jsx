import { useState } from 'react';
import {
    AppShell as MantineAppShell, Group, Text, Burger, ScrollArea, Avatar, Badge,
    ActionIcon, UnstyledButton, Box, Switch, Menu, Divider, useMantineColorScheme,
} from '@mantine/core';
import { useDisclosure } from '@mantine/hooks';
import { usePage, Link } from '@inertiajs/react';
import {
    IconLayoutDashboard, IconChartBar, IconFolder, IconUsers, IconCalendar,
    IconMessage, IconSettings, IconBell, IconMoon, IconChevronDown, IconArrowLeft,
} from '@tabler/icons-react';
import { RoleContext } from '@frontend/lib/role';
import BrandLogo from '@frontend/components/BrandLogo';
import AppFooter from '@frontend/components/AppFooter';
import classes from './AppShell.module.css';

// ── Frontend2 ──────────────────────────────────────────────────────────────
// A SECOND app shell, fully separate from the main /frontend one. Same brand
// header + footer, but its OWN always-expanded sidebar and its own nav list.
// This is the base to iterate on — the sidebar contents/styling get tailored
// to the picture the owner provides. Pages render inside <AppShell2>.
//
// NAV2 is a placeholder menu; swap it for the real sections once decided.
const NAV2 = [
    { section: 'Overview' },
    { label: 'Dashboard', icon: IconLayoutDashboard, href: '/frontend2' },
    { label: 'Analytics', icon: IconChartBar, href: '#' },
    { section: 'Manage' },
    { label: 'Projects', icon: IconFolder, href: '#' },
    { label: 'Team', icon: IconUsers, href: '#' },
    { label: 'Calendar', icon: IconCalendar, href: '#' },
    { section: 'Account' },
    { label: 'Messages', icon: IconMessage, href: '#' },
    { label: 'Settings', icon: IconSettings, href: '#' },
    { divider: true },
    // Returns to the main medication app (and its sidebar).
    { label: 'Back to main app', icon: IconArrowLeft, href: '/medication/medication-round-4' },
];

function NavItem({ item, active }) {
    const Icon = item.icon;
    const disabled = item.href === '#';
    const inner = (
        <Group className={disabled ? undefined : classes.navRow} data-active={active || undefined}
            gap="sm" wrap="nowrap" px="sm" py={10} style={{
                color: active ? 'var(--mantine-color-brandTeal-light-color)' : 'light-dark(var(--mantine-color-gray-7), var(--mantine-color-gray-4))',
                opacity: disabled ? 0.55 : 1,
                cursor: disabled ? 'default' : 'pointer',
            }}>
            <Icon size={20} stroke={1.6} color={active ? 'var(--mantine-color-brandTeal-light-color)' : 'var(--mantine-color-brandTeal-6)'} />
            <Text className={classes.navLabel} size="sm" fw={active ? 700 : 500}>{item.label}</Text>
        </Group>
    );
    if (disabled) return <Box mb={2} title="Coming soon">{inner}</Box>;
    return <Box component={Link} href={item.href} mb={2} style={{ textDecoration: 'none', display: 'block' }}>{inner}</Box>;
}

export default function AppShell({ children }) {
    const [mobileOpened, { toggle: toggleMobile }] = useDisclosure();
    const { props, url } = usePage();
    const role = props?.auth?.user?.role ?? 'carer';
    const userName = props?.auth?.user?.name ?? 'User';
    const { colorScheme, toggleColorScheme } = useMantineColorScheme();
    const path = url.split('?')[0];

    return (
        <RoleContext.Provider value={role}>
            <MantineAppShell
                header={{ height: 64 }}
                navbar={{ width: 252, breakpoint: 'sm', collapsed: { mobile: !mobileOpened } }}
                padding="lg"
            >
                {/* Header — brand bar with a "Frontend 2" tag so it's clear which app this is. */}
                <MantineAppShell.Header style={{ background: 'light-dark(#ffffff, var(--mantine-color-dark-7))', borderBottom: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))' }}>
                    <Group h="100%" px="lg" justify="space-between" wrap="nowrap">
                        <Group gap="sm" wrap="nowrap">
                            <Burger opened={mobileOpened} onClick={toggleMobile} hiddenFrom="sm" size="sm" />
                            <BrandLogo tone="auto" height={24} />
                            <Badge variant="light" color="brandTeal" radius="sm" visibleFrom="sm">Frontend 2</Badge>
                        </Group>

                        <Group gap="md" wrap="nowrap">
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
                                                <Text size="xs" c="dimmed">Frontend 2</Text>
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

                {/* Sidebar — always-expanded (a distinct treatment from the main shell's icon rail). */}
                <MantineAppShell.Navbar style={{
                    background: 'light-dark(#ffffff, var(--mantine-color-dark-7))',
                    borderRight: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))',
                }}>
                    <MantineAppShell.Section grow component={ScrollArea} px="sm" pt="md" pb="md">
                        {NAV2.map((item, idx) => {
                            if (item.divider) return <Divider key={`d${idx}`} my="sm" color="light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))" />;
                            if (item.section) {
                                return (
                                    <Text key={`s${idx}`} size="xs" fw={700} c="dimmed" tt="uppercase"
                                        px="sm" mt={idx === 0 ? 0 : 'md'} mb={6} style={{ letterSpacing: 0.6 }}>
                                        {item.section}
                                    </Text>
                                );
                            }
                            return <NavItem key={item.label} item={item} active={item.href !== '#' && path === item.href} />;
                        })}
                    </MantineAppShell.Section>
                </MantineAppShell.Navbar>

                <MantineAppShell.Main style={{ display: 'flex', flexDirection: 'column', minHeight: '100dvh' }}>
                    <Box style={{ flex: 1, minWidth: 0 }}>{children}</Box>
                    <AppFooter />
                </MantineAppShell.Main>
            </MantineAppShell>
        </RoleContext.Provider>
    );
}
