import { useEffect } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import type { User } from "@/types/user";

// Esquema de validación
export const adminSchema = z.object({
    first_name: z.string().min(1, "Required").max(255),
    last_name: z.string().min(1, "Required").max(255),
    username: z
        .string()
        .min(1, "Required")
        .max(50)
        .regex(/^[a-zA-Z0-9_-]+$/, "Only letters, numbers, underscores, hyphens"),
    email: z.string().min(1, "Required").email("Invalid email").max(255),
});

export type AdminFormValues = z.infer<typeof adminSchema>;

interface UseTenantAdminFormProps {
    open: boolean;
    adminUser?: User | null;
    onClose: () => void;
    onSubmit: (values: AdminFormValues) => Promise<void>;
}

export function useTenantAdminForm({ open, adminUser, onClose, onSubmit }: UseTenantAdminFormProps) {
    const isEdit = Boolean(adminUser);

    const form = useForm<AdminFormValues>({
        resolver: zodResolver(adminSchema),
        defaultValues: {
            first_name: "",
            last_name: "",
            username: "",
            email: "",
        },
    });

    // Sincronización: Cuando el diálogo se abre o cambia el usuario, reseteamos el form
    useEffect(() => {
        if (open) {
            form.reset({
                first_name: adminUser?.firstName ?? "",
                last_name: adminUser?.lastName ?? "",
                username: adminUser?.username ?? "",
                email: adminUser?.email ?? "",
            });
        }
    }, [open, adminUser, form]);

    const handleFormSubmit = async (values: AdminFormValues) => {
        await onSubmit(values);
        form.reset();
    };

    const handleClose = () => {
        form.reset();
        onClose();
    };

    return {
        form,
        isEdit,
        handleFormSubmit,
        handleClose,
        isValid: form.formState.isValid,
        isSubmitting: form.formState.isSubmitting
    };
}