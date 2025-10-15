<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Instructores</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-gray-900 font-sans">

  <div class="max-w-6xl mx-auto px-4 py-10">
    <!-- Encabezado -->
    <h1 class="text-4xl font-extrabold tracking-tight mb-2 text-[#39A900]">Gestión de Instructores</h1>
    <p class="text-gray-500 mb-6">Administra los instructores y sus tipos</p>

    <!-- Card -->
    <div class="bg-white shadow rounded-2xl border border-gray-200">
      <!-- Header card -->
      <div class="flex items-center justify-between p-6 border-b">
        <div>
          <h2 class="text-xl font-semibold">Instructores</h2>
          <p class="text-sm text-gray-500">Lista de todos los instructores registrados</p>
        </div>

        <!-- Botón Nuevo Instructor -->
        <button 
          id="btnAbrirModalInstructor"
          class="bg-[#00324D] text-white px-4 py-2 rounded-xl flex items-center gap-2 hover:bg-[#00273A] active:scale-[0.99] transition"
          type="button"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M12 5v14M5 12h14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span>Nuevo Instructor</span>
        </button>
      </div>

      <!-- Tabla -->
      <div class="overflow-x-auto">
        <table class="w-full text-left" id="tablaInstructores">
          <!-- Encabezado -->
          <thead>
            <tr class="text-gray-600 text-sm border-b">
              <th class="px-6 py-3 font-medium">Nombre</th>
              <th class="px-6 py-3 font-medium text-center">Tipo</th>
              <th class="px-6 py-3 font-medium text-right">Acciones</th>
            </tr>
          </thead>

          <!-- Cuerpo -->
          <tbody class="text-sm">
            <!-- Fila 1 -->
            <tr class="border-b">
              <td class="px-6 py-4 align-middle">Juan Pérez</td>
              <td class="px-6 py-4 align-middle text-center">
                <span class="bg-black text-white text-xs px-3 py-1 rounded-full">Mixto</span>
              </td>
              <td class="px-6 py-4 align-middle text-right">
                <div class="flex justify-end items-center gap-3">
                  <!-- Editar -->
                  <button class="btn-editar p-2 border rounded-xl hover:bg-gray-50 transition" type="button" title="Editar">
                    <img class="w-5 h-5" src="../assets/img/pencil-line.svg" alt="Editar" />
                  </button>
                  <!-- Switch -->
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-green-500 transition"></div>
                    <div class="absolute left-0.5 top-0.5 bg-white w-5 h-5 rounded-full transition peer-checked:translate-x-5"></div>
                  </label>
                </div>
              </td>
            </tr>

            <!-- Fila 2 -->
            <tr>
              <td class="px-6 py-4 align-middle">María García</td>
              <td class="px-6 py-4 align-middle text-center">
                <span class="bg-gray-100 text-gray-700 text-xs px-3 py-1 rounded-full">Tecnico</span>
              </td>
              <td class="px-6 py-4 align-middle text-right">
                <div class="flex justify-end items-center gap-3">
                  <button class="btn-editar p-2 border rounded-xl hover:bg-gray-50 transition" type="button" title="Editar">
                    <img class="w-5 h-5" src="../assets/img/pencil-line.svg" alt="Editar" />
                  </button>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-green-500 transition"></div>
                    <div class="absolute left-0.5 top-0.5 bg-white w-5 h-5 rounded-full transition peer-checked:translate-x-5"></div>
                  </label>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ========== MODAL: Nuevo Instructor ========== -->
  <div id="modalInstructor" class="fixed inset-0 z-50 hidden">
    <!-- Fondo (animable) -->
    <div id="modalBackdrop" class="absolute inset-0 bg-black/60 backdrop-blur-[1px] opacity-0 transition-opacity duration-200"></div>

    <!-- Contenedor -->
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div id="modalPanel"
        class="w-full max-w-[720px] bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 md:p-8 lg:p-10 relative
               opacity-0 scale-95 translate-y-2 transition-all duration-200 ease-out">
        
        <!-- Botón cerrar -->
        <button id="btnCerrarModalInstructor"
          class="absolute right-4 top-4 p-2 rounded-full hover:bg-gray-100 transition"
          type="button">✕</button>

        <!-- Contenido -->
        <div class="space-y-6">
          <div>
            <h3 class="text-2xl font-semibold">Nuevo Instructor</h3>
            <p class="text-gray-400 mt-1">Ingresa los datos del nuevo instructor</p>
          </div>

          <!-- Formulario -->
          <form id="formNuevoInstructor" class="space-y-6">
            <!-- Nombre -->
            <div class="space-y-2">
              <label class="block text-sm font-semibold">Nombre del Instructor</label>
              <input type="text" placeholder="Ej: Juan Pérez"
                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm placeholder:text-gray-400
                       focus:ring-0 focus:outline-none focus:border-gray-300" />
            </div>

            <!-- Tipo -->
            <div class="space-y-2">
              <label class="block text-sm font-semibold">Tipo de Instructor</label>
              <div class="relative">
                <select
                  class="w-full appearance-none rounded-xl border border-gray-200 bg-white px-4 py-3 pr-10 shadow-sm
                         focus:ring-0 focus:outline-none focus:border-gray-300">
                  <option disabled selected>Seleccione un tipo</option>
                  <option>Tecnico</option>
                  <option>Transversal</option>
                  <option>Mixto</option>
                </select>
                <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.18l3.71-3.95a.75.75 0 111.08 1.04l-4.24 4.52a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                </svg>
              </div>
            </div>

            <!-- Acciones -->
            <div class="pt-2 flex items-center justify-end gap-4">
              <button type="button" id="btnCancelarModalInstructor"
                class="px-5 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 transition">
                Cancelar
              </button>
              <button type="submit"
                class="px-6 py-2.5 rounded-xl bg-[#00324D] text-white hover:bg-[#00273A] transition">
                Crear Instructor
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- ========== /MODAL ========== -->

  <!-- JS -->
  <script>
    (() => {
      /* ---------- Modal existente (sin cambios) ---------- */
      const openBtn  = document.getElementById('btnAbrirModalInstructor');
      const modal    = document.getElementById('modalInstructor');
      const panel    = document.getElementById('modalPanel');
      const backdrop = document.getElementById('modalBackdrop');
      const closeBtn = document.getElementById('btnCerrarModalInstructor');
      const cancelBt = document.getElementById('btnCancelarModalInstructor');

      const open = () => {
        modal.classList.remove('hidden');
        panel.classList.add('opacity-0','scale-95','translate-y-2');
        backdrop.classList.add('opacity-0');
        requestAnimationFrame(() => {
          panel.classList.remove('opacity-0','scale-95','translate-y-2');
          panel.classList.add('opacity-100','scale-100','translate-y-0');
          backdrop.classList.remove('opacity-0');
          backdrop.classList.add('opacity-100');
        });
      };

      const close = () => {
        panel.classList.remove('opacity-100','scale-100','translate-y-0');
        panel.classList.add('opacity-0','scale-95','translate-y-2');
        backdrop.classList.remove('opacity-100');
        backdrop.classList.add('opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 200);
      };

      openBtn?.addEventListener('click', open);
      closeBtn?.addEventListener('click', close);
      cancelBt?.addEventListener('click', close);
      backdrop?.addEventListener('click', (e) => { if (e.target === backdrop) close(); });
      window.addEventListener('keydown', (e) => { if (!modal.classList.contains('hidden') && e.key === 'Escape') close(); });
      document.getElementById('formNuevoInstructor')?.addEventListener('submit', (e) => { e.preventDefault(); close(); });

      /* ---------- Edición inline en la tabla ---------- */
      const tabla = document.getElementById('tablaInstructores');

      // Utilidad: devuelve HTML de pill por tipo
      const pillHTML = (tipo) => {
        const t = (tipo || '').trim();
        const negro = 'bg-black text-white';
        const gris  = 'bg-gray-100 text-gray-700';
        const cls = (t.toLowerCase() === 'mixto') ? negro : gris;
        return `<span class="${cls} text-xs px-3 py-1 rounded-full">${t || '—'}</span>`;
      };

      // Delegación de eventos para botón Editar / Guardar / Cancelar
      tabla.addEventListener('click', (e) => {
        const btn = e.target.closest('button');
        if (!btn) return;

        // Guardar
        if (btn.classList.contains('btn-guardar')) {
          const tr = btn.closest('tr');
          const nombreInput = tr.querySelector('input[data-edit="nombre"]');
          const tipoSelect  = tr.querySelector('select[data-edit="tipo"]');
          const tdNombre = tr.children[0];
          const tdTipo   = tr.children[1];
          const tdAcc    = tr.children[2];

          // Set nuevos valores
          const nuevoNombre = nombreInput.value.trim() || tr.dataset.origNombre || '';
          const nuevoTipo   = tipoSelect.value;

          tdNombre.innerHTML = nuevoNombre;
          tdTipo.innerHTML   = `<div class="text-center">${pillHTML(nuevoTipo)}</div>`;

          // Restaurar acciones (mostrar boton editar y quitar guardar/cancelar)
          const accionesBox = tdAcc.querySelector('.flex');
          accionesBox.querySelector('.btn-editar')?.classList.remove('hidden');
          accionesBox.querySelector('.btn-guardar')?.remove();
          accionesBox.querySelector('.btn-cancelar')?.remove();

          // Limpia dataset
          delete tr.dataset.editing;
          delete tr.dataset.origNombre;
          delete tr.dataset.origTipo;
          return;
        }

        // Cancelar
        if (btn.classList.contains('btn-cancelar')) {
          const tr = btn.closest('tr');
          const tdNombre = tr.children[0];
          const tdTipo   = tr.children[1];
          const tdAcc    = tr.children[2];

          tdNombre.textContent = tr.dataset.origNombre || tdNombre.textContent;
          tdTipo.innerHTML = `<div class="text-center">${pillHTML(tr.dataset.origTipo || '')}</div>`;

          const accionesBox = tdAcc.querySelector('.flex');
          accionesBox.querySelector('.btn-editar')?.classList.remove('hidden');
          accionesBox.querySelector('.btn-guardar')?.remove();
          accionesBox.querySelector('.btn-cancelar')?.remove();

          delete tr.dataset.editing;
          delete tr.dataset.origNombre;
          delete tr.dataset.origTipo;
          return;
        }

        // Editar
        if (btn.classList.contains('btn-editar')) {
          const tr = btn.closest('tr');
          if (tr.dataset.editing === '1') return; // ya en edición

          const tdNombre = tr.children[0];
          const tdTipo   = tr.children[1];
          const tdAcc    = tr.children[2];

          // Guardar valores originales
          tr.dataset.origNombre = tdNombre.textContent.trim();
          const tipoActualText = (tdTipo.textContent || '').trim();
          tr.dataset.origTipo = tipoActualText;
          tr.dataset.editing = '1';

          // Reemplazar por inputs
          tdNombre.innerHTML = `
            <input data-edit="nombre" type="text"
              class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 shadow-sm placeholder:text-gray-400
                     focus:ring-0 focus:outline-none focus:border-gray-300"
              value="${tr.dataset.origNombre}">
          `;

          tdTipo.innerHTML = `
            <div class="relative max-w-full">
              <select data-edit="tipo"
                class="w-full appearance-none rounded-xl border border-gray-200 bg-white px-3 py-2 pr-9 shadow-sm
                       focus:ring-0 focus:outline-none focus:border-gray-300 text-center">
                <option ${tipoActualText==='Tecnico'?'selected':''}>Tecnico</option>
                <option ${tipoActualText==='Transversal'?'selected':''}>Transversal</option>
                <option ${tipoActualText==='Mixto'?'selected':''}>Mixto</option>
              </select>
              <svg class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.18l3.71-3.95a.75.75 0 111.08 1.04l-4.24 4.52a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
              </svg>
            </div>
          `;

          // Acciones: ocultar editar y añadir Guardar/Cancelar
          const accionesBox = tdAcc.querySelector('.flex');
          btn.classList.add('hidden');

          const btnGuardar = document.createElement('button');
          btnGuardar.type = 'button';
          btnGuardar.className = 'btn-guardar px-3 py-2 rounded-xl border border-green-600 text-green-700 hover:bg-green-50 transition';
          btnGuardar.textContent = 'Guardar';

          const btnCancelar = document.createElement('button');
          btnCancelar.type = 'button';
          btnCancelar.className = 'btn-cancelar px-3 py-2 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 transition';
          btnCancelar.textContent = 'Cancelar';

          accionesBox.insertBefore(btnCancelar, accionesBox.lastElementChild); // antes del switch
          accionesBox.insertBefore(btnGuardar, btnCancelar);
        }
      });
    })();
  </script>
</body>
</html>
