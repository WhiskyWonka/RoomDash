"use client"

import React, { useState } from "react"
import { ChevronLeft, ChevronRight, Home, Clock } from "lucide-react"
import { Button } from "@/components/ui/shadcn/button"

const HABITACIONES = [
  { id: 101, nombre: "Hab. 101" },
  { id: 102, nombre: "Hab. 102" },
  { id: 103, nombre: "Suite 201" },
  { id: 104, nombre: "Hab. 202" },
];

// Generar slots de media hora: ["00:00", "00:30", ..., "23:30"]
const GENERAR_HORAS = () => {
  const slots = [];
  for (let i = 0; i < 24; i++) {
    const hora = i.toString().padStart(2, '0');
    slots.push(`${hora}:00`);
    slots.push(`${hora}:30`);
  }
  return slots;
};

const SLOTS_HORA = GENERAR_HORAS();

export default function ReservasPage() {
  const [fechaSeleccionada, setFechaSeleccionada] = useState(new Date());

  const cambiarDia = (offset: number) => {
    const nuevaFecha = new Date(fechaSeleccionada);
    nuevaFecha.setDate(nuevaFecha.getDate() + offset);
    setFechaSeleccionada(nuevaFecha);
  };

  return (
    <div className="flex flex-1 flex-col gap-4 p-4 md:p-8 h-screen">
      
      {/* NAVEGACIÓN DE FECHA */}
      <div className="flex items-center justify-between bg-card p-4 rounded-xl border shadow-sm">
        <Button variant="outline" onClick={() => cambiarDia(-1)} className="gap-2">
          <ChevronLeft className="h-4 w-4" /> Día anterior
        </Button>
        
        <div className="text-center">
          <h2 className="text-xl font-bold capitalize">
            {fechaSeleccionada.toLocaleDateString('es-AR', { dateStyle: 'full' })}
          </h2>
          <p className="text-xs text-muted-foreground uppercase tracking-widest">Vista Diaria de Operaciones</p>
        </div>

        <Button variant="outline" onClick={() => cambiarDia(1)} className="gap-2">
          Día posterior <ChevronRight className="h-4 w-4" />
        </Button>
      </div>

      {/* GRILLA DE HORARIOS */}
      <div className="flex-1 rounded-xl border bg-card shadow-sm overflow-hidden flex flex-col">
        <div className="overflow-auto flex-1">
          <div 
            className="inline-grid" 
            style={{ 
              gridTemplateColumns: `150px repeat(${SLOTS_HORA.length}, 80px)`,
            }}
          >
            {/* Esquina superior izquierda vacía/Habitación */}
            <div className="sticky top-0 left-0 z-30 bg-muted/90 backdrop-blur-sm p-4 border-b border-r font-bold flex items-center gap-2">
              <Home className="h-4 w-4" /> Recursos
            </div>

            {/* HEADER DE HORAS (Sticky top) */}
            {SLOTS_HORA.map((hora) => (
              <div 
                key={hora} 
                className="sticky top-0 z-20 bg-muted/90 backdrop-blur-sm p-3 border-b border-r text-center text-[11px] font-mono font-semibold"
              >
                {hora}
              </div>
            ))}

            {/* FILAS DE HABITACIONES */}
            {HABITACIONES.map((hab) => (
              <React.Fragment key={hab.id}>
                {/* Nombre de Habitación (Sticky left) */}
                <div className="sticky left-0 z-10 bg-card p-4 border-b border-r text-sm font-medium flex items-center shadow-[2px_0_5px_rgba(0,0,0,0.05)]">
                  {hab.nombre}
                </div>

                {/* Celdas de tiempo (Media hora) */}
                {SLOTS_HORA.map((slot) => (
                  <div 
                    key={slot} 
                    className="h-14 border-b border-r border-border/40 hover:bg-primary/5 transition-colors cursor-crosshair relative group"
                  >
                    {/* Guía visual sutil para los cuartos de hora o divisiones */}
                    <div className="absolute inset-0 opacity-0 group-hover:opacity-100 flex items-center justify-center pointer-events-none">
                      <span className="text-[10px] text-primary font-bold">+</span>
                    </div>
                  </div>
                ))}
              </React.Fragment>
            ))}
          </div>
        </div>
      </div>
    </div>
  )
}