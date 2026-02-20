import { CalendarEvent } from './types'

interface CalendarEventProps {
  event: CalendarEvent
  columnStart: number
  columnSpan: number
  onClick?: (event: CalendarEvent) => void
}

export function CalendarEventBlock({ event, columnStart, columnSpan, onClick }: CalendarEventProps) {
  const backgroundColor = event.color || 'hsl(var(--primary))'
  
  return (
    <div
      className="absolute h-[36px] rounded-md px-2 py-1 text-xs font-medium text-primary-foreground cursor-pointer hover:opacity-90 transition-opacity overflow-hidden"
      style={{
        backgroundColor,
        gridColumn: `${columnStart} / span ${columnSpan}`,
        left: '0',
        right: '0',
        top: '50%',
        transform: 'translateY(-50%)',
      }}
      onClick={() => onClick?.(event)}
    >
      <div className="truncate">{event.title}</div>
    </div>
  )
}