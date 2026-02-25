import { useEffect } from "react";
import { useForm, Controller } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import type { Tenant } from "@/types/tenant";
import type { User } from "@/types/user";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from "@/components/ui/8bit/dialog";
import { Button } from "@/components/ui/8bit/button";
import { Input } from "@/components/ui/8bit/input";
import { Field, FieldLabel, FieldContent, FieldDescription, FieldError } from "@/components/ui/shadcn/field";
import { Separator } from "@/components/ui/8bit/separator"

const adminSchema = z.object({
  first_name: z.string().min(1, "Required").max(255),
  last_name: z.string().min(1, "Required").max(255),
  username: z
    .string()
    .min(1, "Required")
    .max(50)
    .regex(/^[a-zA-Z0-9_-]+$/, "Only letters, numbers, underscores, hyphens"),
  email: z.string().min(1, "Required").email("Invalid email").max(255),
});

type AdminFormValues = z.infer<typeof adminSchema>;

interface Props {
  open: boolean;
  tenant: Tenant | null;
  adminUser?: User | null;
  onClose: () => void;
  onSubmit: (data: AdminFormValues) => Promise<void>;
  onDelete?: () => void;
  onResendVerification?: () => void;
}

export function TenantAdminDialog({ open, tenant, adminUser, onClose, onSubmit, onDelete, onResendVerification }: Props) {
  const isEdit = Boolean(adminUser);

  const form = useForm<AdminFormValues>({
    resolver: zodResolver(adminSchema),
    defaultValues: {
      first_name: "",
      last_name: "",
      username: "",
      email: "",
    },
  });

  useEffect(() => {
    if (open) {
      form.reset({
        first_name: adminUser?.firstName ?? "",
        last_name: adminUser?.lastName ?? "",
        username: adminUser?.username ?? "",
        email: adminUser?.email ?? "",
      });
    }
  }, [open, adminUser, form]);

  const handleSubmit = async (values: AdminFormValues) => {
    await onSubmit(values);
    form.reset();
  };

  const handleClose = () => {
    form.reset();
    onClose();
  };

  return (
    <Dialog open={open} onOpenChange={(v) => { if (!v) handleClose(); }}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle className="mb-8">{isEdit ? "Edit Admin" : "Create Admin"} — {tenant?.name}</DialogTitle>
          <DialogDescription className="text-xs">
            {isEdit ? "Update the admin user for this tenant." : "Create a root admin user for this tenant."}
          </DialogDescription>
        </DialogHeader>
        <form onSubmit={form.handleSubmit(handleSubmit)} className="grid gap-4 py-4">
          <div className="grid grid-cols-2 gap-4">
            <Controller
              name="first_name"
              control={form.control}
              render={({ field, fieldState }) => (
                <Field data-invalid={fieldState.invalid || undefined}>
                  <FieldContent>
                    <FieldLabel htmlFor="first_name">First Name</FieldLabel>
                    <Input id="first_name" placeholder="John" {...field} aria-invalid={fieldState.invalid} />
                    {fieldState.error && <FieldError errors={[fieldState.error]} />}
                  </FieldContent>
                </Field>
              )}
            />
            <Controller
              name="last_name"
              control={form.control}
              render={({ field, fieldState }) => (
                <Field data-invalid={fieldState.invalid || undefined}>
                  <FieldContent>
                    <FieldLabel htmlFor="last_name">Last Name</FieldLabel>
                    <Input id="last_name" placeholder="Doe" {...field} aria-invalid={fieldState.invalid} />
                    {fieldState.error && <FieldError errors={[fieldState.error]} />}
                  </FieldContent>
                </Field>
              )}
            />
          </div>
          <Controller
            name="username"
            control={form.control}
            render={({ field, fieldState }) => (
              <Field data-invalid={fieldState.invalid || undefined}>
                <FieldContent>
                  <FieldLabel htmlFor="username">Username</FieldLabel>
                  <Input id="username" placeholder="john_doe" {...field} aria-invalid={fieldState.invalid} />
                  <FieldDescription>Letters, numbers, underscores and hyphens only.</FieldDescription>
                  {fieldState.error && <FieldError errors={[fieldState.error]} />}
                </FieldContent>
              </Field>
            )}
          />
          <Controller
            name="email"
            control={form.control}
            render={({ field, fieldState }) => (
              <Field data-invalid={fieldState.invalid || undefined}>
                <FieldContent>
                  <FieldLabel htmlFor="email">Email</FieldLabel>
                  <Input id="email" type="email" placeholder="john@example.com" {...field} aria-invalid={fieldState.invalid} />
                  {fieldState.error && <FieldError errors={[fieldState.error]} />}
                </FieldContent>
              </Field>
            )}
          />
          <DialogFooter className="gap-4">
            <Button type="button" variant="outline" onClick={handleClose}>Cancel</Button>
            {isEdit && onDelete && (
              <Button type="button" variant="warning" onClick={onDelete}>Delete</Button>
            )}
            <Button type="submit">{isEdit ? "Save" : "Create Admin"}</Button>
          </DialogFooter>
          <Separator />
          <DialogFooter className="gap-4">
            {isEdit && onResendVerification && (
              <Button className="w-full" type="button" variant="secondary" onClick={onResendVerification}>
                Resend Verification Email
              </Button>
            )}
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}
