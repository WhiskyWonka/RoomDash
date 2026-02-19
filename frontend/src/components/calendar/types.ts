export interface CalendarResource {
  id: string | number
  title: string
  subtitle?: string
}

export interface CalendarEvent {
  id: string | number
  resourceId: string | number
  title: string
  start: Date
  end: Date
  color?: string
  data?: any
}

export interface CalendarProps {
  resources: CalendarResource[]
  events: CalendarEvent[]
  currentDate: Date // Cambiado de startDate/endDate
  onDateChange?: (date: Date) => void
  onEventClick?: (event: CalendarEvent) => void
  onEventMove?: (eventId: string | number, newResourceId: string | number, newStart: Date) => void
  onEventResize?: (eventId: string | number, newStart: Date, newEnd: Date) => void
}

export interface TimeSlot {
  date: Date
  label: string
  hour: number
  minute: number
}