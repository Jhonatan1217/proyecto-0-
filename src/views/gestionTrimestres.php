<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Gestión de Trimestres</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-gray-900 font-sans">

  <div class="max-w-6xl mx-auto px-4 py-10">
    <!-- Encabezado -->
    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-1 text-[#2a7f00]">Gestión de Trimestres</h1>
    <p class="text-gray-600 mb-8">Administra los trimestres</p>

    <!-- Card -->
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm">
      <!-- Header card -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Trimestres</h2>
        </div>

        <!-- Botón Nuevo Trimestre -->
        <button 
          id="btnAbrirModalTrimestre"
          class="bg-[#00324D] text-white px-4 py-2 rounded-xl flex items-center gap-2 hover:bg-[#00273A] transition"
          type="button"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M12 5v14M5 12h14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span>Nuevo Trimestre</span>
        </button>
      </div>

      <!-- Tabla -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-gray-800" id="tablaTrimestres">
          <!-- Encabezado -->
          <thead>
            <tr class="border-b bg-gray-50 text-sm text-gray-700">
              <th class="px-6 py-3 font-semibold">N° Trimestre</th>
              <th class="px-6 py-3 font-semibold text-right">Acciones</th>
            </tr>
          </thead>

          <!-- Cuerpo -->
          <tbody class="text-sm">
            <!-- Ejemplo de fila - la puedes generar dinámicamente desde tu backend -->
            <tr class="border-b" data-id="1">
              <td class="px-6 py-4 align-middle">Trimestre 1</td>
              <td class="px-6 py-4 align-middle text-right">
                <div class="flex justify-end items-center gap-3">
                  <!-- Editar -->
                  <button class="btn-editar p-2 border rounded-xl hover:bg-gray-100 transition" type="button" title="Editar">
                    <img class="w-5 h-5" src="<?= BASE_URL ?>src/assets/img/pencil-line.svg" alt="Editar" />
                  </button>
                  <!-- Switch -->
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#2a7f00] transition"></div>
                    <div class="absolute left-0.5 top-0.5 bg-white w-5 h-5 rounded-full transition peer-checked:translate-x-5"></div>
                  </label>
                </div>
              </td>
            </tr>

            <tr class="border-b" data-id="2">
              <td class="px-6 py-4 align-middle">Trimestre 5</td>
              <td class="px-6 py-4 align-middle text-right">
                <div class="flex justify-end items-center gap-3">
                  <button class="btn-editar p-2 border rounded-xl hover:bg-gray-100 transition" type="button" title="Editar">
                    <img class="w-5 h-5" src="<?= BASE_URL ?>src/assets/img/pencil-line.svg" alt="Editar" />
                  </button>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#2a7f00] transition"></div>
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

  <!-- Modal: Nuevo Trimestre -->
  <div id="modalTrimestre" class="fixed inset-0 z-50 hidden">
    <div id="backdrop" class="absolute inset-0 bg-black/70 opacity-0 transition-opacity duration-200"></div>

    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div id="modalPanel" class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6 opacity-0 scale-95 transition-all duration-200">
        <button id="btnCerrarModalTrimestre" class="absolute right-4 top-4 p-2 rounded-full hover:bg-gray-100 transition" type="button">✕</button>

        <h3 class="text-xl font-semibold mb-1">Nuevo Trimestre</h3>
        <p class="text-sm text-gray-500 mb-5">Ingresa el número de trimestre</p>

        <form id="formNuevoTrimestre" class="space-y-5">
          <div>
            <label class="block text-sm font-semibold mb-1">Número de trimestre</label>
            <input id="inputNumeroTrimestre" type="number" placeholder="Ej: 1"
              class="w-full rounded-xl border border-gray-300 px-4 py-2 focus:border-[#2a7f00] focus:ring-0 outline-none" />
          </div>

          <div class="flex justify-end gap-4 pt-2">
            <button type="button" id="btnCancelarModalTrimestre"
              class="px-5 py-2 rounded-xl border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition">
              Cancelar
            </button>
            <button type="submit"
              class="px-6 py-2 rounded-xl bg-[#00324D] text-white hover:bg-[#00273A] transition">
              Crear Trimestre
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- JS -->
  <script>
    (function () {
      /* ---------- Modal: abrir / cerrar ---------- */
      const btnOpen = document.getElementById('btnAbrirModalTrimestre');
      const modal = document.getElementById('modalTrimestre');
      const backdrop = document.getElementById('backdrop');
      const panel = document.getElementById('modalPanel');
      const btnClose = document.getElementById('btnCerrarModalTrimestre');
      const btnCancelModal = document.getElementById('btnCancelarModalTrimestre');

      function openModal() {
        modal.classList.remove('hidden');
        // force reflow then animate in
        requestAnimationFrame(() => {
          backdrop.classList.remove('opacity-0');
          backdrop.classList.add('opacity-100');
          panel.classList.remove('opacity-0', 'scale-95');
          panel.classList.add('opacity-100', 'scale-100');
        });
      }

      function closeModal() {
        backdrop.classList.remove('opacity-100');
        backdrop.classList.add('opacity-0');
        panel.classList.remove('opacity-100', 'scale-100');
        panel.classList.add('opacity-0', 'scale-95');
        setTimeout(() => modal.classList.add('hidden'), 200);
      }

      btnOpen?.addEventListener('click', openModal);
      btnClose?.addEventListener('click', closeModal);
      btnCancelModal?.addEventListener('click', closeModal);
      backdrop?.addEventListener('click', (e) => { if (e.target === backdrop) closeModal(); });
      window.addEventListener('keydown', (e) => { if (!modal.classList.contains('hidden') && e.key === 'Escape') closeModal(); });

      document.getElementById('formNuevoTrimestre')?.addEventListener('submit', (e) => {
        e.preventDefault();
        // Aquí puedes agregar la lógica para enviar al backend y, si va bien, insertar la fila en la tabla.
        closeModal();
      });

      /* ---------- Edición inline en la tabla (delegación) ---------- */
      const tabla = document.getElementById('tablaTrimestres');

      tabla.addEventListener('click', (e) => {
        const btn = e.target.closest('button');
        if (!btn) return;

        // Guardar
        if (btn.classList.contains('btn-guardar')) {
          const tr = btn.closest('tr');
          const inputNumero = tr.querySelector('input[data-edit="numero"]');
          const tdNumero = tr.children[0];
          const tdAcc = tr.children[1];

          const nuevoValor = (inputNumero.value || '').trim() || tr.dataset.origNumero || '';
          tdNumero.textContent = nuevoValor;

          // Restaurar acciones: mostrar editar, quitar guardar/cancelar
          const accionesBox = tdAcc.querySelector('.flex');
          accionesBox.querySelector('.btn-editar')?.classList.remove('hidden');
          accionesBox.querySelector('.btn-guardar')?.remove();
          accionesBox.querySelector('.btn-cancelar')?.remove();

          // Limpiar dataset
          delete tr.dataset.editing;
          delete tr.dataset.origNumero;

          // -> Aquí puedes hacer la llamada AJAX para guardar el cambio en el server
          return;
        }

        // Cancelar
        if (btn.classList.contains('btn-cancelar')) {
          const tr = btn.closest('tr');
          const tdNumero = tr.children[0];
          const tdAcc = tr.children[1];

          tdNumero.textContent = tr.dataset.origNumero || tdNumero.textContent;

          const accionesBox = tdAcc.querySelector('.flex');
          accionesBox.querySelector('.btn-editar')?.classList.remove('hidden');
          accionesBox.querySelector('.btn-guardar')?.remove();
          accionesBox.querySelector('.btn-cancelar')?.remove();

          delete tr.dataset.editing;
          delete tr.dataset.origNumero;
          return;
        }

        // Editar
        if (btn.classList.contains('btn-editar')) {
          const tr = btn.closest('tr');
          if (tr.dataset.editing === '1') return; // ya en edición

          const tdNumero = tr.children[0];
          const tdAcc = tr.children[1];

          // Guardar original
          tr.dataset.origNumero = tdNumero.textContent.trim();
          tr.dataset.editing = '1';

          // Reemplazar por input
          tdNumero.innerHTML = `
            <input data-edit="numero" type="text" class="w-full rounded-xl border border-gray-200 px-3 py-2 focus:ring-0 focus:outline-none" value="${escapeHtml(tr.dataset.origNumero)}">
          `;

          // Acciones: ocultar editar y añadir Guardar/Cancelar (antes del switch)
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

          // insertarlos antes del switch (último hijo dentro .flex es el switch wrapper)
          accionesBox.insertBefore(btnGuardar, accionesBox.lastElementChild);
          accionesBox.insertBefore(btnCancelar, accionesBox.lastElementChild);
          return;
        }
      });

      // pequeña función para escapar valores insertados en value="" (anti-rotura)
      function escapeHtml(str) {
        if (!str && str !== 0) return '';
        return String(str).replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
      }
    })();
  </script>
</body>
</html>
