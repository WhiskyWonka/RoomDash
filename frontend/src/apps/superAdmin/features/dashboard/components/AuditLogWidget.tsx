import { useAuditLogs } from "../hooks/useAuditLogs";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/8bit/card";
import { ScrollArea } from "@/components/ui/8bit/scroll-area";
import { Terminal } from "lucide-react";

export function AuditLogWidget() {
    const { logs, isLoading, error } = useAuditLogs();

    return (
        <Card className="border-2 border-[#00ff00]/20 shadow-[4px_4px_0px_0px_rgba(0,255,0,0.1)]">
            <CardHeader>
                <CardTitle className="flex items-center gap-2 text-[#00ff00]">
                    <Terminal className="w-5 h-5" /> 
                    [ SYS_AUDIT_TRAIL ]
                </CardTitle>
                <CardDescription className="text-[10px] uppercase opacity-50 font-mono">
                    Monitoring_Audit_log
                </CardDescription>
            </CardHeader>
            <CardContent className="p-0">
                <ScrollArea className="h-[177px] w-full px-4 pt-0 pb-4">
                    {isLoading ? (
                        <div className="flex items-center gap-2 text-zinc-600 animate-pulse">
                            <span className="animate-spin text-[#00ff00]">/</span> INITIALIZING_STREAM...
                        </div>
                    ) : error ? (
                        <div className="text-red-500 uppercase p-2 border border-red-900 bg-red-950/20">
                            [CRITICAL_ERROR]: FETCH_FAILED
                        </div>
                    ) : (
                        <div className="space-y-1 font-mono text-[10px]">
                            {logs.map((log: any) => (
                                <div key={log.id} className="group border-b border-zinc-900/50 pb-1 last:border-0 hover:bg-[#00ff00]/5 px-1">
                                    <div className="flex justify-between items-start gap-4">
                                        <span className="text-zinc-500 shrink-0">
                                            {/* Usamos createdAt (camelCase) */}
                                            [{log.createdAt ? new Date(log.createdAt).toLocaleTimeString() : '??:??'}]
                                        </span>
                                        <span className="text-[#00ff00] uppercase font-bold shrink-0">
                                            {/* Usamos action en lugar de event */}
                                            {log.action?.replace('.', '_') || 'UNKNOWN_EVENT'}
                                        </span>
                                        <span className="text-zinc-300 flex-1 truncate">
                                            {/* Mostramos el entityType o una descripción */}
                                            {log.entityType ? `TARGET: ${log.entityType.toUpperCase()}` : 'SYSTEM_PROC'}
                                        </span>
                                        <span className="text-zinc-600 text-[8px] uppercase">
                                            {/* Usamos userId (camelCase) */}
                                            UID: {log.userId?.substring(0, 4) || 'SYS'}
                                        </span>
                                    </div>
                                    <div className="hidden group-hover:block text-[8px] text-[#00ff00]/40 mt-1 leading-none italic">
                                        IP: {log.ipAddress} | AGENT: {log.userAgent?.substring(0, 50)}...
                                    </div>
                                </div>
                            ))}
                            {logs.length === 0 && (
                                <div className="text-zinc-700 italic py-4 text-center">
                                    {">"} NO_RECORDS_FOUND
                                </div>
                            )}
                        </div>
                    )}
                </ScrollArea>
            </CardContent>
        </Card>
    );
}