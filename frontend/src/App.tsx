import { RouterProvider } from "react-router-dom";
import { AuthProvider } from "@/context/AuthContext";
import { superAdminRouter } from "@/apps/superAdmin/routes";
import { hotelAdminRouter } from "@/apps/hotelAdmin/routes";

export default function App() {
    const hostname = window.location.hostname;
    const mainDomain = "roomdash.test";
    const isTenant = hostname !== mainDomain && hostname.endsWith(mainDomain);

    // Seleccionamos el router adecuado
    const router = isTenant ? hotelAdminRouter : superAdminRouter;

    return (
        <AuthProvider>
            <RouterProvider router={router} />
        </AuthProvider>
    );
}