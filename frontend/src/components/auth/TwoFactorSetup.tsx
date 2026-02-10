import { useState, useEffect } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Field, FieldLabel, FieldContent, FieldDescription } from "@/components/ui/field";
import type { TwoFactorSetupResponse } from "@/types/auth";

interface Props {
  onGetSetup: () => Promise<TwoFactorSetupResponse | null>;
  onConfirm: (code: string) => Promise<string[] | null>;
  onComplete: (recoveryCodes: string[]) => void;
  error: string | null;
  onClearError: () => void;
}

export function TwoFactorSetup({ onGetSetup, onConfirm, onComplete, error, onClearError }: Props) {
  const [setup, setSetup] = useState<TwoFactorSetupResponse | null>(null);
  const [code, setCode] = useState("");
  const [loading, setLoading] = useState(false);
  const [showManualEntry, setShowManualEntry] = useState(false);

  useEffect(() => {
    onGetSetup().then(setSetup);
  }, [onGetSetup]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    const recoveryCodes = await onConfirm(code);
    setLoading(false);
    if (recoveryCodes) {
      onComplete(recoveryCodes);
    }
  };

  if (!setup) {
    return (
      <div className="w-full max-w-sm mx-auto text-center">
        <p className="text-muted-foreground">Loading 2FA setup...</p>
      </div>
    );
  }

  return (
    <div className="w-full max-w-sm mx-auto">
      <div className="text-center mb-6">
        <h1 className="text-2xl font-bold">Set up Two-Factor Authentication</h1>
        <p className="text-muted-foreground mt-2">
          Scan the QR code with Google Authenticator or a compatible app
        </p>
      </div>

      <div className="flex justify-center mb-6">
        <div className="p-4 bg-white rounded-lg">
          <img src={setup.qrCode} alt="2FA QR Code" className="w-48 h-48" />
        </div>
      </div>

      <div className="text-center mb-6">
        <button
          type="button"
          className="text-sm text-primary hover:underline"
          onClick={() => setShowManualEntry(!showManualEntry)}
        >
          {showManualEntry ? "Hide manual entry code" : "Can't scan? Enter code manually"}
        </button>
        {showManualEntry && (
          <div className="mt-2 p-3 bg-muted rounded-md font-mono text-sm break-all">
            {setup.secret}
          </div>
        )}
      </div>

      <form onSubmit={handleSubmit} className="space-y-4">
        {error && (
          <div className="p-3 text-sm text-destructive bg-destructive/10 rounded-md">
            {error}
          </div>
        )}

        <Field>
          <FieldContent>
            <FieldLabel htmlFor="code">Verification Code</FieldLabel>
            <Input
              id="code"
              type="text"
              inputMode="numeric"
              pattern="[0-9]*"
              maxLength={6}
              value={code}
              onChange={(e) => {
                setCode(e.target.value.replace(/\D/g, ""));
                onClearError();
              }}
              placeholder="000000"
              required
              autoComplete="one-time-code"
              autoFocus
              className="text-center text-2xl tracking-widest"
            />
            <FieldDescription>Enter the 6-digit code from your authenticator app</FieldDescription>
          </FieldContent>
        </Field>

        <Button type="submit" className="w-full" disabled={loading || code.length !== 6}>
          {loading ? "Verifying..." : "Verify and Enable 2FA"}
        </Button>
      </form>
    </div>
  );
}
