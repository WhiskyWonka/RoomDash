import type { Feature } from "@/types/feature";
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from "@/components/ui/8bit/table";
import { Button } from "@/components/ui/8bit/button";

interface Props {
  features: Feature[];
  onEdit: (feature: Feature) => void;
  onDelete: (feature: Feature) => void;
}

export function FeatureTable({ features, onEdit, onDelete }: Props) {
  if (features.length === 0) {
    return <p className="py-8 text-center text-muted-foreground">No features yet.</p>;
  }

  return (
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Name</TableHead>
          <TableHead>Slug</TableHead>
          <TableHead>Description</TableHead>
          <TableHead>Created</TableHead>
          <TableHead className="text-right">Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {features.map((t) => (
          <TableRow key={t.id}>
            <TableCell className="font-medium">{t.name}</TableCell>
            <TableCell className="font-medium">{t.slug}</TableCell>
            <TableCell>{t.description}</TableCell>
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
