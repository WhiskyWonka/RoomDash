import { QueryClient } from '@tanstack/react-query';

export const queryClient = new QueryClient({
    defaultOptions: {
        queries: {
            staleTime: 1000 * 60 * 5, // La data se considera "fresca" por 5 min
            retry: 1, // Si falla, reintenta una vez sola
            refetchOnWindowFocus: false, // Evita recargar cada vez que cambias de pestaña
        },
    },
});
