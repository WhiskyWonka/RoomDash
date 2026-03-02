import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";

const setPasswordSchema = z.object({
    password: z.string().min(8, "PASSWORD_TOO_SHORT_MIN_8_CHARS"),
    password_confirmation: z.string(),
}).refine((data) => data.password === data.password_confirmation, {
    message: "PASSWORDS_DO_NOT_MATCH",
    path: ["password_confirmation"],
});

export type SetPasswordValues = z.infer<typeof setPasswordSchema>;

export function useSetPasswordForm(onSubmit: (values: SetPasswordValues) => Promise<void>) {
    const form = useForm<SetPasswordValues>({
        resolver: zodResolver(setPasswordSchema),
        defaultValues: { password: "", password_confirmation: "" },
    });

    const handleFormSubmit = form.handleSubmit(async (values) => {
        await onSubmit(values);
    });

    return {
        form,
        handleFormSubmit,
        isSubmitting: form.formState.isSubmitting
    };
}