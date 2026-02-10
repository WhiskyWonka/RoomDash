export interface AdminUser {
  id: string;
  email: string;
  twoFactorEnabled: boolean;
  twoFactorConfirmedAt: string | null;
  createdAt: string;
}

export interface LoginResponse {
  user: AdminUser;
  twoFactorEnabled: boolean;
  requiresTwoFactorSetup: boolean;
}

export interface Verify2faResponse {
  user: AdminUser;
  verified: boolean;
}

export interface TwoFactorSetupResponse {
  secret: string;
  qrCode: string;
}

export interface TwoFactorConfirmResponse {
  recoveryCodes: string[];
  enabled: boolean;
}

export interface TwoFactorStatusResponse {
  enabled: boolean;
  confirmedAt: string | null;
}

export interface MeResponse {
  user: AdminUser;
  twoFactorVerified: boolean;
  twoFactorPending: boolean;
}

export type AuthState =
  | { status: "loading" }
  | { status: "unauthenticated" }
  | { status: "needs_2fa_setup"; user: AdminUser }
  | { status: "needs_2fa_verify"; user: AdminUser }
  | { status: "authenticated"; user: AdminUser };
