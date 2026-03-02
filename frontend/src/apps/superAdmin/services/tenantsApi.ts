import api, { createResource } from "@/lib/api";
import type { 
    Tenant, CreateTenantInput, UpdateTenantInput, 
    CreateTenantAdminInput, UpdateTenantAdminInput 
} from "@/types/tenant";
import type { User } from "@/types/user";

const baseTenants = createResource<Tenant, CreateTenantInput, UpdateTenantInput>("/api/tenants");

export const tenantsApi = {
    ...baseTenants,

    activate: (id: string | number) =>
        api.patch(`/api/tenants/${id}/activate`),

    deactivate: (id: string | number) =>
        api.patch(`/api/tenants/${id}/deactivate`),

    createAdmin: (tenantId: string | number, data: CreateTenantAdminInput) =>
        api.post(`/api/tenants/${tenantId}/create-admin`, data),

    getAdmin: (tenantId: string | number) =>
        api.get<{ data: User }>(`/api/tenants/${tenantId}/admin`),

    updateAdmin: (tenantId: string | number, data: UpdateTenantAdminInput) =>
        api.put(`/api/tenants/${tenantId}/admin`, data),

    deleteAdmin: (tenantId: string | number) =>
        api.delete(`/api/tenants/${tenantId}/admin`),

    resendAdminVerification: (tenantId: string | number) =>
        api.post(`/api/tenants/${tenantId}/admin/resend-verification`),
};