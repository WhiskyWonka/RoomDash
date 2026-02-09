import { Calendar, dateFnsLocalizer, Views } from "react-big-calendar"
import { format, parse, startOfWeek, getDay } from "date-fns";
import { es } from "date-fns/locale"
import "react-big-calendar/lib/css/react-big-calendar.css"
//import "../../../../styles/calendar-custom.css"
import "@/styles/calendar-custom.css"

const locales = {
  "es": es,
}

const localizer = dateFnsLocalizer({
  format,
  parse,
  startOfWeek,
  getDay,
  locales,
})

// 1. Las Habitaciones (Resources)
const myResources = [
  { id: 1, title: "Hab. 101 - Simple" },
  { id: 2, title: "Hab. 102 - Doble" },
  { id: 3, title: "Suite 201" },
]

// 2. Las Reservas (Events)
const myEvents = [
  {
    id: 0,
    title: "Reserva Familia Pérez",
    start: new Date(2026, 1, 9, 10, 0), // 9 de Feb, 10:00 AM
    end: new Date(2026, 1, 12, 14, 0),
    resourceId: 1,
  },
  {
    id: 1,
    title: "John Doe",
    start: new Date(2026, 1, 10, 12, 0),
    end: new Date(2026, 1, 15, 11, 0),
    resourceId: 2,
  },
]
export default function ReservasPage() {
    return (
        <div className="@container/main flex flex-1 flex-col gap-2 p-4 md:gap-4 md:p-6">
            <div className="h-[600px] rounded-xl border bg-card p-4 shadow-sm">
                <Calendar
                    localizer={localizer}
                    events={myEvents}
                    resources={myResources}
                    resourceIdAccessor="id"
                    resourceTitleAccessor="title"
                    defaultView={Views.DAY} // Vista de día para ver los recursos
                    views={['day', 'work_week']} // Limitamos las vistas
                    step={60}
                    defaultDate={new Date(2026, 1, 9)}
                    style={{ height: "100%" }}
                    messages={{
                        next: "Sig.",
                        previous: "Ant.",
                        today: "Hoy",
                        day: "Día",
                        work_week: "Semana",
                    }}
                />
            </div>
        </div>
    );
}