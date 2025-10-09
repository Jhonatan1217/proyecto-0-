<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/register_tables.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Proyecto 0 - Visualización de registro de tablas</title>
    </head>
    <body class="text-center font-sans">

    <h1 class="text-3xl font-bold mt-6">
        VISUALIZACIÓN DE REGISTRO TRIMESTRALIZACIÓN - ZONA 
        <?php echo isset($_GET['zona']) ? htmlspecialchars($_GET['zona']) : '—'; ?>
    </h1>
    <h2 class="text-xl text-gray-700 mb-6">
        Sistema de gestión de trimestralización <br> SENA
    </h2>

    <main class="flex flex-col items-center">
        <section id="tabla-horarios" class="w-11/12 max-h-[500px] overflow-y-auto">
        <table class="border border-gray-700 border-collapse w-full text-sm">
            <thead "sticky top-0 bg-green-600 text-white z-10">
            <tr class="bg-green-600 text-white">
                <th class="border border-gray-700 p-6">Hora</th>
                <th class="border border-gray-700 p-2">Lunes</th>
                <th class="border border-gray-700 p-2">Martes</th>
                <th class="border border-gray-700 p-2">Miércoles</th>
                <th class="border border-gray-700 p-2">Jueves</th>
                <th class="border border-gray-700 p-2">Viernes</th>
                <th class="border border-gray-700 p-2">Sábado</th>
            </tr>
            </thead>

            <tbody>
            <tr class="bg-gray-50">
            <td class="border border-gray-700 p-2 font-medium">6-7</td>

            <!-- Repite este bloque para cada día -->
            <td class="border border-gray-700 p-2"></td>
            <td class="border border-gray-700 p-2"></td>
            <td class="border border-gray-700 p-2"></td>
            <td class="border border-gray-700 p-2"></td>
            <td class="border border-gray-700 p-2"></td>
            <td class="border border-gray-700 p-2"></td>
            </tr>

            <!-- RESTO DE FILAS VACÍAS -->
            <tr class="bg-white">
                <td class="border border-gray-700 p-2">7-8</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
            </tr>
            <tr class="bg-gray-50">
                <td class="border border-gray-700 p-2">8-9</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
            </tr>
            <tr class="bg-gray-50">
                <td class="border border-gray-700 p-2">9-10</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
            </tr>
            <tr class="bg-gray-50">
                <td class="border border-gray-700 p-2">10-11</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
            </tr>
            <tr class="bg-white ">
                <td class="border border-gray-700 p-2">11-12</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
            </tr>
            <tr class="bg-white">
                <td class="border border-gray-700 p-2">12-13</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
            </tr>
            <tr class="bg-white">
                <td class="border border-gray-700 p-2">13-14</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
            </tr>
            <tr class="bg-white">
                <td class="border border-gray-700 p-2">14-15</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
            </tr>
            <tr class="bg-white">
                <td class="border border-gray-700 p-2">15-16</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
            </tr>
            <tr class="bg-white">
                <td class="border border-gray-700 p-2">16-17</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
            </tr>
            <tr class="bg-white">
                <td class="border border-gray-700 p-2">17-18</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
            </tr>
            <tr class="bg-white">
                <td class="border border-gray-700 p-2">18-19</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
            </tr>
            <tr class="bg-white">
                <td class="border border-gray-700 p-2">19-20</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
            </tr>
            <tr class="bg-white">
                <td class="border border-gray-700 p-2">20-21</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
            </tr>
            <tr class="bg-white">
                <td class="border border-gray-700 p-2">21-22</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
                <td class="border border-gray-700 p-2">&nbsp;</td>
            </tr>
            </tbody>
        </table>
        </section>

        <div class="mt-6 mb-6 flex gap-6">
        <button onclick="mostrarModalEliminar()" class="bg-[#00324D] text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">Eliminar Trimestralización</button>
        <button onclick="actualizar()" class="bg-[#00324D] text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">Actualizar Trimestralización</button>
        <button onclick="descargarPDF()" class="bg-[#00324D] text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition" style= "display: flex;">Descargar PDF<img src="src/assets/img/descargar.png" style = "margin-left: 5px; width: 20px; height: 20px"><img/></button>
        </div>
    </main>
    <div id="modalEliminar" class="modal-overlay">
        <div class="bg-white rounded-3xl shadow-2xl p-8 max-w-2xl w-11/12 border-4 border-red-600">
            <div class="warning-icon">
                <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <path d="M50 10 L90 80 L10 80 Z" fill="none" stroke="#dc2626" stroke-width="6" stroke-linejoin="round"/>
                    <circle cx="50" cy="65" r="3" fill="#dc2626"/>
                    <line x1="50" y1="35" x2="50" y2="55" stroke="#dc2626" stroke-width="6" stroke-linecap="round"/>
                </svg>
            </div>

            <h2 class="text-3xl font-bold text-center mb-8 text-gray-900">
                ¿Estas seguro de querer<br>eliminar la trimestralización?
            </h2>

            <div class="flex gap-6 justify-center">
                <button onclick="confirmarEliminar()" class="bg-green-600 hover:bg-green-700 text-white font-bold text-xl px-12 py-4 rounded-xl transition shadow-lg">
                    Aceptar
                </button>
                <button onclick="cerrarModal()" class="bg-red-600 hover:bg-red-700 text-white font-bold text-xl px-12 py-4 rounded-xl transition shadow-lg">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    <script src="<?= BASE_URL ?>src/assets/js/registerTables.js"></script>
</body>
</html>
