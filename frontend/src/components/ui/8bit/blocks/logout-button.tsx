import { useAuth } from "@/context/AuthContext";
import { Button } from "../button";

export function LogoutButton() {
    const { logout } = useAuth();

    const handleLogout = async () => {
        // El context ya se encarga del try/catch y de redirigir
        await logout();
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