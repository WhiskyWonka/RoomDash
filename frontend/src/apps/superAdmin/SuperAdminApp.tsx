import SuperAdminLayout from '../../layouts/SuperAdminLayout'
import { Routes, Route, Navigate, useLocation } from "react-router-dom";
import TenantsPage from './pages/TenantsPage'
import DashboardPage from './pages/DashboardPage'
import UsersPage from './pages/UsersPage'
import LoginPage from "./pages/LoginPage";
import FeaturesPage from './pages/FeaturesPage'
import { useEffect, useState } from "react";
import { authApi } from "@/lib/authApi";

function SuperAdminApp() {
    const [loading, setLoading] = useState(true);
    const [user, setUser] = useState<any>(null);
    const location = useLocation();

    // 1. Definimos la función de chequeo
    const checkAuth = async () => {
        try {
            console.log("FETCHING_USER_DATA...");
            const data = await authApi.me();
            console.log("SUCCESS_USER:", data);
            setUser(data.user);
        } catch (error: any) {
            console.log("DEBUG_AUTH_ERROR:", error.message);
            setUser(null);
        } finally {
            setLoading(false);
        }
    };

    // 2. Único useEffect para el montaje inicial
    useEffect(() => {
        // Si ya estamos en login, no bloqueamos la UI preguntando al server
        if (location.pathname.includes('/login')) {
            setLoading(false);
            return;
        }
        checkAuth();
    }, []);

    // 3. Segundo useEffect para cambios de ruta (OPCIONAL)
    // Pero OJO: Todos los Hooks van ANTES de cualquier return
    useEffect(() => {
        if (user && location.pathname.includes('/login')) {
            // Si ya hay usuario y estamos en login, no hace falta re-chequear,
            // la lógica de abajo nos redireccionará.
            return;
        }
    }, [location.pathname]);

    // 4. AHORA SÍ: Los returns de renderizado van al final
    if (loading) {
        return (
            <div className="min-h-screen bg-black text-[#00ff00] flex items-center justify-center font-mono">
                [SYSTEM_CHECKING_CREDENTIALS...]
            </div>
        );
    }

    const isAuthenticated = !!user;

    return (
        <Routes>
            {/* Si ya estoy autenticado, el login me manda al dashboard */}
            <Route 
                path="login" 
                element={
                    isAuthenticated 
                        ? <Navigate to="/superadmin/dashboard" replace /> 
                        : <LoginPage onLoginSuccess={checkAuth} /> 
                }
            />

            <Route 
                path="/" 
                element={isAuthenticated ? <SuperAdminLayout /> : <Navigate to="login" replace />}
            >
                <Route index element={<Navigate to="dashboard" replace />} />
                <Route path="dashboard" element={<DashboardPage />} />
                <Route path="tenants" element={<TenantsPage />} />
                <Route path="users" element={<UsersPage />} />
                <Route path="features" element={<FeaturesPage />} />
            </Route>
        </Routes>
    );
}

export default SuperAdminApp