import { authApi } from "@/lib/authApi";
import { Button } from "@/components/ui/8bit/button";
import { Input } from "@/components/ui/8bit/input";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/8bit/card";
import { useSetPasswordForm, type SetPasswordValues } from "../hooks/useSetPasswordForm";
import { Controller } from "react-hook-form";

interface Props {
    token: string;
    onSuccess: () => void;
    onError: (msg: string) => void;
}

export function SetPasswordForm({ token, onSuccess, onError }: Props) {
    
    const onSubmit = async (values: SetPasswordValues) => {
        try {
            await authApi.verifyEmail({ token, ...values });
            onSuccess();
        } catch (err: any) {
            onError(err.message || "AUTHENTICATION_TOKEN_EXPIRED_OR_INVALID");
        }
    };

    const { form, handleFormSubmit, isSubmitting } = useSetPasswordForm(onSubmit);

    return (
        <Card>
            <CardHeader className="text-center uppercase">
                <CardTitle className="text-2xl text-[#00ff00] mb-3">
                    Setup_Account
                </CardTitle>
                <CardDescription className="text-xs">
                    <p className="text-[10px] text-foreground/40 font-mono">TOKEN_VALIDATION</p>
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleFormSubmit} className="space-y-6">
                    <div className="space-y-4">
                        <Controller
                            name="password"
                            control={form.control}
                            render={({ field, fieldState }) => (
                                <div>
                                    <label className="text-[10px] text-foreground/50 block mb-1">NEW_PASSWORD</label>
                                    <Input 
                                        {...field}
                                        type="password"
                                        disabled={isSubmitting}
                                    />
                                    {fieldState.error && (
                                        <p className="text-[10px] text-red-500 mt-1 uppercase">
                                            {fieldState.error.message}
                                        </p>
                                    )}
                                </div>
                            )}
                        />

                        <Controller
                            name="password_confirmation"
                            control={form.control}
                            render={({ field, fieldState }) => (
                                <div>
                                    <label className="text-[10px] text-foreground/50 block mb-1">CONFIRM_PASSWORD</label>
                                    <Input 
                                        {...field}
                                        type="password"
                                        disabled={isSubmitting}
                                    />
                                    {fieldState.error && (
                                        <p className="text-[10px] text-red-500 mt-1 uppercase">
                                            {fieldState.error.message}
                                        </p>
                                    )}
                                </div>
                            )}
                        />
                    </div>

                    <Button disabled={isSubmitting} className="w-full py-6">
                        {isSubmitting ? "VALIDATING_ALL..." : "ACTIVATE"}
                    </Button>
                </form>
            </CardContent>
        </Card>
    );
}