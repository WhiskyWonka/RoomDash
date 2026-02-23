import { useState, useEffect, useCallback } from "react";
import type { Tenant } from "@/types/tenant";
import { tenantsApi } from "@/lib/api";

export function useTenants() {
    const [tenants, setTenants] = useState<Tenant[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    const loadTenants = useCallback(async () => {
        setLoading(true);
        setError(null);
        try {
            const response: any = await tenantsApi.list();
            setTenants(response.data?.items || []);
        } catch (err: any) {
            setError("ERR_FETCHING_TENANTS: " + (err.message || "UNKNOWN_ERROR"));
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => { loadTenants(); }, [loadTenants]);

    const executeAction = async (action: () => Promise<any>) => {
        try {
            setError(null);
            await action();
            await loadTenants();
            return true;
        } catch (err: any) {
            setError(err.response?.data?.message || "ACTION_FAILED");
            return false;
        }
    };

    return {
        tenants,
        loading,
        error,
        setError, // Para poder cerrarlo desde la UI
        createTenant: (data: any) => executeAction(() => tenantsApi.create(data)),
        updateTenant: (id: string, data: any) => executeAction(() => tenantsApi.update(id, data)),
        deleteTenant: (id: string) => executeAction(() => tenantsApi.delete(id))
    };
}