import { useEffect, useState } from "react";
import type { Tenant } from "@/types/tenant";
import { tenantsApi } from "@/lib/api";
import { TenantTable } from "@/components/ui/8bit/blocks/tenants/TenantTable";
import { TenantDialog } from "@/components/ui/8bit/blocks/tenants/TenantDialog";
import { DeleteTenantDialog } from "@/components/ui/8bit/blocks/tenants/DeleteTenantDialog";
import { Button } from "@/components/ui/8bit/button";
import { SectionHeader } from "@/components/ui/8bit/blocks/SectionHeader";

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
            <SectionHeader action={<Button variant="outline" onClick={handleCreate}>[+] ADD_NEW_TENANT</Button>} />

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