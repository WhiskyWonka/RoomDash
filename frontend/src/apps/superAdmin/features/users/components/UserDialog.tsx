import { Controller } from "react-hook-form";
import type { User } from "@/types/user";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from "@/components/ui/8bit/dialog";
import { Button } from "@/components/ui/8bit/button";
import { Input } from "@/components/ui/8bit/input";
import { Field, FieldLabel, FieldContent, FieldDescription, FieldError } from "@/components/ui/shadcn/field";
import { useUserForm } from "../hooks/useUserForm";

interface Props {
    open: boolean;
    user: User | null;
    onClose: () => void;
    onSubmit: (firstName: string, lastName: string, username: string, email: string) => Promise<void>;
}

export function UserDialog({ open, user, onClose, onSubmit }: Props) {
    const { form, handleFormSubmit, isSubmitting } = useUserForm(user, onSubmit);

    return (
        <Dialog open={open} onOpenChange={(v) => { if (!v && !isSubmitting) onClose(); }}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle className="mb-8">{user ? "Edit User" : "Create User"}</DialogTitle>
                    <DialogDescription className="text-xs">
                        {user ? "Update the user details below." : "Fill in the details to create a new user."}
                    </DialogDescription>
                </DialogHeader>
                <form onSubmit={handleFormSubmit} className="grid gap-4 py-4">
                    <Controller
                        name="firstName"
                        control={form.control}
                        render={({ field, fieldState }) => (
                            <Field data-invalid={fieldState.invalid || undefined}>
                                <FieldContent>
                                    <FieldLabel htmlFor="firstName">Name</FieldLabel>
                                    <Input id="firstName" placeholder="My User" {...field} disabled={isSubmitting} />
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
                                    <FieldLabel htmlFor="lastName">Last Name</FieldLabel>
                                    <Input id="lastName" placeholder="My User" {...field} disabled={isSubmitting} />
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
                                    <Input id="username" placeholder="myuser" {...field} disabled={isSubmitting} />
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
                                    <Input id="email" placeholder="my-user@roomdash.com" {...field} disabled={isSubmitting} />
                                    <FieldDescription>The email used to contact this user.</FieldDescription>
                                    {fieldState.error && <FieldError errors={[fieldState.error]} />}
                                </FieldContent>
                            </Field>
                        )}
                    />
                    <DialogFooter className="gap-4">
                        <Button type="button" variant="outline" onClick={onClose} disabled={isSubmitting}>Cancel</Button>
                        <Button type="submit" disabled={isSubmitting}>
                            {isSubmitting ? "Saving..." : (user ? "Save" : "Create")}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}