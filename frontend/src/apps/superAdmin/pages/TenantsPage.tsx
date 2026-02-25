import { useEffect, useState } from "react";
import type { Tenant } from "@/types/tenant";
import type { User } from "@/types/user";
import { tenantsApi } from "@/lib/api";
import { TenantTable } from "@/components/ui/8bit/blocks/tenants/TenantTable";
import { TenantDialog } from "@/components/ui/8bit/blocks/tenants/TenantDialog";
import { TenantAdminDialog } from "@/components/ui/8bit/blocks/tenants/TenantAdminDialog";
import { DeleteTenantDialog } from "@/components/ui/8bit/blocks/tenants/DeleteTenantDialog";
import { DeleteTenantAdminDialog } from "@/components/ui/8bit/blocks/tenants/DeleteTenantAdminDialog";
import { Button } from "@/components/ui/8bit/button";
import { Tabs, TabsList, TabsTrigger } from "@/components/ui/8bit/tabs";
import { SectionHeader } from "@/components/ui/8bit/blocks/SectionHeader";

export default function TenantsPage() {

    const [tenants, setTenants] = useState<Tenant[]>([]);
    const [tab, setTab] = useState<"all" | "active" | "inactive">("all");
    const [dialogOpen, setDialogOpen] = useState(false);
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [adminOpen, setAdminOpen] = useState(false);
    const [deleteAdminOpen, setDeleteAdminOpen] = useState(false);
    const [currentAdmin, setCurrentAdmin] = useState<User | null>(null);
    const [editing, setEditing] = useState<Tenant | null>(null);
    const [deleting, setDeleting] = useState<Tenant | null>(null);

    const load = () => {
        tenantsApi.list()
        .then((response:any) => {
            console.log("LOAD_TENANTS_RESPONSE:", response);
            const tenantsArray = response.data?.items || [];
            setTenants(tenantsArray);
        })
        .catch((error) => {
            console.error("CRITICAL_ERROR: API_REQUEST_FAILED", error);
        });
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

    const handleSubmit = async (name: string, domain: string, isActive: boolean) => {
        try {
            if (editing) {
                await tenantsApi.update(editing.id, { name, domain });
                if (isActive !== editing.isActive) {
                    await (isActive ? tenantsApi.activate(editing.id) : tenantsApi.deactivate(editing.id));
                }
            } else {
                await tenantsApi.create({ name, domain });
            }
            setDialogOpen(false);
            load();
        } catch (error) {
            console.error("CRITICAL_ERROR: API_REQUEST_FAILED", error);
        }
    };

    const handleCreateAdmin = async () => {
        if (!editing) { return; }
        setDialogOpen(false);
        try {
            const response = await tenantsApi.getAdmin(editing.id) as any;
            setCurrentAdmin(response.data ?? null);
        } catch {
            setCurrentAdmin(null);
        }
        setAdminOpen(true);
    };

    const handleSubmitAdmin = async (data: any) => {
        if (!editing) { return; }
        if (currentAdmin) {
            await tenantsApi.updateAdmin(editing.id, data);
        } else {
            await tenantsApi.createAdmin(editing.id, data);
        }
        setAdminOpen(false);
    };

    const handleDeleteAdmin = () => {
        setAdminOpen(false);
        setDeleteAdminOpen(true);
    };

    const handleConfirmDeleteAdmin = async () => {
        if (!editing) { return; }
        await tenantsApi.deleteAdmin(editing.id);
        setCurrentAdmin(null);
        setDeleteAdminOpen(false);
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
                <Tabs value={tab} onValueChange={(v) => setTab(v as typeof tab)} className="mb-4">
                    <TabsList>
                        <TabsTrigger value="all">All</TabsTrigger>
                        <TabsTrigger value="active">Actives</TabsTrigger>
                        <TabsTrigger value="inactive">Inactives</TabsTrigger>
                    </TabsList>
                </Tabs>
                <TenantTable
                    tenants={
                        tab === "active" ? tenants.filter((t) => t.isActive)
                        : tab === "inactive" ? tenants.filter((t) => !t.isActive)
                        : tenants
                    }
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
                onCreateAdmin={editing ? handleCreateAdmin : undefined}
            />
            <TenantAdminDialog
                open={adminOpen}
                tenant={editing}
                adminUser={currentAdmin}
                onClose={() => setAdminOpen(false)}
                onSubmit={handleSubmitAdmin}
                onDelete={currentAdmin ? handleDeleteAdmin : undefined}
            />
            <DeleteTenantAdminDialog
                open={deleteAdminOpen}
                tenant={editing}
                adminUser={currentAdmin}
                onClose={() => setDeleteAdminOpen(false)}
                onConfirm={handleConfirmDeleteAdmin}
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