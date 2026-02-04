import { useEffect } from "react";
import { useForm, Controller } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import type { Tenant } from "@/types/tenant";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Field, FieldLabel, FieldContent, FieldDescription, FieldError } from "@/components/ui/field";

const tenantSchema = z.object({
  name: z.string().min(1, "Name is required").max(255),
  domain: z
    .string()
    .min(1, "Domain is required")
    .max(255)
    .regex(/^[a-z0-9-]+$/, "Only lowercase letters, numbers, and hyphens"),
});

type TenantFormValues = z.infer<typeof tenantSchema>;

interface Props {
  open: boolean;
  tenant: Tenant | null;
  onClose: () => void;
  onSubmit: (name: string, domain: string) => void;
}

export function TenantDialog({ open, tenant, onClose, onSubmit }: Props) {
  const form = useForm<TenantFormValues>({
    resolver: zodResolver(tenantSchema),
    defaultValues: { name: "", domain: "" },
  });

  useEffect(() => {
    if (open) {
      form.reset({
        name: tenant?.name ?? "",
        domain: tenant?.domain ?? "",
      });
    }
  }, [open, tenant, form]);

  const handleSubmit = (values: TenantFormValues) => {
    onSubmit(values.name, values.domain);
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
        <form onSubmit={form.handleSubmit(handleSubmit)} className="grid gap-4 py-4">
          <Controller
            name="name"
            control={form.control}
            render={({ field, fieldState }) => (
              <Field data-invalid={fieldState.invalid || undefined}>
                <FieldContent>
                  <FieldLabel htmlFor="name">Name</FieldLabel>
                  <Input id="name" placeholder="My Tenant" {...field} aria-invalid={fieldState.invalid} />
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
                  <FieldLabel htmlFor="domain">Domain</FieldLabel>
                  <Input id="domain" placeholder="my-tenant" {...field} aria-invalid={fieldState.invalid} />
                  <FieldDescription>The subdomain used to access this tenant.</FieldDescription>
                  {fieldState.error && <FieldError errors={[fieldState.error]} />}
                </FieldContent>
              </Field>
            )}
          />
          <DialogFooter>
            <Button type="button" variant="outline" onClick={onClose}>Cancel</Button>
            <Button type="submit">{tenant ? "Save" : "Create"}</Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}
