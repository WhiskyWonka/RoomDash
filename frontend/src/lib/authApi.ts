import { request } from "./api";

const AUTH_BASE = "/api";

export const authApi = {
    // El "Handshake": Laravel nos da la cookie CSRF
    // Sin esto, el POST de login fallará con error 419 (CSRF mismatch)
    csrf: () => request<void>("/sanctum/csrf-cookie"),

    login: (credentials: { email: string; password: string }) =>
        request<any>(`${AUTH_BASE}/login`, {
            method: "POST",
            body: JSON.stringify(credentials),
        }),

    logout: () =>
        request<void>(`${AUTH_BASE}/logout`, { method: "POST" }),

    // Obtener el usuario actual (para saber si estamos logueados)
    me: () => request<any>(`${AUTH_BASE}/user`),
};