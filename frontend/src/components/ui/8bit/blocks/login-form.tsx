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
    onVerify2FA: (code: string) => void;
    onCancel2FA: () => void;
}

export function LoginForm({ 
        className, 
        onSubmit, 
        show2FA, 
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
    
    return (
        <div className={cn("flex flex-col gap-6", className)} {...props}>
            <Card>
                <CardHeader className="text-center">
                    <CardTitle className="text-2xl text-[#00ff00] mb-3">
                        {show2FA ? "[ 2FA_VERIFICATION ]" : "[ RoomDash ]"}
                    </CardTitle>
                    <CardDescription className="text-xs">
                        <p className="text-[10px] text-zinc-500 uppercase">Super_Admin_Authentication_v1.0</p>
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    {!show2FA ? (
                        /* FORMULARIO DE LOGIN */
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
                        /* FORMULARIO DE 2FA */
                        <div className="flex flex-col gap-6">
                            <Label className="text-xs text-center">INGRESE CÓDIGO 2FA</Label>
                            <Input 
                                name="2fa_code" 
                                placeholder="000000" 
                                className="text-center text-xl tracking-[0.5em] text-[#00ff00]"
                                maxLength={6}
                                autoFocus
                                onChange={(e) => {
                                    if(e.target.value.length === 6) onVerify2FA(e.target.value);
                                }}
                            />
                            <Button variant="outline" onClick={onCancel2FA}>VOLVER</Button>
                        </div>
                    )}
                </CardContent>
            </Card>
        </div>
    );
}
