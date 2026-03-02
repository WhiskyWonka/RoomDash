import api from "@/lib/api";

export const auditApi = {
    list: (params?: { page?: number; limit?: number }) => 
        api.get("/api/audit-logs", { params }),
    
    get: (id: string | number) => 
        api.get(`/api/audit-logs/${id}`),
};