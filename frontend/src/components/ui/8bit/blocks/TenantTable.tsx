import type { Tenant } from "@/types/tenant";
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from "@/components/ui/8bit/table";
import { Button } from "@/components/ui/8bit/button";

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
          <TableHead>Created</TableHead>
          <TableHead className="text-right">Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {tenants.map((t) => (
          <TableRow key={t.id}>
            <TableCell className="font-medium">{t.name}</TableCell>
            <TableCell>{t.domain}</TableCell>
            <TableCell>{new Date(t.createdAt).toLocaleDateString()}</TableCell>
            <TableCell className="flex justify-end gap-4">
              <Button variant="outline" size="sm" onClick={() => onEdit(t)}>Edit</Button>
              <Button variant="destructive" size="sm" onClick={() => onDelete(t)}>Delete</Button>
            </TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  );
}
