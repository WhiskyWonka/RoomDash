import { useState } from "react";
import { useSearchParams, useNavigate } from "react-router-dom";
import { Button } from "@/components/ui/8bit/button";
import { SetPasswordForm } from "@/components/ui/8bit/blocks/users/SetPasswordForm";
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from "@/components/ui/8bit/card";

type Step = "FORM" | "SUCCESS" | "ERROR";

export default function VerifyRootUserPage() {
    const [searchParams] = useSearchParams();
    const navigate = useNavigate();
    const token = searchParams.get("token");

    const [step, setStep] = useState<Step>("FORM");
    const [errorMsg, setErrorMsg] = useState("");

    // Si no hay token en la URL, mostramos error de entrada
    if (!token) {
        return (
            <div className="min-h-screen bg-black flex items-center justify-center p-4">
                <div className="retro border-2 border-red-500 p-8 bg-card max-w-md w-full text-center">
                    <h2 className="text-red-500 text-xl font-bold mb-4">!! CRITICAL_ERROR !!</h2>
                    <p className="text-red-400 text-xs font-mono">NO_SECURITY_TOKEN_DETECTED_IN_URL</p>
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-screen flex items-center justify-center p-4 font-mono">
            <div className="">

                {step === "FORM" && (
                    <SetPasswordForm 
                        token={token} 
                        onSuccess={() => setStep("SUCCESS")}
                        onError={(msg) => { setStep("ERROR"); setErrorMsg(msg); }}
                    />
                )}

                {step === "ERROR" && (
                    <Card>
                        <CardHeader className="text-center uppercase">
                            <CardTitle>
                                <h2 className="text-red-500 text-xl font-bold uppercase">Activation_Failed</h2>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="bg-red-500/10 border border-red-500 p-3 my-6">
                                <p className="text-red-500 leading-tight">
                                    {errorMsg || "INVALID_OR_EXPIRED_TOKEN"}
                                </p>
                            </div>
                        </CardContent>
                        <CardFooter className="justify-center">
                            <p className="text-[10px]">
                                PLEASE CONTACT YOUR SYSTEM ADMINISTRATOR.
                            </p>
                            {/*<Button onClick={() => window.location.reload()} className="w-full" variant="warning">
                                RETRY_PROCESS
                            </Button>*/}
                        </CardFooter>
                    </Card>
                )}

                {step === "SUCCESS" && (
                    <Card>
                        <CardHeader className="text-center uppercase">
                            <CardTitle>
                                <h2 className="text-green-500 text-2xl font-bold">CORE_ACTIVATED</h2>
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="my-8">
                            <p className="text-sm">ROOT_USER_VERIFIED_AND_SECURED</p>
                        </CardContent>
                        <CardFooter>
                            <Button onClick={() => navigate("/superadmin/login")} className="w-full">
                                PROCEED_TO_LOGIN
                            </Button>
                        </CardFooter>
                    </Card>
                )}
            </div>
        </div>
    );
}