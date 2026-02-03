export interface Tenant {
  id: string;
  name: string;
  domain: string;
  createdAt: string;
}

export interface CreateTenantInput {
  name: string;
  domain: string;
}

export interface UpdateTenantInput {
  name: string;
  domain: string;
}
