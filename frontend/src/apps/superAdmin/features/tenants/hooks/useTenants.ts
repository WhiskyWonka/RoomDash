import { useState, useEffect, useCallback } from "react";
import type { Tenant } from "@/types/tenant";
import { tenantsApi } from "../../../services/tenantsApi";

export function useTenants() {
    const [tenants, setTenants] = useState<Tenant[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    const loadTenants = useCallback(async () => {
        setLoading(true);
        setError(null);
        try {
            const response: any = await tenantsApi.list();
            // AJUSTE: Dependiendo de cómo responda tu Laravel, 
            // puede ser response.data o response.data.items
            setTenants(response.data?.items || response.data || []);
        } catch (err: any) {
            // El interceptor ya nos da el mensaje limpio en err.message
            setError("ERR_FETCHING_TENANTS: " + err.message);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => { loadTenants(); }, [loadTenants]);

    // Esta función es genial porque recarga la lista automáticamente tras un cambio
    const executeAction = async (action: () => Promise<any>) => {
        try {
            setError(null);
            await action();
            await loadTenants(); // Recarga la lista para ver el cambio (ej: nuevo tenant)
            return true;
        } catch (err: any) {
            // Ya no necesitas err.response?.data?.message
            // El interceptor ya hizo ese trabajo sucio por vos
            setError(err.message || "ACTION_FAILED");
            return false;
        }
    };

    return {
        tenants,
        loading,
        error,
        setError,
        // Funciones listas para usar en el componente
        createTenant: (data: any) => executeAction(() => tenantsApi.create(data)),
        updateTenant: (id: string | number, data: any) => executeAction(() => tenantsApi.update(id, data)),
        deleteTenant: (id: string | number) => executeAction(() => tenantsApi.delete(id)),
        refresh: loadTenants // Útil para un botón de "actualizar" manual
    };
}