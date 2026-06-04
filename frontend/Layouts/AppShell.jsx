import { useState } from 'react';
import {
    AppShell as MantineAppShell, Group, Text, Burger, NavLink, ScrollArea,
    Avatar, Menu, Badge, SegmentedControl, Divider,
} from '@mantine/core';
import { useDisclosure } from '@mantine/hooks';
import { usePage, Link } from '@inertiajs/react';
import { RoleContext } from '@frontend/lib/role';
import logo from '@frontend/assets/logo.jpg';

// Full menu — managers see everything.
const MANAGER_MENU = [
    { label: 'Dashboard', href: '#' },
    { label: 'Clients', href: '#' },
    { label: 'Medication Round', href: '/medication/medication-round-react' },
    { label: 'Medication Stock', href: '/medication/stock-react' },
    { label: 'Controlled Drugs', href: '/medication/controlled-drugs-react' },
    { label: 'Missed Doses', href: '/medication/missed-doses-react' },
    { label: 'Shift Handover', href: '/medication/shift-handover-react' },
    { label: 'Schedule', href: '#' },
    { label: 'Staff', href: '#' },
    { label: 'Compliance', href: '#' },
    { label: 'Reports', href: '#' },
    { label: 'Admin', href: '#' },
];

// Focused menu — carers see only what they need to do their shift.
const CARER_MENU = [
    { label: 'My Shift', href: '#' },
    { label: 'My Clients', href: '#' },
    { label: 'Medication Round', href: '/medication/medication-round-react' },
    { label: 'Medication Stock', href: '/medication/stock-react' },
    { label: 'Controlled Drugs', href: '/medication/controlled-drugs-react' },
    { label: 'Missed Doses', href: '/medication/missed-doses-react' },
    { label: 'Shift Handover', href: '/medication/shift-handover-react' },
    { label: 'Daily Log', href: '#' },
    { label: 'Handover', href: '#' },
];

export default function AppShell({ children }) {
    const [opened, { toggle }] = useDisclosure();
    const { props, url } = usePage();
    const realRole = props?.auth?.user?.role ?? 'carer';
    const userName = props?.auth?.user?.name ?? 'User';

    // Preview override (dev only) — defaults to the real role.
    const [previewRole, setPreviewRole] = useState(realRole);
    const menu = previewRole === 'manager' ? MANAGER_MENU : CARER_MENU;

    return (
        <RoleContext.Provider value={previewRole}>
            <MantineAppShell
                header={{ height: 60 }}
                navbar={{ width: 260, breakpoint: 'sm', collapsed: { mobile: !opened } }}
                padding="md"
            >
                <MantineAppShell.Header bg="#2778A5">
                    <Group h="100%" px="md" justify="space-between" wrap="nowrap">
                        <Group gap="sm" wrap="nowrap">
                            <Burger opened={opened} onClick={toggle} hiddenFrom="sm" size="sm" color="white" />
                            <img src={logo} alt="Care One OS" style={{ height: 32, display: 'block' }} />
                            <Badge variant="white" color={previewRole === 'manager' ? 'indigo' : 'teal'}>
                                {previewRole === 'manager' ? 'Manager portal' : 'Carer view'}
                            </Badge>
                        </Group>

                        <Group gap="md" wrap="nowrap">
                            <Group gap={6} visibleFrom="sm">
                                <Text size="xs" c="white">Preview as:</Text>
                                <SegmentedControl
                                    size="xs"
                                    value={previewRole}
                                    onChange={setPreviewRole}
                                    data={[{ label: 'Manager', value: 'manager' }, { label: 'Carer', value: 'carer' }]}
                                />
                            </Group>
                            <Menu position="bottom-end" withArrow>
                                <Menu.Target>
                                    <Avatar color="indigo" variant="white" radius="xl" style={{ cursor: 'pointer' }}>
                                        {userName.charAt(0).toUpperCase()}
                                    </Avatar>
                                </Menu.Target>
                                <Menu.Dropdown>
                                    <Menu.Label>{userName}</Menu.Label>
                                    <Menu.Item component="a" href="/logout">Log out</Menu.Item>
                                </Menu.Dropdown>
                            </Menu>
                        </Group>
                    </Group>
                </MantineAppShell.Header>

                <MantineAppShell.Navbar p="sm">
                    <ScrollArea>
                        {menu.map((item) => {
                            const isPlaceholder = item.href === '#';
                            return (
                                <NavLink
                                    key={item.label}
                                    label={item.label}
                                    component={isPlaceholder ? undefined : Link}
                                    href={isPlaceholder ? undefined : item.href}
                                    active={!isPlaceholder && url.startsWith(item.href)}
                                    disabled={isPlaceholder}
                                />
                            );
                        })}
                        <Divider my="sm" />
                        <Text size="xs" c="dimmed" px="xs">Greyed items are future pages.</Text>
                    </ScrollArea>
                </MantineAppShell.Navbar>

                <MantineAppShell.Main>{children}</MantineAppShell.Main>
            </MantineAppShell>
        </RoleContext.Provider>
    );
}
