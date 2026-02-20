<?php
// Cargar datos desde la base de datos para los selects
require_once __DIR__ . '/../../config/database.php';

$areas = [];
$zonas = [];
$instructores = [];
$trimestres = [];
$competencias = [];
$programas = []; // <- NUEVO: arreglo para los programas

try {
    if (isset($conn)) {
        // Áreas
        $s = $conn->prepare("SELECT id_area, nombre_area FROM areas WHERE estado = 1 ORDER BY nombre_area ASC");
        $s->execute();
        $areas = $s->fetchAll(PDO::FETCH_ASSOC);

        // Zonas
        $s = $conn->prepare("SELECT id_zona, id_area FROM zonas WHERE estado = 1 ORDER BY id_zona ASC");
        $s->execute();
        $zonas = $s->fetchAll(PDO::FETCH_ASSOC);

        // Instructores
        $s = $conn->prepare("SELECT nombre_instructor, tipo_instructor FROM instructores WHERE estado = 1 ORDER BY nombre_instructor ASC");
        $s->execute();
        $instructores = $s->fetchAll(PDO::FETCH_ASSOC);

        // Trimestres
        $s = $conn->prepare("SELECT numero_trimestre, estado FROM trimestre WHERE estado = 1 ORDER BY numero_trimestre ASC");
        $s->execute();
        $trimestres = $s->fetchAll(PDO::FETCH_ASSOC);

        // Programas de formación
        $s = $conn->prepare("
            SELECT id_programa, nombre_programa
            FROM programas
            WHERE estado = 1
            ORDER BY nombre_programa ASC
        ");
        $s->execute();
        $programas = $s->fetchAll(PDO::FETCH_ASSOC);

        // Competencias
        $s = $conn->prepare("SELECT id_competencia, nombre_competencia, id_programa FROM competencias WHERE estado = 1 ORDER BY nombre_competencia ASC");
        $s->execute();
        $competencias = $s->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // No interrumpo la vista si falla la carga, se muestran los selects vacíos
}
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Proyecto Z</title>

    <!-- Fuente Work Sans (el modal la usa) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/fonts.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/formulario_crear_trimestralizacion.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- SweetAlert2 -->
    <script src="<?= BASE_URL ?>src/assets/js/sweetalert2.all.min.js"></script>

    <style>
      #modalCard .form-grid { display: block; }

      @media (min-width: 768px) and (max-width: 1600px) {
        #modalCard .form-grid {
          display: grid;
          grid-template-columns: repeat(2, minmax(0, 1fr));
          column-gap: 0.75rem;
          row-gap: 0.75rem;
        }
        #modalCard .form-grid .field-full { grid-column: span 2; }
      }

      @media (min-width: 1601px) {
        #modalCard .form-grid { display: block; }
        #modalCard .form-grid .field-full { grid-column: auto; }
      }

      @media (max-width: 640px) {
        #modalCard .form-grid .field,
        #modalCard .form-grid .field-full { margin-bottom: 12px; }

        #modalCard select,
        #modalCard input {
          white-space: normal;
          line-height: 1.3;
          font-size: 14px;
          padding-right: 2.5rem;
        }

        #formTrimestralizacion {
          margin-bottom: 20px;
          padding-bottom: 12px;
        }

        #modalWrapperCrear {
          align-items: flex-start;
          padding-top: 1.7rem;
          padding-bottom: 1.7rem;
        }

        #modalCard {
          margin-left: 1rem;
          margin-right: 1rem;
          max-height: calc(100vh - 3rem);
          overflow-y: auto;
          padding-bottom: 25px !important;
        }
      }
    </style>

  </head>
  <body class="flex flex-col min-h-screen font-sans bg-white text-gray-900 relative overflow-x-hidden">

    <!-- Contenido principal -->
    <main class="relative z-10 flex-1 flex flex-col lg:flex-row items-center lg:items-start justify-between gap-10 lg:gap-16 px-6 sm:px-10 lg:px-16 xl:px-24 2xl:px-32 pt-20 sm:pt-24 lg:pt-32 pb-16 max-w-[1440px] mx-auto w-full">

      <!-- Columna izquierda: texto y botones -->
      <div class="flex flex-col items-start max-w-xl lg:max-w-lg xl:max-w-xl w-full">
        <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-extrabold text-[#39A900] mb-4 leading-tight tracking-tight">
          PROYECTO Z
        </h1>
        <h2 class="text-lg sm:text-xl lg:text-2xl xl:text-3xl font-bold text-[#1a1a2e] mb-8 leading-snug">
          Crea y ajusta horarios en segundos
        </h2>

        <p class="text-sm sm:text-base text-gray-400 leading-relaxed mb-12 max-w-md">
           Gestiona fácilmente las trimestralizaciones, asigna competencias, instructores y horarios de manera rápida y organizada.
          Optimiza la planificación académica centralizando toda la información en un solo lugar, reduce errores manuales y mejora la coordinación entre equipos.
          Visualiza, edita y ajusta la programación en tiempo real para garantizar una distribución eficiente de recursos y un seguimiento claro del avance formativo.
        </p>

        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
          <!-- Boton Iniciar Sesion (outline, pill) -->
          <a href="<?= BASE_URL ?>index.php?page=src/views/login"
            class="inline-flex items-center justify-center min-w-[180px] sm:min-w-[200px] px-8 py-3 border border-gray-300 text-sm rounded-full text-[#1a1a2e] font-semibold bg-white hover:bg-gray-50 transition-colors duration-200 cursor-pointer no-underline">
            Iniciar Sesion
          </a>

          <!-- Boton Ver horario (green, pill) -->
          <a href="<?= BASE_URL ?>index.php?page=src/views/register_tables"
            class="inline-flex items-center justify-center min-w-[180px] sm:min-w-[200px] px-8 py-3 border-0 text-sm rounded-full text-white font-semibold bg-[#39A900] hover:bg-[#2d8a00] transition-colors duration-200 cursor-pointer shadow-lg shadow-green-300/40">
            Ver horario
          </a>
        </div>
      </div>

      <!-- Columna derecha: tarjeta Horario Semanal -->
      <div class="w-full lg:w-auto flex-shrink-0 flex justify-center lg:justify-end mt-2 lg:mt-4">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 px-5 sm:px-7 py-6 sm:py-7 w-full max-w-[560px] lg:max-w-[600px] xl:max-w-[660px]">

          <!-- Cabecera -->
          <div class="flex items-center justify-between mb-5">
            <h3 class="text-sm sm:text-base font-bold text-[#1a1a2e]">Horario Semanal</h3>
            <span class="text-xs text-gray-400 font-medium">Enero 2026</span>
          </div>

          <!-- Tabla de horario -->
          <div class="overflow-x-auto">
            <table class="w-full border-collapse" style="min-width:460px">
              <thead>
                <tr>
                  <th class="pb-3 px-1 text-xs sm:text-sm font-semibold text-[#1a1a2e] text-center w-1/5">Lun</th>
                  <th class="pb-3 px-1 text-xs sm:text-sm font-semibold text-[#1a1a2e] text-center w-1/5">Mar</th>
                  <th class="pb-3 px-1 text-xs sm:text-sm font-semibold text-[#1a1a2e] text-center w-1/5">Mie</th>
                  <th class="pb-3 px-1 text-xs sm:text-sm font-semibold text-[#1a1a2e] text-center w-1/5">Jue</th>
                  <th class="pb-3 px-1 text-xs sm:text-sm font-semibold text-[#1a1a2e] text-center w-1/5">Vie</th>
                </tr>
              </thead>
              <tbody>
                <!-- Fila 1 -->
                <tr>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-green-50 text-green-700 border border-green-200">Investigacion</span>
                  </td>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">Derechos</span>
                  </td>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-green-50 text-green-700 border border-green-200">Calidad</span>
                  </td>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-gray-50 text-gray-600 border border-gray-200">Comunicacion</span>
                  </td>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-gray-50 text-gray-600 border border-gray-200">Finanzas</span>
                  </td>
                </tr>
                <!-- Fila 2 -->
                <tr>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-green-50 text-green-700 border border-green-200">Ingles</span>
                  </td>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">Python</span>
                  </td>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-gray-50 text-gray-600 border border-gray-200">Negocios</span>
                  </td>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-green-50 text-green-700 border border-green-200">Analisis</span>
                  </td>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-gray-50 text-gray-600 border border-gray-200">Gestion</span>
                  </td>
                </tr>
                <!-- Fila 3 -->
                <tr>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-gray-50 text-gray-500 border border-gray-100">Ingles</span>
                  </td>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-green-50 text-green-700 border border-green-200">Deporte</span>
                  </td>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-gray-50 text-gray-600 border border-gray-200">Negocios</span>
                  </td>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-gray-50 text-gray-600 border border-gray-200">Analisis</span>
                  </td>
                  <td class="py-1.5 px-1 text-center">
                    <span class="inline-block w-full px-2 py-2 rounded-lg text-[11px] sm:text-xs font-medium bg-gray-50 text-gray-600 border border-gray-200">Gestion</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>

    <!-- ============== MODAL CREAR TRIMESTRALIZACION ============== -->
    <!-- (Los modales, scripts y toda la logica JS se mantienen exactamente igual) -->
    <!-- ... resto del archivo sin cambios ... -->
    <!-- ============== /MODAL DUPLICAR ============== -->

    <script>
      window.BASE_URL = window.BASE_URL || "<?= BASE_URL ?>";
    </script>

    <script src="<?= BASE_URL ?>src/assets/js/landing.js"></script>
    <script src="<?= BASE_URL ?>src/assets/js/formulario_trimestralizacion.js"></script>

    <!-- Filtro zonas por área -->
    <script>
      (function(){
        const selArea = document.getElementById('id_area');
        const selZona = document.getElementById('id_zona');
        if (!selArea || !selZona) return;

        function filterZonas() {
          const areaVal = selArea.value;

          for (const opt of selZona.options) {
            if (opt.value === "") {
              opt.hidden = false;
              opt.disabled = false;
              continue;
            }
            const optArea = opt.dataset.area ?? "";
            const show = areaVal !== "" ? (String(optArea) === String(areaVal)) : true;
            opt.hidden = !show;
            opt.disabled = !show;
          }

          const selectedOpt = selZona.selectedOptions[0];
          if (selectedOpt && selectedOpt.hidden) selZona.value = "";
        }

        selArea.addEventListener('change', filterZonas);
        document.addEventListener('DOMContentLoaded', filterZonas);
      })();
    </script>

    <!-- Filtro competencias por programa -->
    <script>
      (function () {
        const selProg = document.getElementById('id_programa_select');
        const selComp = document.getElementById('id_competencia');
        if (!selProg || !selComp) return;

        function filtrarCompetenciasPorPrograma() {
          const progVal = selProg.value;

          for (const opt of selComp.options) {
            if (opt.value === "") {
              opt.hidden = false;
              opt.disabled = false;
              continue;
            }
            const optProg = opt.dataset.programa ?? "";
            const show = progVal !== "" && (String(optProg) === String(progVal));
            opt.hidden = !show;
            opt.disabled = !show;
          }

          const selectedOpt = selComp.selectedOptions[0];
          if (selectedOpt && selectedOpt.hidden) {
            selComp.value = "";
          }
        }

        selProg.addEventListener('change', filtrarCompetenciasPorPrograma);
        document.addEventListener('DOMContentLoaded', filtrarCompetenciasPorPrograma);
      })();
    </script>

    <!-- Modal RAEs -->
    <script>
    (function () {
        const BASE_URL = window.BASE_URL || '';
        const API_RAES = (BASE_URL + 'src/controllers/RaeController.php?accion=listar').replace(/\/+$/, '');
        
        const form = document.getElementById('formTrimestralizacion');
        if (!form) return;

        const selComp = document.getElementById('id_competencia');
        const hiddenRaes = document.getElementById('id_rae_field');
        const resumenRaes = document.getElementById('textoResumenRaes');
        const btnRaes = document.getElementById('btnSeleccionarRaes');

        const modalRaes = document.getElementById('modalRaes');
        const backdropRaes = document.getElementById('modalRaesBackdrop');
        const btnCerrarModalRaes = document.getElementById('btnCerrarModalRaes');
        const btnCancelarRaes = document.getElementById('btnCancelarRaes');
        const btnGuardarRaes = document.getElementById('btnGuardarRaes');
        const listaRaesModal = document.getElementById('listaRaesModal');
        const chkRaesTodos = document.getElementById('chkRaesTodos');
        const contadorRaesSeleccionadas = document.getElementById('contadorRaesSeleccionadas');
        const subtituloModalRaes = document.getElementById('subtituloModalRaes');

        if (!selComp || !btnRaes || !modalRaes) return;

        function toast(msg, type = 'info') {
          if (window.Swal) {
            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: type,
              title: msg,
              showConfirmButton: false,
              timer: 2200,
              timerProgressBar: true
            });
          } else {
            alert(msg);
          }
        }

        function actualizarResumen() {
          const valor = (hiddenRaes.value || '').trim();
          if (!valor) {
            resumenRaes.textContent = 'No hay RAEs seleccionadas.';
            return;
          }
          const partes = valor.split(',').map(v => v.trim()).filter(Boolean);
          if (!partes.length) {
            resumenRaes.textContent = 'No hay RAEs seleccionadas.';
            return;
          }
          resumenRaes.textContent = partes.length === 1
            ? '1 RAE seleccionada.'
            : partes.length + ' RAEs seleccionadas.';
        }

        function abrirModalRaes() {
          modalRaes.classList.remove('hidden');
        }

        function cerrarModalRaes() {
          modalRaes.classList.add('hidden');
        }

        function contarSeleccionadas() {
          const checks = listaRaesModal.querySelectorAll('.chk-rae-modal:checked');
          const cantidad = checks.length;

          contadorRaesSeleccionadas.textContent =
            cantidad === 0 ? '' :
            cantidad === 1 ? '1 RAE seleccionada' :
            cantidad + ' RAEs seleccionadas';
        }

        function actualizarHiddenAuto() {
          const checks = listaRaesModal.querySelectorAll('.chk-rae-modal:checked');
          const ids = Array.from(checks).map(ch => ch.value);
          hiddenRaes.value = ids.join(',');
          actualizarResumen();
        }

        function aplicarSeleccionTodos() {
          const checks = listaRaesModal.querySelectorAll('.chk-rae-modal');
          const checked = chkRaesTodos.checked;
          checks.forEach(ch => { ch.checked = checked; });
          contarSeleccionadas();
          actualizarHiddenAuto();
        }

        async function cargarRaesPorCompetencia(idComp) {
          listaRaesModal.innerHTML = '<p class="text-gray-500 text-xs">Cargando RAEs...</p>';
          chkRaesTodos.checked = false;
          contadorRaesSeleccionadas.textContent = '';

          try {
            const resp = await fetch(API_RAES + '&id_competencia=' + encodeURIComponent(idComp));
            const data = await resp.json();

            const lista = Array.isArray(data) ? data : (data.data || []);
            if (!lista.length) {
              listaRaesModal.innerHTML = '<p class="text-gray-500 text-xs">No hay RAEs asociadas a esta competencia.</p>';
              hiddenRaes.value = "";
              actualizarResumen();
              return;
            }

            const seleccionadasPrevias = (hiddenRaes.value || '').split(',')
              .map(v => v.trim())
              .filter(Boolean);

            const frag = document.createDocumentFragment();

            lista.forEach((r) => {
              const id = r.id_rae || r.id || r.ID_RAE;
              const codigo = r.codigo_rae || r.codigo || r.codigoRAE || '';
              const desc = r.descripcion || r.descripcion_rae || r.nombre_rae || r.nombre || '';

              if (!id) return;

              const label = document.createElement('label');
              label.className = 'flex items-start gap-2 py-1 border-b border-gray-100 last:border-b-0 cursor-pointer text-[11px] sm:text-xs text-gray-800';

              const input = document.createElement('input');
              input.type = 'checkbox';
              input.value = id;
              input.className = 'mt-[3px] chk-rae-modal rounded border-gray-300';

              if (seleccionadasPrevias.includes(String(id))) {
                input.checked = true;
              }

              const span = document.createElement('span');
              span.innerHTML = (codigo ? ('<strong>' + codigo + '</strong> — ') : '') +
                               (desc || '(sin descripción)');

              label.appendChild(input);
              label.appendChild(span);
              frag.appendChild(label);
            });

            listaRaesModal.innerHTML = '';
            listaRaesModal.appendChild(frag);

            contarSeleccionadas();
            actualizarHiddenAuto();
          } catch (err) {
            console.error(err);
            listaRaesModal.innerHTML = '<p class="text-red-500 text-xs">Error al cargar las RAEs.</p>';
          }
        }

        function toggleBotonRaes() {
          btnRaes.disabled = !selComp.value;
        }

        toggleBotonRaes();
        actualizarResumen();

        selComp.addEventListener('change', () => {
          toggleBotonRaes();
          hiddenRaes.value = "";
          actualizarResumen();
        });

        btnRaes.addEventListener('click', async () => {
          const idComp = selComp.value;
          if (!idComp) {
            toast('Primero selecciona una competencia.', 'warning');
            return;
          }

          const opt = selComp.selectedOptions[0];
          const nombreComp = opt ? (opt.textContent || '').trim() : '';
          subtituloModalRaes.textContent = nombreComp;

          await cargarRaesPorCompetencia(idComp);
          abrirModalRaes();
        });

        [btnCerrarModalRaes, btnCancelarRaes].forEach(btn => {
          if (btn) btn.addEventListener('click', cerrarModalRaes);
        });

        if (backdropRaes) backdropRaes.addEventListener('click', cerrarModalRaes);

        chkRaesTodos.addEventListener('change', aplicarSeleccionTodos);

        listaRaesModal.addEventListener('change', (e) => {
          if (e.target.classList.contains('chk-rae-modal')) {
            contarSeleccionadas();
            actualizarHiddenAuto();
          }
        });

        btnGuardarRaes.addEventListener('click', () => {
          cerrarModalRaes();
        });
    })();
    </script>

  </body>
</html>
