<?php /* views/gestionSolicitudes.php */ ?>
<?php require_once __DIR__ . '/../includes/header-private.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/components/modal-enterprise.css">

<script src="https://unpkg.com/lucide@latest"></script>

<style>
  /* Filtros Solicitudes: estilos en CSS para que no dependan del purge de Tailwind sobre strings en JS */
  #solicitudesFilterBar .filter-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.75rem;
    border: 1px solid #e5e7eb;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    line-height: 1.25;
    background: #fff;
    color: #374151;
    transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
    box-shadow: none;
  }
  #solicitudesFilterBar .filter-btn:focus-visible {
    outline: none;
    box-shadow: 0 0 0 2px #fff, 0 0 0 4px rgba(57, 169, 0, 0.35);
  }
  #solicitudesFilterBar .filter-btn[data-estado="Todas"] {
    border-color: #e5e7eb;
    color: #374151;
  }
  #solicitudesFilterBar .filter-btn[data-estado="Todas"]:hover:not(.solicitud-filter--active) {
    background: #fafafa;
    border-color: #d4d4d8;
  }
  #solicitudesFilterBar .filter-btn.solicitud-filter--active[data-estado="Todas"] {
    border-color: #64748b;
    background: #f8fafc;
    color: #0f172a;
    font-weight: 600;
  }
  #solicitudesFilterBar .filter-btn[data-estado="Pendiente"] {
    border-color: #fde68a;
    color: rgba(120, 53, 15, 0.95);
  }
  #solicitudesFilterBar .filter-btn[data-estado="Pendiente"]:hover:not(.solicitud-filter--active) {
    background: #fffbeb;
    border-color: #fcd34d;
  }
  #solicitudesFilterBar .filter-btn.solicitud-filter--active[data-estado="Pendiente"] {
    border-color: #f59e0b;
    background: #fffbeb;
    color: #422006;
    font-weight: 600;
  }
  #solicitudesFilterBar .filter-btn[data-estado="Aprobada"] {
    border-color: #a7f3d0;
    color: rgba(6, 78, 59, 0.92);
  }
  #solicitudesFilterBar .filter-btn[data-estado="Aprobada"]:hover:not(.solicitud-filter--active) {
    background: #ecfdf5;
    border-color: #6ee7b7;
  }
  #solicitudesFilterBar .filter-btn.solicitud-filter--active[data-estado="Aprobada"] {
    border-color: #059669;
    background: #ecfdf5;
    color: #022c22;
    font-weight: 600;
  }
  #solicitudesFilterBar .filter-btn[data-estado="Devuelto"] {
    border-color: #fecdd3;
    color: rgba(136, 19, 55, 0.92);
  }
  #solicitudesFilterBar .filter-btn[data-estado="Devuelto"]:hover:not(.solicitud-filter--active) {
    background: #fff1f2;
    border-color: #fda4af;
  }
  #solicitudesFilterBar .filter-btn.solicitud-filter--active[data-estado="Devuelto"] {
    border-color: #f43f5e;
    background: #fff1f2;
    color: #4c0519;
    font-weight: 600;
  }
  /* Avatar iniciales (misma lógica que perfil; escala compacta para el modal) */
  #modalSolicitudAvatar {
    width: 2.75rem;
    height: 2.75rem;
    min-width: 2.75rem;
    min-height: 2.75rem;
    border-radius: 9999px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #d1d5db;
    font-size: 0.75rem;
    font-weight: 700;
    color: #4b5563;
    flex-shrink: 0;
    line-height: 1;
    letter-spacing: 0.01em;
  }
  /* Botón Devolver: inline CSS garantiza el rojo aunque Tailwind no compile la clase */
  #btnDevolver {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.75rem;
    background-color: #dc2626;
    padding: 0.625rem 1.25rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #fff;
    box-shadow: 0 1px 2px 0 rgba(0,0,0,.05);
    transition: background-color 0.15s;
    border: none;
    cursor: pointer;
  }
  #btnDevolver:hover {
    background-color: #b91c1c;
  }
  #btnDevolver:focus-visible {
    outline: none;
    box-shadow: 0 0 0 2px #fff, 0 0 0 4px rgba(220,38,38,.5);
  }

  /* Tabla solicitudes: estado (columna Solicitud) vs tipo (columna Tipo) — contorno distinto */
  #tablaSolicitudes .sol-badge-estado,
  #tablaSolicitudes .sol-badge-tipo {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.2rem 0.55rem;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    border-radius: 9999px;
    border: 1.5px solid;
    line-height: 1.2;
    max-width: 100%;
    box-sizing: border-box;
  }
  #tablaSolicitudes .sol-badge-estado--pendiente {
    background: #fffbeb;
    color: #92400e;
    border-color: #f59e0b;
  }
  #tablaSolicitudes .sol-badge-estado--aprobado {
    background: #ecfdf5;
    color: #166534;
    border-color: #22c55e;
  }
  #tablaSolicitudes .sol-badge-estado--devuelto {
    background: #fff1f2;
    color: #9f1239;
    border-color: #f43f5e;
  }
  #tablaSolicitudes .sol-badge-estado--otro {
    background: #f9fafb;
    color: #374151;
    border-color: #9ca3af;
  }
  /* Tipo: paleta distinta (azul/violeta) para no confundir con estado */
  #tablaSolicitudes .sol-badge-tipo--datos {
    background: #eff6ff;
    color: #1e40af;
    border-color: #3b82f6;
  }
  #tablaSolicitudes .sol-badge-tipo--horario {
    background: #f5f3ff;
    color: #5b21b6;
    border-color: #8b5cf6;
  }
  #tablaSolicitudes .sol-badge-tipo--otro {
    background: #f8fafc;
    color: #475569;
    border-color: #64748b;
  }

  /* Gutter único del modal detalle (= px-6): mismo valor para hueco vertical entre meta y alineación con contenido */
  #modalDetalle.modal-detalle-solicitud {
    --md-gutter: 1.5rem;
  }
  #modalDetalle .modal-detalle-pad-x {
    padding-left: var(--md-gutter);
    padding-right: var(--md-gutter);
  }
  #modalDetalle .modal-detalle-separator {
    margin-left: var(--md-gutter);
    margin-right: var(--md-gutter);
  }
  #modalDetalle .modal-solicitud-meta {
    display: flex;
    flex-direction: column;
    gap: var(--md-gutter);
    margin-top: var(--md-gutter);
  }
  #modalDetalle #modalContenido {
    max-width: 100%;
    overflow-x: hidden;
    word-break: break-word;
  }
  /* Scroll solo dentro de la tarjeta (barra junto al modal, no en la página) */
  #modalDetalle.modal-detalle-solicitud {
    align-items: center;
    justify-content: center;
  }
  /* La tarjeta recorta; el scroll real va en un contenedor interno inset para que no se vea afuera. */
  #modalDetalle .modal-detalle-card {
    width: 100%;
    max-width: 36rem;
    max-height: min(78vh, calc(100dvh - 2.5rem));
    overflow: hidden;
    box-sizing: border-box;
    border: 1px solid rgba(229, 231, 235, 0.9);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.22);
    border-radius: 1rem;
  }
  @media (min-width: 640px) {
    #modalDetalle .modal-detalle-card {
      max-height: min(80vh, calc(100dvh - 3rem));
    }
  }
  #modalDetalle .modal-detalle-scroll-inner {
    -webkit-overflow-scrolling: touch;
    max-height: inherit;
    overflow-x: hidden;
    overflow-y: auto;
    margin-right: 0.35rem;
    padding-bottom: 1.5rem;
    scrollbar-gutter: stable;
    scrollbar-width: thin;
    scrollbar-color: rgba(148, 163, 184, 0.85) rgba(241, 245, 249, 0.6);
  }
  /* WebKit/Chromium: barra redondeada e inset respecto al borde del modal */
  #modalDetalle .modal-detalle-scroll-inner::-webkit-scrollbar {
    width: 9px;
  }
  #modalDetalle .modal-detalle-scroll-inner::-webkit-scrollbar-track {
    background: rgba(241, 245, 249, 0.75);
    border-radius: 999px;
    margin: 12px 0;
  }
  #modalDetalle .modal-detalle-scroll-inner::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.75);
    border-radius: 999px;
    border: 2px solid rgba(241, 245, 249, 0.75);
    background-clip: padding-box;
  }
  #modalDetalle .modal-detalle-scroll-inner::-webkit-scrollbar-thumb:hover {
    background: rgba(100, 116, 139, 0.85);
  }
  #modalDetalle .modal-detalle-scroll-inner::-webkit-scrollbar-corner {
    background: transparent;
  }
  /* Motivo devolución al ver solicitud rechazada: aire arriba, izquierda y abajo */
  #modalDetalle #motivoDevolucion {
    padding-top: 1rem;
    padding-bottom: 1.25rem;
    padding-left: var(--md-gutter);
    padding-right: var(--md-gutter);
    box-sizing: border-box;
  }
  #modalDetalle #motivoDevolucion .motivo-devolucion-caja {
    padding: 0.875rem 1rem 1rem 1rem;
  }

  /* Modal “pedir” devolución: título, subtítulo y textarea alineados al mismo borde izquierdo */
  #modalDevolverMotivo .modal-devolver-card {
    padding: 1.5rem 1.75rem 1.5rem 1.75rem;
    box-sizing: border-box;
  }
  #modalDevolverMotivo .modal-devolver-body {
    margin-bottom: 1rem;
  }
  #modalDevolverMotivo .modal-devolver-titulo {
    margin: 0 0 0.5rem 0;
    font-size: 1.25rem;
    font-weight: 700;
    line-height: 1.3;
    color: #111827;
  }
  #modalDevolverMotivo .modal-devolver-sub {
    margin: 0 0 0.75rem 0;
    font-size: 0.875rem;
    line-height: 1.45;
    color: #4b5563;
  }
  /* Padding interior del textarea (no depender de Tailwind p-3 en output.css) */
  #modalDevolverMotivo #motivoDevolucionInput {
    display: block;
    width: 100%;
    box-sizing: border-box;
    min-height: 6.5rem;
    padding: 0.75rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
    font-family: inherit;
    resize: vertical;
    margin: 0;
  }
  #modalDevolverMotivo #motivoDevolucionInput:focus {
    outline: none;
    border-color: #39a900;
    box-shadow: 0 0 0 2px rgba(57, 169, 0, 0.2);
  }
  #modalDevolverMotivo #motivoDevolucionInput::placeholder {
    color: #9ca3af;
  }
  #btnConfirmarDevolver {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.75rem;
    background-color: #dc2626;
    padding: 0.625rem 1.25rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #fff;
    box-shadow: 0 1px 2px 0 rgba(0,0,0,.05);
    border: none;
    cursor: pointer;
    transition: background-color 0.15s;
  }
  #btnConfirmarDevolver:hover {
    background-color: #b91c1c;
  }
</style>

<div class="px-4 sm:px-6 py-8 flex-1 min-h-0 flex flex-col min-h-[calc(100vh-8rem)]">

  <div class="max-w-6xl mx-auto w-full flex flex-col min-h-0 flex-1">

    <h1 class="text-4xl font-extrabold tracking-tight mb-2 text-[#39A900]">Gestión de Solicitudes</h1>
    <p class="text-gray-500 mb-6">Revisa y responde las solicitudes de horario y de datos personales</p>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm flex flex-col min-h-[calc(100vh-13rem)] max-h-[calc(100vh-4.5rem)]">

      <!-- Cabecera: semitítulo, filtros y buscador (altura fija en bloque superior) -->
      <div class="p-6 border-b border-gray-100 shrink-0 space-y-5">
        <div>
          <h2 class="text-xl font-semibold text-gray-800">Solicitudes</h2>
          <p class="text-sm text-gray-500 mt-0.5">Lista de todas las solicitudes registradas</p>
        </div>

        <div class="flex flex-wrap gap-2 items-center" id="solicitudesFilterBar">
          <button type="button" class="filter-btn solicitud-filter--active" data-estado="Todas">Todas</button>
          <button type="button" class="filter-btn" data-estado="Pendiente">Pendientes</button>
          <button type="button" class="filter-btn" data-estado="Aprobada">Aprobadas</button>
          <button type="button" class="filter-btn" data-estado="Devuelto">Devueltas</button>
        </div>

        <div class="w-full max-w-md space-y-2">
          <input
            type="text"
            id="searchInput"
            placeholder="Buscar por nombre, programa o ID"
            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 outline-none transition focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/25"
            autocomplete="off"
          />
          <button
            type="button"
            id="btnRefrescarSolicitudes"
            class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl border border-[#39A900] bg-white px-4 py-2.5 text-sm font-medium text-[#39A900] transition hover:bg-[#39A900]/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#39A900]/30"
            aria-label="Refrescar la lista de solicitudes desde el servidor"
          >
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refrescar solicitudes
          </button>
        </div>
      </div>

      <!-- Tabla con scroll interno (no crece con la cantidad de filas) -->
      <div class="flex-1 min-h-0 flex flex-col overflow-hidden">
        <div class="overflow-x-auto overflow-y-auto flex-1 min-h-0">
          <table class="w-full text-sm min-w-[720px]">

            <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 sticky top-0 z-10">
              <tr>
                <th class="px-4 py-3 font-semibold text-left">Código</th>
                <th class="px-4 py-3 font-semibold text-left">Solicitante</th>
                <th class="px-4 py-3 font-semibold text-left">Solicitud</th>
                <th class="px-4 py-3 font-semibold text-left">Tipo</th>
                <th class="px-4 py-3 font-semibold text-left">Fecha</th>
                <th class="px-4 py-3 font-semibold text-center">Visualizar</th>
              </tr>
            </thead>

            <tbody id="tablaSolicitudes" class="divide-y divide-gray-100 bg-white">
            </tbody>

          </table>
        </div>
      </div>

      <!-- Paginación (mismo patrón que Competencias) -->
      <div id="solicitudesPagination" class="hidden shrink-0 border-t border-gray-100 py-4 px-6 flex items-center justify-center gap-2">
        <button type="button" id="solPrev" class="px-4 py-2 rounded-xl border border-zinc-300 bg-white text-sm hover:bg-zinc-100 disabled:opacity-40 disabled:pointer-events-none transition-colors" aria-label="Página anterior">&lt;</button>
        <div class="flex items-center gap-3 px-2">
          <span id="solPageInfo" class="text-sm text-zinc-600"></span>
        </div>
        <button type="button" id="solNext" class="px-4 py-2 rounded-xl border border-zinc-300 bg-white text-sm hover:bg-zinc-100 disabled:opacity-40 disabled:pointer-events-none transition-colors" aria-label="Página siguiente">&gt;</button>
      </div>

    </div>

  </div>

</div>
<!-- ================= MODAL DETALLE ================= -->
<div id="modalDetalle" class="modal-detalle-solicitud fixed inset-0 z-50 hidden overflow-hidden bg-black/40 p-3 sm:p-5" role="dialog" aria-modal="true">

    <div id="modalDetalleCard" class="modal-detalle-card rounded-2xl bg-white relative mx-auto w-full">

        <button type="button" id="cerrarModalDetalle"
            onclick="cerrarModal()"
            class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-xl font-bold z-20 leading-none bg-white/95 rounded-md"
            style="line-height:1;padding:4px 8px;"
            aria-label="Cerrar">
            ✕
        </button>

        <div id="modalDetalleScrollInner" class="modal-detalle-scroll-inner">

        <!-- Cabecera: avatar+nombre; debajo, meta alineada al mismo borde izquierdo que el cuadro de datos -->
        <div class="modal-detalle-pad-x pt-6 pb-4">
            <div class="flex items-start" style="gap:1.125rem;">
                <div id="modalSolicitudAvatar" aria-hidden="true"></div>
                <div class="min-w-0 flex-1">
                    <h2 id="modalSolicitante" class="text-[15px] font-bold text-gray-900 leading-snug"></h2>
                    <div class="flex flex-wrap items-center gap-2" style="margin-top:0.625rem;">
                        <span id="modalEstado" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold"></span>
                        <span id="modalTipoBadge" class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2.5 py-0.5 text-[11px] font-medium text-gray-600"></span>
                    </div>
                </div>
            </div>
            <div class="modal-solicitud-meta">
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-wide text-gray-400">Número solicitud</p>
                    <p id="modalCodigo" class="mt-0.5 text-sm font-semibold text-blue-600"></p>
                </div>
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-wide text-gray-400">Programa</p>
                    <p id="modalPrograma" class="mt-0.5 text-sm font-semibold text-gray-900"></p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Solicitado el</p>
                    <p id="modalFecha" class="mt-0.5 text-sm font-semibold text-gray-800"></p>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-100 modal-detalle-separator"></div>

        <!-- Ubicación (solo solicitudes de horario): área y zona -->
        <div id="modalHorarioUbicacionWrap" class="hidden modal-detalle-pad-x pt-3 pb-0">
            <div class="rounded-xl border border-violet-200 bg-violet-50/90 px-4 py-3 text-sm text-violet-950">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-violet-700 mb-1.5">Ubicación del horario</p>
                <div id="modalHorarioUbicacionLineas" class="flex flex-col gap-1 font-medium text-violet-900"></div>
            </div>
        </div>

        <!-- CONTENIDO DINÁMICO (bloque horario o datos) -->
        <div id="modalContenido" class="modal-detalle-pad-x py-4"></div>

        <!-- MOTIVO DEVOLUCIÓN (ver solicitud devuelta) -->
        <div id="motivoDevolucion" class="hidden">
            <div class="motivo-devolucion-caja bg-rose-50 border border-rose-200 rounded-xl text-sm">
                <p class="font-semibold text-rose-700 mb-1">Motivo de devolución:</p>
                <p id="textoMotivoDevolucion" class="text-rose-600"></p>
            </div>
        </div>

        <!-- Coordinador: solicitud propia pendiente (no puede aprobarla él mismo) -->
        <div id="avisoSolicitudPropiaCoordinador" class="modal-detalle-pad-x pb-2 hidden">
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                Como coordinador, no puede aprobar ni devolver sus propias solicitudes. Solo otro coordinador o un administrador del sistema puede hacerlo.
            </div>
        </div>

        <!-- Acciones (solo PENDIENTE y con permiso) -->
        <div id="botonesAccion" class="modal-detalle-pad-x pb-0 space-y-4 hidden">
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                Al confirmar, la solicitud pasará a <strong>Aprobados</strong> y los cambios mostrados arriba se aplicarán al usuario en el sistema.
            </div>
            <div class="flex flex-wrap justify-end gap-3">
                <button type="button" id="btnDevolver">Devolver</button>
                <button type="button" id="btnAprobar" class="btn-modal-primary">Confirmar aprobación</button>
            </div>
        </div>

        </div>

    </div>
</div>

<!-- ================= MODAL DEVOLVER CON MOTIVO ================= -->
<div id="modalDevolverMotivo" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
  <div class="modal-devolver-card bg-white rounded-2xl relative shadow-xl" style="width:100%;max-width:448px;">
    <div class="modal-devolver-body">
      <h3 class="modal-devolver-titulo">Devolver solicitud</h3>
      <p class="modal-devolver-sub">Indica el motivo de la devolución:</p>
      <textarea id="motivoDevolucionInput" rows="4" autocomplete="off"></textarea>
    </div>
    <div class="flex flex-wrap justify-end gap-3">
      <button type="button" onclick="cerrarModalDevolver()" class="btn-modal-secondary">Cancelar</button>
      <button type="button" id="btnConfirmarDevolver">Devolver</button>
    </div>
  </div>
</div>
<script>
  window.API_URL = "<?= BASE_URL ?>src/controllers/SolicitudController.php";
  window.BASE_URL = "<?= BASE_URL ?>";
</script>

<script src="<?= BASE_URL ?>src/assets/js/gestionarSolicitudes.js?v=19" defer></script>
