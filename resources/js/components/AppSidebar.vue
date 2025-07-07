<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const page = usePage();
const userRole = page.props.auth.user.role;

let mainNavItems: NavItem[] = [];

if (userRole === 'agency') {
    mainNavItems = [
        {
            title: 'Dashboard',
            href: '/agency/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Clients',
            href: '/agency/client',
            icon: Folder,
        },
    ];
} else if (userRole === 'client') {
    mainNavItems = [
        {
            title: 'Dashboard',
            href: '/client/dashboard',
            icon: LayoutGrid,
        },
    ];
} else if (userRole === 'admin') {
    mainNavItems = [
        {
            title: 'Dashboard',
            href: '/admin/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Agencies',
            href: '/admin/agencies',
            icon: Folder,
        },
    ];
}

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route(
  $page.props.auth.user.role === 'admin' ? 'admin.dashboard' :
  $page.props.auth.user.role === 'client' ? 'client.dashboard' :
  'agency.dashboard'
)">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
