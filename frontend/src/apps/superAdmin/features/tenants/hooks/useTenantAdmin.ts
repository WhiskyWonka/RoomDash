import { useState } from "react";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { tenantsApi } from "../../../services/tenantsApi";
import type { User } from "@/types/user";
import type { Tenant } from "@/types/tenant";

export function useTenantAdmin(editingTenant: Tenant | null) {
    const queryClient = useQueryClient();
    const [adminOpen, setAdminOpen] = useState(false);
    const [deleteAdminOpen, setDeleteAdminOpen] = useState(false);
    const [currentAdmin, setCurrentAdmin] = useState<User | null>(null);

    // --- ACCIÓN: Buscar el Admin ---
    const handleCreateAdmin = async () => {
        if (!editingTenant) return;
        try {
            const response = await tenantsApi.getAdmin(editingTenant.id);
            setCurrentAdmin(response.data?.data || response.data || null);
        } catch (err) {
            setCurrentAdmin(null);
        } finally {
            setAdminOpen(true);
        }
    };

    // --- MUTATION: Guardar (Create/Update) ---
    const { mutateAsync: submitAdminMutation } = useMutation({
        mutationFn: (data: any) => {
            if (!editingTenant) throw new Error("No tenant selected");
            return currentAdmin 
                ? tenantsApi.updateAdmin(editingTenant.id, data)
                : tenantsApi.createAdmin(editingTenant.id, data);
        },
        onSuccess: () => {
            // Invalidamos 'tenants' para que la tabla muestre si el tenant tiene admin ahora
            queryClient.invalidateQueries({ queryKey: ['tenants'] });
            setAdminOpen(false);
        }
    });

    // --- MUTATION: Borrar ---
    const { mutateAsync: deleteAdminMutation } = useMutation({
        mutationFn: () => {
            if (!editingTenant) throw new Error("No tenant selected");
            return tenantsApi.deleteAdmin(editingTenant.id);
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['tenants'] });
            setCurrentAdmin(null);
            setDeleteAdminOpen(false);
        }
    });

    // --- MUTATION: Reenviar Verificación ---
    const { mutateAsync: resendVerificationMutation } = useMutation({
        mutationFn: () => {
            if (!editingTenant) throw new Error("No tenant selected");
            return tenantsApi.resendAdminVerification(editingTenant.id);
        }
    });

    return {
        adminOpen,
        setAdminOpen,
        deleteAdminOpen,
        setDeleteAdminOpen,
        currentAdmin,
        handleCreateAdmin,
        handleSubmitAdmin: submitAdminMutation,
        handleDeleteAdmin: () => {
            setAdminOpen(false);
            setDeleteAdminOpen(true);
        },
        handleConfirmDeleteAdmin: deleteAdminMutation,
        handleResendAdminVerification: resendVerificationMutation
    };
}