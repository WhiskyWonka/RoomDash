import { Button } from "@/components/ui/button";
import logo from "@/assets/logo.svg";

interface Props {
  theme: "light" | "dark";
  onToggleTheme: () => void;
}

export function TopBar({ theme, onToggleTheme }: Props) {
  return (
    <header className="fixed top-0 left-0 right-0 z-50 border-b border-border bg-background/80 backdrop-blur-sm">
      <div className="mx-auto max-w-4xl flex items-center justify-between px-4 h-14">
        <img src={logo} alt="RoomDash" className={`h-8 ${theme === "dark" ? "invert" : ""}`} />
        <Button variant="ghost" size="icon" onClick={onToggleTheme} aria-label="Toggle theme">
          {theme === "dark" ? (
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
          ) : (
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
          )}
        </Button>
      </div>
    </header>
  );
}
