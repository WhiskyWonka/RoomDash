import { useQuery } from "@tanstack/react-query";
import { auditApi } from "../../../services/auditApi";

export function useAuditLogs() {
    const { data, isLoading, error, refetch } = useQuery({
        queryKey: ["audit-logs"],
        queryFn: () => auditApi.list(),
        refetchInterval: 30000, // Auto-refresco cada 30 segundos
    });

    return {
        logs: data?.data?.items || data?.data || [],
        isLoading,
        error: error ? (error as any).message : null,
        refetch
    };
}