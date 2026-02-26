import { createBrowserRouter, Navigate } from "react-router-dom";
import HotelAdminLayout from "@/layouts/HotelAdminLayout";
import HotelLoginPage from "./pages/HotelLoginPage";
import DashboardPage from "./pages/DashboardPage";
import ReservasPage from "./pages/ReservasPage";
import { ProtectedRoute } from "@/components/auth/ProtectedRoute";
import { ErrorPage } from "@/components/auth/ErrorPage";
import Landing from "../landing-tenant/Landing";

export const hotelAdminRouter = createBrowserRouter([
    {
        path: "/",
        errorElement: <ErrorPage />,
        children: [
            // 1. SITIO PÚBLICO (mypod.roomdash.test/)
            { index: true, element: <Landing /> },

            // 2. GRUPO ADMINISTRATIVO (/admin)
            {
                path: "admin",
                children: [
                    // Ruta pública dentro de admin (mypod.roomdash.test/admin/login)
                    {
                        path: "login",
                        element: <HotelLoginPage />,
                    },
                    
                    // Rutas privadas dentro de admin (/admin/...)
                    {
                        element: <ProtectedRoute />, 
                        children: [
                            {
                                element: <HotelAdminLayout />, 
                                children: [
                                    { index: true, element: <Navigate to="dashboard" replace /> },
                                    { path: "dashboard", element: <DashboardPage /> },
                                    { path: "reservas", element: <ReservasPage /> },
                                    { path: "habitaciones", element: <div>MODULO_HABITACIONES</div> },
                                ]
                            }
                        ]
                    }
                ]
            },

            // Comodín para cualquier otra cosa
            { path: "*", element: <Navigate to="/" replace /> }
        ]
    }
]);