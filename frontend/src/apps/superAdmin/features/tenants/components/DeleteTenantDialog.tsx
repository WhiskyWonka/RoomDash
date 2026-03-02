import type { Tenant } from "@/types/tenant";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from "@/components/ui/8bit/dialog";
import { Button } from "@/components/ui/8bit/button";
import { useState } from "react";

interface Props {
    open: boolean;
    tenant: Tenant | null;
    onClose: () => void;
    onConfirm: () => Promise<void>; // Esta ahora es la mutation.mutateAsync
}

export function DeleteTenantDialog({ open, tenant, onClose, onConfirm }: Props) {
    const [isDeleting, setIsDeleting] = useState(false);

    const handleConfirm = async () => {
        setIsDeleting(true);
        try {
            await onConfirm();
            // No hace falta onClose() acá porque el padre lo cierra en el .then() o onSuccess
        } catch (error) {
            // El error ya lo captura el hook global, pero acá frenamos el loading
            setIsDeleting(false); 
        }
    };

    return (
        <Dialog open={open} onOpenChange={(v) => { if (!v && !isDeleting) onClose(); }}>
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
                    <Button variant="outline" onClick={onClose} disabled={isDeleting}>
                        CANCEL
                    </Button>
                    <Button 
                        variant="destructive" 
                        disabled={isDeleting}
                        onClick={handleConfirm}
                    >
                        {isDeleting ? "DELETING..." : "Delete"}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
