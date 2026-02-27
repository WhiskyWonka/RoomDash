import { createBrowserRouter, Navigate } from "react-router-dom";
import SuperAdminLayout from '../../layouts/SuperAdminLayout'
import DashboardPage from "./pages/DashboardPage";
import TenantsPage from "./pages/TenantsPage";
import UsersPage from "./pages/UsersPage";
import FeaturesPage from "./pages/FeaturesPage";
import LoginPage from "./pages/LoginPage";
import VerifyUserPage from './pages/VerifyUserPage';
import { ProtectedRoute } from "@/components/auth/ProtectedRoute";
import { ErrorPage } from "@/components/auth/ErrorPage";
import MainLanding from "../landing/MainLanding";

export const superAdminRouter = createBrowserRouter([
    {
        path: "/admin",
        errorElement: <ErrorPage />,
        children: [
            // RUTAS PÚBLICAS
            { path: "login", element: <LoginPage /> },
            { path: "verify-email", element: <VerifyUserPage /> },

            // RUTAS PROTEGIDAS
            {
                path: "",
                element: <ProtectedRoute />, // 1. Verifica auth
                children: [
                    {
                        element: <SuperAdminLayout />, // 2. Pone el Sidebar/Header
                        children: [
                            // Esto captura el "/admin" o "/admin/" y lo manda a dashboard
                            { index: true, element: <Navigate to="dashboard" replace /> },
                            { path: "dashboard", element: <DashboardPage /> },
                            { path: "tenants", element: <TenantsPage /> },
                            { path: "users", element: <UsersPage /> },
                            { path: "features", element: <FeaturesPage /> },
                        ]
                    }
                ]
            },
        ],
    },
    // RUTA PARA LA LANDING PRINCIPAL (roomdash.test/)
    {
        path: "/",
        element: <MainLanding />,
    },
    // Captura cualquier ruta no definida y manda a 404 o Home
    {
        path: "*",
        element: <Navigate to="/" replace />,
    }
]);