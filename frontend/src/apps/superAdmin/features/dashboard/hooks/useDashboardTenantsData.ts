import { useQuery } from "@tanstack/react-query";
import { tenantsApi } from "../../../services/tenantsApi";

export function useDashboardTenantsData() {
    const { data, isLoading, error } = useQuery({
        queryKey: ["tenants"], // Usamos la misma key que en la página de Tenants
        queryFn: async () => {
            const response = await tenantsApi.list();
            // Normalizamos la respuesta según tu interceptor
            return response.data?.items || response.data || [];
        }
    });

    return {
        tenantsCount: data?.length || 0,
        loading: isLoading,
        error
    };
}