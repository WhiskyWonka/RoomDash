import { useState } from "react";
import { Button } from "@/components/ui/8bit/button";
import { authApi } from "@/lib/authApi"; // Importamos tu objeto de API

interface Props {
    message: string;
    email?: string; // Es ideal pasar el email si lo tenemos de la URL o estado previo
    onRetry: () => void;
}

export function VerifyError({ message, email, onRetry }: Props) {
    const [status, setStatus] = useState<'idle' | 'loading' | 'success' | 'error'>('idle');

    const handleResend = async () => {
        if (!email) {
            alert("REQUIRED_DATA_MISSING: EMAIL_NOT_FOUND");
            return;
        }

        setStatus('loading');
        try {
            // USANDO TU authApi:
            await authApi.resendVerificationEmail(email);
            setStatus('success');
        } catch (err) {
            console.error("RESEND_ERROR", err);
            setStatus('error');
        }
    };

    return (
        <div className="text-center space-y-6">
            <div className="text-red-500 font-bold text-xl uppercase drop-shadow-[2px_2px_0px_rgba(0,0,0,1)]">
                !! TOKEN_FAILURE !!
            </div>
            
            <div className="text-[10px] text-red-400 bg-red-950/20 p-4 border-2 border-dashed border-red-500/50 font-mono">
                {message.toUpperCase()}
            </div>

            <div className="flex flex-col gap-3">
                {status !== 'success' ? (
                    <>
                        <Button 
                            variant="warning" 
                            onClick={handleResend} 
                            disabled={status === 'loading' || !email}
                            className="w-full"
                        >
                            {status === 'loading' ? "DISPATCHING..." : "RESEND_ACTIVATION_LINK"}
                        </Button>
                        {status === 'error' && (
                            <p className="text-[9px] text-red-500 animate-pulse">FAILED_TO_SEND_RETRY_LATER</p>
                        )}
                    </>
                ) : (
                    <div className="text-[#00ff00] text-[10px] border border-[#00ff00]/50 p-3 bg-[#00ff00]/5">
                        [SUCCESS]: NEW_LINK_SENT_TO_INBOX
                    </div>
                )}
                
                <button 
                    onClick={onRetry} 
                    className="text-[10px] text-foreground/40 hover:text-foreground mt-2"
                >
                    [ RETURN_TO_LOGIN ]
                </button>
            </div>
        </div>
    );
}