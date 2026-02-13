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

    me: () => request<any>(`${AUTH_BASE}/me`)
};