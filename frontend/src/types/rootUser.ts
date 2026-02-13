export interface RootUser {
  id: string;
  name: string;
  email: string;
  createdAt: string;
}

export interface CreateRootUserInput {
  name: string;
  email: string;
}

export interface UpdateRootUserInput {
  name: string;
  email: string;
}
