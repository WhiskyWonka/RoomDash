import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { authApi } from "@/lib/authApi";
import { LoginForm } from "@/components/ui/8bit/blocks/login-form";

interface LoginPageProps {
    onLoginSuccess: () => Promise<void>;
}

export default function LoginPage({ onLoginSuccess }: LoginPageProps) {
    const [show2FA, setShow2FA] = useState(false);
    const [qrData, setQrData] = useState<{ qrCode: string; secret: string } | null>(null);
    const navigate = useNavigate();

    const handleLogin = async (data: any) => {
        try {
            // CRÍTICO: Obtener cookie CSRF ANTES de hacer login
            await authApi.csrf();
            const response = await authApi.login(data);
            
            console.log("DEBUG_RESPONSE_FULL:", response);

            const result = response.data;

            if (result && result.twoFactorRequired) {
                if (result.requiresSetup) {
                    console.log("FETCHING_2FA_SETUP_DATA...");
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
                console.log("LOGIN_EXITOSO_SIN_2FA");
                await onLoginSuccess(); 
                navigate("/superadmin/dashboard", { replace: true });
            }
        } catch (error: any) {
            console.error("ERROR_EN_LOGIN:", error);
            alert("Error: " + (error.message || "Credenciales incorrectas"));
        }
    };

    const handleVerify2FA = async (code: string) => {
        try {
            if (qrData) {
                // Si hay qrData, estamos en flujo de CONFIRMACIÓN (setup)
                await authApi.confirm2FA(code);
            } else {
                // Si no hay qrData, es VERIFICACIÓN normal
                await authApi.verify2FA(code);
            }
            
            await onLoginSuccess();
            navigate("/superadmin/dashboard", { replace: true });
            
            /*setTimeout(() => {
                navigate("/superadmin/dashboard", { replace: true });
            }, 200);*/
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
                onCancel2FA={() => {
                    setShow2FA(false);
                    setQrData(null);
                }}
            />
        </div>
    );
}