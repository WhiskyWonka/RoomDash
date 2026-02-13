import { SidebarTrigger } from "@/components/ui/8bit/sidebar"
import { useLocation } from "react-router-dom"
import { LogoutButton } from "./logout-button"

export function Header() {

    const location = useLocation()

    const routeNames: Record<string, string> = {
        "/dashboard": "DASHBOARD",
        "/tenants": "TENANTS",
        "/settings": "SYSTEM_SETTINGS",
    }

    const currentPage = routeNames[location.pathname] || "RoomDash"

    return (
        <header className="retro flex justify-between h-14 shrink-0 items-center gap-2 border-b border-dashed border-[#737373] px-4">
            <div className="flex gap-4 items-center">
                <SidebarTrigger className="hover:text-gray !border-0" />
                <span className="text-[#00ff00] font-bold text-lg">{currentPage}</span>
            </div>

            <LogoutButton />
        </header>
    )
}