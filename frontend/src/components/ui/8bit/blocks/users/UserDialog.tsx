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
    firstName: z.string().min(1, "Name is required").max(255),
    lastName: z.string().min(1, "Last Name is required").max(255),
    username: z.string().min(1, "Username is required").max(32),
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
    onSubmit: (firstName: string, lastName: string, username: string, email: string) => void;
}

export function UserDialog({ open, user, onClose, onSubmit }: Props) {
    const form = useForm<UserFormValues>({
        resolver: zodResolver(userSchema),
        defaultValues: { firstName: "", lastName: "", username: "", email: "" },
    });

    useEffect(() => {
        if (open) {
            form.reset({
                firstName: user?.firstName ?? "",
                lastName: user?.lastName ?? "",
                username: user?.username ?? "",
                email: user?.email ?? "",
            });
        }
    }, [open, user, form]);

    const handleSubmit = (values: UserFormValues) => {
        onSubmit(values.firstName, values.lastName, values.username, values.email);
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
                        name="firstName"
                        control={form.control}
                        render={({ field, fieldState }) => (
                            <Field data-invalid={fieldState.invalid || undefined}>
                                <FieldContent>
                                    <FieldLabel htmlFor="name">Name</FieldLabel>
                                    <Input id="firstName" placeholder="My User" {...field} aria-invalid={fieldState.invalid} />
                                    <FieldDescription>The first name for this user.</FieldDescription>
                                    {fieldState.error && <FieldError errors={[fieldState.error]} />}
                                </FieldContent>
                            </Field>
                        )}
                    />
                    <Controller
                        name="lastName"
                        control={form.control}
                        render={({ field, fieldState }) => (
                            <Field data-invalid={fieldState.invalid || undefined}>
                                <FieldContent>
                                    <FieldLabel htmlFor="name">Last Name</FieldLabel>
                                    <Input id="lastName" placeholder="My User" {...field} aria-invalid={fieldState.invalid} />
                                    <FieldDescription>The last name for this user.</FieldDescription>
                                    {fieldState.error && <FieldError errors={[fieldState.error]} />}
                                </FieldContent>
                            </Field>
                        )}
                    />
                    <Controller
                        name="username"
                        control={form.control}
                        render={({ field, fieldState }) => (
                            <Field data-invalid={fieldState.invalid || undefined}>
                                <FieldContent>
                                    <FieldLabel htmlFor="username">Username</FieldLabel>
                                    <Input id="username" placeholder="myuser" {...field} aria-invalid={fieldState.invalid} />
                                    <FieldDescription>The username for this user.</FieldDescription>
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
