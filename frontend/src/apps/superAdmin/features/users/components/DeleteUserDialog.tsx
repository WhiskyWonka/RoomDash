import type { User } from "@/types/user";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from "@/components/ui/8bit/dialog";
import { Button } from "@/components/ui/8bit/button";
import { useState } from "react";

interface Props {
    open: boolean;
    user: User | null;
    onClose: () => void;
    onConfirm: () => Promise<void>; // Ahora es una Promesa
}

export function DeleteUserDialog({ open, user, onClose, onConfirm }: Props) {
    const [isDeleting, setIsDeleting] = useState(false);

    const handleConfirm = async () => {
        setIsDeleting(true);
        try {
            await onConfirm();
            // No cerramos aquí, el padre se encarga tras el éxito
        } catch (error) {
            // Si falla, liberamos el botón para que el usuario pueda reintentar o cerrar
            setIsDeleting(false);
        }
    };

    return (
        <Dialog open={open} onOpenChange={(v) => { if (!v && !isDeleting) onClose(); }}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete User</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete <strong>{user?.username}</strong>? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" onClick={onClose} disabled={isDeleting}>
                        Cancel
                    </Button>
                    <Button
                        variant="destructive"
                        onClick={handleConfirm}
                        disabled={isDeleting}
                    >
                        {isDeleting ? "Deleting..." : "Delete"}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}