import { useEffect, useState } from "react";
import { tenantsApi } from "../services/tenantsApi";
import { TenantsCountWidget } from "@/components/ui/8bit/blocks/dashboard/TenantsCountWidget";

export default function DashboardPage() {
    const [tenantsCount, setTenantsCount] = useState(0);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        // Usamos una función asíncrona dentro del useEffect para mayor claridad
        const fetchDashboardData = async () => {
            try {
                setLoading(true);
                const response = await tenantsApi.list();
                
                // Con el interceptor, 'response' es el JSON { success, message, data }
                // Y 'data' contiene la lista o el objeto con 'items'
                const items = response.data?.items || response.data || [];
                
                setTenantsCount(items.length);
            } catch (error) {
                console.error("DASHBOARD_FETCH_ERROR:", error);
                // Aquí podrías setear un error para mostrar un mensaje retro de "SYSTEM FAILURE"
            } finally {
                setLoading(false);
            }
        };

        fetchDashboardData();
    }, []);

    return (
        <div className="p-2">
            {/* Grid para futuros widgets */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <TenantsCountWidget
                    count={tenantsCount}
                    limit={50}
                    loading={loading}
                />

                {/* Aquí podrías agregar más widgets como: RoomsCountWidget, RevenueWidget, etc. */}
            </div>
        </div>
    );
}