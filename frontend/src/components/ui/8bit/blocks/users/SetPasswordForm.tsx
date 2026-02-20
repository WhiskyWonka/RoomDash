import { useState } from "react";
import { authApi } from "@/lib/authApi";
import { Button } from "@/components/ui/8bit/button";
import { Input } from "@/components/ui/8bit/input";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/8bit/card";

interface Props {
    token: string;
    onSuccess: () => void;
    onError: (msg: string) => void;
}

export function SetPasswordForm({ token, onSuccess, onError }: Props) {
    const [loading, setLoading] = useState(false);
    const [form, setForm] = useState({ password: "", password_confirmation: "" });

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        try {
            // El backend recibe todo y valida el token + passwords al mismo tiempo
            await authApi.verifyEmail({ token, ...form });
            onSuccess();
        } catch (err: any) {
            // Si el token falló o los datos son inválidos, el backend devuelve el error aquí
            onError(err.message || "AUTHENTICATION_TOKEN_EXPIRED_OR_INVALID");
        } finally {
            setLoading(false);
        }
    };

    return (
        <Card>
            <CardHeader className="text-center uppercase">
                <CardTitle className="text-2xl text-[#00ff00] mb-3">
                    Setup_Account
                </CardTitle>
                <CardDescription className="text-xs">
                    <p className="text-[10px] text-foreground/40 font-mono">TOKEN_VALIDATION</p>
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="space-y-4">
                        <div>
                            <label className="text-[10px] text-foreground/50 block mb-1">NEW_PASSWORD</label>
                            <Input 
                                type="password"
                                required
                                value={form.password}
                                onChange={e => setForm({...form, password: e.target.value})}
                            />
                        </div>
                        <div>
                            <label className="text-[10px] text-foreground/50 block mb-1">CONFIRM_PASSWORD</label>
                            <Input 
                                type="password"
                                required
                                value={form.password_confirmation}
                                onChange={e => setForm({...form, password_confirmation: e.target.value})}
                            />
                        </div>
                    </div>

                    <Button disabled={loading} className="w-full py-6">
                        {loading ? "VALIDATING_ALL..." : "ACTIVATE"}
                    </Button>
                </form>
            </CardContent>
        </Card>
    );
}