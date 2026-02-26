import { useRouteError, isRouteErrorResponse } from "react-router-dom";

export function ErrorPage() {
    const error = useRouteError();
    console.error("ROUTER_ERROR:", error);

    return (
        <div className="min-h-screen bg-black text-[#ff0000] flex flex-col items-center justify-center font-mono p-4 border-4 border-[#ff0000]">
            <h1 className="text-4xl mb-4 font-bold underline"> [SYSTEM_CRITICAL_ERROR] </h1>
            <p className="mb-4">
                {isRouteErrorResponse(error) 
                    ? `${error.status} ${error.statusText}` 
                    : "UNKNOWN_EXCEPTION_DETECTED"}
            </p>
            <button 
                onClick={() => window.location.href = "/"}
                className="bg-[#ff0000] text-black px-4 py-2 hover:bg-white transition-colors"
            >
                REBOOT_SYSTEM
            </button>
        </div>
    );
}