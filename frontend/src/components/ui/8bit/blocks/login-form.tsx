import { useEffect, useState } from "react";

import { cn } from "@/lib/utils";

import { Alert, AlertDescription } from "@/components/ui/8bit/alert";
import { Button } from "@/components/ui/8bit/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/8bit/card";
import { Input } from "@/components/ui/8bit/input";
import { InputOTP, InputOTPGroup, InputOTPSlot } from "@/components/ui/8bit/input-otp";
import { Label } from "@/components/ui/8bit/label";

interface LoginFormProps extends React.ComponentPropsWithoutRef<"div"> {
    onSubmit: (credentials: any) => void;
    show2FA: boolean;
    qrData?: { qrCode: string; secret: string } | null
    onVerify2FA: (code: string) => void;
    onCancel2FA: () => void;
    error?: string | null;
}

export function LoginForm({
        className,
        onSubmit,
        show2FA,
        qrData,
        onVerify2FA,
        onCancel2FA,
        error,
        ...props
    }: LoginFormProps) {

    const [otpValue, setOtpValue] = useState("");

    useEffect(() => {
        if (!show2FA) {
            setOtpValue("");
        }
    }, [show2FA]);

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        const formData = new FormData(e.currentTarget);
        const data = Object.fromEntries(formData);
        onSubmit(data); // Envía {email: '...', password: '...'} a LoginPage
    };

    const handleOtpChange = (val: string) => {
        setOtpValue(val);
        if (val.length === 6) {
            onVerify2FA(val);
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
                    {error && (
                        <Alert variant="destructive" className="mb-4">
                            <AlertDescription>{error}</AlertDescription>
                        </Alert>
                    )}
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
                                            <img src={qrData.qrCode} alt="2FA QR" className="w-32 h-32" />
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

                            <div className="flex flex-col items-center gap-2">
                                <Label className="text-[10px] uppercase text-zinc-500">
                                    {qrData ? "CONFIRM_SETUP_CODE" : "ENTER_6_DIGIT_CODE"}
                                </Label>
                                <InputOTP maxLength={6} value={otpValue} onChange={handleOtpChange} autoFocus>
                                    <InputOTPGroup>
                                        <InputOTPSlot index={0} />
                                        <InputOTPSlot index={1} />
                                        <InputOTPSlot index={2} />
                                        <InputOTPSlot index={3} />
                                        <InputOTPSlot index={4} />
                                        <InputOTPSlot index={5} />
                                    </InputOTPGroup>
                                </InputOTP>
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
