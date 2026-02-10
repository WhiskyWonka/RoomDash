import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import SuperAdminApp from "./apps/superAdmin/SuperAdminApp";
import HotelAdminApp from "./apps/hotelAdmin/HotelAdminApp";

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        {/* Puerta para el dueño del sistema */}
        <Route path="/superadmin/*" element={<SuperAdminApp />} />

        {/* Puerta para los hoteles (Tenants) */}
        <Route path="/admin/*" element={<HotelAdminApp />} />

        {/* Redirección por defecto si entran a la raíz */}
        <Route path="/" element={<Navigate to="/admin" replace />} />
      </Routes>
    </BrowserRouter>
  );
}