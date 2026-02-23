import { useTenants } from "../features/tenants/hooks/useTenants";
import { useTenantModals } from "../features/tenants/hooks/useTenantModals";
import { TenantTable } from "../features/tenants/components/TenantTable";
import { TenantDialog } from "../features/tenants/components/TenantDialog";
import { DeleteTenantDialog } from "../features/tenants/components/DeleteTenantDialog";
import { Button } from "@/components/ui/8bit/button";
import { Alert } from "@/components/ui/8bit/alert";
import { SectionHeader } from "@/components/ui/8bit/blocks/SectionHeader";

export default function TenantsPage() {
    // 1. Hook de Datos (Controller de API)
    const { 
        tenants, loading, error, setError, 
        createTenant, updateTenant, deleteTenant 
    } = useTenants();

    // 2. Hook de UI (Controller de Ventanas)
    const { modals, openCreate, openEdit, openDelete, closeModals } = useTenantModals();

    // Handlers de acción (Orquestadores)
    const handleSubmit = async (name: string, domain: string) => {
        const success = modals.editing 
            ? await updateTenant(modals.editing.id, { name, domain })
            : await createTenant({ name, domain });
        
        if (success) closeModals();
    };

    const handleConfirmDelete = async () => {
        if (modals.deleting) {
            const success = await deleteTenant(modals.deleting.id);
            if (success) closeModals();
        }
    };

    return (
        <div className="space-y-4">
            {/* Sistema de alertas centralizado */}
            {error && (
                <Alert variant="destructive" onClose={() => setError(null)}>
                    [SYSTEM_FAILURE]: {error}
                </Alert>
            )}

            <SectionHeader 
                action={<Button onClick={openCreate}>[+] ADD_NEW_TENANT</Button>} 
            />

            {loading ? (
                <div className="font-mono text-[#00ff00] p-10 text-center animate-pulse">
                    [LOADING_DATABASE...]
                </div>
            ) : (
                <TenantTable
                    tenants={tenants}
                    onEdit={openEdit}
                    onDelete={openDelete}
                />
            )}

            <TenantDialog
                open={modals.dialogOpen}
                tenant={modals.editing}
                onClose={closeModals}
                onSubmit={handleSubmit}
            />

            <DeleteTenantDialog
                open={modals.deleteOpen}
                tenant={modals.deleting}
                onClose={closeModals}
                onConfirm={handleConfirmDelete}
            />
        </div>
    );
}