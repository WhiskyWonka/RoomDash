import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Field, FieldLabel, FieldContent, FieldDescription } from "@/components/ui/field";

interface Props {
  onVerify: (code: string) => Promise<boolean>;
  onVerifyRecovery: (code: string) => Promise<boolean>;
  onLogout: () => void;
  error: string | null;
  onClearError: () => void;
}

export function TwoFactorVerify({ onVerify, onVerifyRecovery, onLogout, error, onClearError }: Props) {
  const [code, setCode] = useState("");
  const [loading, setLoading] = useState(false);
  const [useRecovery, setUseRecovery] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    if (useRecovery) {
      await onVerifyRecovery(code);
    } else {
      await onVerify(code);
    }
    setLoading(false);
  };

  return (
    <div className="w-full max-w-sm mx-auto">
      <div className="text-center mb-6">
        <h1 className="text-2xl font-bold">
          {useRecovery ? "Enter Recovery Code" : "Two-Factor Authentication"}
        </h1>
        <p className="text-muted-foreground mt-2">
          {useRecovery
            ? "Enter one of your recovery codes"
            : "Enter the code from your authenticator app"}
        </p>
      </div>

      <form onSubmit={handleSubmit} className="space-y-4">
        {error && (
          <div className="p-3 text-sm text-destructive bg-destructive/10 rounded-md">
            {error}
          </div>
        )}

        <Field>
          <FieldContent>
            <FieldLabel htmlFor="code">
              {useRecovery ? "Recovery Code" : "Verification Code"}
            </FieldLabel>
            {useRecovery ? (
              <Input
                id="code"
                type="text"
                value={code}
                onChange={(e) => {
                  setCode(e.target.value.toUpperCase());
                  onClearError();
                }}
                placeholder="XXXX-XXXX"
                required
                autoComplete="off"
                autoFocus
                className="text-center text-lg tracking-widest font-mono"
              />
            ) : (
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
            )}
            <FieldDescription>
              {useRecovery
                ? "Each recovery code can only be used once"
                : "Enter the 6-digit code from your authenticator app"}
            </FieldDescription>
          </FieldContent>
        </Field>

        <Button
          type="submit"
          className="w-full"
          disabled={loading || (!useRecovery && code.length !== 6)}
        >
          {loading ? "Verifying..." : "Verify"}
        </Button>

        <div className="text-center space-y-2">
          <button
            type="button"
            className="text-sm text-primary hover:underline"
            onClick={() => {
              setUseRecovery(!useRecovery);
              setCode("");
              onClearError();
            }}
          >
            {useRecovery ? "Use authenticator app instead" : "Use a recovery code"}
          </button>
          <div>
            <button
              type="button"
              className="text-sm text-muted-foreground hover:underline"
              onClick={onLogout}
            >
              Sign in with a different account
            </button>
          </div>
        </div>
      </form>
    </div>
  );
}
