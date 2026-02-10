import type { Tenant, CreateTenantInput, UpdateTenantInput } from "@/types/tenant";
import type { User, CreateUserInput, UpdateUserInput } from "@/types/user";

export async function request<T>(url: string, options?: RequestInit): Promise<T> {
    const res = await fetch(url, {
        headers: {
            "Content-Type": "application/json",
            Accept: "application/json"
        },
        credentials: "include",
        ...options,
    });

    if (!res.ok) {
        const body = await res.json().catch(() => ({}));
        throw new Error(body.message ?? `Error ${res.status}: Request failed`);
    }

    if (res.status === 204) return undefined as T;
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