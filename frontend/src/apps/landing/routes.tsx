import { createBrowserRouter } from "react-router-dom";
import MainLanding from "./MainLanding";
import { ErrorPage } from "@/components/auth/ErrorPage";

export const landingRouter = createBrowserRouter([
    {
        path: "/",
        element: <MainLanding />,
        errorElement: <ErrorPage />,
    },
    // Si tienes páginas de "Términos", "Precios", etc, van aquí
]);