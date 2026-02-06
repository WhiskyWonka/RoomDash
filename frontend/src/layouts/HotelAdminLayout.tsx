import React, { ReactNode } from "react";
import { LayoutDashboard, Building2, Search, Settings, HelpCircle, UserCircle, SquareActivity } from "lucide-react";

// Definimos qué puede recibir nuestro Layout
interface LayoutProps {
  children: ReactNode;
}

export default function HotelAdminLayout({ children }: LayoutProps) {
  return (
    <div className="flex h-screen w-full bg-[#09090b] text-zinc-400 font-sans">
      
      {/* SIDEBAR */}
      <aside className="w-64 border-r border-zinc-800 flex flex-col p-4 bg-[#09090b]">
        <div className="mb-8 px-2 flex items-center gap-2 text-white font-bold text-lg">
          <SquareActivity size={24} /> 
          RoomDash
        </div>

        <nav className="flex-1 space-y-2">
          <p className="text-[10px] uppercase tracking-widest font-bold text-zinc-500 px-2 mb-4">Principal</p>
          <NavItem icon={<LayoutDashboard size={20} />} label="Dashboard" active />
          <NavItem icon={<Building2 size={20} />} label="Reservas" />
          <NavItem icon={<Search size={20} />} label="Personal" />
        </nav>

        <div className="mt-auto pt-4 border-t border-zinc-800 space-y-2">
          <NavItem icon={<Settings size={20} />} label="Configuración" />
          <NavItem icon={<HelpCircle size={20} />} label="Soporte" />
          
          <div className="flex items-center gap-3 px-2 pt-6">
            <div className="w-10 h-10 rounded-full bg-zinc-800 flex items-center justify-center border border-zinc-700">
                <UserCircle className="text-zinc-400" size={24} />
            </div>
            <div className="overflow-hidden">
              <p className="text-sm text-white font-medium truncate">Victor Admin</p>
              <p className="text-xs text-zinc-500 truncate">admin@roomdash.com</p>
            </div>
          </div>
        </div>
      </aside>

      {/* MAIN CONTENT */}
      <main className="flex-1 flex flex-col min-w-0 bg-[#09090b]">
        <header className="h-16 border-b border-zinc-800 flex items-center justify-between px-8 bg-[#09090b]/80 backdrop-blur-md sticky top-0 z-10">
          <h1 className="text-xl font-semibold text-white tracking-tight">Dashboard</h1>
          <button className="bg-white text-black px-4 py-2 rounded-lg text-sm font-bold hover:bg-zinc-200 transition-all active:scale-95 shadow-lg shadow-white/5">
            + Quick Create
          </button>
        </header>

        <div className="flex-1 overflow-y-auto p-8">
          {children}
        </div>
      </main>
    </div>
  );
}

// Tipamos las propiedades del NavItem
interface NavItemProps {
  icon: ReactNode;
  label: string;
  active?: boolean;
}

function NavItem({ icon, label, active = false }: NavItemProps) {
  return (
    <div className={`
      flex items-center gap-3 px-3 py-2.5 rounded-xl cursor-pointer transition-all duration-200
      ${active 
        ? 'bg-zinc-900 text-white border border-zinc-800' 
        : 'hover:bg-zinc-900/50 hover:text-zinc-100'}
    `}>
      <span className={active ? "text-white" : "text-zinc-500"}>
        {icon}
      </span>
      <span className="text-sm font-semibold tracking-wide">{label}</span>
    </div>
  );
}