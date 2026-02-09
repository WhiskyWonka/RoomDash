import { ChartAreaInteractive } from "@/components/ui/shadcn/chart-area-interactive"
import { DataTable } from "@/components/ui/shadcn/data-table"
import { SectionCards } from "@/components/ui/shadcn/section-cards"


import data from '../../../app/dashboard/data.json';


export default function DashboardPage() {
    return (
        <div className="@container/main flex flex-1 flex-col gap-2 p-4 md:gap-4 md:p-6">
            <SectionCards />
            <div className="lg:px-6">
                <ChartAreaInteractive />
            </div>
            <DataTable data={data} />
        </div>
    );
}