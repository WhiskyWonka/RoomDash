import { useEffect } from "react";
import { useForm, Controller } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import type { User } from "@/types/user";
import { DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from "@/components/ui/8bit/dialog";
import { Button } from "@/components/ui/8bit/button";
import { Input } from "@/components/ui/8bit/input";
import { Field, FieldLabel, FieldContent, FieldDescription, FieldError } from "@/components/ui/shadcn/field";

import { Dialog } from "@/components/ui/8bit/dialog"

const userSchema = z.object({
  name: z.string().min(1, "Name is required").max(255),
  email: z
    .string()
    .min(1, "Email is required")
    .max(255)
    .email("Invalid email address")
});

type UserFormValues = z.infer<typeof userSchema>;

interface Props {
  open: boolean;
  user: User | null;
  onClose: () => void;
  onSubmit: (name: string, email: string) => void;
}

export function UserDialog({ open, user, onClose, onSubmit }: Props) {
  const form = useForm<UserFormValues>({
    resolver: zodResolver(userSchema),
    defaultValues: { name: "", email: "" },
  });

  useEffect(() => {
    if (open) {
      form.reset({
        name: user?.name ?? "",
        email: user?.email ?? "",
      });
    }
  }, [open, user, form]);

  const handleSubmit = (values: UserFormValues) => {
    onSubmit(values.name, values.email);
  };

  return (
    <Dialog open={open} onOpenChange={(v) => { if (!v) onClose(); }}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle className="mb-8">{user ? "Edit User" : "Create User"}</DialogTitle>
          <DialogDescription className="text-xs">
            {user ? "Update the user details below." : "Fill in the details to create a new user."}
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
                  <Input id="name" placeholder="My User" {...field} aria-invalid={fieldState.invalid} />
                  <FieldDescription>The display name for this user.</FieldDescription>
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
                  <Input id="email" placeholder="my-user@roomdash.com" {...field} aria-invalid={fieldState.invalid} />
                  <FieldDescription>The email used to contact this user.</FieldDescription>
                  {fieldState.error && <FieldError errors={[fieldState.error]} />}
                </FieldContent>
              </Field>
            )}
          />
          <DialogFooter className="gap-4">
            <Button type="button" variant="outline" onClick={onClose}>Cancel</Button>
            <Button type="submit">{user ? "Save" : "Create"}</Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}
