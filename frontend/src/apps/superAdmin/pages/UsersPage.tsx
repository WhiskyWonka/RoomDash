import { SectionHeader } from '@/components/ui/8bit/blocks/SectionHeader';
import { DeleteUserDialog } from '@/components/ui/8bit/blocks/users/DeleteUserDialog';
import { UserDialog } from '@/components/ui/8bit/blocks/users/UserDialog';
import { UsersTable } from '@/components/ui/8bit/blocks/users/UserTable';
import { Button } from '@/components/ui/8bit/button';
import { usersApi } from '@/lib/api';
import { User } from '@/types/user';
import { useEffect, useState } from "react";



export default function UsersPage() {

    const [users, setUsers] = useState<User[]>([]);
    const [dialogOpen, setDialogOpen] = useState(false);
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [editing, setEditing] = useState<User | null>(null);
    const [deleting, setDeleting] = useState<User | null>(null);


    const load = () => {
        usersApi.list().then(setUsers);
    };

    useEffect(load, []);

    const handleCreate = () => {
        setEditing(null);
        setDialogOpen(true);
    };

    const handleEdit = (t: User) => {
        setEditing(t);
        setDialogOpen(true);
    };

    const handleDelete = (t: User) => {
        setDeleting(t);
        setDeleteOpen(true);
    };

    const handleSubmit = async (name: string, email: string) => {
        try {
            if (editing) {
                await usersApi.update(editing.id, { name, email });
            } else {
                await usersApi.create({ name, email });
            }
            setDialogOpen(false);
            load();
        } catch (error) {
            console.error("CRITICAL_ERROR: API_REQUEST_FAILED", error);
        }
    };

    const handleConfirmDelete = async () => {
        if (deleting) {
            await usersApi.delete(deleting.id);
        }
        setDeleteOpen(false);
        load();
    };

    return (
        <div className="">
            <SectionHeader action={<Button variant="outline" onClick={handleCreate}>[+] ADD_NEW_USER</Button>} />
            
            <div className="">
                <UsersTable
                    users={users}
                    onEdit={handleEdit}
                    onDelete={handleDelete}
                />
            </div>

            {/* Diálogos de acción */}
            <UserDialog
                open={dialogOpen}
                user={editing}
                onClose={() => setDialogOpen(false)}
                onSubmit={handleSubmit}
            />
            <DeleteUserDialog
                open={deleteOpen}
                user={deleting}
                onClose={() => setDeleteOpen(false)}
                onConfirm={handleConfirmDelete}
            />
        </div>
    );
};
