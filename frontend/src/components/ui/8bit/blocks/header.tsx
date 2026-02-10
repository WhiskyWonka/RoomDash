import { SidebarTrigger } from "@/components/ui/8bit/sidebar"
import { useLocation } from "react-router-dom"

export function Header() {

    const location = useLocation()

    const routeNames: Record<string, string> = {
        "/dashboard": "DASHBOARD",
        "/tenants": "TENANTS",
        "/settings": "SYSTEM_SETTINGS",
    }

    const currentPage = routeNames[location.pathname] || "RoomDash"

    return (
        <header className="retro flex h-14 shrink-0 items-center gap-2 border-b border-dashed border-[#737373] px-4">
        {/* El SidebarTrigger de 8bitcn suele ser un botón con borde verde */}
        <SidebarTrigger className="retro hover:text-gray !border-0" />
        
        <div className="ml-4 flex items-center gap-2">
            <span className="text-[#00ff00] font-bold text-lg">{currentPage}</span>
        </div>
        </header>
    )
}