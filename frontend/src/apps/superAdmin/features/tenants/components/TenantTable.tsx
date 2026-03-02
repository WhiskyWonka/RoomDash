import type { Tenant } from "@/types/tenant";
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
} from "@/components/ui/8bit/dropdown-menu"

interface Props {
    tenants: Tenant[];
    onEdit: (tenant: Tenant) => void;
    onDelete: (tenant: Tenant) => void;
}

export function TenantTable({ tenants, onEdit, onDelete }: Props) {
    if (tenants.length === 0) {
        return <p className="py-8 text-center text-muted-foreground">No tenants yet.</p>;
    }

    return (
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Name</TableHead>
                    <TableHead>Domain</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Created</TableHead>
                    <TableHead className="text-right">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {tenants.map((t) => (
                    <TableRow key={t.id}>
                        <TableCell className="font-medium">{t.name}</TableCell>
                        <TableCell className="text-blue-400">{t.domain}</TableCell>
                        <TableCell>
                            <span className={`text-[10px] px-2 py-0.5 border ${t.isActive ? 'border-green-500 text-green-500' : 'border-red-500 text-red-500'}`}>
                                {t.isActive ? "ACTIVE" : "INACTIVE"}
                            </span>
                        </TableCell>
                        <TableCell className="text-xs opacity-70">
                            {new Date(t.createdAt).toLocaleDateString()}
                        </TableCell>
                        <TableCell>
                            <div className="flex justify-end gap-2">
                                <Button variant="outline" size="sm" onClick={() => onEdit(t)}>Edit</Button>
                                
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <Button variant="outline" size="sm">...</Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuLabel>Tenant Actions</DropdownMenuLabel>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuGroup>
                                            <DropdownMenuItem onClick={() => window.open(`https://${t.domain}.roomdash.test`, '_blank')}>
                                                Visit Site
                                            </DropdownMenuItem>
                                            <DropdownMenuItem onClick={() => onEdit(t)}>
                                                Configure Admin
                                            </DropdownMenuItem>
                                        </DropdownMenuGroup>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem 
                                            className="text-red-500 focus:text-red-500" 
                                            onClick={() => onDelete(t)}
                                        >
                                            [!] Delete Tenant
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