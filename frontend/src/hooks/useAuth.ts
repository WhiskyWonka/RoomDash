import { useState, useCallback, useEffect } from "react";
import type { AuthState, AdminUser } from "@/types/auth";
import { authApi, ApiError } from "@/lib/api";

export function useAuth() {
  const [state, setState] = useState<AuthState>({ status: "loading" });
  const [error, setError] = useState<string | null>(null);

  const checkAuth = useCallback(async () => {
    try {
      const response = await authApi.me();
      const { user, twoFactorVerified, twoFactorPending } = response;

      if (!user.twoFactorEnabled) {
        setState({ status: "needs_2fa_setup", user });
      } else if (twoFactorPending && !twoFactorVerified) {
        setState({ status: "needs_2fa_verify", user });
      } else if (twoFactorVerified) {
        setState({ status: "authenticated", user });
      } else {
        setState({ status: "needs_2fa_verify", user });
      }
    } catch (err) {
      if (err instanceof ApiError && err.status === 401) {
        setState({ status: "unauthenticated" });
      } else {
        console.error("Auth check failed:", err);
        setState({ status: "unauthenticated" });
      }
    }
  }, []);

  useEffect(() => {
    checkAuth();
  }, [checkAuth]);

  const login = useCallback(async (email: string, password: string) => {
    setError(null);
    try {
      const response = await authApi.login(email, password);
      if (response.requiresTwoFactorSetup) {
        setState({ status: "needs_2fa_setup", user: response.user });
      } else {
        setState({ status: "needs_2fa_verify", user: response.user });
      }
      return true;
    } catch (err) {
      setError(err instanceof Error ? err.message : "Login failed");
      return false;
    }
  }, []);

  const verify2fa = useCallback(async (code: string) => {
    setError(null);
    try {
      const response = await authApi.verify2fa(code);
      setState({ status: "authenticated", user: response.user });
      return true;
    } catch (err) {
      setError(err instanceof Error ? err.message : "Verification failed");
      return false;
    }
  }, []);

  const verifyRecovery = useCallback(async (code: string) => {
    setError(null);
    try {
      const response = await authApi.verifyRecovery(code);
      setState({ status: "authenticated", user: response.user });
      return true;
    } catch (err) {
      setError(err instanceof Error ? err.message : "Verification failed");
      return false;
    }
  }, []);

  const logout = useCallback(async () => {
    try {
      await authApi.logout();
    } catch (err) {
      console.error("Logout error:", err);
    }
    setState({ status: "unauthenticated" });
  }, []);

  const get2faSetup = useCallback(async () => {
    try {
      return await authApi.get2faSetup();
    } catch (err) {
      setError(err instanceof Error ? err.message : "Failed to get 2FA setup");
      return null;
    }
  }, []);

  const confirm2fa = useCallback(async (code: string) => {
    setError(null);
    try {
      const response = await authApi.confirm2fa(code);
      return response.recoveryCodes;
    } catch (err) {
      setError(err instanceof Error ? err.message : "Confirmation failed");
      return null;
    }
  }, []);

  const finishSetup = useCallback((user: AdminUser) => {
    setState({ status: "authenticated", user });
  }, []);

  const clearError = useCallback(() => {
    setError(null);
  }, []);

  return {
    state,
    error,
    login,
    verify2fa,
    verifyRecovery,
    logout,
    get2faSetup,
    confirm2fa,
    finishSetup,
    clearError,
  };
}
