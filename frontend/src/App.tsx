import { useEffect, useState } from "react";
import type { Tenant } from "@/types/tenant";
import { tenantsApi } from "@/lib/api";
import { TenantTable } from "@/components/TenantTable";
import { TenantDialog } from "@/components/TenantDialog";
import { DeleteTenantDialog } from "@/components/DeleteTenantDialog";
import { TopBar } from "@/components/TopBar";
import { Button } from "@/components/ui/button";
import { useTheme } from "@/hooks/useTheme";
import { useAuth } from "@/hooks/useAuth";
import { LoginForm } from "@/components/auth/LoginForm";
import { TwoFactorSetup } from "@/components/auth/TwoFactorSetup";
import { TwoFactorVerify } from "@/components/auth/TwoFactorVerify";
import { RecoveryCodesDisplay } from "@/components/auth/RecoveryCodesDisplay";

function Dashboard({ onLogout }: { onLogout: () => void }) {
  const { theme, toggle } = useTheme();
  const [tenants, setTenants] = useState<Tenant[]>([]);
  const [dialogOpen, setDialogOpen] = useState(false);
  const [deleteOpen, setDeleteOpen] = useState(false);
  const [editing, setEditing] = useState<Tenant | null>(null);
  const [deleting, setDeleting] = useState<Tenant | null>(null);

  const load = () => {
    tenantsApi.list().then(setTenants);
  };

  useEffect(load, []);

  const handleCreate = () => {
    setEditing(null);
    setDialogOpen(true);
  };

  const handleEdit = (t: Tenant) => {
    setEditing(t);
    setDialogOpen(true);
  };

  const handleDelete = (t: Tenant) => {
    setDeleting(t);
    setDeleteOpen(true);
  };

  const handleSubmit = async (name: string, domain: string) => {
    if (editing) {
      await tenantsApi.update(editing.id, { name, domain });
    } else {
      await tenantsApi.create({ name, domain });
    }
    setDialogOpen(false);
    load();
  };

  const handleConfirmDelete = async () => {
    if (deleting) {
      await tenantsApi.delete(deleting.id);
    }
    setDeleteOpen(false);
    load();
  };

  return (
    <>
      <TopBar theme={theme} onToggleTheme={toggle} onLogout={onLogout} />
      <div className="mx-auto max-w-4xl px-4 pt-22 pb-8">
        <div className="flex items-center justify-between mb-6">
          <h1 className="text-2xl font-bold">Tenants</h1>
          <Button onClick={handleCreate}>Create Tenant</Button>
        </div>
        <TenantTable tenants={tenants} onEdit={handleEdit} onDelete={handleDelete} />
        <TenantDialog
          open={dialogOpen}
          tenant={editing}
          onClose={() => setDialogOpen(false)}
          onSubmit={handleSubmit}
        />
        <DeleteTenantDialog
          open={deleteOpen}
          tenant={deleting}
          onClose={() => setDeleteOpen(false)}
          onConfirm={handleConfirmDelete}
        />
      </div>
    </>
  );
}

function AuthLayout({ children }: { children: React.ReactNode }) {
  const { theme, toggle } = useTheme();

  return (
    <div className="min-h-screen flex flex-col">
      <header className="border-b border-border bg-background">
        <div className="mx-auto max-w-4xl flex items-center justify-between px-4 h-14">
          <span className="text-xl font-bold">RoomDash</span>
          <Button variant="ghost" size="icon" onClick={toggle} aria-label="Toggle theme">
            {theme === "dark" ? (
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
              >
                <circle cx="12" cy="12" r="4" />
                <path d="M12 2v2" />
                <path d="M12 20v2" />
                <path d="m4.93 4.93 1.41 1.41" />
                <path d="m17.66 17.66 1.41 1.41" />
                <path d="M2 12h2" />
                <path d="M20 12h2" />
                <path d="m6.34 17.66-1.41 1.41" />
                <path d="m19.07 4.93-1.41 1.41" />
              </svg>
            ) : (
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
              >
                <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
              </svg>
            )}
          </Button>
        </div>
      </header>
      <main className="flex-1 flex items-center justify-center p-4">{children}</main>
    </div>
  );
}

function App() {
  const {
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
  } = useAuth();
  const [recoveryCodes, setRecoveryCodes] = useState<string[] | null>(null);

  if (state.status === "loading") {
    return (
      <AuthLayout>
        <div className="text-center">
          <p className="text-muted-foreground">Loading...</p>
        </div>
      </AuthLayout>
    );
  }

  if (state.status === "unauthenticated") {
    return (
      <AuthLayout>
        <LoginForm onSubmit={login} error={error} onClearError={clearError} />
      </AuthLayout>
    );
  }

  if (state.status === "needs_2fa_setup") {
    if (recoveryCodes) {
      return (
        <AuthLayout>
          <RecoveryCodesDisplay
            codes={recoveryCodes}
            onComplete={() => {
              setRecoveryCodes(null);
              finishSetup({ ...state.user, twoFactorEnabled: true });
            }}
          />
        </AuthLayout>
      );
    }

    return (
      <AuthLayout>
        <TwoFactorSetup
          onGetSetup={get2faSetup}
          onConfirm={confirm2fa}
          onComplete={setRecoveryCodes}
          error={error}
          onClearError={clearError}
        />
      </AuthLayout>
    );
  }

  if (state.status === "needs_2fa_verify") {
    return (
      <AuthLayout>
        <TwoFactorVerify
          onVerify={verify2fa}
          onVerifyRecovery={verifyRecovery}
          onLogout={logout}
          error={error}
          onClearError={clearError}
        />
      </AuthLayout>
    );
  }

  return <Dashboard onLogout={logout} />;
}

export default App;
