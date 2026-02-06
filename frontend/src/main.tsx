import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import './app.css'
//import App from './App'
import SuperAdminApp from './apps/superAdmin/SuperAdminApp'

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <SuperAdminApp />
  </StrictMode>,
)
