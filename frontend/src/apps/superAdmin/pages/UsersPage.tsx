import { SectionHeader } from '@/components/ui/8bit/blocks/SectionHeader';
import { DeleteUserDialog } from '@/components/ui/8bit/blocks/users/DeleteUserDialog';
import { UserDialog } from '@/components/ui/8bit/blocks/users/UserDialog';
import { UsersTable } from '@/components/ui/8bit/blocks/users/UserTable';
import { Button } from '@/components/ui/8bit/button';
import { rootUsersApi } from '@/lib/api';
import { User } from '@/types/user';
import { useEffect, useState } from "react";



export default function UsersPage() {

    const [users, setUsers] = useState<User[]>([]);
    const [dialogOpen, setDialogOpen] = useState(false);
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [editing, setEditing] = useState<User | null>(null);
    const [deleting, setDeleting] = useState<User | null>(null);


    const load = () => {
        rootUsersApi.list()
            .then((response: any) => {
                const usersArray = response.data || [];
                
                // Si tu componente espera "name" pero el backend manda firstName/lastName, 
                // puedes normalizarlo aquí:
                /*const normalizedUsers = usersArray.map((u: any) => ({
                    ...u,
                    name: u.name || `${u.firstName} ${u.lastName}`, // Fallback por si acaso
                }));*/

                setUsers(usersArray);
            })
            .catch(err => {
                console.error("LOAD_USERS_ERROR:", err);
                setUsers([]);
            });
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
                await rootUsersApi.update(editing.id, { name, email });
            } else {
                await rootUsersApi.create({ name, email });
            }
            setDialogOpen(false);
            load();
        } catch (error) {
            console.error("CRITICAL_ERROR: API_REQUEST_FAILED", error);
        }
    };

    const handleConfirmDelete = async () => {
        if (deleting) {
            await rootUsersApi.delete(deleting.id);
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
