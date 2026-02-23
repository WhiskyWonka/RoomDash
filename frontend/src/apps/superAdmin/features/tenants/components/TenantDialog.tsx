import { useTenantForm } from "../hooks/useTenantForm";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/8bit/dialog";
import { Button } from "@/components/ui/8bit/button";
import { Input } from "@/components/ui/8bit/input";
import type { Tenant } from "@/types/tenant";

interface TenantDialogProps {
    open: boolean;
    tenant: Tenant | null;
    onClose: () => void;
    onSubmit: (name: string, domain: string) => Promise<void>;
}

export function TenantDialog({ open, tenant, onClose, onSubmit }: TenantDialogProps) {
    // Extraemos la lógica al hook
    const { name, setName, domain, setDomain, handleFormSubmit } = useTenantForm(tenant, onSubmit);

    return (
        <Dialog open={open} onOpenChange={onClose}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {tenant ? `[EDIT_TENANT: ${tenant.name}]` : "[CREATE_NEW_TENANT]"}
                    </DialogTitle>
                </DialogHeader>

                <form onSubmit={handleFormSubmit} className="space-y-4">
                    <div className="space-y-2">
                        <label className="font-mono text-xs text-[#00ff00]">NAME_ID:</label>
                        <Input 
                            value={name} 
                            onChange={(e) => setName(e.target.value)} 
                            placeholder="Ej: My Awesome Hotel"
                            required 
                        />
                    </div>

                    <div className="space-y-2">
                        <label className="font-mono text-xs text-[#00ff00]">DOMAIN_PREFIX:</label>
                        <Input 
                            value={domain} 
                            onChange={(e) => setDomain(e.target.value)} 
                            placeholder="Ej: myhotel"
                            required 
                        />
                    </div>

                    <DialogFooter className="mt-6">
                        <Button type="button" variant="outline" onClick={onClose}>
                            [CANCEL]
                        </Button>
                        <Button type="submit">
                            [SAVE_CHANGES]
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}