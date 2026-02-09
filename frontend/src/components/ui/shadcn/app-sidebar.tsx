"use client"

import * as React from "react"
import {
    ArrowUpCircleIcon,
    BarChartIcon,
    CameraIcon,
    ClipboardListIcon,
    DatabaseIcon,
    FileCodeIcon,
    FileIcon,
    FileTextIcon,
    FolderIcon,
    HelpCircleIcon,
    LayoutDashboardIcon,
    ListIcon,
    SearchIcon,
    SettingsIcon,
    UsersIcon,
    UsersRound,
    Diamond,
} from "lucide-react"

import { NavDocuments } from "@/components/ui/shadcn/nav-documents"
import { NavMain } from "@/components/ui/shadcn/nav-main"
import { NavSecondary } from "@/components/ui/shadcn/nav-secondary"
import { NavUser } from "@/components/ui/shadcn/nav-user"
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
} from "@/components/ui/shadcn/sidebar"
import { NavTenant } from "./nav-tenant"

const data = {
    user: {
        name: "shadcn",
        email: "m@example.com",
        avatar: "/avatars/shadcn.jpg",
    },
    navMain: [
        {
            title: "Dashboard",
            url: "dashboard",
            icon: LayoutDashboardIcon,
        },
        {
            title: "Reservas",
            url: "reservas",
            icon: ListIcon,
        },
        {
            title: "Habitaciones",
            url: "habitaciones",
            icon: Diamond,
        },
        /*{
            title: "Analytics",
            url: "#",
            icon: BarChartIcon,
        },
        {
            title: "Projects",
            url: "#",
            icon: FolderIcon,
        },*/
        {
            title: "Empleados",
            url: "empleados",
            icon: UsersRound,
        },
        {
            title: "Usuarios",
            url: "usuarios",
            icon: UsersIcon,
        },
    ],
    navClouds: [
        {
            title: "Capture",
            icon: CameraIcon,
            isActive: true,
            url: "#",
            items: [
                {
                    title: "Active Proposals",
                    url: "#",
                },
                {
                    title: "Archived",
                    url: "#",
                },
            ],
        },
        {
            title: "Proposal",
            icon: FileTextIcon,
            url: "#",
            items: [
                {
                    title: "Active Proposals",
                    url: "#",
                },
                {
                    title: "Archived",
                    url: "#",
                },
            ],
        },
        {
            title: "Prompts",
            icon: FileCodeIcon,
            url: "#",
            items: [
                {
                    title: "Active Proposals",
                    url: "#",
                },
                {
                    title: "Archived",
                    url: "#",
                },
            ],
        },
    ],
    navSecondary: [
        {
            title: "Configuracion",
            url: "configuracion",
            icon: SettingsIcon,
        },
        /*{
            title: "Get Help",
            url: "#",
            icon: HelpCircleIcon,
        },
        {
            title: "Search",
            url: "#",
            icon: SearchIcon,
        },*/
    ],
    documents: [
        /*{
            name: "Data Library",
            url: "#",
            icon: DatabaseIcon,
        },*/
        {
            name: "Reportes",
            url: "#",
            icon: ClipboardListIcon,
        },
        /*{
            name: "Word Assistant",
            url: "#",
            icon: FileIcon,
        },*/
    ],

    // Agregamos la info del hotel aquí por ahora
    hotel: {
        name: "Hotel 1",
        plan: "Premium",
        logo: ArrowUpCircleIcon,
    },
}

export function AppSidebar({ ...props }: React.ComponentProps<typeof Sidebar>) {
    return (
        <Sidebar collapsible="icon" {...props}>
            <SidebarHeader>
                <SidebarMenu>
                    <NavTenant hotel={data.hotel} />
                </SidebarMenu>
            </SidebarHeader>
            <SidebarContent>
                <NavMain items={data.navMain} />
                <NavDocuments items={data.documents} />
                <NavSecondary items={data.navSecondary} className="mt-auto" />
            </SidebarContent>
            <SidebarFooter>
                <NavUser user={data.user} />
            </SidebarFooter>
        </Sidebar>
    )
}
