import { Button } from "@/components/ui/8bit/button";

export function VerifyError({ message, onRetry }: { message: string, onRetry: () => void }) {
    const handleResend = async () => {
        // Aquí llamarías a authApi.resendVerification()
        alert("REQUESTING_NEW_LINK...");
    };

    return (
        <div className="text-center space-y-6">
            <div className="text-red-500 font-bold text-xl">!! SYSTEM_ERROR !!</div>
            <p className="text-xs text-red-400 bg-red-500/10 p-2 border border-red-500/50">
                {message.toUpperCase()}
            </p>
            <div className="flex flex-col gap-3">
                <Button variant="warning" onClick={handleResend} className="w-full">
                    RESEND_EMAIL_LINK
                </Button>
                <button onClick={onRetry} className="text-[10px] text-foreground/30 hover:text-foreground">
                    TRY_AGAIN
                </button>
            </div>
        </div>
    );
}