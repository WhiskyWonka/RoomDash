import { BrowserRouter, Routes, Route } from "react-router-dom";
import SuperAdminApp from "./apps/superAdmin/SuperAdminApp";
import HotelAdminApp from "./apps/hotelAdmin/HotelAdminApp";
import MainLanding from "./apps/landing/MainLanding";
import Landing from "./apps/landing-tenant/Landing";
import { AuthProvider } from "@/context/AuthContext";

export default function App() {
    // Obtenemos el host actual (ej: roomdash.test o mypod.roomdash.test)
    const hostname = window.location.hostname;

    // Definimos cuál es nuestro dominio base
    const mainDomain = "roomdash.test";

    // Verificamos si es un subdominio (tenant)
    // Esto será true si el hostname es algo como "mypod.roomdash.test"
    const isTenant = hostname !== mainDomain && hostname.endsWith(mainDomain);

    return (
        <BrowserRouter>
            <AuthProvider>
                <Routes>
                    {isTenant ? (
                        /* --- RUTAS PARA EL TENANT (mypod.roomdash.test) --- */
                        <>
                            <Route path="/admin/*" element={<HotelAdminApp />} />
                            <Route path="/" element={<Landing />} />
                        </>
                    ) : (
                        /* --- RUTAS PARA EL SUPERADMIN (roomdash.test) --- */
                        <>
                            <Route path="/admin/*" element={<SuperAdminApp />} />
                            <Route path="/" element={<MainLanding />} />
                            {/* <Route path="/" element={<h1>Landing Page Principal ROOMDASH</h1>} /> */}
                        </>
                    )}
                </Routes>
            </AuthProvider>
        </BrowserRouter>
    );
}