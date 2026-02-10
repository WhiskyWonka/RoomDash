import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Field, FieldLabel, FieldContent } from "@/components/ui/field";

interface Props {
  onSubmit: (email: string, password: string) => Promise<boolean>;
  error: string | null;
  onClearError: () => void;
}

export function LoginForm({ onSubmit, error, onClearError }: Props) {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    await onSubmit(email, password);
    setLoading(false);
  };

  return (
    <div className="w-full max-w-sm mx-auto">
      <div className="text-center mb-8">
        <h1 className="text-2xl font-bold">Sign in to RoomDash</h1>
        <p className="text-muted-foreground mt-2">Enter your credentials to continue</p>
      </div>

      <form onSubmit={handleSubmit} className="space-y-4">
        {error && (
          <div className="p-3 text-sm text-destructive bg-destructive/10 rounded-md">
            {error}
          </div>
        )}

        <Field>
          <FieldContent>
            <FieldLabel htmlFor="email">Email</FieldLabel>
            <Input
              id="email"
              type="email"
              value={email}
              onChange={(e) => {
                setEmail(e.target.value);
                onClearError();
              }}
              placeholder="admin@example.com"
              required
              autoComplete="email"
              autoFocus
            />
          </FieldContent>
        </Field>

        <Field>
          <FieldContent>
            <FieldLabel htmlFor="password">Password</FieldLabel>
            <Input
              id="password"
              type="password"
              value={password}
              onChange={(e) => {
                setPassword(e.target.value);
                onClearError();
              }}
              placeholder="Enter your password"
              required
              autoComplete="current-password"
            />
          </FieldContent>
        </Field>

        <Button type="submit" className="w-full" disabled={loading}>
          {loading ? "Signing in..." : "Sign in"}
        </Button>
      </form>
    </div>
  );
}
