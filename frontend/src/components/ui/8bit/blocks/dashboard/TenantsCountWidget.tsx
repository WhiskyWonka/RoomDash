import { UserStar } from "lucide-react";
import { Progress } from "@/components/ui/8bit/progress";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/8bit/card";

interface Props {
    count: number;
    limit?: number;
    loading?: boolean;
}

export function TenantsCountWidget({ count, limit = 100, loading }: Props) {

    // Calculamos el porcentaje para la barra de progreso
    const percentage = Math.min(Math.round((count / limit) * 100), 100);

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2"><UserStar />Active_Tenants</CardTitle>
                <CardDescription>Card Description</CardDescription>
            </CardHeader>
            <CardContent>
                <span className="text-[10px] text-zinc-500 font-bold">ID: 0x42A</span>
                <p>Card Content</p>
                <div className="mb-2">
                {loading ? (
                        <p className="text-2xl text-zinc-700 animate-pulse">SCANNING...</p>
                    ) : (
                        <div className="flex items-baseline gap-2">
                            <span className="text-4xl font-black text-[#00ff00] tabular-nums">
                                {count.toString().padStart(3, '0')}
                            </span>
                            <span className="text-zinc-500 text-xs">/ {limit} UNITS</span>
                        </div>
                    )}
                </div>
                            <div className="space-y-1">
                <div className="flex justify-between text-[9px] uppercase text-[#00ff00]/70">
                    <span>System_Load</span>
                    <span>{percentage}%</span>
                </div>
                {/* Usamos la Progress Bar de 8bitcn */}
                <Progress value={percentage} className="h-4 retro" />
            </div>

            <p className="mt-4 text-[9px] text-zinc-600 leading-none uppercase">
                {">"} STATUS: {percentage > 90 ? "CRITICAL_CAPACITY" : "STABLE_OPERATION"}
            </p>
            </CardContent>
        </Card>
    );
}