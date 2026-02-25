import SuperAdminLayout from '../../layouts/SuperAdminLayout'
import { Routes, Route, Navigate, useLocation } from "react-router-dom";
import TenantsPage from './pages/TenantsPage'
import DashboardPage from './pages/DashboardPage'
import UsersPage from './pages/UsersPage'
import LoginPage from "./pages/LoginPage";
import FeaturesPage from './pages/FeaturesPage'
import { useEffect } from "react";
import VerifyUserPage from './pages/VerifyUserPage';
import { ProtectedRoute } from "@/components/auth/ProtectedRoute";
import { useAuth } from "@/context/AuthContext";


function SuperAdminApp() {
    const { isAuthenticated, loading, checkAuth } = useAuth();
    const location = useLocation();

    // Determinamos si es una ruta que no requiere validación bloqueante
    const isPublicRoute = location.pathname.includes('/login') || location.pathname.includes('/verify-email');

    useEffect(() => {
        // Validamos siempre, pero el Contexto ya sabe si mostrar loading o no
        checkAuth();
    }, [location.pathname, checkAuth]);

    if (loading && !isPublicRoute) {
        return (
            <div className="min-h-screen bg-black text-[#00ff00] flex items-center justify-center font-mono">
                [SYSTEM_CHECKING_CREDENTIALS...]
            </div>
        );
    }

    if (loading) return null; // O un spinner global

    return (
        <Routes>
            <Route 
                path="login" 
                element={
                    isAuthenticated 
                        ? <Navigate to="/admin/dashboard" replace /> 
                        : <LoginPage onLoginSuccess={checkAuth} /> 
                }
            />
            <Route path="verify-email" element={<VerifyUserPage />} />

            <Route 
                path="/" 
                element={
                    <ProtectedRoute>
                        <SuperAdminLayout />
                    </ProtectedRoute>
                }
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

export default SuperAdminApp;