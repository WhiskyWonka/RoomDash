import { useMemo } from 'react'
import { CalendarProps, TimeSlot } from './types'
import { CalendarHeader } from './CalendarHeader'
import { CalendarRow } from './CalendarRow'
import { startOfDay, isSameDay } from 'date-fns'

export function Calendar({
  resources,
  events,
  currentDate,
  onDateChange,
  onEventClick,
}: CalendarProps) {
  // Generar slots de 30 minutos (00:00, 00:30, 01:00, ..., 23:30)
  const timeSlots = useMemo<TimeSlot[]>(() => {
    const slots: TimeSlot[] = []
    const dayStart = startOfDay(currentDate)
    
    for (let hour = 0; hour < 24; hour++) {
      for (let minute = 0; minute < 60; minute += 30) {
        const slotDate = new Date(dayStart)
        slotDate.setHours(hour, minute, 0, 0)
        
        slots.push({
          date: slotDate,
          label: `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`,
          hour,
          minute
        })
      }
    }
    
    return slots
  }, [currentDate])

  // Filtrar eventos solo del día actual
  const dayEvents = useMemo(() => {
    return events.filter(event => 
      isSameDay(event.start, currentDate) || isSameDay(event.end, currentDate)
    )
  }, [events, currentDate])

  return (
    <div className="w-full h-full overflow-auto rounded-lg border border-border bg-background">
      <CalendarHeader 
        currentDate={currentDate}
        timeSlots={timeSlots} 
        onDateChange={onDateChange}
      />
      
      <div>
        {resources.map(resource => {
          const resourceEvents = dayEvents.filter(e => e.resourceId === resource.id)
          return (
            <CalendarRow
              key={resource.id}
              resource={resource}
              events={resourceEvents}
              timeSlots={timeSlots}
              onEventClick={onEventClick}
            />
          )
        })}
      </div>
    </div>
  )
}