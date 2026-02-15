import { cn } from "@/lib/utils";

import { Button } from "@/components/ui/8bit/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/8bit/card";
import { Input } from "@/components/ui/8bit/input";
import { Label } from "@/components/ui/8bit/label";

interface LoginFormProps extends React.ComponentPropsWithoutRef<"div"> {
    onSubmit: (credentials: any) => void;
    show2FA: boolean;
    qrData?: { qr_code_url: string; secret: string } | null;
    onVerify2FA: (code: string) => void;
    onCancel2FA: () => void;
}

export function LoginForm({ 
        className, 
        onSubmit, 
        show2FA, 
        qrData,
        onVerify2FA, 
        onCancel2FA, 
        ...props 
    }: LoginFormProps) {

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault(); 
        const formData = new FormData(e.currentTarget);
        const data = Object.fromEntries(formData);
        onSubmit(data); // Envía {email: '...', password: '...'} a LoginPage
    };

    const handleCodeChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const val = e.target.value.replace(/\D/g, ""); // Solo números
        if (val.length <= 6) {
            // Podrías usar un estado local si quieres mostrar los guiones bajos _ _ _ _ _ _
            if (val.length === 6) {
                onVerify2FA(val);
            }
        }
    };
    
    return (
        <div className={cn("flex flex-col gap-6", className)} {...props}>
            <Card>
                <CardHeader className="text-center">
                    <CardTitle className="text-2xl text-[#00ff00] mb-3">
                        {show2FA ? "[ SECURITY_CHECK ]" : "[ RoomDash ]"}
                    </CardTitle>
                    <CardDescription className="text-xs">
                        <p className="text-[10px] text-zinc-500 uppercase">
                            {qrData ? "SETUP_REQUIRED_v1.0" : "Super_Admin_Authentication_v1.0"}
                        </p>
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    {!show2FA ? (
                        /* LOGIN */
                        <form onSubmit={handleSubmit}>
                            <div className="flex flex-col gap-6">
                                <div className="grid gap-2">
                                    <Label className="text-xs" htmlFor="email">Email</Label>
                                    <Input id="email" type="email" name="email" className="text-[#00ff00]" required />
                                </div>
                                <div className="grid gap-2">
                                    <Label className="text-xs" htmlFor="password">Password</Label>
                                    <Input id="password" name="password" className="text-[#00ff00]" type="password" required />
                                </div>
                                <Button type="submit" className="w-full">LOGIN</Button>
                            </div>
                        </form>
                    ) : (
                        /* 2FA FLOW */
                        <div className="flex flex-col gap-6">
                            {qrData ? (
                                /* VISTA DE SETUP (PRIMERA VEZ) */
                                <div className="space-y-4">
                                    <div className="flex flex-col items-center gap-4 border-2 border-dashed border-[#00ff00]/30 p-4 bg-zinc-950/50">
                                        <p className="text-[9px] text-center text-[#00ff00] animate-pulse">
                                            [ NEW_SECURITY_LAYER_DETECTED ]
                                        </p>
                                        <div className="bg-white p-2 border-4 border-zinc-800">
                                            <img src={qrData.qr_code_url} alt="2FA QR" className="w-32 h-32" />
                                        </div>
                                        <div className="text-center">
                                            <p className="text-[8px] text-zinc-500 mb-1">MANUAL_KEY</p>
                                            <code className="text-[10px] text-green-500 font-mono break-all bg-black px-2 py-1 border border-zinc-800">
                                                {qrData.secret}
                                            </code>
                                        </div>
                                    </div>
                                    <p className="text-[10px] text-zinc-400 text-center italic">
                                        Scan the QR and enter the generated code to link your account.
                                    </p>
                                </div>
                            ) : (
                                /* VISTA DE VERIFICACIÓN (USUARIO RECURRENTE) */
                                <div className="text-center py-4">
                                    <p className="text-[10px] text-zinc-500 mb-2">IDENT_VERIFICATION_REQUIRED</p>
                                    <div className="inline-block p-2 border-2 border-[#00ff00] bg-[#00ff00]/5">
                                        <span className="text-xs text-[#00ff00]">MFA_ACTIVE</span>
                                    </div>
                                </div>
                            )}

                            <div className="grid gap-2 text-center">
                                <Label className="text-[10px] uppercase text-zinc-500">
                                    {qrData ? "CONFIRM_SETUP_CODE" : "ENTER_6_DIGIT_CODE"}
                                </Label>
                                <Input 
                                    name="2fa_code" 
                                    placeholder="000000" 
                                    className="text-center text-2xl tracking-[0.5em] text-[#00ff00] bg-black border-2 border-zinc-800 focus:border-[#00ff00]"
                                    maxLength={6}
                                    autoFocus
                                    onChange={handleCodeChange}
                                />
                            </div>
                            
                            <Button variant="outline" className="w-full" onClick={onCancel2FA}>
                                CANCEL_AND_BACK
                            </Button>
                        </div>
                    )}
                </CardContent>
            </Card>
        </div>
    );
}
