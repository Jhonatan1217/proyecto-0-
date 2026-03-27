<?php
?>

  <meta charset="utf-8">
  <title>Sistema de Gestión Académica</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Alertas -->
  <script src="<?= BASE_URL ?>src/assets/js/sweetalert2.all.min.js"></script>
  <!-- Estilos propios (competencias) -->
  <link rel="stylesheet" href="src/assets/css/gestionCompetencias.css" />

  <!-- ✨ Ajustes extra SOLO AÑADIDOS: flecha responsive y wrapper de filtros -->
  <style>
    /* Flecha personalizada para TODOS los selects con .select-nice */
    .select-nice {
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
      background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%238a8f98' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
      background-repeat:no-repeat;
      background-position:right 1rem center;
      background-size:16px 16px;
      padding-right:2.5rem;
    }

    /* En pantallas pequeñas */
    @media (max-width: 640px) {
      .select-nice {
        font-size: 0.9rem;
        background-position:right 0.9rem center;
      }

      /* 🔥 Grupo de filtros de competencias centrado y alineado */
      .competencias-filtros-wrapper {
        max-width: 100% !important;
        align-items: stretch !important;
      }
      .competencias-filtros-wrapper > * {
        width: 100%;
      }

      /* 🔥 Botón Nueva Competencia: mismo ancho, más delgado y centrado */
      #btnNewCompetency {
        width: 100% !important;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.55rem 1.1rem !important; /* menos alto */
        font-size: 0.95rem !important;
        border-radius: 0.9rem !important;
      }
    }

    /* En tablet también dejamos que el wrapper use todo el ancho si lo necesita */
    @media (max-width: 1024px) {
      .competencias-filtros-wrapper {
        max-width: 100% !important;
      }
    }

    /* =====================================================
       🔥 Ajustes tarjetas de COMPETENCIAS (blancas)
       - Letra del título más pequeña
       - Flecha/ícono visible al lado del texto
       - Info inferior en línea recta
       ===================================================== */

    /* Título dentro de cada tarjeta */
    #competenciesList .comp-title,
    #competenciesList h3 {
      font-size: 1rem !important;
      line-height: 1.25rem !important;
      font-weight: 600;
      white-space: normal;
    }

    /* Header de la tarjeta: título + iconos/chevron */
    #competenciesList .comp-header,
    #competenciesList .competencia-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem;
      overflow: visible !important; /* que no se corte la flecha */
    }

    /* Contenedor para “Código / Programa / Estado” en una sola línea */
    #competenciesList .comp-meta,
    #competenciesList .competencia-meta {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      flex-wrap: nowrap;
      white-space: nowrap;           /* intenta mantenerlo en línea recta */
    }

    /* Chips/tags dentro de la meta alineados */
    #competenciesList .comp-meta > * ,
    #competenciesList .competencia-meta > * {
      display: inline-flex;
      align-items: center;
    }

    /* Versión un poco más pequeña aún en móvil para que quepa mejor */
    @media (max-width: 640px) {
      #competenciesList .comp-title,
      #competenciesList h3 {
        font-size: 0.95rem !important;
        line-height: 1.2rem !important;
      }
    }

    /* ==========================================
       🔥 Texto del navbar: oculto en cel, visible en pantallas grandes
       ========================================== */
    .nav-label {
      display: none; /* móvil: solo iconos */
    }

    @media (min-width: 768px) { /* desde tablet hacia arriba (incluye tu Mac) */
      .nav-label {
        display: inline-flex !important;
      }
    }
  </style>
  <main class="max-w-7xl mx-auto px-4 py-8">
    <div class="w-full">

      <!-- Tabs: cambiamos de sección sin recargar -->
      <div class="bg-zinc-100 rounded-2xl p-1 flex items-center gap-1 justify-around">
        <!-- CARGA EXCEL -->
        <?php if ($cargo != 'INSTRUCTOR'): ?>
          <button
            data-tab-btn="upload"
            class="tab-btn flex items-center justify-center gap-2 px-4 py-2 rounded-xl w-full sm:w-auto text-zinc-700"
          >
            <img src="src/assets/img/upload-grey.svg" class="w-4 h-4" alt="icono carga excel">
            <span class="hidden sm:inline nav-label">Carga Excel</span>
          </button>
        <?php endif; ?>

        <!-- PROGRAMAS -->
        <button
          data-tab-btn="programs"
          class="tab-btn flex items-center justify-center gap-2 px-4 py-2 rounded-xl w-full sm:w-auto text-zinc-700"
        >
          <img src="src/assets/img/graduation-cap.svg" class="w-4 h-4" alt="icono programas">
          <span class="hidden sm:inline nav-label">Programas</span>
        </button>

        <!-- COMPETENCIAS -->
        <button
          data-tab-btn="competencies"
          class="tab-btn flex items-center justify-center gap-2 px-4 py-2 rounded-xl w-full sm:w-auto text-zinc-700"
        >
          <img src="src/assets/img/book-open.svg" class="w-4 h-4" alt="icono competencias">
          <span class="hidden sm:inline nav-label">Competencias</span>
        </button>

        <!-- RAE -->
        <button
          data-tab-btn="raes"
          class="tab-btn flex items-center justify-center gap-2 px-4 py-2 rounded-xl w-full sm:w-auto text-zinc-700"
        >
          <img src="src/assets/img/target.svg" class="w-4 h-4" alt="icono rae">
          <span class="hidden sm:inline nav-label">RAE</span>
        </button>
      </div>

      <!-- INCLUSIÓN DE LAS VISTAS PARCIALES -->
      <?php if ($cargo != 'INSTRUCTOR'): ?>
        <?php include 'carga_excel.php'; ?>
      <?php endif; ?>
      <?php include 'programas.php'; ?>
      <?php include 'competencias.php'; ?>
      <?php include 'raes.php'; ?>

    </div>
  </main>

  <!-- ========= SCRIPTS ========= -->
  <!-- Tabs: muestra una sección y oculta el resto -->
  <script>
        (function(){
        const btns = document.querySelectorAll('[data-tab-btn]');
        const panes = document.querySelectorAll('.tab-pane');

        function activate(key){
            panes.forEach(p => 
            p.classList.toggle('hidden', p.getAttribute('data-tab') !== key)
            );

            btns.forEach(b => {
            const on = b.getAttribute('data-tab-btn') === key;
            b.classList.toggle('tabs-pill-active', on);
            b.setAttribute('aria-selected', on ? 'true' : 'false');
            b.classList.toggle('text-zinc-900', on);
            b.classList.toggle('text-zinc-700', !on);
            });

            window.lucide?.createIcons();
        }

        btns.forEach(b => 
            b.addEventListener('click', () => {
            const tab = b.getAttribute('data-tab-btn');
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.pushState({}, '', url);
            activate(tab);
            })
        );

       const params = new URLSearchParams(window.location.search);

        let defaultTab = 'upload';

        <?php if ($cargo === 'INSTRUCTOR'): ?>
          defaultTab = 'programs';
        <?php endif; ?>

        const tabFromUrl = params.get('tab') || defaultTab;

        activate(tabFromUrl);
    })();
</script>

  <!-- Endpoints y flags globales que usan los JS -->
<script>
    // ESTA ES LA LÍNEA QUE TE FALTA PARA QUE EL JS SEPA QUIÉN ES EL USUARIO
    window.USER_CARGO = "<?= strtoupper($_SESSION['usuario_cargo'] ?? '') ?>";

    window.API_PROGRAMAS     = encodeURI('<?= BASE_URL ?? '' ?>src/controllers/ProgramasController.php');
    window.PROGRAMS_MANAGED_BY_API = true;
    window.API_COMPETENCIAS  = encodeURI('<?= BASE_URL ?? '' ?>src/controllers/CompetenciaController.php');
    window.API_RAES = encodeURI('<?= BASE_URL ?? '' ?>src/controllers/RaeController.php');
</script>

  <!-- Combobox / selects estilizados (Programas) -->
  <script src="<?= BASE_URL ?? '' ?>src/assets/js/components/combobox.js?v=8"></script>
  <!-- Módulos: cada uno maneja su CRUD/UX. El ?v= ayuda a romper caché -->
  <script src="<?= BASE_URL ?? '' ?>src/assets/js/gestionProgramas.js?v=6"></script>
  <script src="<?= BASE_URL ?? '' ?>src/assets/js/gestionCompetencias.js?v=3" defer></script>
  <script src="<?= BASE_URL ?? '' ?>src/assets/js/gestionRaes.js?v=2" defer></script>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof ComboboxComponent === 'undefined') return;
    if (typeof ComboboxComponent.enhanceSelectStyled === 'function') {
      ComboboxComponent.enhanceSelectStyled({ selector: '.select-academicos' });
    }
    if (typeof ComboboxComponent.enhance === 'function') {
      ComboboxComponent.enhance({
        selector: '.combobox-academicos-upload',
        placeholder: 'Buscar programa…',
        allowClear: true,
        restoreValueOnBlurWhenEmpty: true,
        clearValue: ''
      });
      ComboboxComponent.enhance({
        selector: '.combobox-academicos-filter-programa',
        placeholder: 'Buscar programa…',
        allowClear: true,
        restoreValueOnBlurWhenEmpty: true,
        clearValue: 'all'
      });
      ComboboxComponent.enhance({
        selector: '.combobox-academicos-filter-rae-comp',
        placeholder: 'Buscar competencia…',
        allowClear: true,
        restoreValueOnBlurWhenEmpty: true,
        clearValue: 'all'
      });
      ComboboxComponent.enhance({
        selector: '.combobox-rae-competency',
        placeholder: 'Buscar competencia…',
        allowClear: true,
        restoreValueOnBlurWhenEmpty: true,
        clearValue: '',
        forceDropup: true
      });
    }
  });
  </script>

  <!-- Agregar cargar programas dinámicamente -->
  <script>
    document.addEventListener("DOMContentLoaded", () => {

      const selectProgram = document.getElementById("upload_program");

      fetch("<?= BASE_URL ?>src/controllers/ProgramasController.php?accion=listar")
        .then(res => res.json())
        .then(programas => {

          if (!Array.isArray(programas)) return;

          programas.forEach(p => {
            selectProgram.innerHTML += `
              <option value="${p.id_programa}">
                ${p.nombre_programa}
              </option>`;
          });
          selectProgram.dispatchEvent(new Event('change', { bubbles: true }));
        })
        .catch(err => {
          console.error("Error cargando programas:", err);
          // Toast de error (solo toast)
          if (window.Swal) {
            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'error',
              title: 'No se pudieron cargar los programas',
              timer: 2500,
              showConfirmButton: false,
              timerProgressBar: true
            });
          }
        });
    });
  </script>


  <script>
    const inputExcel = document.getElementById('inputExcel');
    const fileNameSpan = document.getElementById('file-name');

    inputExcel.addEventListener('change', () => {
      const file = inputExcel.files[0];
      if (file) {
        fileNameSpan.textContent = `${file.name}`;
        fileNameSpan.classList.remove('hidden');
      } else {
        fileNameSpan.textContent = '';
        fileNameSpan.classList.add('hidden');
      }
    });
  </script>

  <!-- Implementar SweetAlert para la carga de Excel (SOLO TOASTS) -->
  <script>
    document.addEventListener("DOMContentLoaded", () => {

      const btnProcesar   = document.getElementById("btnProcesarExcel");
      const inputFile     = document.getElementById("inputExcel");
      const selectProgram = document.getElementById("upload_program");

      btnProcesar.addEventListener("click", function () {

        // Validar que hay un programa seleccionado (toast)
        if (selectProgram.value === "") {
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'warning',
            title: 'Seleccione un programa de formación',
            timer: 2200,
            showConfirmButton: false,
            timerProgressBar: true
          });
          return;
        }

        // Validar que hay un archivo seleccionado (toast)
        if (inputFile.files.length === 0) {
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'Seleccione un archivo Excel (.xlsx)',
            timer: 2200,
            showConfirmButton: false,
            timerProgressBar: true
          });
          return;
        }

        let formData = new FormData();
        formData.append("archivo", inputFile.files[0]);
        formData.append("programa", selectProgram.value);

        // Toast de carga persistente (con loading). Se cierra manualmente.
        Swal.fire({
          toast: true,
          position: 'top-end',
          title: 'Procesando archivo…',
          icon: 'info',
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        fetch("<?= BASE_URL ?>src/controllers/ETLController.php?accion=subir", {
          method: "POST",
          body: formData
        })
        .then(r => r.text())
        .then(r => {
          // Cerrar el loading
          Swal.close();

          // ✅ Notificar éxito
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Archivo procesado correctamente',
            timer: 2600,
            showConfirmButton: false,
            timerProgressBar: true
          });

          // Limpiar inputs después de éxito
          inputFile.value = '';
          selectProgram.value = '';

          // ✅ Lanzar evento global para que otros módulos recarguen datos
          window.dispatchEvent(new Event('excel-subido-ok'));
        })
        .catch(e => {
          console.error("ERROR:", e);

          // Cerrar el loading y mostrar error como toast
          Swal.close();
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'Error al procesar el archivo',
            timer: 2600,
            showConfirmButton: false,
            timerProgressBar: true
          });
        });

      });

    });
  </script>


  <!-- ==========================
       PAGINACIÓN FRONTEND (PROGRAMAS, COMPETENCIAS, RAE)
       ========================== -->
  <script src="src/assets/js/paginacion.js"></script>

</body>
</html>