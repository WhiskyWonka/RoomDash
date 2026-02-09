import { Calendar, Home, Inbox, Search, Settings, User, UserStar, Sparkles } from "lucide-react";
import { Link } from "react-router-dom"

import {
  Sidebar,
  SidebarContent,
  SidebarGroup,
  SidebarGroupContent,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from "@/components/ui/8bit/sidebar";

import "@/components/ui/8bit/styles/retro.css";

// Menu items.
const items = [
  {
    title: "Dashboard",
    url: "dashboard",
    icon: Home,
  },
  /*{
    title: "Inbox",
    url: "#",
    icon: Inbox,
  },
  {
    title: "Calendar",
    url: "#",
    icon: Calendar,
  },*/
  {
    title: "Tenants",
    url: "tenants",
    icon: UserStar,
  },
  {
    title: "Users",
    url: "users",
    icon: User,
  },
  {
    title: "Features",
    url: "features",
    icon: Sparkles,
  },
  {
    title: "Settings",
    url: "settings",
    icon: Settings,
  },
];

export function AppSidebar() {
  return (
    <Sidebar collapsible="icon"
      className={`${"retro"} border-r border-dashed border-foreground dark:border-ring`}
    >
      <SidebarContent>
        <SidebarGroup>
          <SidebarGroupLabel className="mb-4">RoomDash</SidebarGroupLabel>
          <SidebarGroupContent>
            <SidebarMenu>
              {items.map((item) => (
                <SidebarMenuItem key={item.title}>
                  <SidebarMenuButton asChild>
                    <Link to={item.url}>
                      <item.icon />
                      <span>{item.title}</span>
                    </Link>
                  </SidebarMenuButton>
                </SidebarMenuItem>
              ))}
            </SidebarMenu>
          </SidebarGroupContent>
        </SidebarGroup>
      </SidebarContent>
    </Sidebar>
  );
}
