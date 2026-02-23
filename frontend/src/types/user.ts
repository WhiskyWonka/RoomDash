export interface User {
  id: string;
  firstName: string;
  lastName: string;
  email: string;
  avatarPath: string;
  username: string;
  isActive: boolean;
  twoFactorEnabled: boolean;
  createdAt: string;
}

export interface CreateUserInput {
  first_name: string;
  last_name: string;
  username: string;
  email: string;
  password: string;
  password_confirmation: string;
  //avatarPath: string;
}

export interface UpdateUserInput {
  first_name: string;
  last_name: string;
  email: string;
  username: string;
  password: string;
  password_confirmation: string;
  //avatarPath: string;
  //twoFactorEnabled: boolean;
}
