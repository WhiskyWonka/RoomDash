import { useState } from "react";

export function useTenantDelete(onConfirm: () => Promise<void>) {
    const [isDeleting, setIsDeleting] = useState(false);

    const handleConfirm = async () => {
        setIsDeleting(true);
        try {
            await onConfirm();
        } finally {
            setIsDeleting(false);
        }
    };

    return { isDeleting, handleConfirm };
}