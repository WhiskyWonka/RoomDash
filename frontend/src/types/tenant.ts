export interface Tenant {
  id: string;
  name: string;
  domain: string;
  isActive: boolean;
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

export interface UpdateTenantAdminInput {
  first_name: string;
  last_name: string;
  username: string;
  email: string;
  password?: string;
  password_confirmation?: string;
}

export interface CreateTenantAdminInput {
  first_name: string;
  last_name: string;
  username: string;
  email: string;
  password: string;
  password_confirmation: string;
}
