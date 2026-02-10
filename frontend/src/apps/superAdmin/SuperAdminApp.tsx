import SuperAdminLayout from '../../layouts/SuperAdminLayout'
import { Routes, Route, Navigate } from "react-router-dom"
import TenantsPage from './pages/TenantsPage'
import DashboardPage from './pages/DashboardPage'
import UsersPage from './pages/UsersPage'
import LoginPage from "./pages/LoginPage";

function SuperAdminApp() {
    return (
        <Routes>
            <Route path="login" element={<LoginPage />} />

            <Route path="/" element={<SuperAdminLayout />}>
                {/* Redirigir la raíz al dashboard */}
                <Route index element={<Navigate to="dashboard" replace />} />
                <Route path="dashboard" element={<DashboardPage />} />
                <Route path="tenants" element={<TenantsPage />} />
                <Route path="users" element={<UsersPage />} />
            </Route>
        </Routes>
    )
}

export default SuperAdminApp