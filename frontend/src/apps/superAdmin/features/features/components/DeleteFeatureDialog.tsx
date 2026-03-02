import type { Feature } from "@/types/feature";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from "@/components/ui/8bit/dialog";
import { Button } from "@/components/ui/8bit/button";

interface Props {
  open: boolean;
  feature: Feature | null;
  onClose: () => void;
  onConfirm: () => void;
}

export function DeleteFeatureDialog({ open, feature, onClose, onConfirm }: Props) {
  return (
    <Dialog open={open} onOpenChange={(v) => { if (!v) onClose(); }}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Delete Feature</DialogTitle>
          <DialogDescription>
            Are you sure you want to delete <strong>{feature?.name}</strong>? This action cannot be undone.
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
