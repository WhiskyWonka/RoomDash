import { TenantsCountWidget } from "@/apps/superAdmin/features/dashboard/components/TenantsCountWidget";
import { AuditLogWidget } from "../features/dashboard/components/AuditLogWidget";
import { useDashboardTenantsData } from "../../superAdmin/features/dashboard/hooks/useDashboardTenantsData";

export default function DashboardPage() {
    const { tenantsCount, loading, error } = useDashboardTenantsData();

    if (error) {
        console.error("DASHBOARD_CRITICAL_FAILURE:", error);
    }

    return (
        <div className="p-2">
            {/* Grid para futuros widgets */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <TenantsCountWidget
                    count={tenantsCount}
                    limit={50}
                    loading={loading}
                />

                <AuditLogWidget />
            </div>
        </div>
    );
}