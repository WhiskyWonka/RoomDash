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

const baseSchema = z.object({
  first_name: z.string().min(1, "Required").max(255),
  last_name: z.string().min(1, "Required").max(255),
  username: z
    .string()
    .min(1, "Required")
    .max(50)
    .regex(/^[a-zA-Z0-9_-]+$/, "Only letters, numbers, underscores, hyphens"),
  email: z.string().min(1, "Required").email("Invalid email").max(255),
  password: z.string().optional(),
  password_confirmation: z.string().optional(),
});

const createSchema = baseSchema
  .extend({
    password: z.string().min(8, "At least 8 characters"),
    password_confirmation: z.string().min(1, "Required"),
  })
  .refine((d) => d.password === d.password_confirmation, {
    message: "Passwords don't match",
    path: ["password_confirmation"],
  });

const editSchema = baseSchema.superRefine((d, ctx) => {
  if (d.password || d.password_confirmation) {
    if (!d.password || d.password.length < 8) {
      ctx.addIssue({ code: "custom", message: "At least 8 characters", path: ["password"] });
    }
    if (d.password !== d.password_confirmation) {
      ctx.addIssue({ code: "custom", message: "Passwords don't match", path: ["password_confirmation"] });
    }
  }
});

type AdminFormValues = z.infer<typeof baseSchema>;

interface Props {
  open: boolean;
  tenant: Tenant | null;
  adminUser?: User | null;
  onClose: () => void;
  onSubmit: (data: AdminFormValues) => Promise<void>;
  onDelete?: () => void;
}

export function TenantAdminDialog({ open, tenant, adminUser, onClose, onSubmit, onDelete }: Props) {
  const isEdit = Boolean(adminUser);

  const form = useForm<AdminFormValues>({
    resolver: zodResolver(isEdit ? editSchema : createSchema),
    defaultValues: {
      first_name: "",
      last_name: "",
      username: "",
      email: "",
      password: "",
      password_confirmation: "",
    },
  });

  useEffect(() => {
    if (open) {
      form.reset({
        first_name: adminUser?.firstName ?? "",
        last_name: adminUser?.lastName ?? "",
        username: adminUser?.username ?? "",
        email: adminUser?.email ?? "",
        password: "",
        password_confirmation: "",
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
          <DialogTitle className="mb-8">
            {isEdit ? "Edit Admin" : "Create Admin"} — {tenant?.name}
          </DialogTitle>
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
          <Controller
            name="password"
            control={form.control}
            render={({ field, fieldState }) => (
              <Field data-invalid={fieldState.invalid || undefined}>
                <FieldContent>
                  <FieldLabel htmlFor="password">Password{isEdit && " (leave blank to keep current)"}</FieldLabel>
                  <Input id="password" type="password" placeholder="••••••••" {...field} aria-invalid={fieldState.invalid} />
                  {!isEdit && <FieldDescription>Min 8 chars, upper/lowercase, numbers and symbols.</FieldDescription>}
                  {fieldState.error && <FieldError errors={[fieldState.error]} />}
                </FieldContent>
              </Field>
            )}
          />
          <Controller
            name="password_confirmation"
            control={form.control}
            render={({ field, fieldState }) => (
              <Field data-invalid={fieldState.invalid || undefined}>
                <FieldContent>
                  <FieldLabel htmlFor="password_confirmation">Confirm Password</FieldLabel>
                  <Input id="password_confirmation" type="password" placeholder="••••••••" {...field} aria-invalid={fieldState.invalid} />
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
        </form>
      </DialogContent>
    </Dialog>
  );
}
