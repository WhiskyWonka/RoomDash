import { Navigate, Outlet } from "react-router-dom";
import { useAuth } from "@/context/AuthContext";

interface ProtectedRouteProps {
    children?: React.ReactNode;
}

export const ProtectedRoute = ({ children }: ProtectedRouteProps) => {
    const { isAuthenticated, loading } = useAuth();

    if (loading) {
        return (
            <div className="min-h-screen bg-black text-[#00ff00] flex items-center justify-center font-mono">
                [SYSTEM_CHECKING_ACCESS_LEVEL...]
            </div>
        );
    }

    if (!isAuthenticated) {
        // Podríamos guardar la ruta intentada para volver después del login
        return <Navigate to="/admin/login" replace />;
    }

    // Si tiene children (uso tradicional), los muestra. 
    // Si no, renderiza las rutas hijas definidas en el router (Outlet).
    return children ? <>{children}</> : <Outlet />;
};