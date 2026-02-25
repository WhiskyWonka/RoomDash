import type { Tenant, CreateTenantInput, UpdateTenantInput, CreateTenantAdminInput, UpdateTenantAdminInput } from "@/types/tenant";
import type { User, CreateUserInput, UpdateUserInput } from "@/types/user";
import type { Feature, CreateFeatureInput, UpdateFeatureInput } from "@/types/feature";

function getCsrfToken(): string | undefined {
    const name = "XSRF-TOKEN=";
    const decodedCookie = decodeURIComponent(document.cookie);
    const ca = decodedCookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i].trim();
        if (c.indexOf(name) === 0) {
            return c.substring(name.length, c.length);
        }
    }
    return undefined;
}

const API_BASE_URL = ""; // ← VACÍO porque todo va por el mismo puerto (8000)

export async function request<T>(url: string, options?: RequestInit): Promise<T> {
    const fullUrl = url.startsWith('http') ? url : `${API_BASE_URL}${url}`;
    const csrfToken = getCsrfToken();
    
    const customHeaders: Record<string, string> = {
        "Content-Type": "application/json",
        "Accept": "application/json",
    };

    if (csrfToken) {
        customHeaders["X-XSRF-TOKEN"] = csrfToken;
    }

    const res = await fetch(fullUrl, {
        ...options,
        headers: {
            ...customHeaders,
            ...options?.headers
        },
        credentials: "include",
    });

    // Si es 401, lanzamos error pero NO redirigimos automáticamente
    // Dejamos que cada componente decida qué hacer
    if (!res.ok) {
        const body = await res.json().catch(() => null);
        
        if (res.status === 401) {
            throw new Error("UNAUTHORIZED");
        }

        if (res.status === 422 && body.errors) {
            console.group("❌ VALIDATION ERROR DETAILS");
            console.table(body.errors);
            console.groupEnd();
        }
        
        throw new Error(body?.message ?? `Error ${res.status}: Server Error`);
    }

    if (res.status === 204) {
        return undefined as T;
    }
    
    return res.json();
}

function createResource<T, CreateInput = any, UpdateInput = any>(basePath: string) {
    return {
        list: () => request<T[]>(basePath),
        get: (id: string | number) => request<T>(`${basePath}/${id}`),
        create: (data: CreateInput) =>
            request<T>(basePath, {
                method: "POST",
                body: JSON.stringify(data)
            }),
        update: (id: string | number, data: UpdateInput) =>
            request<T>(`${basePath}/${id}`, {
                method: "PUT",
                body: JSON.stringify(data)
            }),
        delete: (id: string | number) =>
            request<void>(`${basePath}/${id}`, {
                method: "DELETE"
            }),
    };
}

const baseTenants = createResource<Tenant, CreateTenantInput, UpdateTenantInput>("/api/tenants");

export const tenantsApi = {
    ...baseTenants,

    activate: (id: string | number) =>
        request<void>(`/api/tenants/${id}/activate`, { method: "PATCH" }),

    deactivate: (id: string | number) =>
        request<void>(`/api/tenants/${id}/deactivate`, { method: "PATCH" }),

    createAdmin: (tenantId: string | number, data: CreateTenantAdminInput) =>
        request<void>(`/api/tenants/${tenantId}/create-admin`, {
            method: "POST",
            body: JSON.stringify(data),
        }),

    getAdmin: (tenantId: string | number) =>
        request<{ data: User }>(`/api/tenants/${tenantId}/admin`),

    updateAdmin: (tenantId: string | number, data: UpdateTenantAdminInput) =>
        request<void>(`/api/tenants/${tenantId}/admin`, {
            method: "PUT",
            body: JSON.stringify(data),
        }),

    deleteAdmin: (tenantId: string | number) =>
        request<void>(`/api/tenants/${tenantId}/admin`, { method: "DELETE" }),

    resendAdminVerification: (tenantId: string | number) =>
        request<void>(`/api/tenants/${tenantId}/admin/resend-verification`, { method: "POST" }),
};
export const featuresApi = createResource<Feature, CreateFeatureInput, UpdateFeatureInput>("/api/features");

const baseUsers = createResource<User, CreateUserInput, UpdateUserInput>("/api/users");

export const usersApi = {
    ...baseUsers, // Esto trae list, get, create, update, delete

    activate: (id: string | number) =>
        request<User>(`/api/users/${id}/activate`, { method: "PATCH" }),

    deactivate: (id: string | number) =>
        request<User>(`/api/users/${id}/deactivate`, { method: "PATCH" }),

    resendVerification: (id: string | number) =>
        request<void>(`/api/users/${id}/resend-verification`, { method: "POST" }),
};