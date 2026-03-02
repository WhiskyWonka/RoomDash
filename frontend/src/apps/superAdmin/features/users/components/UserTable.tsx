import type { User } from "@/types/user";
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from "@/components/ui/8bit/table";
import { Button } from "@/components/ui/8bit/button";
import { 
    DropdownMenu, 
    DropdownMenuContent, 
    DropdownMenuGroup, 
    DropdownMenuItem, 
    DropdownMenuLabel, 
    DropdownMenuSeparator, 
    DropdownMenuTrigger 
} from "@/components/ui/8bit/dropdown-menu";

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
                    <TableHead>Full Name</TableHead>
                    <TableHead>Username</TableHead>
                    <TableHead>Email Address</TableHead>
                    <TableHead>Created At</TableHead>
                    <TableHead className="text-right">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {users.map((u) => (
                    <TableRow key={u.id}>
                        <TableCell className="font-medium">
                            {u.firstName} {u.lastName}
                        </TableCell>
                        <TableCell className="font-mono text-xs">
                            {typeof u.username === 'string' && u.username.length > 0 
                                ? `@${u.username}` 
                                : '---'}
                        </TableCell>
                        <TableCell className="text-blue-400">{u.email}</TableCell>
                        <TableCell className="text-xs opacity-70">
                            {new Date(u.createdAt).toLocaleDateString()}
                        </TableCell>
                        <TableCell>
                            <div className="flex justify-end gap-2">
                                <Button variant="outline" size="sm" onClick={() => onEdit(u)}>
                                    Edit
                                </Button>
                                
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <Button variant="outline" size="sm">...</Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuLabel>User Options</DropdownMenuLabel>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuGroup>
                                            <DropdownMenuItem onClick={() => onEdit(u)}>
                                                User Settings
                                            </DropdownMenuItem>
                                            <DropdownMenuItem onClick={() => console.log("Logic for password reset here")}>
                                                Reset Password
                                            </DropdownMenuItem>
                                        </DropdownMenuGroup>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem 
                                            className="text-red-500 focus:text-red-500 font-bold" 
                                            onClick={() => onDelete(u)}
                                        >
                                            [!] DELETE_USER
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}