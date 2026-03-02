import React, { createContext, useContext, useState, useCallback, useEffect } from 'react';
import { authApi } from "@/lib/authApi";
import { useQuery, useQueryClient } from "@tanstack/react-query";

interface AuthState {
    user: any;
    twoFactorPending: boolean;
}

interface AuthContextType {
    user: any;
    twoFactorPending: boolean;
    loading: boolean;
    isAuthenticated: boolean;
    checkAuth: () => Promise<void>;
    logout: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider = ({ children }: { children: React.ReactNode }) => {
    const queryClient = useQueryClient();
    
    // Mantenemos tu STATE original exactamente igual
    const [state, setState] = useState<AuthState>({ user: null, twoFactorPending: false });

    // 1. Usamos useQuery como "motor" para pedir el /me
    const { data: response, isLoading: loading, refetch } = useQuery({
        queryKey: ["auth-user"],
        queryFn: authApi.me,
        retry: false, // Importante: si no hay sesión, no queremos reintentos infinitos
        staleTime: 1000 * 60 * 5, // 5 minutos de caché para que no parpadee al navegar
    });

    // 2. Sincronizamos la Query con tu State (La clave para no romper tu lógica)
    useEffect(() => {
        if (response?.data) {
            const payload = response.data;
            setState({
                user: payload.user || null,
                twoFactorPending: payload.twoFactorPending || false
            });
        } else {
            // Si la query falla o no hay data, reseteamos el estado
            setState({ user: null, twoFactorPending: false });
        }
    }, [response]);

    // Tu función checkAuth ahora simplemente le pide a React Query que refresque
    const checkAuth = useCallback(async () => {
        await refetch();
    }, [refetch]);

    const logout = async () => {
        try {
            await authApi.logout();
        } catch (error) {
            console.error("LOGOUT_API_ERROR:", error);
        } finally {
            // Limpiamos caché y estado
            queryClient.clear();
            setState({ user: null, twoFactorPending: false });
            window.location.href = "/admin/login";
        }
    };

    // TU LÓGICA DE SIEMPRE (No se toca)
    const isAuthenticated = !!state.user && !state.twoFactorPending;

    return (
        <AuthContext.Provider value={{ ...state, loading, isAuthenticated, checkAuth, logout }}>
            {children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => {
    const context = useContext(AuthContext);
    if (!context) throw new Error("useAuth debe usarse dentro de un AuthProvider");
    return context;
};