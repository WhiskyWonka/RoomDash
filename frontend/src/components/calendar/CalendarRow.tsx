import { CalendarResource, CalendarEvent, TimeSlot } from './types'
import { CalendarEventBlock } from './CalendarEvent'

interface CalendarRowProps {
  resource: CalendarResource
  events: CalendarEvent[]
  timeSlots: TimeSlot[]
  onEventClick?: (event: CalendarEvent) => void
}

export function CalendarRow({ resource, events, timeSlots, onEventClick }: CalendarRowProps) {
  // Calcular posición de cada evento en la grilla
  const getEventPosition = (event: CalendarEvent) => {
    const eventStartMinutes = event.start.getHours() * 60 + event.start.getMinutes()
    const eventEndMinutes = event.end.getHours() * 60 + event.end.getMinutes()
    
    // Encontrar el slot de inicio (cada slot = 30 min)
    const startSlotIndex = Math.floor(eventStartMinutes / 30)
    const endSlotIndex = Math.ceil(eventEndMinutes / 30)
    
    return {
      start: startSlotIndex + 2, // +2 por la columna de recursos
      span: Math.max(1, endSlotIndex - startSlotIndex)
    }
  }

  return (
    <div 
      className="grid border-b border-border relative"
      style={{ 
        gridTemplateColumns: `200px repeat(${timeSlots.length}, minmax(60px, 1fr))`,
        minHeight: '60px'
      }}
    >
      {/* Columna de recurso (habitación) */}
      <div className="sticky left-0 bg-card border-r border-border px-4 py-3 font-medium z-10">
        <div className="text-sm">{resource.title}</div>
        {resource.subtitle && (
          <div className="text-xs text-muted-foreground">{resource.subtitle}</div>
        )}
      </div>

      {/* Celdas de tiempo */}
      {timeSlots.map((slot, index) => (
        <div
          key={index}
          className="border-r border-border hover:bg-muted/50 transition-colors"
        />
      ))}

      {/* Eventos superpuestos */}
      {events.map((event) => {
        const pos = getEventPosition(event)
        return (
          <CalendarEventBlock
            key={event.id}
            event={event}
            columnStart={pos.start}
            columnSpan={pos.span}
            onClick={onEventClick}
          />
        )
      })}
    </div>
  )
}