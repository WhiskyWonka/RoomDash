// src/components/auth/ProtectedRoute.tsx
import { Navigate } from "react-router-dom";
import { useAuth } from "@/context/AuthContext";

export const ProtectedRoute = ({ children }: { children: React.ReactNode }) => {
    const { isAuthenticated, loading } = useAuth();

    if (loading) {
        return (
            <div className="min-h-screen bg-black text-[#00ff00] flex items-center justify-center font-mono">
                [SYSTEM_CHECKING_ACCESS_LEVEL...]
            </div>
        );
    }

    return isAuthenticated ? <>{children}</> : <Navigate to="/admin/login" replace />;
};