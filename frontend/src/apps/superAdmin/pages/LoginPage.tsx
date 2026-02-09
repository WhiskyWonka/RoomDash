// frontend/src/apps/superAdmin/pages/LoginPage.tsx
import { useState } from "react";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/8bit/card";
import { Button } from "@/components/ui/8bit/button";
import { Input } from "@/components/ui/8bit/input";
import { authApi } from "@/lib/authApi";

export default function LoginPage() {
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");

    const handleLogin = async (e: React.FormEvent) => {
        e.preventDefault();
        try {
            // PASO 1: Pedir permiso de CSRF
            await authApi.csrf();

            // PASO 2: Intentar el login
            await authApi.login({ email, password });

            // PASO 3: Si todo sale bien, redirigir al Dashboard
            console.log("ACCESS_GRANTED");
            window.location.href = "/dashboard";
        } catch (error) {
            console.error("ACCESS_DENIED", error);
            alert("CREDENTIALS_INVALID_OR_SERVER_UNREACHABLE");
        }
    };

    return (
        <div className="min-h-screen bg-black flex items-center justify-center p-4">
            <Card className="w-full max-w-md border-double border-4 border-[#00ff00]">
                <CardHeader className="text-center">
                    <CardTitle font="retro" className="text-2xl text-[#00ff00]">
                        {"[ "}SECURE_ACCESS_GATE{" ]"}
                    </CardTitle>
                    <p className="text-[10px] text-zinc-500 uppercase">Super_Admin_Authentication_v1.0</p>
                </CardHeader>
                <CardContent>
                    <form onSubmit={handleLogin} className="space-y-6">
                        <div className="space-y-2">
                            <label className="text-[10px] text-[#00ff00] uppercase font-mono">User_Email:</label>
                            <Input
                                type="email"
                                className="retro bg-black border-[#004400] text-[#00ff00]"
                                value={email}
                                onChange={(e) => setEmail(e.target.value)}
                                placeholder="admin@system.sys"
                            />
                        </div>
                        <div className="space-y-2">
                            <label className="text-[10px] text-[#00ff00] uppercase font-mono">Access_Key:</label>
                            <Input
                                type="password"
                                className="retro bg-black border-[#004400] text-[#00ff00]"
                                value={password}
                                onChange={(e) => setPassword(e.target.value)}
                                placeholder="********"
                            />
                        </div>
                        <Button type="submit" className="w-full retro bg-[#00ff00] text-black font-bold hover:bg-black hover:text-[#00ff00] transition-all">
                            {">"} AUTHORIZE_ENTRY
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}