import { UserStar } from "lucide-react";
import { Progress } from "@/components/ui/8bit/progress";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/8bit/card";
import { useTenants } from "../../tenants/hooks/useTenants";

interface Props {
    count?: number; // Ahora es opcional
    limit?: number;
    loading?: boolean;
}

export function TenantsCountWidget({ count: manualCount, limit = 100, loading: manualLoading }: Props) {
    // 1. Usamos el hook de tenants (si no hay datos manuales)
    const { tenants, loading: hookLoading } = useTenants();
    
    // 2. Priorizamos props manuales, si no, usamos el conteo del hook
    const finalCount = manualCount ?? tenants.length;
    const isLoading = manualLoading ?? hookLoading;

    // Calculamos el porcentaje para la barra de progreso
    const percentage = Math.min(Math.round((finalCount / limit) * 100), 100);

    return (
        <Card className="border-2 border-[#00ff00]/20 shadow-[4px_4px_0px_0px_rgba(0,255,0,0.1)]">
            <CardHeader>
                <CardTitle className="flex items-center gap-2 text-[#00ff00]">
                    <UserStar className="w-5 h-5" /> 
                    [ ACTIVE_TENANTS ]
                </CardTitle>
                <CardDescription className="text-[10px] uppercase opacity-50 font-mono">
                    Monitoring_Central_Grid
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div className="mb-4">
                    {isLoading ? (
                        <div className="h-10 flex items-center">
                            <p className="text-xl text-zinc-700 animate-pulse font-mono font-bold italic">
                                {">"} SCANNING_DB...
                            </p>
                        </div>
                    ) : (
                        <div className="flex items-baseline gap-2">
                            <span className="text-5xl font-black text-[#00ff00] tabular-nums drop-shadow-[2px_2px_0px_rgba(0,0,0,1)]">
                                {finalCount.toString().padStart(3, '0')}
                            </span>
                            <span className="text-zinc-500 text-[10px] font-mono uppercase">
                                / {limit} UNITS_LIMIT
                            </span>
                        </div>
                    )}
                </div>

                <div className="space-y-2">
                    <div className="flex justify-between text-[9px] uppercase font-mono">
                        <span className="text-zinc-500 italic">Storage_Capacity</span>
                        <span className={percentage > 90 ? "text-red-500 animate-pulse" : "text-[#00ff00]"}>
                            {percentage}%
                        </span>
                    </div>
                    {/* Progress Bar con estilo retro */}
                    <Progress value={percentage} className="h-4 border-2 border-zinc-800 bg-zinc-900 rounded-none overflow-hidden" />
                </div>

                <div className="mt-4 pt-4 border-t border-zinc-800 flex justify-between items-center">
                    <span className="text-[9px] text-zinc-600 font-mono uppercase tracking-tighter">
                        {">"} STATUS: {percentage > 90 ? "CRITICAL_LIMIT" : "STABLE_OPERATION"}
                    </span>
                    <span className="text-[9px] text-zinc-700 font-mono">REV_2.026</span>
                </div>
            </CardContent>
        </Card>
    );
}