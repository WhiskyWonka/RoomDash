import api from "./api";


const AUTH_BASE = "/api/auth";

export const authApi = {
    // CSRF cookie - Axios maneja las cookies automáticamente con withCredentials
    csrf: () => api.get("/sanctum/csrf-cookie"),

    // Fíjate: ya no hay JSON.stringify, solo pasas el objeto
    login: (credentials: { email: string; password: string }) =>
        api.post(`${AUTH_BASE}/login`, credentials),

    verify2FA: (code: string) =>
        api.post(`${AUTH_BASE}/verify-2fa`, { code }),

    logout: () =>
        api.post(`${AUTH_BASE}/logout`),

    me: () => 
        api.get(`${AUTH_BASE}/me`),

    checkVerificationToken: (token: string) =>
        api.get(`${AUTH_BASE}/check-token/${token}`),

    setup2FA: () => 
        api.get(`${AUTH_BASE}/2fa/setup`),

    confirm2FA: (code: string) =>
        api.post(`${AUTH_BASE}/2fa/confirm`, { code }),

    verifyEmail: (data: { 
        token: string; 
        password: string; 
        password_confirmation: string 
    }) =>
        api.post(`${AUTH_BASE}/verify-email`, data),

    resendVerificationEmail: (email: string) =>
        api.post(`${AUTH_BASE}/resend-verification`, { email }),
};