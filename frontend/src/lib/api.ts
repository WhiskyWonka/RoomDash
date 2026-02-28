import axios from 'axios';
import type { Tenant, CreateTenantInput, UpdateTenantInput, CreateTenantAdminInput, UpdateTenantAdminInput } from "@/types/tenant";
import type { User, CreateUserInput, UpdateUserInput } from "@/types/user";
import type { Feature, CreateFeatureInput, UpdateFeatureInput } from "@/types/feature";

// Configuramos la instancia principal
const api = axios.create({
    baseURL: "", // Todo va al mismo host
    withCredentials: true,
    headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
    },
    // Axios busca automáticamente la cookie XSRF-TOKEN y la manda en este header:
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN',
});

// Interceptor para centralizar el manejo de mensajes del Backend
api.interceptors.response.use(
    (response) => {
        // En lugar de devolver todo el objeto de Axios, devolvemos directamente el JSON que mandó Laravel.
        // Ejemplo: { success: true, message: "...", data: {...} }
        return response.data; 
    },
    (error) => {
        const status = error.response?.status;
        const body = error.response?.data;

        // Manejo específico de validaciones (422)
        if (status === 422 && body.errors) {
            console.group("❌ VALIDATION ERROR DETAILS");
            console.table(body.errors);
            console.groupEnd();
        }

        // Si es 401 (No autorizado), Laravel/Sanctum suele manejarlo
        if (status === 401) {
            // Aquí podrías disparar un evento global de logout si quisieras
        }

        // Devolvemos el mensaje exacto del backend (para tus traducciones futuras)
        const customError = {
            ...error,
            message: body?.message ?? `Error ${status}: Server Error`,
            status: status
        };

        return Promise.reject(customError);
    }
);

export default api;

export function createResource<T, CreateInput = any, UpdateInput = any>(basePath: string) {
    return {
        list: () => api.get<T[]>(basePath),
        get: (id: string | number) => api.get<T>(`${basePath}/${id}`),
        create: (data: CreateInput) => api.post<T>(basePath, data),
        update: (id: string | number, data: UpdateInput) => api.put<T>(`${basePath}/${id}`, data),
        delete: (id: string | number) => api.delete<void>(`${basePath}/${id}`),
    };
}