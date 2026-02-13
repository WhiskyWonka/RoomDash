import type { Tenant, CreateTenantInput, UpdateTenantInput } from "@/types/tenant";
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

//const API_BASE_URL = "http://localhost";
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

export const tenantsApi = createResource<Tenant, CreateTenantInput, UpdateTenantInput>("/api/tenants");
export const usersApi = createResource<User, CreateUserInput, UpdateUserInput>("/api/users");
export const featuresApi = createResource<Feature, CreateFeatureInput, UpdateFeatureInput>("/api/features");