import type { Tenant } from "@/types/tenant";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from "@/components/ui/8bit/dialog";
import { Button } from "@/components/ui/8bit/button";
import { useTenantDelete } from "../hooks/useTenantDelete";

interface Props {
    open: boolean;
    tenant: Tenant | null;
    onClose: () => void;
    onConfirm: () => Promise<void>;
}

export function DeleteTenantDialog({ open, tenant, onClose, onConfirm }: Props) {

    const { isDeleting, handleConfirm } = useTenantDelete(onConfirm);

    return (
        <Dialog open={open} onOpenChange={(v) => { if (!v) onClose(); }}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Tenant</DialogTitle>
                    <DialogDescription className="text-red-400">
                        <div className="mt-2">
                            Are you sure you want to delete <strong>{tenant?.name}</strong>? 
                        </div>
                        <div className="my-8 text-center">
                            [THIS_ACTION_CANNOT_BE_UNDONE]
                        </div>
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" onClick={onClose}>Cancel</Button>
                    <Button 
                        variant="destructive" 
                        disabled={isDeleting}
                        onClick={onConfirm}
                    >
                        {isDeleting ? "DELETING..." : "Delete"}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
