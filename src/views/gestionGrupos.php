<?php /* views/grupos.php */ ?>
<?php if(!defined('BASE_URL')) { 
  $base = '/senlock/';
  define('BASE_URL', $base);
} ?>
<style>
/* ===== MODALES ENTERPRISE (Nuevo Grupo) ===== */
.modal-grupo-overlay {
  backdrop-filter: blur(4px);
  background: rgba(0,0,0,0.35);
}
.modal-grupo-box {
  max-height: calc(100vh - 3rem);
  margin: 1.5rem;
  box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
  border: 1px solid rgba(229,231,235,0.8);
}
.modal-grupo-header {
  flex-shrink: 0;
  padding: 0.75rem 1.25rem;
  border-bottom: 1px solid #e5e7eb;
  background: #fff;
}
.modal-grupo-body {
  flex: 1;
  overflow-y: auto;
  padding: 1rem 1.25rem;
  min-height: 0;
}

.modal-grupo-footer {
  flex-shrink: 0;
  padding: 0.75rem 1.25rem;
  border-top: 1px solid #e5e7eb;
  background: #fff;
}
/* Select: padding-right mínimo para el chevron (2rem); texto usa todo el ancho hasta el icono */
:root {
  --select-padding-x: 0.75rem;
  --select-chevron-zone: 2rem;
  --chevron-size: 1.15rem;
}
.input-enterprise {
  width: 100%;
  border-radius: 12px;
  border: 1px solid #d1d5db;
  background: #fff;
  padding: 0.75rem 1rem;
  color: #1f2937;
  transition: border-color 0.15s, box-shadow 0.15s;
}
/* Selects nativos: pr-8 (2rem) para el chevron; truncado solo al llegar al icono */
select.input-enterprise {
  width: 100%;
  min-width: 0;
  box-sizing: border-box;
  padding-left: var(--select-padding-x);
  padding-right: var(--select-chevron-zone);
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.5rem center;
  background-size: var(--chevron-size);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.input-enterprise::placeholder { color: #9ca3af; }
.input-enterprise:hover { border-color: #9ca3af; }
.input-enterprise:focus {
  outline: none;
  border-color: #39A900;
  box-shadow: 0 0 0 3px rgba(57,169,0,0.2);
}
.btn-modal-secondary {
  padding: 0.625rem 1.25rem;
  border-radius: 12px;
  border: 1px solid #d1d5db;
  font-weight: 500;
  color: #374151;
  background: #fff;
  transition: all 0.15s;
}
.btn-modal-secondary:hover { background: #f9fafb; border-color: #9ca3af; }
.btn-modal-secondary:focus { outline: none; box-shadow: 0 0 0 2px rgba(156,163,175,0.5); }
.btn-modal-primary {
  padding: 0.625rem 1.25rem;
  border-radius: 12px;
  font-weight: 500;
  color: #fff;
  background: #0a3a57;
  transition: all 0.15s;
}
.btn-modal-primary:hover { background: #00304D; }
.btn-modal-primary:focus { outline: none; box-shadow: 0 0 0 3px rgba(10,58,87,0.4); }
.btn-modal-primary:active, .btn-modal-secondary:active { transform: scale(0.98); }
.label-enterprise {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.375rem;
}
/* Estilo del select abierto (igual que modales de usuarios) */
.custom-select-dropdown {
  position: absolute;
  left: 0;
  right: 0;
  top: 100%;
  margin-top: 8px;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.05);
  background: white;
  max-height: 220px;
  overflow-y: auto;
  z-index: 100;
}
.custom-select-dropdown.dropdown-up {
  top: auto;
  bottom: 100%;
  margin-top: 0;
  margin-bottom: 8px;
  box-shadow: 0 -10px 25px -5px rgba(0,0,0,0.1), 0 -8px 10px -6px rgba(0,0,0,0.05);
}
.custom-select-dropdown .custom-option {
  padding: 10px 14px;
  cursor: pointer;
  transition: background 0.15s;
}
.custom-select-dropdown .custom-option:hover { background: #f3f4f6; }
.custom-select-dropdown .custom-option.selected {
  background: rgba(57, 169, 0, 0.1);
  color: #0a3a57;
}
.custom-select-wrapper { position: relative; width: 100%; min-width: 0; }
/* Flexbox: texto e icono en extremos; texto usa todo el ancho hasta el icono; truncado solo al llegar */
.custom-select-trigger {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.25rem;
  padding-left: var(--select-padding-x);
  padding-right: 0.5rem;
  box-sizing: border-box;
  min-width: 0;
}
.custom-select-trigger .truncate {
  flex: 1;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  padding-right: 0.25rem;
}
.custom-select-trigger .shrink-0 {
  flex-shrink: 0;
  pointer-events: none;
}

/* Regla de oro 3: Ancho de tabla responsivo — min-width por columna para scroll horizontal legible */
.table-grupos-wrap {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  width: 100%;
}
.table-grupos {
  table-layout: fixed;
  min-width: 60rem;
  width: 100%;
}
.table-grupos th,
.table-grupos td {
  padding: 0.5rem 0.75rem;
  vertical-align: middle;
}
.table-grupos .col-numero,
.table-grupos .col-programa,
.table-grupos .col-nivel,
.table-grupos .col-jornada,
.table-grupos .col-modalidad,
.table-grupos .col-lider,
.table-grupos .col-acciones { box-sizing: border-box; }
.table-grupos .col-numero { min-width: 5.5rem; padding-right: 1.25rem; }
.table-grupos .col-programa { min-width: 14rem; padding-left: 1.25rem; }
.table-grupos .col-nivel { min-width: 5.5rem; }
.table-grupos .col-jornada { min-width: 7rem; }
.table-grupos .col-modalidad { min-width: 7.5rem; }
.table-grupos .col-lider { min-width: 9rem; }
.table-grupos .col-acciones { min-width: 8rem; white-space: nowrap; }
/* Ancho mínimo por celda para área de lectura digna en pantallas pequeñas */
.table-grupos td.col-numero { min-width: 5.5rem; }
.table-grupos td.col-programa { min-width: 14rem; }
.table-grupos td.col-nivel { min-width: 5.5rem; }
.table-grupos td.col-jornada { min-width: 7rem; }
.table-grupos td.col-modalidad { min-width: 7.5rem; }
.table-grupos td.col-lider { min-width: 9rem; }
.table-grupos td.col-acciones { min-width: 8rem; }
.table-grupos .col-nivel,
.table-grupos .col-jornada,
.table-grupos .col-modalidad,
.table-grupos .col-lider {
  white-space: nowrap;
}
.cell-programa-wrap {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  overflow: hidden;
  font-size: 0.8125rem;
  line-height: 1.35;
  word-wrap: break-word;
  overflow-wrap: break-word;
  max-width: 100%;
  min-height: 4.05em;
}
/* Nivel en mayúscula sostenida */
.cell-nivel-tag { text-transform: uppercase; }
/* Pills verdes para Nivel, Jornada, Modalidad, Líder: padding y margen para que no se encimen */
.tag-pill {
  display: inline-block;
  padding: 0.35rem 0.75rem;
  margin: 0.125rem 0.25rem 0.125rem 0;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
  line-height: 1.2;
  background: rgba(57, 169, 0, 0.15);
  color: #166534;
}
.tag-pill:last-child { margin-right: 0; }

/* Fila en edición: misma lógica que zonas — mismo padding en td, controles compactos (py-2) */
.table-grupos tr.editando td {
  min-width: 0;
  overflow: hidden;
  vertical-align: middle;
  padding: 0.5rem 0.75rem;
}
.table-grupos tr.editando .custom-select-trigger,
.table-grupos tr.editando .input-enterprise {
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
}
.table-grupos tr.editando .cell-edit-wrap {
  min-width: 0;
  max-width: 100%;
}
.table-grupos tr.editando .col-numero {
  overflow: visible;
  padding-right: 2rem;
}
.table-grupos tr.editando .col-numero .cell-edit-wrap {
  overflow: visible;
}
.table-grupos tr.editando .col-programa {
  padding-left: 2rem;
  overflow: visible;
  display: flex;
  align-items: center;
}
.table-grupos tr.editando .col-programa .cell-edit-wrap {
  max-width: 100%;
  width: 100%;
}
.table-grupos tr.editando .col-programa select.input-enterprise {
  padding-right: var(--select-chevron-zone);
}
.table-grupos tr.editando .col-jornada,
.table-grupos tr.editando .col-modalidad,
.table-grupos tr.editando .col-lider {
  overflow: visible;
}
.table-grupos tr.editando .col-jornada .cell-edit-wrap,
.table-grupos tr.editando .col-modalidad .cell-edit-wrap,
.table-grupos tr.editando .col-lider .cell-edit-wrap {
  max-width: 100%;
}
/* Input número: 9 dígitos + cursor visible al final, sin flechas de subir/bajar */
.table-grupos tr.editando input.cell-edit.numero {
  width: 14ch;
  min-width: 14ch;
  max-width: 16ch;
  padding-right: 0.75rem;
  box-sizing: border-box;
}
.table-grupos input[type="number"].cell-edit.numero::-webkit-outer-spin-button,
.table-grupos input[type="number"].cell-edit.numero::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
.table-grupos input[type="number"].cell-edit.numero {
  -moz-appearance: textfield;
  appearance: textfield;
}
.table-grupos tr.editando .input-enterprise {
  max-width: 100%;
  min-width: 0;
  box-sizing: border-box;
  padding: 0.5rem 0.75rem;
  font-size: 0.8125rem;
}
.table-grupos tr.editando select.input-enterprise {
  max-width: 100%;
  min-width: 0;
  box-sizing: border-box;
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
  padding-left: var(--select-padding-x);
  padding-right: var(--select-chevron-zone);
  font-size: 0.8125rem;
}
.table-grupos tr.editando .col-acciones {
  overflow: visible;
  min-width: 0;
}
.table-grupos tr.editando .acciones-edit {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 0.25rem;
  flex-wrap: nowrap;
}
/* Botones icono: check (verde) y x (rojo) como en referencia */
.table-grupos tr.editando .btn-icon-check {
  background: #39A900;
  border: none;
  color: #fff;
}
.table-grupos tr.editando .btn-icon-check:hover {
  background: #2d8000;
}
.table-grupos tr.editando .btn-icon-x {
  background: #dc2626;
  border: none;
  color: #fff;
}
.table-grupos tr.editando .btn-icon-x:hover {
  background: #b91c1c;
}
.table-grupos tr.editando .btn-icon-check:focus,
.table-grupos tr.editando .btn-icon-x:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(0,0,0,0.15);
}
</style>

<div class="max-w-6xl mx-auto px-4 py-10">

  <h1 class="text-4xl font-extrabold tracking-tight mb-2 text-[#39A900]">
    Gestión de Grupos
  </h1>
  <p class="text-gray-500 mb-6">Administra los Grupos</p>

  <div class="bg-white shadow rounded-2xl border border-gray-200">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-6">
      <div>
        <h2 class="text-xl font-semibold text-gray-800">Grupos</h2>
        <p class="text-sm text-gray-500">Lista de todos los grupos registrados</p>
      </div>

      <button id="btnAbrirModalGrupo"
        class="w-full md:w-auto bg-[#0a3a57] hover:bg-[#00304D] active:scale-95 transition
               text-white px-5 py-2.5 rounded-xl flex items-center justify-center gap-2 shadow-sm">
        <span>Nuevo Grupo</span>
      </button>
    </div>

    <!-- Filtros -->
    <div class="px-6 py-4 border-b">
      <div class="flex flex-col md:flex-row gap-4">

        <!-- Select -->
        <div class="relative w-full md:w-64">
          <select id="filtroPrograma"
            class="select-grupo w-full appearance-none rounded-xl border border-gray-300 bg-white
                   px-4 py-2.5 pr-10 text-sm
                   focus:ring-2 focus:ring-[#39A900]/20
                   focus:border-[#39A900] outline-none transition">
            <option value="">Todos los programas</option>
          </select>
          <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19 9l-7 7-7-7" />
          </svg>
        </div>

        <!-- Buscador -->
        <div class="relative w-full md:w-64">
          <input type="text" id="buscadorGrupo"
            placeholder="Buscar grupo..."
            class="w-full rounded-full border border-gray-300 bg-white
                   pl-10 pr-4 py-2.5 text-sm
                   focus:ring-2 focus:ring-[#39A900]/20
                   focus:border-[#39A900] outline-none transition" />
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>

      </div>
    </div>

    <!-- Tabla responsiva: scroll horizontal, Acciones sticky a la derecha -->
    <div class="table-grupos-wrap">
      <table class="w-full text-left table-grupos" id="tablaGrupos">
        <colgroup>
          <col style="width:10%">
          <col style="width:24%">
          <col style="width:12%">
          <col style="width:12%">
          <col style="width:14%">
          <col style="width:16%">
          <col style="width:12%">
        </colgroup>
        <thead class="bg-gray-50 border-b border-gray-200 text-sm text-gray-600">
          <tr>
            <th class="font-medium col-numero">Número</th>
            <th class="font-medium col-programa">Programa</th>
            <th class="font-medium col-nivel">Nivel</th>
            <th class="font-medium col-jornada">Jornada</th>
            <th class="font-medium col-modalidad">Modalidad</th>
            <th class="font-medium col-lider">Líder</th>
            <th class="font-medium text-right col-acciones">Acciones</th>
          </tr>
        </thead>
        <tbody id="tbodyGrupos"
          class="text-sm divide-y divide-gray-100">
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Nuevo Grupo -->
<div id="modalGrupo" role="dialog" aria-labelledby="modalGrupoTitle" aria-modal="true"
  class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 modal-grupo-overlay hidden">

  <div class="modal-grupo-box bg-white w-full max-w-xl rounded-2xl flex flex-col overflow-hidden">

    <header class="modal-grupo-header flex items-center justify-between">
      <h2 id="modalGrupoTitle" class="text-xl font-bold text-[#39A900] tracking-tight">
        Nuevo Grupo
      </h2>
      <button type="button" id="btnCerrarModal" aria-label="Cerrar"
        class="p-2 -m-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </header>

    <form id="formGrupo" class="flex flex-col flex-1 min-h-0">
      <div class="modal-grupo-body flex-1">
        <div class="space-y-5">
          <div>
            <label for="inputNumeroFicha" class="label-enterprise">Número de grupo</label>
            <input type="number" name="numero_ficha" id="inputNumeroFicha"
              max="999999999" min="1" required placeholder="Ingrese el número"
              class="input-enterprise">
            <p id="errorNumeroFicha" class="text-red-500 text-sm mt-1 hidden"></p>
          </div>

          <div>
            <label for="selectProgramaModal" class="label-enterprise">Programa</label>
            <div class="relative">
              <select name="id_programa" id="selectProgramaModal" required
                class="select-grupo input-enterprise pr-10 appearance-none cursor-pointer">
                <option value="">Seleccione un programa</option>
              </select>
              <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </div>
          </div>

          <div>
            <label for="selectJornada" class="label-enterprise">Jornada</label>
            <div class="relative">
              <select name="jornada" id="selectJornada" required
                class="select-grupo input-enterprise pr-10 appearance-none cursor-pointer">
                <option value="" disabled selected>Seleccione jornada</option>
                <option value="DIURNA">Diurna</option>
                <option value="NOCTURNA">Nocturna</option>
                <option value="MIXTA">Mixta</option>
              </select>
              <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </div>
          </div>

          <div>
            <label for="selectModalidad" class="label-enterprise">Modalidad</label>
            <div class="relative">
              <select name="modalidad" id="selectModalidad" required
                class="select-grupo input-enterprise pr-10 appearance-none cursor-pointer">
                <option value="" disabled selected>Seleccione modalidad</option>
                <option value="PRESENCIAL">Presencial</option>
                <option value="VIRTUAL">Virtual</option>
                <option value="A DISTANCIA">A Distancia</option>
              </select>
              <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </div>
          </div>

          <div>
            <label for="selectLiderModal" class="label-enterprise">Líder de grupo</label>
            <div class="relative">
              <select name="id_lider_grupo" id="selectLiderModal" required
                class="select-grupo input-enterprise pr-10 appearance-none cursor-pointer">
                <option value="">Seleccione líder</option>
              </select>
              <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      <footer class="modal-grupo-footer flex justify-end gap-3">
        <button type="button" id="btnCancelar" class="btn-modal-secondary">
          Cancelar
        </button>
        <button type="submit" class="btn-modal-primary">
          Guardar Grupo
        </button>
      </footer>
    </form>
  </div>
</div>

<script>
window.API_FICHA = "<?= BASE_URL ?>src/controllers/fichaController.php";
window.BASE_GRUPOS = "<?= BASE_URL ?>";
</script>
<script src="<?= BASE_URL ?>src/assets/js/gestionGrupos.js"></script>