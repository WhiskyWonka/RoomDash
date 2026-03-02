import { SectionHeader } from '@/components/ui/8bit/blocks/SectionHeader';
import { DeleteUserDialog } from '@/apps/superAdmin/features/users/components/DeleteUserDialog';
import { UserDialog } from '@/apps/superAdmin/features/users/components/UserDialog';
import { UsersTable } from '@/apps/superAdmin/features/users/components/UserTable';
import { Button } from '@/components/ui/8bit/button';
import { Alert } from "@/components/ui/8bit/alert";

import { useUsers } from "../features/users/hooks/useUsers";
import { useUserModals } from "../features/users/hooks/useUserModals";

export default function UsersPage() {
    const { users, loading, error, setError, createUser, updateUser, deleteUser } = useUsers();
    const { modals, openCreate, openEdit, openDelete, closeModals } = useUserModals();

    const handleSubmit = async (firstName: string, lastName: string, username: string, email: string) => {
        const tempPassword = "RoomDash_Safe_2026_!@#!"; 
        const payload = { 
            first_name: firstName,
            last_name: lastName, 
            username, 
            email,
            password: tempPassword,
            password_confirmation: tempPassword
        };

        try {
            if (modals.editing) {
                await updateUser(modals.editing.id, payload);
            } else {
                await createUser(payload);
            }
            closeModals();
        } catch (err) {
            // Error manejado por el hook
        }
    };

    return (
        <div className="space-y-4">
            {error && (
                <Alert variant="destructive" onClose={() => setError(null)}>
                    [SYSTEM_FAILURE]: {error}
                </Alert>
            )}

            <SectionHeader action={<Button variant="outline" onClick={openCreate}>[+] ADD_NEW_USER</Button>} />
            
            {loading ? (
                <div className="p-10 text-center animate-pulse">[LOADING_USER_GRID...]</div>
            ) : (
                <UsersTable
                    users={users}
                    onEdit={openEdit}
                    onDelete={openDelete}
                />
            )}

            <UserDialog
                open={modals.dialogOpen}
                user={modals.editing}
                onClose={closeModals}
                onSubmit={handleSubmit}
            />

            <DeleteUserDialog
                open={modals.deleteOpen}
                user={modals.deleting}
                onClose={closeModals}
                onConfirm={async () => {
                    if (modals.deleting) {
                        await deleteUser(modals.deleting.id);
                        closeModals();
                    }
                }}
            />
        </div>
    );
};