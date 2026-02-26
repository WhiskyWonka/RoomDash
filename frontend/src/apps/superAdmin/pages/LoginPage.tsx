import { useState } from "react";
import { Navigate, useNavigate } from "react-router-dom";
import { authApi } from "@/lib/authApi";
import { LoginForm } from "@/components/ui/8bit/blocks/login-form";
import { useAuth } from "@/context/AuthContext";


export default function LoginPage() {
    const { checkAuth, isAuthenticated } = useAuth();
    const [show2FA, setShow2FA] = useState(false);
    const [qrData, setQrData] = useState<{ qrCode: string; secret: string } | null>(null);
    const [error, setError] = useState<string | null>(null);
    const navigate = useNavigate();

    if (isAuthenticated) {
        return <Navigate to="/admin/dashboard" replace />;
    }

    const handleLogin = async (data: any) => {
        setError(null);
        try {
            // CRÍTICO: Obtener cookie CSRF ANTES de hacer login
            await authApi.csrf();
            const response = await authApi.login(data);

            const result = response.data;

            if (result && result.twoFactorRequired) {
                if (result.requiresSetup) {
                    const setupResponse = await authApi.setup2FA();
                    const setupData = setupResponse.data;

                    setQrData({
                        qrCode: setupData.qrCode,
                        secret: setupData.secret
                    });
                } else {
                    setQrData(null);
                }

                setShow2FA(true);

            } else {
                await checkAuth();
                navigate("/admin/dashboard", { replace: true });
            }
        } catch (err: any) {
            setError(err.message || "Credenciales incorrectas");
        }
    };

    const handleVerify2FA = async (code: string) => {
        setError(null);
        try {
            if (qrData) {
                await authApi.confirm2FA(code);
            } else {
                await authApi.verify2FA(code);
            }

            console.log("2FA_VERIFIED_SUCCESSFULLY_WAITING_FOR_CONTEXT");
            
            await onLoginSuccess();
            
        } catch (error: any) {
            console.error("ERROR_2FA:", error);
            alert("Código incorrecto");
        }
    };

    return (
        <div className="min-h-screen bg-black flex items-center justify-center p-4 font-mono">
            <LoginForm
                onSubmit={handleLogin}
                show2FA={show2FA}
                qrData={qrData}
                onVerify2FA={handleVerify2FA}
                error={error}
                onCancel2FA={() => {
                    setShow2FA(false);
                    setQrData(null);
                    setError(null);
                }}
            />
        </div>
    );
}