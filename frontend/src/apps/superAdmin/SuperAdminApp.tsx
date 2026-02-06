import SuperAdminLayout from '../../layouts/SuperAdminLayout'
import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom"
import TenantsPage from './pages/TenantsPage'

function SuperAdminApp() {
  return (
    <BrowserRouter>
      <Routes>
        <Route element={<SuperAdminLayout />}>
          {/* Redirigir la raíz al dashboard */}
          <Route path="/" element={<Navigate to="/dashboard" replace />} />
          
          <Route path="/dashboard" element={
            <div className="text-[#00ff00]">
              <h2 className="text-2xl font-bold uppercase">{">"} Dashboard_Main</h2>
              <p className="text-zinc-400 mt-2">Bienvenido al sistema.</p>
            </div>
          } />

          <Route path="/tenants" element={<TenantsPage />} />
        </Route>
      </Routes>
    </BrowserRouter>
  )
}

export default SuperAdminApp