import { BrowserRouter, Routes, Route } from "react-router-dom";
import SuperAdminApp from "./apps/superAdmin/SuperAdminApp";
import HotelAdminApp from "./apps/hotelAdmin/HotelAdminApp";

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
      <Routes>
        {isTenant ? (
          /* --- RUTAS PARA EL TENANT (mypod.roomdash.test) --- */
          <>
            <Route path="/admin/*" element={<HotelAdminApp />} />
            <Route path="/" element={<h1>Landing Page del Hotel: {hostname.split('.')[0]}</h1>} />
          </>
        ) : (
          /* --- RUTAS PARA EL SUPERADMIN (roomdash.test) --- */
          <>
            <Route path="/admin/*" element={<SuperAdminApp />} />
            <Route path="/" element={<h1>Landing Page Principal ROOMDASH</h1>} />
          </>
        )}
      </Routes>
    </BrowserRouter>
  );
}