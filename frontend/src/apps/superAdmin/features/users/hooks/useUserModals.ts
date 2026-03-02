import { useState } from "react";
import { User } from "@/types/user";

export function useUserModals() {
    const [dialogOpen, setDialogOpen] = useState(false);
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [editing, setEditing] = useState<User | null>(null);
    const [deleting, setDeleting] = useState<User | null>(null);

    const openCreate = () => { setEditing(null); setDialogOpen(true); };
    const openEdit = (u: User) => { setEditing(u); setDialogOpen(true); };
    const openDelete = (u: User) => { setDeleting(u); setDeleteOpen(true); };
    const closeModals = () => { setDialogOpen(false); setDeleteOpen(false); };

    return {
        modals: { dialogOpen, deleteOpen, editing, deleting },
        openCreate, openEdit, openDelete, closeModals
    };
}