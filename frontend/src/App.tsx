import { RouterProvider } from "react-router-dom";
import { AuthProvider } from "@/context/AuthContext";
import { superAdminRouter } from "@/apps/superAdmin/routes";
import { hotelAdminRouter } from "@/apps/hotelAdmin/routes";
import { QueryClientProvider } from '@tanstack/react-query';
import { ReactQueryDevtools } from '@tanstack/react-query-devtools';
import { queryClient } from '@/lib/queryClient';

export default function App() {
    const hostname = window.location.hostname;
    const mainDomain = "roomdash.test";
    const isTenant = hostname !== mainDomain && hostname.endsWith(mainDomain);

    // Seleccionamos el router adecuado
    const router = isTenant ? hotelAdminRouter : superAdminRouter;

    return (
        <QueryClientProvider client={queryClient}>
            <AuthProvider>
                <RouterProvider router={router} />
            </AuthProvider>
            <ReactQueryDevtools initialIsOpen={false} />
        </QueryClientProvider>
    );
}