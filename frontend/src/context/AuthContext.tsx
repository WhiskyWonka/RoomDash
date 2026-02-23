import React, { createContext, useContext, useState, useCallback } from 'react';
import { authApi } from "@/lib/authApi";

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
    const [state, setState] = useState<AuthState>({ user: null, twoFactorPending: false });
    const [loading, setLoading] = useState(true);

    const checkAuth = useCallback(async () => {
        setLoading(true);
        try {
            const data = await authApi.me();
            setState({
                user: data.user,
                twoFactorPending: data.twoFactorPending || false
            });
        } catch (error) {
            setState({ user: null, twoFactorPending: false });
        } finally {
            setLoading(false);
        }
    }, []);

    const logout = async () => {
        try {
            await authApi.logout();
        } finally {
            setState({ user: null, twoFactorPending: false });
            window.location.href = "/admin/login";
        }
    };

    const isAuthenticated = !!state.user && !state.twoFactorPending;

    return (
        <AuthContext.Provider value={{ ...state, loading, isAuthenticated, checkAuth, logout }}>
            {children}
        </AuthContext.Provider>
    );
};

// Hook personalizado para usar el auth en cualquier parte
export const useAuth = () => {
    const context = useContext(AuthContext);
    if (!context) throw new Error("useAuth debe usarse dentro de un AuthProvider");
    return context;
};