import { useEffect, useState } from "react";
import type { Tenant } from "@/types/tenant";
import { tenantsApi } from "@/lib/api";
import { TenantTable } from "@/components/ui/8bit/blocks/TenantTable";
import { TenantDialog } from "@/components/ui/8bit/blocks/TenantDialog";
import { DeleteTenantDialog } from "@/components/ui/8bit/blocks/DeleteTenantDialog";
import { Button } from "@/components/ui/8bit/button";

export default function TenantsPage() {

    const [tenants, setTenants] = useState<Tenant[]>([]);
    const [dialogOpen, setDialogOpen] = useState(false);
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [editing, setEditing] = useState<Tenant | null>(null);
    const [deleting, setDeleting] = useState<Tenant | null>(null);

    const load = () => {
        tenantsApi.list().then(setTenants);
    };

    useEffect(load, []);

    const handleCreate = () => {
        setEditing(null);
        setDialogOpen(true);
    };

    const handleEdit = (t: Tenant) => {
        setEditing(t);
        setDialogOpen(true);
    };

    const handleDelete = (t: Tenant) => {
        setDeleting(t);
        setDeleteOpen(true);
    };

    const handleSubmit = async (name: string, domain: string) => {
        console.log("DATA_CHECK:", { name, domain });
        try {
            if (editing) {
                await tenantsApi.update(editing.id, { name, domain });
            } else {
                await tenantsApi.create({ name, domain });
            }
            setDialogOpen(false);
            load();
        } catch (error) {
            console.error("CRITICAL_ERROR: API_REQUEST_FAILED", error);
        }
    };

    const handleConfirmDelete = async () => {
        if (deleting) {
            await tenantsApi.delete(deleting.id);
        }
        setDeleteOpen(false);
        load();
    };

    return (
        <div className="">
            {/* Encabezado de la página dentro del main */}
            <div className="flex items-center justify-between mb-8 border-b-2 border-dashed border-[#004400] pb-4">
                <div>
                    <p className="text-xs mt-1 text-green-500">{">"} ACCESSING_RECORDS... OK</p>
                </div>

                <Button
                    variant="outline"
                    onClick={handleCreate}
                    className="retro border-2"
                >
                    [+] ADD_NEW_TENANT
                </Button>
            </div>

            <div className="">
                <TenantTable
                    tenants={tenants}
                    onEdit={handleEdit}
                    onDelete={handleDelete}
                />
            </div>

            {/* Diálogos de acción */}
            <TenantDialog
                open={dialogOpen}
                tenant={editing}
                onClose={() => setDialogOpen(false)}
                onSubmit={handleSubmit}
            />
            <DeleteTenantDialog
                open={deleteOpen}
                tenant={deleting}
                onClose={() => setDeleteOpen(false)}
                onConfirm={handleConfirmDelete}
            />
        </div>
    );
}