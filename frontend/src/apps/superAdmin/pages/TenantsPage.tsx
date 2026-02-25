import { useTenants } from "../features/tenants/hooks/useTenants";
import { useTenantModals } from "../features/tenants/hooks/useTenantModals";
import { useTenantAdmin } from "../features/tenants/hooks/useTenantAdmin";

import { useState } from "react";
import { useMemo } from "react";
import { TenantTable } from "../features/tenants/components/TenantTable";
import { TenantDialog } from "../features/tenants/components/TenantDialog";
import { TenantAdminDialog } from "../features/tenants/components/TenantAdminDialog";
import { DeleteTenantDialog } from "../features/tenants/components/DeleteTenantDialog";
import { DeleteTenantAdminDialog } from "../features/tenants/components/DeleteTenantAdminDialog";

import { Button } from "@/components/ui/8bit/button";
import { Alert } from "@/components/ui/8bit/alert";
import { SectionHeader } from "@/components/ui/8bit/blocks/SectionHeader";
import { Tabs, TabsList, TabsTrigger } from "@/components/ui/8bit/tabs";

export default function TenantsPage() {

    const [tab, setTab] = useState<"all" | "active" | "inactive">("all");

    // 1. Hook de Datos Principales
    const { tenants, loading, error, setError, createTenant, updateTenant, deleteTenant } = useTenants();

    // 2. Hook de UI (Control de modales de Tenant)
    const { modals, openCreate, openEdit, openDelete, closeModals } = useTenantModals();

    // 3. Hook de Admin (Lógica extraída)
    const adminLogic = useTenantAdmin(modals.editing);

    // Filtrado memoizado para rendimiento
    const filteredTenants = useMemo(() => {
        if (tab === "active") return tenants.filter((t) => t.isActive);
        if (tab === "inactive") return tenants.filter((t) => !t.isActive);
        return tenants;
    }, [tenants, tab]);

    const handleSubmit = async (name: string, domain: string, isActive: boolean) => {
        const success = modals.editing 
            ? await updateTenant(modals.editing.id, { name, domain, isActive })
            : await createTenant({ name, domain, isActive });
        if (success) closeModals();
    };

    return (
        <div className="space-y-4">
            {error && (
                <Alert variant="destructive" onClose={() => setError(null)}>
                    [SYSTEM_FAILURE]: {error}
                </Alert>
            )}

            <SectionHeader 
                action={<Button onClick={openCreate}>[+] ADD_NEW_TENANT</Button>} 
            />

            <div className="">
                <Tabs value={tab} onValueChange={(v) => setTab(v as typeof tab)} className="mb-4">
                    <TabsList>
                        <TabsTrigger value="all">All</TabsTrigger>
                        <TabsTrigger value="active">Actives</TabsTrigger>
                        <TabsTrigger value="inactive">Inactives</TabsTrigger>
                    </TabsList>
                </Tabs>
                {loading ? (
                    <div className="p-10 text-center animate-pulse">[LOADING_TENANT_GRID...]</div>
                ) : (
                    <TenantTable 
                        tenants={filteredTenants} 
                        onEdit={openEdit} 
                        onDelete={openDelete} 
                    />
                )}
            </div>

            {/* Modales de Tenant */}
            <TenantDialog
                open={modals.dialogOpen}
                tenant={modals.editing}
                onClose={closeModals}
                onSubmit={handleSubmit}
                onCreateAdmin={modals.editing ? adminLogic.handleCreateAdmin : undefined}
            />

            <DeleteTenantDialog
                open={modals.deleteOpen}
                tenant={modals.deleting}
                onClose={closeModals}
                onConfirm={async () => {
                    if (modals.deleting && await deleteTenant(modals.deleting.id)) closeModals();
                }}
            />

            {/* Modales de Admin (Nuevos) */}
            <TenantAdminDialog
                open={adminLogic.adminOpen}
                tenant={modals.editing}
                adminUser={adminLogic.currentAdmin}
                onClose={() => adminLogic.setAdminOpen(false)}
                onSubmit={adminLogic.handleSubmitAdmin}
                onDelete={adminLogic.currentAdmin ? adminLogic.handleDeleteAdmin : undefined}
                onResendVerification={adminLogic.currentAdmin ? adminLogic.handleResendAdminVerification : undefined}
            />

            <DeleteTenantAdminDialog
                open={adminLogic.deleteAdminOpen}
                tenant={modals.editing}
                adminUser={adminLogic.currentAdmin}
                onClose={() => adminLogic.setDeleteAdminOpen(false)}
                onConfirm={adminLogic.handleConfirmDeleteAdmin}
            />
        </div>
    );
}