import { Routes, Route, Navigate } from "react-router-dom";
import HotelAdminLayout from "../../layouts/HotelAdminLayout";
import HotelLoginPage from "./pages/HotelLoginPage";
import DashboardPage from "./pages/DashboardPage";
import ReservasPage from "./pages/ReservasPage";

export default function HotelAdminApp() {
  return (
    <Routes>
      <Route path="login" element={<HotelLoginPage />} />
      
      <Route path="/" element={<HotelAdminLayout />}>
        <Route index element={<Navigate to="dashboard" replace />} />
        <Route path="dashboard" element={<DashboardPage />} />
        <Route path="reservas" element={<ReservasPage />} />
        <Route path="habitaciones" element={<div>Pagina de Habitaciones</div>} />
      </Route>
    </Routes>
  );
}