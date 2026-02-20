import { TimeSlot } from './types'
import { format, addDays, subDays } from 'date-fns'
import { es } from 'date-fns/locale'
import { ChevronLeft, ChevronRight } from 'lucide-react'
import { Button } from '@/components/ui/shadcn/button'

interface CalendarHeaderProps {
  currentDate: Date
  timeSlots: TimeSlot[]
  onDateChange?: (date: Date) => void
}

export function CalendarHeader({ currentDate, timeSlots, onDateChange }: CalendarHeaderProps) {
  const handlePreviousDay = () => {
    onDateChange?.(subDays(currentDate, 1))
  }

  const handleNextDay = () => {
    onDateChange?.(addDays(currentDate, 1))
  }

  const handleToday = () => {
    onDateChange?.(new Date())
  }

  return (
    <div className="sticky top-0 z-20 bg-background border-b border-border">
      {/* Header de navegación de fecha */}
      <div 
        className="grid border-b border-border bg-muted/50"
        style={{ 
          gridTemplateColumns: `200px 1fr` 
        }}
      >
        <div className="sticky left-0 bg-card border-r border-border px-4 py-3 font-semibold text-sm z-10">
          Recursos
        </div>
        
        <div className="flex items-center justify-between px-4 py-2">
          <Button
            variant="ghost"
            size="icon"
            onClick={handlePreviousDay}
            className="h-8 w-8"
          >
            <ChevronLeft className="h-4 w-4" />
          </Button>

          <div className="flex items-center gap-2">
            <h2 className="text-lg font-semibold capitalize">
              {format(currentDate, "EEEE dd 'de' MMMM, yyyy", { locale: es })}
            </h2>
            <Button
              variant="outline"
              size="sm"
              onClick={handleToday}
              className="ml-2"
            >
              Hoy
            </Button>
          </div>

          <Button
            variant="ghost"
            size="icon"
            onClick={handleNextDay}
            className="h-8 w-8"
          >
            <ChevronRight className="h-4 w-4" />
          </Button>
        </div>
      </div>

      {/* Header de horas */}
      <div 
        className="grid"
        style={{ 
          gridTemplateColumns: `200px repeat(${timeSlots.length}, minmax(60px, 1fr))` 
        }}
      >
        <div className="sticky left-0 bg-card border-r border-border z-10" />
        
        {timeSlots.map((slot, index) => (
          <div
            key={index}
            className="px-1 py-2 text-center text-xs text-muted-foreground border-r border-border"
          >
            {slot.label}
          </div>
        ))}
      </div>
    </div>
  )
}