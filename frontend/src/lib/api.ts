import type { Tenant, CreateTenantInput, UpdateTenantInput } from "@/types/tenant";
import type {
  LoginResponse,
  Verify2faResponse,
  TwoFactorSetupResponse,
  TwoFactorConfirmResponse,
  TwoFactorStatusResponse,
  MeResponse,
} from "@/types/auth";

const TENANTS_BASE = "/api/tenants";
const AUTH_BASE = "/api/auth";

export class ApiError extends Error {
  constructor(
    message: string,
    public status: number,
    public code?: string
  ) {
    super(message);
    this.name = "ApiError";
  }
}

async function request<T>(url: string, options?: RequestInit): Promise<T> {
  const res = await fetch(url, {
    headers: { "Content-Type": "application/json", Accept: "application/json" },
    credentials: "include",
    ...options,
  });
  if (!res.ok) {
    const body = await res.json().catch(() => ({}));
    throw new ApiError(body.message ?? `Request failed: ${res.status}`, res.status, body.code);
  }
  if (res.status === 204) return undefined as T;
  return res.json();
}

export const authApi = {
  login: (email: string, password: string) =>
    request<LoginResponse>(`${AUTH_BASE}/login`, {
      method: "POST",
      body: JSON.stringify({ email, password }),
    }),

  verify2fa: (code: string) =>
    request<Verify2faResponse>(`${AUTH_BASE}/verify-2fa`, {
      method: "POST",
      body: JSON.stringify({ code }),
    }),

  verifyRecovery: (code: string) =>
    request<Verify2faResponse>(`${AUTH_BASE}/verify-recovery`, {
      method: "POST",
      body: JSON.stringify({ code }),
    }),

  logout: () =>
    request<{ message: string }>(`${AUTH_BASE}/logout`, { method: "POST" }),

  me: () => request<MeResponse>(`${AUTH_BASE}/me`),

  get2faSetup: () => request<TwoFactorSetupResponse>(`${AUTH_BASE}/2fa/setup`),

  confirm2fa: (code: string) =>
    request<TwoFactorConfirmResponse>(`${AUTH_BASE}/2fa/confirm`, {
      method: "POST",
      body: JSON.stringify({ code }),
    }),

  get2faStatus: () => request<TwoFactorStatusResponse>(`${AUTH_BASE}/2fa/status`),
};

export const tenantsApi = {
  list: () => request<Tenant[]>(TENANTS_BASE),
  get: (id: string) => request<Tenant>(`${TENANTS_BASE}/${id}`),
  create: (data: CreateTenantInput) => request<Tenant>(TENANTS_BASE, { method: "POST", body: JSON.stringify(data) }),
  update: (id: string, data: UpdateTenantInput) => request<Tenant>(`${TENANTS_BASE}/${id}`, { method: "PUT", body: JSON.stringify(data) }),
  delete: (id: string) => request<void>(`${TENANTS_BASE}/${id}`, { method: "DELETE" }),
};
