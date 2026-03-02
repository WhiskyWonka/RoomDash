import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { authApi } from "@/lib/authApi";
import { useAuth } from "@/context/AuthContext";

export function useLogin() {
    const { checkAuth } = useAuth();
    const navigate = useNavigate();
    
    const [show2FA, setShow2FA] = useState(false);
    const [qrData, setQrData] = useState<{ qrCode: string; secret: string } | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [isLoading, setIsLoading] = useState(false);

    const handleLogin = async (data: any) => {
        setError(null);
        setIsLoading(true);
        try {
            await authApi.csrf();
            const response = await authApi.login(data);
            const result = response.data;

            if (result?.twoFactorRequired) {
                if (result.requiresSetup) {
                    const setupRes = await authApi.setup2FA();
                    setQrData(setupRes.data);
                } else {
                    setQrData(null);
                }
                setShow2FA(true);
            } else {
                await checkAuth();
                navigate("/admin/dashboard", { replace: true });
            }
        } catch (err: any) {
            setError(err.message || "LOGIN_FAILED: INVALID_CREDENTIALS");
        } finally {
            setIsLoading(false);
        }
    };

    const handleVerify2FA = async (code: string) => {
        setError(null);
        setIsLoading(true);
        try {
            // Decidimos qué método usar según si hay qrData (es confirmación inicial) o no (es login diario)
            if (qrData) {
                await authApi.confirm2FA(code);
            } else {
                await authApi.verify2FA(code);
            }
            
            await checkAuth(); 
            navigate("/admin/dashboard", { replace: true });
        } catch (err: any) {
            setError(err.message || "2FA_ERROR: INVALID_CODE");
        } finally {
            setIsLoading(false);
        }
    };

    const reset2FA = () => {
        setShow2FA(false);
        setQrData(null);
        setError(null);
    };

    return {
        show2FA,
        qrData,
        error,
        isLoading,
        handleLogin,
        handleVerify2FA,
        reset2FA
    };
}