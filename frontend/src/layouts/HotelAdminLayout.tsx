import { AppSidebar } from "@/components/ui/shadcn/app-sidebar"
import { SiteHeader } from "@/components/ui/shadcn/site-header"
import { Outlet } from "react-router-dom"
import {
    SidebarInset,
    SidebarProvider,
} from "@/components/ui/shadcn/sidebar"

export default function HotelAdminLayout() {
    return (
        <SidebarProvider>
            <AppSidebar />
            <SidebarInset className="flex w-full flex-1 flex-col md:ml-[var(--sidebar-width)] transition-[margin] duration-200 ease-linear">
                <SiteHeader />
                <main className="flex flex-1 flex-col">
                    <Outlet />
                </main>
            </SidebarInset>
        </SidebarProvider>
    )
}