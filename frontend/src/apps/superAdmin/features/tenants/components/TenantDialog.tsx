import { useTenantForm } from "../hooks/useTenantForm";
import { Controller } from "react-hook-form";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from "@/components/ui/8bit/dialog";
import { Button } from "@/components/ui/8bit/button";
import { Input } from "@/components/ui/8bit/input";
import { Checkbox } from "@/components/ui/8bit/checkbox";
import { Separator } from "@/components/ui/8bit/separator";
import { Field, FieldLabel, FieldContent, FieldDescription, FieldError } from "@/components/ui/shadcn/field";
import type { Tenant } from "@/types/tenant";

interface Props {
    open: boolean;
    tenant: Tenant | null;
    onClose: () => void;
    onSubmit: (name: string, domain: string, isActive: boolean) => Promise<void>;
    onCreateAdmin?: () => void;
}

export function TenantDialog({ open, tenant, onClose, onSubmit, onCreateAdmin }: Props) {
    // Usamos el hook evolucionado
    const { form, handleFormSubmit } = useTenantForm(tenant, onSubmit);

    return (
        <Dialog open={open} onOpenChange={(v) => { if (!v) onClose(); }}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {tenant ? `[EDIT_TENANT: ${tenant.name}]` : "[CREATE_NEW_TENANT]"}
                    </DialogTitle>
                    <DialogDescription className="text-xs mt-8">
                        {tenant ? "Update the tenant details below." : "Fill in the details to create a new tenant."}
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={handleFormSubmit} className="space-y-4 py-4">
                    <Controller
                        name="name"
                        control={form.control}
                        render={({ field, fieldState }) => (
                            <Field data-invalid={fieldState.invalid || undefined}>
                                <FieldContent>
                                    <FieldLabel className="text-[#00ff00]">NAME_ID</FieldLabel>
                                    <Input {...field} placeholder="My Tenant" className="font-mono" />
                                    <FieldDescription>The display name for this tenant.</FieldDescription>
                                    {fieldState.error && <FieldError errors={[fieldState.error]} />}
                                </FieldContent>
                            </Field>
                        )}
                    />

                    <Controller
                        name="domain"
                        control={form.control}
                        render={({ field, fieldState }) => (
                            <Field data-invalid={fieldState.invalid || undefined}>
                                <FieldContent>
                                    <FieldLabel className="text-[#00ff00]">DOMAIN_KEY</FieldLabel>
                                    <Input {...field} placeholder="my-tenant" className="font-mono" />
                                    <FieldDescription>The subdomain used to access this tenant.</FieldDescription>
                                    {fieldState.error && <FieldError errors={[fieldState.error]} />}
                                </FieldContent>
                            </Field>
                        )}
                    />

                    {tenant && (
                        <Controller
                            name="isActive"
                            control={form.control}
                            render={({ field }) => (
                                <Field>
                                    <FieldContent>
                                        <div className="flex items-center gap-3">
                                            <Checkbox
                                                id="isActive"
                                                checked={field.value}
                                                onCheckedChange={field.onChange}
                                            />
                                            <FieldLabel htmlFor="isActive">Active</FieldLabel>
                                        </div>
                                        <FieldDescription>Enable or disable this tenant.</FieldDescription>
                                    </FieldContent>
                                </Field>
                            )}
                        />
                    )}

                    <Separator />

                    <DialogFooter className="gap-4">
                        {tenant && onCreateAdmin && (
                            <Button className="w-full" type="button" variant="outline" onClick={onCreateAdmin}>Admin User</Button>
                        )}
                        <Button className="mr-auto" type="button" variant="outline" onClick={onClose}>Cancel</Button>
                        <Button type="submit">{tenant ? "Save" : "Create"}</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}