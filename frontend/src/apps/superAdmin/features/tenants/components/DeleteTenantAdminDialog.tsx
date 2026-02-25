import type { Tenant } from "@/types/tenant";
import type { User } from "@/types/user";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from "@/components/ui/8bit/dialog";
import { Button } from "@/components/ui/8bit/button";

interface Props {
  open: boolean;
  tenant: Tenant | null;
  adminUser: User | null;
  onClose: () => void;
  onConfirm: () => void;
}

export function DeleteTenantAdminDialog({ open, tenant, adminUser, onClose, onConfirm }: Props) {
  return (
    <Dialog open={open} onOpenChange={(v) => { if (!v) onClose(); }}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Delete Admin User</DialogTitle>
          <DialogDescription>
            Are you sure you want to delete <strong>{adminUser?.firstName} {adminUser?.lastName}</strong> from <strong>{tenant?.name}</strong>? This action cannot be undone.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button variant="outline" onClick={onClose}>Cancel</Button>
          <Button variant="destructive" onClick={onConfirm}>Delete</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
