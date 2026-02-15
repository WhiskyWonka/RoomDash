import { request } from "./api";

//const AUTH_BASE = "/api";
const AUTH_BASE = "/api/auth";

export const authApi = {
    // CSRF cookie
    csrf: () => request<void>("/sanctum/csrf-cookie"),

    login: (credentials: { email: string; password: string }) =>
        request<any>(`${AUTH_BASE}/login`, {
            method: "POST",
            body: JSON.stringify(credentials),
        }),

    verify2FA: (code: string) =>
        request<any>(`${AUTH_BASE}/verify-2fa`, {
            method: "POST",
            body: JSON.stringify({ code }),
        }),

    logout: () =>
        request<void>(`${AUTH_BASE}/logout`, { method: "POST" }),

    me: () => 
        request<any>(`${AUTH_BASE}/me`),

    // Solo para validar si el link es usable al cargar la página
    checkVerificationToken: (token: string) =>
        request<any>(`${AUTH_BASE}/check-token/${token}`, {
            method: "GET",
        }),

    confirm2FA: (code: string) =>
        request<any>(`${AUTH_BASE}/2fa/confirm`, {
            method: "POST",
            body: JSON.stringify({ code }),
        }),

    // La acción definitiva que marca el email como verificado y setea pass
    verifyEmail: (data: { 
        token: string; 
        password: string; 
        password_confirmation: string 
    }) =>
        request<any>(`${AUTH_BASE}/verify-email`, {
            method: "POST",
            body: JSON.stringify(data),
        }),

    // Por si el token expiró y el usuario necesita otro
    resendVerificationEmail: (email: string) =>
        request<any>(`${AUTH_BASE}/resend-verification`, {
            method: "POST",
            body: JSON.stringify({ email }),
        }),
};