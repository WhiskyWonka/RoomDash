import api, { createResource } from "@/lib/api";
import type { User, CreateUserInput, UpdateUserInput } from "@/types/user";

const baseUsers = createResource<User, CreateUserInput, UpdateUserInput>("/api/users");

export const usersApi = {
    ...baseUsers,

    activate: (id: string | number) =>
        api.patch<User>(`/api/users/${id}/activate`),

    deactivate: (id: string | number) =>
        api.patch<User>(`/api/users/${id}/deactivate`),

    resendVerification: (id: string | number) =>
        api.post<void>(`/api/users/${id}/resend-verification`),
};