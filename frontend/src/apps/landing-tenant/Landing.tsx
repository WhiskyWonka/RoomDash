import { useState, useEffect } from 'react';

export default function Landing() {
  const [hotelName, setHotelName] = useState<string>("");
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    async function getHotelInfo() {
      try {
        setLoading(true);
        setError(null);

        // 1. Extraer el subdominio (ej: "mypod" de "mypod.roomdash.test")
        // const hostname = window.location.hostname;
        // const slug = hostname.split('.')[0];

        // 2. Fetch al backend (Ajusta la URL a tu endpoint real de Laravel)
        const response = await fetch('/api/public/info', { // URL actualizada
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
        });

        if (!response.ok) {
          throw new Error(`Error ${response.status}: No se pudo obtener la información.`);
        }

        const data = await response.json();

        // 3. Guardar el nombre (suponiendo que el backend devuelve { name: "..." })
        setHotelName(data.name);

      } catch (err: any) {
        console.error("Fallo en la carga del hotel:", err);
        setError(err.message);
      } finally {
        setLoading(false);
      }
    }

    getHotelInfo();
  }, []);

  // --- VISTA DE CARGA ---
  if (loading) {
    return (
      <div className="min-h-screen flex flex-col items-center justify-center bg-gray-100">
        {/* Spinner animado con Tailwind */}
        <div className="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
        <p className="mt-4 text-gray-500 font-medium">Cargando hotel...</p>
      </div>
    );
  }

  // --- VISTA DE ERROR ---
  if (error) {
    return (
      <div className="min-h-screen flex flex-col items-center justify-center bg-gray-100">
        <h1 className="text-2xl font-bold text-red-500">Ups! Algo salió mal</h1>
        <p className="text-gray-600">{error}</p>
        <button 
          onClick={() => window.location.reload()}
          className="mt-4 px-4 py-2 bg-blue-600 text-white rounded shadow"
        >
          Reintentar
        </button>
      </div>
    );
  }

  // --- VISTA FINAL ---
  return (
    <div className="min-h-screen flex flex-col items-center justify-center bg-gray-100">
      <h1 className="text-4xl font-bold text-blue-600 uppercase tracking-wide">
        {hotelName || "Hotel Desconocido"}
      </h1>
      <p className="mt-4 text-gray-600 text-lg">Hace tu reserva ahora</p>
    </div>
  );
}