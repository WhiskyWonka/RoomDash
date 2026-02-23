import type { User } from "@/types/user";
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from "@/components/ui/8bit/table";
import { Button } from "@/components/ui/8bit/button";

interface Props {
    users: User[];
    onEdit: (user: User) => void;
    onDelete: (user: User) => void;
}

export function UsersTable({ users, onEdit, onDelete }: Props) {
    if (users.length === 0) {
        return <p className="py-8 text-center text-muted-foreground">No users yet.</p>;
    }

    return (
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Name</TableHead>
                    <TableHead>Username</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead>Created</TableHead>
                    <TableHead className="text-right">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {users.map((t) => (
                    <TableRow key={t.id}>
                        <TableCell>{t.firstName} {t.lastName}</TableCell>
                        <TableCell className="font-medium">
                            {typeof t.username === 'string' && t.username.length > 0 
                                ? t.username 
                                : 'N/A'}
                        </TableCell>
                        <TableCell>{t.email}</TableCell>
                        <TableCell>{new Date(t.createdAt).toLocaleDateString()}</TableCell>
                        <TableCell className="flex justify-end gap-4">
                            <Button variant="outline" size="sm" onClick={() => onEdit(t)}>Edit</Button>
                            <Button variant="warning" size="sm" onClick={() => onDelete(t)}>Delete</Button>
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}
