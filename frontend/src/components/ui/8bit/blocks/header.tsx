import { SidebarTrigger } from "@/components/ui/8bit/sidebar"
import { useLocation } from "react-router-dom"
import { LogoutButton } from "./logout-button"

export function Header() {

    const location = useLocation()
    const path = location.pathname.split("/").pop();
    const currentPage = path?.split(/[-_]/)
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(" ") || 'RoomDash';

    return (
        <header className="retro flex justify-between h-14 shrink-0 items-center gap-2 border-b border-dashed border-[#737373] px-4">
            <div className="flex gap-4 items-center">
                <SidebarTrigger className="hover:text-gray !border-0" />
                <span className="text-lime-500 font-bold text-md">{currentPage}</span>
            </div>

            <LogoutButton />
        </header>
    )
}