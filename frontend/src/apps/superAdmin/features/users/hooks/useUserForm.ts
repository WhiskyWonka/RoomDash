import { useEffect } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import type { User } from "@/types/user";

const userSchema = z.object({
    firstName: z.string().min(1, "Name is required").max(255),
    lastName: z.string().min(1, "Last Name is required").max(255),
    username: z.string().min(1, "Username is required").max(32),
    email: z.string().min(1, "Email is required").max(255).email("Invalid email address")
});

export type UserFormValues = z.infer<typeof userSchema>;

export function useUserForm(
    user: User | null,
    onSubmit: (firstName: string, lastName: string, username: string, email: string) => Promise<void>
) {
    const form = useForm<UserFormValues>({
        resolver: zodResolver(userSchema),
        defaultValues: { firstName: "", lastName: "", username: "", email: "" },
    });

    useEffect(() => {
        form.reset({
            firstName: user?.firstName ?? "",
            lastName: user?.lastName ?? "",
            username: user?.username ?? "",
            email: user?.email ?? "",
        });
    }, [user, form]);

    const handleFormSubmit = form.handleSubmit(async (values) => {
        await onSubmit(values.firstName, values.lastName, values.username, values.email);
    });

    return {
        form,
        handleFormSubmit,
        isSubmitting: form.formState.isSubmitting
    };
}