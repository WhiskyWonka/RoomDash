import { useState } from "react";
import { tenantsApi } from "../../../services/tenantsApi";
import type { User } from "@/types/user";
import type { Tenant } from "@/types/tenant";

export function useTenantAdmin(editingTenant: Tenant | null) {
    const [adminOpen, setAdminOpen] = useState(false);
    const [deleteAdminOpen, setDeleteAdminOpen] = useState(false);
    const [currentAdmin, setCurrentAdmin] = useState<User | null>(null);

    const handleCreateAdmin = async () => {
        if (!editingTenant) return;
        try {
            const response = await tenantsApi.getAdmin(editingTenant.id);
            
            // Laravel manda { success, message, data: { data: User } } 
            // O a veces directamente { data: User } según tu API
            setCurrentAdmin(response.data?.data || response.data || null);
        } catch (err) {
            console.error("Error fetching admin:", err);
            setCurrentAdmin(null);
        } finally {
            setAdminOpen(true);
        }
    };

    const handleSubmitAdmin = async (data: any) => {
        if (!editingTenant) return;
        try {
            if (currentAdmin) {
                await tenantsApi.updateAdmin(editingTenant.id, data);
            } else {
                await tenantsApi.createAdmin(editingTenant.id, data);
            }
            setAdminOpen(false);
        } catch (err: any) {
            // Aquí podrías usar un toast o alert con err.message
            alert(err.message || "Error al guardar el administrador");
        }
    };

    const handleResendAdminVerification = async () => {
        if (!editingTenant) return;
        try {
            await tenantsApi.resendAdminVerification(editingTenant.id);
        } catch (err: any) {
            alert(err.message);
        }
    };

    const handleDeleteAdmin = () => {
        setAdminOpen(false);
        setDeleteAdminOpen(true);
    };

    const handleConfirmDeleteAdmin = async () => {
        if (!editingTenant) return;
        try {
            await tenantsApi.deleteAdmin(editingTenant.id);
            setCurrentAdmin(null);
            setDeleteAdminOpen(false);
        } catch (err: any) {
            alert(err.message);
        }
    };

    return {
        adminOpen,
        setAdminOpen,
        deleteAdminOpen,
        setDeleteAdminOpen,
        currentAdmin,
        handleCreateAdmin,
        handleSubmitAdmin,
        handleResendAdminVerification,
        handleDeleteAdmin,
        handleConfirmDeleteAdmin
    };
}