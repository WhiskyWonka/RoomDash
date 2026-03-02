import { usersApi } from "../../../services/usersApi";
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useState, useEffect } from 'react';

export function useUsers() {
    const queryClient = useQueryClient();
    const [localError, setLocalError] = useState<string | null>(null);

    const { data: response, isLoading: loading, error: queryError } = useQuery({
        queryKey: ['users'],
        queryFn: usersApi.list
    });

    useEffect(() => {
        if (queryError) setLocalError((queryError as any).message);
    }, [queryError]);

    const { mutateAsync: createMutation } = useMutation({
        mutationFn: (data: any) => usersApi.create(data),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['users'] })
    });

    const { mutateAsync: updateMutation } = useMutation({
        mutationFn: ({ id, data }: { id: string | number, data: any }) => usersApi.update(id, data),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['users'] })
    });

    const { mutateAsync: deleteMutation } = useMutation({
        mutationFn: (id: string | number) => usersApi.delete(id),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['users'] })
    });

    return {
        users: response?.data?.items || response?.data || [],
        loading,
        error: localError,
        setError: setLocalError,
        createUser: createMutation,
        updateUser: (id: string | number, data: any) => updateMutation({ id, data }),
        deleteUser: deleteMutation,
    };
}