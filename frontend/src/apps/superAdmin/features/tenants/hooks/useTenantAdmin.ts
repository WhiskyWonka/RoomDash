import { useState } from "react";
import { tenantsApi } from "@/lib/api";
import type { User } from "@/types/user";
import type { Tenant } from "@/types/tenant";

export function useTenantAdmin(editingTenant: Tenant | null) {
    const [adminOpen, setAdminOpen] = useState(false);
    const [deleteAdminOpen, setDeleteAdminOpen] = useState(false);
    const [currentAdmin, setCurrentAdmin] = useState<User | null>(null);

    const handleCreateAdmin = async () => {
        if (!editingTenant) return;
        try {
            const response = await tenantsApi.getAdmin(editingTenant.id) as any;
            setCurrentAdmin(response.data ?? null);
        } catch {
            setCurrentAdmin(null);
        } finally {
            setAdminOpen(true);
        }
    };

    const handleSubmitAdmin = async (data: any) => {
        if (!editingTenant) return;
        if (currentAdmin) {
            await tenantsApi.updateAdmin(editingTenant.id, data);
        } else {
            await tenantsApi.createAdmin(editingTenant.id, data);
        }
        setAdminOpen(false);
    };

    const handleResendAdminVerification = async () => {
        if (!editingTenant) return;
        await tenantsApi.resendAdminVerification(editingTenant.id);
    };

    const handleDeleteAdmin = () => {
        setAdminOpen(false);
        setDeleteAdminOpen(true);
    };

    const handleConfirmDeleteAdmin = async () => {
        if (!editingTenant) return;
        await tenantsApi.deleteAdmin(editingTenant.id);
        setCurrentAdmin(null);
        setDeleteAdminOpen(false);
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