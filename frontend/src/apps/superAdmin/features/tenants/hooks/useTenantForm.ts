import { useState, useEffect } from "react";
import type { Tenant } from "@/types/tenant";

export function useTenantForm(tenant: Tenant | null, onSubmit: (name: string, domain: string) => Promise<void>) {
    const [name, setName] = useState("");
    const [domain, setDomain] = useState("");

    // Sincronizar cuando cambia el tenant (al abrir para editar)
    useEffect(() => {
        setName(tenant?.name || "");
        setDomain(tenant?.domain || "");
    }, [tenant]);

    const handleFormSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        await onSubmit(name, domain);
    };

    return {
        name, setName,
        domain, setDomain,
        handleFormSubmit
    };
}