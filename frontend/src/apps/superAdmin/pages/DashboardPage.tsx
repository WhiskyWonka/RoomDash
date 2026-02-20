// frontend/src/pages/DashboardPage.tsx
import { useEffect, useState } from "react";
import { tenantsApi } from "@/lib/api";
import { TenantsCountWidget } from "@/components/ui/8bit/blocks/dashboard/TenantsCountWidget";

export default function DashboardPage() {
  const [tenantsCount, setTenantsCount] = useState(0);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    tenantsApi.list()
      .then((response: any) => {
        const tenantsArray = response.data || []; 
        setTenantsCount(tenantsArray.length);
      })
      .finally(() => setLoading(false));
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