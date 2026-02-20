export default function MainLanding() {
  return (
    <div className="min-h-screen flex flex-col items-center justify-center bg-gray-100">
      <h1 className="text-4xl font-bold text-blue-600">ROOMDASH</h1>
      <p className="mt-4 text-gray-600">La plataforma definitiva para gestión hotelera.</p>
      <div className="mt-8 space-x-4">
        <a href="/admin" className="px-6 py-2 bg-blue-500 text-white rounded">
          Ir al Panel
        </a>
      </div>
    </div>
  );
}