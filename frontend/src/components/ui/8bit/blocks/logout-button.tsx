import { authApi } from "@/lib/authApi";
import { Button } from "../button";

export function LogoutButton() {
    const handleLogout = async () => {
        try {
            // 1. Avisar al servidor
            await authApi.logout();
        } catch (error) {
            console.error("Error al cerrar sesión en el servidor", error);
        } finally {
            // 2. Limpiar y redirigir (Forzamos recarga para limpiar todo el estado de React)
            window.location.href = "/superadmin/login";
        }
    };

    return (
        <Button 
            onClick={handleLogout}
            className="px-4 py-2 bg-red-900 text-white border-2 border-red-500 hover:bg-red-700 font-mono text-xs uppercase"
        >
            [ Logout ]
        </Button>
    );
}