import { useEffect, useState } from "react";
import type { Tenant } from "@/types/tenant";
import { tenantsApi } from "@/lib/api";
import { TenantTable } from "@/components/TenantTable";
import { TenantDialog } from "@/components/TenantDialog";
import { DeleteTenantDialog } from "@/components/DeleteTenantDialog";
import { TopBar } from "@/components/TopBar";
import { Button } from "@/components/ui/button";
import { useTheme } from "@/hooks/useTheme";

function App() {
  const { theme, toggle } = useTheme();
  const [tenants, setTenants] = useState<Tenant[]>([]);
  const [dialogOpen, setDialogOpen] = useState(false);
  const [deleteOpen, setDeleteOpen] = useState(false);
  const [editing, setEditing] = useState<Tenant | null>(null);
  const [deleting, setDeleting] = useState<Tenant | null>(null);

  const load = () => { tenantsApi.list().then(setTenants); };

  useEffect(load, []);

  const handleCreate = () => { setEditing(null); setDialogOpen(true); };

  const handleEdit = (t: Tenant) => { setEditing(t); setDialogOpen(true); };

  const handleDelete = (t: Tenant) => { setDeleting(t); setDeleteOpen(true); };

  const handleSubmit = async (name: string, domain: string) => {
    if (editing) {
      await tenantsApi.update(editing.id, { name, domain });
    } else {
      await tenantsApi.create({ name, domain });
    }
    setDialogOpen(false);
    load();
  };

  const handleConfirmDelete = async () => {
    if (deleting) {
      await tenantsApi.delete(deleting.id);
    }
    setDeleteOpen(false);
    load();
  };

  return (
    <>
      <TopBar theme={theme} onToggleTheme={toggle} />
      <div className="mx-auto max-w-4xl px-4 pt-22 pb-8">
        <div className="flex items-center justify-between mb-6">
          <h1 className="text-2xl font-bold">Tenants</h1>
          <Button onClick={handleCreate}>Create Tenant</Button>
        </div>
        <TenantTable tenants={tenants} onEdit={handleEdit} onDelete={handleDelete} />
        <TenantDialog open={dialogOpen} tenant={editing} onClose={() => setDialogOpen(false)} onSubmit={handleSubmit} />
        <DeleteTenantDialog open={deleteOpen} tenant={deleting} onClose={() => setDeleteOpen(false)} onConfirm={handleConfirmDelete} />
      </div>
    </>
  );
}

export default App;
