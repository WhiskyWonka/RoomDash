import { useEffect } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import type { Tenant } from "@/types/tenant";

// El esquema de validación se queda en el hook (lógica de negocio)
const tenantSchema = z.object({
    name: z.string().min(1, "Name is required").max(255),
    domain: z
        .string()
        .min(1, "Domain is required")
        .max(255)
        .regex(/^[a-z0-9-]+$/, "Only lowercase letters, numbers, and hyphens"),
    isActive: z.boolean(),
});

type TenantFormValues = z.infer<typeof tenantSchema>;

export function useTenantForm(
    tenant: Tenant | null,
    onSubmit: (name: string, domain: string, isActive: boolean) => Promise<void>
) {
    const form = useForm<TenantFormValues>({
        resolver: zodResolver(tenantSchema),
        defaultValues: { name: "", domain: "", isActive: true },
    });

    // Sincronización al abrir el modal
    useEffect(() => {
        form.reset({
            name: tenant?.name ?? "",
            domain: tenant?.domain ?? "",
            isActive: tenant?.isActive ?? true,
        });
    }, [tenant, form]);

    const handleFormSubmit = form.handleSubmit(async (values) => {
        await onSubmit(values.name, values.domain, values.isActive);
    });

    return {
        form,
        handleFormSubmit,
    };
}