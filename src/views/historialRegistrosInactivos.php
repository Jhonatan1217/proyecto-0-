<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horarios Inactivos</title>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;600;700&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex flex-col min-h-screen font-sans bg-gray-100 text-gray-900">
    <div class="flex-1 max-w-6xl w-full mx-auto px-4 md:px-6 py-6 md:py-8">
        <!-- Header section with green title matching SENA branding -->
        <div class="bg-white rounded-lg shadow-sm p-6 md:p-8 mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-green-500 mb-2">Horarios Inactivos</h1>
            <p class="text-gray-600 text-sm">Visualice y edite los horarios inactivos del sistema</p>
        </div>

        <!-- Card-based layout using Tailwind flexbox -->
        <div class="flex flex-col gap-4">
            <!-- Schedule Card 1 -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 hover:shadow-md hover:translate-y-[-2px] transition-all duration-200">
                <div class="flex flex-col md:flex-row md:justify-between md:items-start pb-3 md:pb-4 border-b border-gray-100 mb-4">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-gray-600">1001</span>
                        <span class="text-lg font-bold text-gray-900">Lunes</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Hora Inicio y Fin</span>
                        <span class="text-sm font-medium text-gray-900">08:00 - 10:00</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">ID Zona</span>
                        <span class="text-sm font-medium text-gray-900">Z-01</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Area</span>
                        <span class="text-sm font-medium text-gray-900">Area de ejemplo</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Ficha</span>
                        <span class="text-sm font-medium text-gray-900">Ficha 308485</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Instructor</span>
                        <span class="text-sm font-medium text-gray-900">Juan Manuel Gonzales Torres</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Competencia</span>
                        <span class="text-sm font-medium text-gray-900">Desarrollo de ejemplo</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-100">
                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">Trimestre 1</span>
                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">RAE-001</span>
                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">Programa de ejemplo</span>
                </div>
            </div>

            <!-- Schedule Card 2 -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 hover:shadow-md hover:translate-y-[-2px] transition-all duration-200">
                <div class="flex flex-col md:flex-row md:justify-between md:items-start pb-3 md:pb-4 border-b border-gray-100 mb-4">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-gray-600">1002</span>
                        <span class="text-lg font-bold text-gray-900">Martes</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Horario</span>
                        <span class="text-sm font-medium text-gray-900">10:00 - 12:00</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">ID Zona</span>
                        <span class="text-sm font-medium text-gray-900">Z-02</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Area</span>
                        <span class="text-sm font-medium text-gray-900">Area de ejemplo</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Ficha</span>
                        <span class="text-sm font-medium text-gray-900">Ficha 308485</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Instructor</span>
                        <span class="text-sm font-medium text-gray-900">Juan Manuel Gonzales Torres</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Competencia</span>
                        <span class="text-sm font-medium text-gray-900">Desarrollo de ejemplo</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-100">
                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">Trimestre 2</span>
                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">RAE-002</span>
                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">Programa de ejemplo</span>
                </div>
            </div>

            <!-- Schedule Card 3 -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 hover:shadow-md hover:translate-y-[-2px] transition-all duration-200">
                <div class="flex flex-col md:flex-row md:justify-between md:items-start pb-3 md:pb-4 border-b border-gray-100 mb-4">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-gray-600">1003</span>
                        <span class="text-lg font-bold text-gray-900">Miércoles</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Horario</span>
                        <span class="text-sm font-medium text-gray-900">14:00 - 16:00</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">ID Zona</span>
                        <span class="text-sm font-medium text-gray-900">Z-01</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Area</span>
                        <span class="text-sm font-medium text-gray-900">Area de ejemplo</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Ficha</span>
                        <span class="text-sm font-medium text-gray-900">Ficha 308485</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Instructor</span>
                        <span class="text-sm font-medium text-gray-900">Juan Manuel Gonzales Torres</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Competencia</span>
                        <span class="text-sm font-medium text-gray-900">Desarrollo de ejemplo</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-100">
                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">Trimestre 1</span>
                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">RAE-003</span>
                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">Programa de ejemplo</span>
                </div>
            </div>

            <!-- Schedule Card 4 -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 hover:shadow-md hover:translate-y-[-2px] transition-all duration-200">
                <div class="flex flex-col md:flex-row md:justify-between md:items-start pb-3 md:pb-4 border-b border-gray-100 mb-4">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-gray-600">1004</span>
                        <span class="text-lg font-bold text-gray-900">Jueves</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Horario</span>
                        <span class="text-sm font-medium text-gray-900">08:00 - 10:00</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">ID Zona</span>
                        <span class="text-sm font-medium text-gray-900">Z-03</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Area</span>
                        <span class="text-sm font-medium text-gray-900">Area de ejemplo</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Ficha</span>
                        <span class="text-sm font-medium text-gray-900">Ficha 308485</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Instructor</span>
                        <span class="text-sm font-medium text-gray-900">Juan Manuel Gonzales Torres</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Competencia</span>
                        <span class="text-sm font-medium text-gray-900">Desarrollo de ejemplo</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-100">
                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">Trimestre 3</span>
                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">RAE-004</span>
                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">Programa de ejemplo</span>
                </div>
            </div>

            <!-- Schedule Card 5 -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 hover:shadow-md hover:translate-y-[-2px] transition-all duration-200">
                <div class="flex flex-col md:flex-row md:justify-between md:items-start pb-3 md:pb-4 border-b border-gray-100 mb-4">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-gray-600">1005</span>
                        <span class="text-lg font-bold text-gray-900">Viernes</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Horario</span>
                        <span class="text-sm font-medium text-gray-900">16:00 - 18:00</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">ID Zona</span>
                        <span class="text-sm font-medium text-gray-900">Z-02</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Area</span>
                        <span class="text-sm font-medium text-gray-900">Area de ejemplo</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Ficha</span>
                        <span class="text-sm font-medium text-gray-900">Ficha 308485</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Instructor</span>
                        <span class="text-sm font-medium text-gray-900">Juan Manuel Gonzales Torres</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Competencia</span>
                        <span class="text-sm font-medium text-gray-900">Desarrollo de ejemplo</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-100">
                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">Trimestre 2</span>
                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">RAE-005</span>
                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">Programa de ejemplo</span>
                </div>
            </div>

            <!-- Schedule Card 6 -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 hover:shadow-md hover:translate-y-[-2px] transition-all duration-200">
                <div class="flex flex-col md:flex-row md:justify-between md:items-start pb-3 md:pb-4 border-b border-gray-100 mb-4">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-gray-600">1006</span>
                        <span class="text-lg font-bold text-gray-900">Lunes</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Horario</span>
                        <span class="text-sm font-medium text-gray-900">12:00 - 14:00</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">ID Zona</span>
                        <span class="text-sm font-medium text-gray-900">Z-01</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Area</span>
                        <span class="text-sm font-medium text-gray-900">Area de ejemplo</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Ficha</span>
                        <span class="text-sm font-medium text-gray-900">Ficha 308485</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Instructor</span>
                        <span class="text-sm font-medium text-gray-900">Juan Manuel Gonzales Torres</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Competencia</span>
                        <span class="text-sm font-medium text-gray-900">Desarrollo de ejemplo</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-100">
                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">Trimestre 1</span>
                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">RAE-006</span>
                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">Programa de ejemplo</span>
                </div>
            </div>

            <!-- Schedule Card 7 -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 hover:shadow-md hover:translate-y-[-2px] transition-all duration-200">
                <div class="flex flex-col md:flex-row md:justify-between md:items-start pb-3 md:pb-4 border-b border-gray-100 mb-4">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-gray-600">1007</span>
                        <span class="text-lg font-bold text-gray-900">Martes</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Horario</span>
                        <span class="text-sm font-medium text-gray-900">14:00 - 16:00</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">ID Zona</span>
                        <span class="text-sm font-medium text-gray-900">Z-03</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Area</span>
                        <span class="text-sm font-medium text-gray-900">Area de ejemplo</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Ficha</span>
                        <span class="text-sm font-medium text-gray-900">Ficha 308485</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Instructor</span>
                        <span class="text-sm font-medium text-gray-900">Juan Manuel Gonzales Torres</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Competencia</span>
                        <span class="text-sm font-medium text-gray-900">Desarrollo de ejemplo</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-100">
                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">Trimestre 3</span>
                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">RAE-007</span>
                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">Programa de ejemplo</span>
                </div>
            </div>

            <!-- Schedule Card 8 -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 hover:shadow-md hover:translate-y-[-2px] transition-all duration-200">
                <div class="flex flex-col md:flex-row md:justify-between md:items-start pb-3 md:pb-4 border-b border-gray-100 mb-4">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-gray-600">1008</span>
                        <span class="text-lg font-bold text-gray-900">Miércoles</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Horario</span>
                        <span class="text-sm font-medium text-gray-900">08:00 - 10:00</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">ID Zona</span>
                        <span class="text-sm font-medium text-gray-900">Z-02</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Area</span>
                        <span class="text-sm font-medium text-gray-900">Area de ejemplo</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Ficha</span>
                        <span class="text-sm font-medium text-gray-900">Ficha 308485</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Instructor</span>
                        <span class="text-sm font-medium text-gray-900">Juan Manuel Gonzales Torres</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Competencia</span>
                        <span class="text-sm font-medium text-gray-900">Desarrollo de ejemplo</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-100">
                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">Trimestre 2</span>
                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">RAE-008</span>
                    <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600">Programa de ejemplo</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
