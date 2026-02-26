import { useState } from "react";
import { Tenant } from "@/types/tenant";

/**
 * Aisla toda esa lógica de "quién se está editando" y si el modal está abierto o cerrado
 */
export function useTenantModals() {
    const [dialogOpen, setDialogOpen] = useState(false);
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [editing, setEditing] = useState<Tenant | null>(null);
    const [deleting, setDeleting] = useState<Tenant | null>(null);

    const openCreate = () => { setEditing(null); setDialogOpen(true); };
    const openEdit = (t: Tenant) => { setEditing(t); setDialogOpen(true); };
    const openDelete = (t: Tenant) => { setDeleting(t); setDeleteOpen(true); };

    return {
        modals: { dialogOpen, deleteOpen, editing, deleting },
        closeModals: () => { setDialogOpen(false); setDeleteOpen(false); },
        openCreate, openEdit, openDelete
    };
}