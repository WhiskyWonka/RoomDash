"use client"

import * as React from "react"
import { ArrowUpCircleIcon, ChevronsUpDown, Building2 } from "lucide-react"

import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from "@/components/ui/shadcn/sidebar"

interface NavHotelProps {
    hotel: {
        name: string
        logo?: React.ElementType
        plan: string
    }
}

export function NavTenant({ hotel }: NavHotelProps) {

    // El logo puede ser uno pasado por props o uno por defecto (Building2)
    const Logo = hotel.logo || Building2

    return (
        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton
                    size="lg"
                    className="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                >
                    <div className="flex aspect-square size-8 items-center justify-center rounded-lg bg-sidebar-primary text-sidebar-primary-foreground">
                        <Logo className="h-5 w-5" />
                    </div>
                    <div className="grid flex-1 text-left text-sm leading-tight">
                        <span className="text-base font-semibold">{hotel.name}</span>
                        <span className="truncate text-xs">{hotel.plan}</span>
                    </div>
                    {/* Opcional: un icono de selector si vas a permitir cambiar de hotel */}
                    {/* <ChevronsUpDown className="ml-auto size-4" /> */}
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    )
}