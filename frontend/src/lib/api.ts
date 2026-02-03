import type { Tenant, CreateTenantInput, UpdateTenantInput } from "@/types/tenant";

const BASE = "/api/tenants";

async function request<T>(url: string, options?: RequestInit): Promise<T> {
  const res = await fetch(url, {
    headers: { "Content-Type": "application/json", Accept: "application/json" },
    ...options,
  });
  if (!res.ok) {
    const body = await res.json().catch(() => ({}));
    throw new Error(body.message ?? `Request failed: ${res.status}`);
  }
  if (res.status === 204) return undefined as T;
  return res.json();
}

export const tenantsApi = {
  list: () => request<Tenant[]>(BASE),
  get: (id: string) => request<Tenant>(`${BASE}/${id}`),
  create: (data: CreateTenantInput) => request<Tenant>(BASE, { method: "POST", body: JSON.stringify(data) }),
  update: (id: string, data: UpdateTenantInput) => request<Tenant>(`${BASE}/${id}`, { method: "PUT", body: JSON.stringify(data) }),
  delete: (id: string) => request<void>(`${BASE}/${id}`, { method: "DELETE" }),
};
