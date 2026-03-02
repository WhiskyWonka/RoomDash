import { Navigate } from "react-router-dom";
import { LoginForm } from "@/apps/superAdmin/features/login/components/login-form";
import { useAuth } from "@/context/AuthContext";
import { useLogin } from "../features/login/hooks/useLogin";

export default function LoginPage() {
    const { isAuthenticated } = useAuth();
    const { 
        show2FA, 
        qrData, 
        error, 
        isLoading, 
        handleLogin, 
        handleVerify2FA, 
        reset2FA 
    } = useLogin();

    if (isAuthenticated) {
        return <Navigate to="/admin/dashboard" replace />;
    }

    return (
        <div className="min-h-screen bg-black flex items-center justify-center p-4 font-mono">
            <LoginForm
                onSubmit={handleLogin}
                show2FA={show2FA}
                qrData={qrData}
                onVerify2FA={handleVerify2FA}
                error={error}
                onCancel2FA={reset2FA}
                isLoading={isLoading} // Le pasamos el loading para deshabilitar botones
            />
        </div>
    );
}