/**
 * Plantillas HTML para SweetAlert (popups de trimestralización y zona libre).
 */
(function (w) {
  "use strict";
  const RT = w.RegisterTables;
  const U = RT.util;

  RT.templates = {
    accionesPopupTrimestralizacion: function (isAuth) {
      if (isAuth) {
        return `
              <div class="mt-6 flex justify-end gap-2">
                <button id="btnEditarRegistro"
                  class="bg-[#00324d] text-white text-sm px-4 py-2 rounded-lg hover:bg-[#00304D] transition">
                  Editar
                </button>
                <button id="btnCerrarPopup"
                  class="bg-gray-200 text-gray-800 text-sm px-4 py-2 rounded-lg hover:bg-gray-300 transition">
                  Aceptar
                </button>
              </div>
            `;
      }
      return `
              <div class="mt-6 flex justify-end gap-2">
                <button id="btnCerrarPopup"
                  class="bg-gray-200 text-gray-800 text-sm px-4 py-2 rounded-lg hover:bg-gray-300 transition">
                  Cerrar
                </button>
              </div>
            `;
    },

    /**
     * @param {{ competencia: string, ficha: string, programa: string, instructor: string, dia: string, hora: string, raesDisplay: string, descripcionJornada: string, accionesHtml: string }} p
     */
    trimestralizacionPopupHtml: function (p) {
      const ficha = U.escapeHtml(p.ficha);
      const programa = U.escapeHtml(p.programa);
      const instructor = U.escapeHtml(p.instructor);
      const dia = U.escapeHtml(p.dia);
      const hora = U.escapeHtml(p.hora);
      const raesDisplay = U.escapeHtml(p.raesDisplay.replace(/\|/g, ", "));
      const descJ = String(p.descripcionJornada || "").trim();
      const descripcionJornadaHtml = U.escapeHtml(descJ || "—");
      return `
              <div class="text-left" style="max-height: min(26rem, calc(100vh - 14rem)); overflow-y: auto;">
                <div class="mb-4 pb-2 flex items-center justify-between gap-3">
                  <h2 class="text-xl font-bold text-[#00324D]">Datos de Trimestralización</h2>
                  <button id="btnCerrarXPopup" type="button" class="text-gray-400 hover:text-gray-700 focus:outline-none text-2xl w-8 h-8 flex items-center justify-center leading-none">&times;</button>
                </div>

                <div class="mb-4 pb-2 border-b border-[#000]">
                  <p class="text-sm text-gray-500">${dia} • ${hora}</p>
                </div>

                <div class="space-y-3 text-sm">
                  <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" style="color: #39a900;" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                      <p class="text-gray-400 text-xs">Instructor</p>
                      <p class="text-gray-800 font-medium">${instructor}</p>
                    </div>
                  </div>

                  <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 mt-0.5 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                      <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                      <p class="text-gray-400 text-xs">Grupo</p>
                      <p class="text-gray-800 font-medium">${ficha}</p>
                    </div>
                  </div>

                  <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 mt-0.5 text-purple-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M10 2L1 6l9 4 9-4-9-4z"/>
                      <path d="M4 8v4c0 1.5 2.7 3 6 3s6-1.5 6-3V8l-6 2.7L4 8z"/>
                    </svg>
                    <div>
                      <p class="text-gray-400 text-xs">Programa de Formación</p>
                      <p class="text-gray-800 font-medium">${programa}</p>
                    </div>
                  </div>

                  <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 mt-0.5 text-yellow-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.536-10.95a1 1 0 10-1.414-1.414L9 8.757 7.879 7.636a1 1 0 10-1.414 1.414l1.828 1.829a1 1 0 001.414 0l3.829-3.829z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                      <p class="text-gray-400 text-xs">RAEs</p>
                      <p class="text-gray-800 font-medium">${raesDisplay}</p>
                    </div>
                  </div>

                  <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 mt-0.5 text-gray-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V9.414a2 2 0 00-.586-1.414l-5.414-5.414A2 2 0 0010.586 2H4z"/>
                      <path d="M9 2v5a2 2 0 002 2h5"/>
                    </svg>
                    <div>
                      <p class="text-gray-400 text-xs">Descripción de la jornada</p>
                      <p class="text-gray-800 font-medium whitespace-pre-wrap">${descripcionJornadaHtml}</p>
                    </div>
                  </div>
                </div>
              </div>

              ${p.accionesHtml}
            `;
    },

    accionesZonaLibre: function (isAuth) {
      if (isAuth) {
        return `
          <div class="mt-8 flex flex-col gap-3 items-end sm:flex-row sm:justify-end sm:items-center">
            <button id="btnAbrirModalZonaLibre" class="bg-[#00324d] text-white text-sm px-6 py-2 rounded-lg hover:bg-[#00304D] transition flex items-center justify-center w-auto max-w-full">
              Agregar Horario
            </button>
            <button id="btnCerrarPopupZonaLibre" type="button" class="bg-white border border-gray-300 text-gray-800 text-sm px-6 py-2 rounded-lg hover:bg-gray-50 transition flex items-center justify-center w-auto max-w-full">Cerrar</button>
          </div>
        `;
      }
      return `
          <div class="mt-8 flex justify-end gap-3">
            <button id="btnCerrarPopupZonaLibre" type="button" class="bg-white border border-gray-300 text-gray-800 text-sm px-6 py-2 rounded-lg hover:bg-gray-50 transition flex items-center justify-center w-full sm:w-auto">Cerrar</button>
          </div>
        `;
    },

    zonaLibrePopupHtml: function (dia, hora, accionesHtml) {
      const d = U.escapeHtml(dia);
      const h = U.escapeHtml(hora);
      return `
        <div class="mb-3 text-sm text-left space-y-2 text-gray-500 italic">
          <p><strong>Día:</strong> ${d}</p>
          <p><strong>Hora:</strong> ${h}</p>
          <p>En esta franja no hay ninguna competencia programada.</p>
          </div>
          ${accionesHtml}
          `;
    },
  };
})(window);
