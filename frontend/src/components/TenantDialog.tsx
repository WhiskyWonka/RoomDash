import { useEffect, useState } from "react";
import type { Tenant } from "@/types/tenant";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

interface Props {
  open: boolean;
  tenant: Tenant | null;
  onClose: () => void;
  onSubmit: (name: string, domain: string) => void;
}

export function TenantDialog({ open, tenant, onClose, onSubmit }: Props) {
  const [name, setName] = useState("");
  const [domain, setDomain] = useState("");

  useEffect(() => {
    if (open) {
      setName(tenant?.name ?? "");
      setDomain(tenant?.domain ?? "");
    }
  }, [open, tenant]);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSubmit(name, domain);
  };

  return (
    <Dialog open={open} onOpenChange={(v) => { if (!v) onClose(); }}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{tenant ? "Edit Tenant" : "Create Tenant"}</DialogTitle>
          <DialogDescription>
            {tenant ? "Update the tenant details below." : "Fill in the details to create a new tenant."}
          </DialogDescription>
        </DialogHeader>
        <form onSubmit={handleSubmit} className="grid gap-4 py-4">
          <div className="grid gap-2">
            <Label htmlFor="name">Name</Label>
            <Input id="name" value={name} onChange={(e) => setName(e.target.value)} required />
          </div>
          <div className="grid gap-2">
            <Label htmlFor="domain">Domain</Label>
            <Input id="domain" value={domain} onChange={(e) => setDomain(e.target.value)} required />
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" onClick={onClose}>Cancel</Button>
            <Button type="submit">{tenant ? "Save" : "Create"}</Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}
