import { tenantsApi } from "../../../services/tenantsApi";
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useState, useEffect } from 'react';

export function useTenants() {
    const queryClient = useQueryClient();
    
    // 1. Necesitamos este estado local para poder "limpiar" el error desde la UI
    const [localError, setLocalError] = useState<string | null>(null);

    const { 
        data: response, 
        isLoading: loading, 
        error: queryError 
    } = useQuery({
        queryKey: ['tenants'],
        queryFn: tenantsApi.list
    });

    // 2. Sincronizamos el error de la query con el estado local
    useEffect(() => {
        if (queryError) {
            setLocalError((queryError as any).message);
        }
    }, [queryError]);

    // MUTACIONES (Se mantienen igual)
    const { mutateAsync: createMutation } = useMutation({
        mutationFn: (data: any) => tenantsApi.create(data),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['tenants'] })
    });

    const { mutateAsync: updateMutation } = useMutation({
        mutationFn: ({ id, data }: { id: string | number, data: any }) => tenantsApi.update(id, data),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['tenants'] })
    });

    const { mutateAsync: deleteMutation } = useMutation({
        mutationFn: (id: string | number) => tenantsApi.delete(id),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['tenants'] })
    });

    return {
        tenants: response?.data?.items || response?.data || [],
        loading,
        // 3. Devolvemos el error local y el setter para que el componente padre no de error
        error: localError,
        setError: setLocalError, 
        
        createTenant: createMutation,
        updateTenant: (id: string | number, data: any) => updateMutation({ id, data }),
        deleteTenant: deleteMutation,
        
        refresh: () => queryClient.invalidateQueries({ queryKey: ['tenants'] })
    };
}