import { SidebarProvider, SidebarInset } from "@/components/ui/8bit/sidebar"
import { AppSidebar } from "@/components/ui/8bit/blocks/sidebar"
import { Header } from "@/components/ui/8bit/blocks/header"
import { Outlet } from "react-router-dom"

function SuperAdminLayout() {
    return (
        <SidebarProvider>
            <AppSidebar />
            <SidebarInset className="bg-black flex flex-col min-w-0 flex-1 transition-all duration-300">
                <Header />
                <main className="retro p-4">
                    <Outlet />
                </main>
            </SidebarInset>
        </SidebarProvider>
    )
}

export default SuperAdminLayout