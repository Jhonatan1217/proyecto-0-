<?php
$cargo = $_SESSION['cargo'] ?? '';

if ($cargo === 'INSTRUCTOR') {
    header("Location: index.php?page=register_tables");
    exit;
}
?>

<?php /* views/grupos.php */ ?>
<?php if(!defined('BASE_URL')) { 
  $base = '/senlock/';
  define('BASE_URL', $base);
} ?>
<style>
/* ===== MODALES ENTERPRISE (Nuevo/Editar Usuario) ===== */
.modal-usuario-overlay {
  backdrop-filter: blur(4px);
  background: rgba(0,0,0,0.35);
}
.modal-usuario-box {
  max-height: calc(100vh - 3rem);
  margin: 1.5rem;
  box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
  border: 1px solid rgba(229,231,235,0.8);
}
.modal-usuario-header {
  flex-shrink: 0;
  padding: 0.75rem 1.25rem;
  border-bottom: 1px solid #e5e7eb;
  background: #fff;
}
.modal-usuario-body {
  flex: 1;
  overflow-y: auto;
  padding: 1rem 1.25rem;
  min-height: 0;
}
.modal-usuario-footer {
  flex-shrink: 0;
  padding: 0.75rem 1.25rem;
  border-top: 1px solid #e5e7eb;
  background: #fff;
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
.input-enterprise::placeholder { color: #9ca3af; }
.input-enterprise:hover { border-color: #9ca3af; }
.input-enterprise:focus {
  outline: none;
  border-color: #39A900;
  box-shadow: 0 0 0 3px rgba(57,169,0,0.2);
}
.input-enterprise:disabled {
  background: #f3f4f6;
  cursor: not-allowed;
  opacity: 0.7;
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
.btn-modal-secondary:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}
.btn-modal-secondary:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(156,163,175,0.5);
}
.btn-modal-secondary:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-modal-primary {
  padding: 0.625rem 1.25rem;
  border-radius: 12px;
  font-weight: 500;
  color: #fff;
  background: #0a3a57;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
  transition: all 0.15s;
}
.btn-modal-primary:hover { background: #00304D; }
.btn-modal-primary:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(10,58,87,0.4);
}
.btn-modal-primary:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-modal-primary:active, .btn-modal-secondary:active { transform: scale(0.98); }
.label-enterprise {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.375rem;
}

/* Tabla usuarios: máximo 5 filas visibles (mismo alto que tabla grupos), resto con scroll vertical */
.table-usuarios-wrap {
  overflow-x: auto;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
  max-height: calc(5 * 71px + 2.75rem);
}
.table-usuarios-wrap table thead th {
  position: sticky;
  top: 0;
  z-index: 1;
  background: #f9fafb;
  box-shadow: 0 1px 0 0 #e5e7eb;
}
.table-usuarios-wrap table tbody tr {
  height: 71px;
}
.table-usuarios-wrap table tbody td {
  height: 71px;
  box-sizing: border-box;
  vertical-align: middle;
}

/* Panel desplegable personalizado */
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
.custom-select-dropdown .custom-option:hover {
  background: #f3f4f6;
}
.custom-select-dropdown .custom-option.selected {
  background: rgba(57, 169, 0, 0.1);
  color: #0a3a57;
}
.custom-select-wrapper {
  position: relative;
  width: 100%;
}
.custom-select-wrapper .select-usuario {
  cursor: pointer;
}

/* Filtro roles desactivado cuando no se ha seleccionado Instructor */
#filtroRoles:disabled {
  background-color: #f3f4f6;
  color: #9ca3af;
  border-color: #e5e7eb;
  cursor: not-allowed;
  opacity: 0.85;
}

/* Estilo mejorado para mensajes de error en modales */
.alert-error {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  border-radius: 12px;
  background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
  border: 1px solid #fecaca;
  color: #991b1b;
  font-size: 0.875rem;
  line-height: 1.5;
  box-shadow: 0 2px 8px rgba(220, 38, 38, 0.08);
}
/* Asegurar que el contenedor se oculte cuando tiene la clase hidden (evita conflicto con display: flex) */
.alert-error.hidden {
  display: none !important;
}
/* Nombre completo en tabla: máximo 2 líneas */
.cell-nombre-wrap {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  overflow: hidden;
  word-break: break-word;
  line-clamp: 2;
}
.alert-error svg {
  flex-shrink: 0;
  margin-top: 0.125rem;
}
</style>

<div class="max-w-6xl mx-auto px-4 py-10">

  <h1 class="text-4xl font-extrabold tracking-tight mb-2 text-[#39A900]">
    Gestión de Usuarios
  </h1>
  <p class="text-gray-500 mb-6">Administra los Usuarios</p>

  <div class="bg-white shadow rounded-2xl border border-gray-200">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-6">
      <div>
        <h2 class="text-xl font-semibold text-gray-800">Usuarios</h2>
        <p class="text-sm text-gray-500">Lista de todos los usuarios registrados</p>
      </div>

      <button id="btnAbrirModalUsuario"
        class="w-full md:w-auto bg-[#0a3a57] hover:bg-[#00304D] active:scale-95 transition
               text-white px-5 py-2.5 rounded-xl flex items-center justify-center gap-2 shadow-sm">
        <span>Nuevo Usuario</span>
      </button>
    </div>

    <!-- Filtros -->
    <div class="px-6 py-4 border-b">
      <div class="flex flex-col md:flex-row gap-4">

        <!-- Select cargos-->
        <div class="relative w-full md:w-64">
          <select id="filtroCargos"
            class="select-usuario w-full appearance-none rounded-xl border border-gray-300 bg-white
                   px-4 py-2.5 pr-10 text-sm text-gray-700
                   focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900] outline-none transition
                   hover:border-gray-400">
            <option value="">Todos los cargos</option>
            <option value="Instructor">Instructor</option>
            <option value="Coordinador">Coordinador</option>
          </select>
          <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19 9l-7 7-7-7" />
          </svg>
        </div>

        <!-- Select roles -->
        <div class="relative w-full md:w-64">
          <select id="filtroRoles"
            class="select-usuario w-full appearance-none rounded-xl border border-gray-300 bg-white
                   px-4 py-2.5 pr-10 text-sm text-gray-700
                   focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900] outline-none transition
                   hover:border-gray-400">
            <option value="">Todos los roles</option>
          </select>
          <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19 9l-7 7-7-7" />
          </svg>
        </div>

        <!-- Buscador -->
        <div class="relative w-full md:w-64">
          <input type="text" id="buscadorUsuario"
            placeholder="Buscar usuario..."
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

    <div id="errorTablaUsuarios" class="hidden alert-error mx-6 mb-4" role="alert">
      <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
      </svg>
      <span class="alert-error-text"></span>
    </div>

    <!-- Tabla: máximo 5 filas visibles, scroll para el resto -->
    <div class="overflow-x-auto table-usuarios-wrap">
      <table class="w-full text-left">
        <thead class="bg-gray-50 border-b border-gray-200 text-sm text-gray-600">
          <tr>
            <th class="px-6 py-3 font-medium">Número de Documento</th>
            <th class="px-6 py-3 font-medium">Nombre Completo</th>
            <th class="px-6 py-3 font-medium">Cargo</th>
            <th class="px-6 py-3 font-medium">Correo electrónico</th>
            <th class="px-6 py-3 font-medium text-right">Acciones</th>
          </tr>
        </thead>
        <tbody id="tbodyUsuarios"
          class="text-sm divide-y divide-gray-100">
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ================= MODALES USUARIOS ================= -->
<div id="contenedorModalesUsuarios">
<!-- Modal Nuevo Usuario -->
<div id="modalNuevoUsuario" role="dialog" aria-labelledby="modalNuevoUsuarioTitle" aria-modal="true"
  class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 modal-usuario-overlay hidden">

  <div class="modal-usuario-box bg-white w-full max-w-xl rounded-2xl flex flex-col overflow-hidden">

    <header class="modal-usuario-header flex items-center justify-between">
      <h2 id="modalNuevoUsuarioTitle" class="text-xl font-bold text-[#39A900] tracking-tight">
        Nuevo Usuario
      </h2>
      <button type="button" id="btnCerrarModal" aria-label="Cerrar"
        class="p-2 -m-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </header>

    <form id="formUsuario" class="flex flex-col flex-1 min-h-0" novalidate>
      <div id="errorFormUsuario" class="hidden alert-error mx-6 mt-4" role="alert">
        <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <span class="alert-error-text"></span>
      </div>

      <div class="modal-usuario-body flex-1">
        <div class="space-y-5">
          <div>
            <label for="nombre_completo_nuevo" class="label-enterprise">Nombre completo</label>
            <input type="text" id="nombre_completo_nuevo" name="nombre_completo" required
              placeholder="Ingrese el nombre completo" class="input-enterprise">
            <span class="error-input hidden block mt-1 text-xs text-red-600" data-field="nombre_completo"></span>
          </div>

          <div>
            <label for="tipo_documento_nuevo" class="label-enterprise">Tipo de documento</label>
            <div class="relative">
              <select id="tipo_documento_nuevo" name="tipo_documento" required
                class="select-usuario input-enterprise pr-10 appearance-none cursor-pointer">
                <option value="" disabled selected>Seleccione tipo de documento</option>
                <option value="CC">Cédula de Ciudadanía</option>
                <option value="CE">Cédula de Extranjería</option>
                <option value="PASAPORTE">Pasaporte</option>
              </select>
              <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </div>
          </div>

          <div>
            <label for="numero_documento_nuevo" class="label-enterprise">Número de documento</label>
            <input type="number" id="numero_documento_nuevo" name="numero_documento" required min="1" max="999999999999" data-max-digits="12"
              placeholder="Ingrese el número de documento" class="input-enterprise">
            <span class="error-input hidden block mt-1 text-xs text-red-600" data-field="numero_documento"></span>
          </div>

          <div>
            <label for="correo_nuevo" class="label-enterprise">Correo electrónico</label>
            <input type="email" id="correo_nuevo" name="correo_electronico" required
              placeholder="correo@ejemplo.com" class="input-enterprise">
            <span class="error-input hidden block mt-1 text-xs text-red-600" data-field="correo_electronico"></span>
          </div>

          <div>
            <label for="cargo_nuevo" class="label-enterprise">Cargo</label>
            <div class="relative">
              <select id="cargo_nuevo" name="cargo" class="selectCargoModal select-usuario input-enterprise pr-10 appearance-none cursor-pointer" required>
                <option value="" disabled selected>Seleccione cargo</option>
                <option value="Instructor">Instructor</option>
                <option value="Coordinador">Coordinador</option>
              </select>
              <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </div>
          </div>

          <div class="grupoInstructor hidden space-y-5 pt-4 border-t border-gray-100">
            <div>
              <label for="modalidad_nuevo" class="label-enterprise">Tipo de instructor</label>
              <div class="relative">
                <select id="modalidad_nuevo" name="modalidad" required
                  class="select-usuario input-enterprise pr-10 appearance-none cursor-pointer">
                  <option value="" disabled selected>Seleccione tipo de instructor</option>
                  <option value="Técnico">Técnico</option>
                  <option value="Transversal">Transversal</option>
                </select>
                <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </div>
            </div>
            <div>
              <label for="tipo_contrato_nuevo" class="label-enterprise">Tipo de contrato</label>
              <div class="relative">
                <select id="tipo_contrato_nuevo" name="tipo_contrato" required
                  class="select-usuario input-enterprise pr-10 appearance-none cursor-pointer">
                  <option value="" disabled selected>Seleccione tipo de contrato</option>
                  <option value="Planta">Planta</option>
                  <option value="Contratista">Contratista</option>
                </select>
                <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </div>
            </div>
          </div>

          <div class="grupoCoordinador hidden pt-4 border-t border-gray-100">
            <label for="area_nuevo" class="label-enterprise">Área del coordinador</label>
            <input type="text" id="area_nuevo" name="area_coordinador" required
              placeholder="Ingrese el área" class="input-enterprise">
          </div>
        </div>
      </div>

      <footer class="modal-usuario-footer flex justify-end gap-3">
        <button type="button" id="btnCancelarNuevo" class="btn-modal-secondary">
          Cancelar
        </button>
        <button type="submit" class="btn-modal-primary">
          Guardar Usuario
        </button>
      </footer>
    </form>
  </div>
</div>
<!-- Modal Editar Usuario -->
<div id="modalEditarUsuario" role="dialog" aria-labelledby="modalEditarUsuarioTitle" aria-modal="true"
  class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 modal-usuario-overlay hidden">

  <div class="modal-usuario-box bg-white w-full max-w-xl rounded-2xl flex flex-col overflow-hidden">

    <header class="modal-usuario-header flex items-center justify-between">
      <h2 id="modalEditarUsuarioTitle" class="text-xl font-bold text-[#39A900] tracking-tight">
        Editar Usuario
      </h2>
      <button type="button" id="btnCerrarModalEditar" aria-label="Cerrar"
        class="p-2 -m-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </header>

    <form id="formEditarUsuario" class="flex flex-col flex-1 min-h-0" novalidate>
      <div id="errorFormEditarUsuario" class="hidden alert-error mx-6 mt-4" role="alert">
        <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <span class="alert-error-text"></span>
      </div>

      <div class="modal-usuario-body flex-1">
        <div class="space-y-5">
          <div>
            <label for="nombre_completo_editar" class="label-enterprise">Nombre completo</label>
            <input type="text" id="nombre_completo_editar" name="nombre_completo" required
              placeholder="Ingrese el nombre completo" class="input-enterprise">
            <span class="error-input hidden block mt-1 text-xs text-red-600" data-field="nombre_completo"></span>
          </div>

          <div>
            <label for="tipo_documento_editar" class="label-enterprise">Tipo de documento</label>
            <div class="relative">
              <select id="tipo_documento_editar" name="tipo_documento" required
                class="select-usuario input-enterprise pr-10 appearance-none cursor-pointer">
                <option value="" disabled selected>Seleccione tipo de documento</option>
                <option value="CC">Cédula de Ciudadanía</option>
                <option value="CE">Cédula de Extranjería</option>
                <option value="PASAPORTE">Pasaporte</option>
              </select>
              <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </div>
          </div>

          <div>
            <label for="numero_documento_editar" class="label-enterprise">Número de documento</label>
            <input type="number" id="numero_documento_editar" name="numero_documento" required min="1" max="999999999999"
              placeholder="Máx. 12 dígitos" class="input-enterprise">
            <span class="error-input hidden block mt-1 text-xs text-red-600" data-field="numero_documento"></span>
          </div>

          <div>
            <label for="correo_editar" class="label-enterprise">Correo electrónico</label>
            <input type="email" id="correo_editar" name="correo_electronico" required
              placeholder="correo@ejemplo.com" class="input-enterprise">
            <span class="error-input hidden block mt-1 text-xs text-red-600" data-field="correo_electronico"></span>
          </div>

          <div>
            <label for="cargo_editar" class="label-enterprise">Cargo</label>
            <div class="relative">
              <select id="cargo_editar" name="cargo" class="selectCargoModal select-usuario input-enterprise pr-10 appearance-none cursor-pointer" required>
                <option value="" disabled selected>Seleccione cargo</option>
                <option value="Instructor">Instructor</option>
                <option value="Coordinador">Coordinador</option>
              </select>
              <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </div>
          </div>

          <div class="grupoInstructor hidden space-y-5 pt-4 border-t border-gray-100">
            <div>
              <label for="modalidad_editar" class="label-enterprise">Tipo de instructor</label>
              <div class="relative">
                <select id="modalidad_editar" name="modalidad" required
                  class="select-usuario input-enterprise pr-10 appearance-none cursor-pointer">
                  <option value="" disabled selected>Seleccione tipo de instructor</option>
                  <option value="Técnico">Técnico</option>
                  <option value="Transversal">Transversal</option>
                </select>
                <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </div>
            </div>
            <div>
              <label for="tipo_contrato_editar" class="label-enterprise">Tipo de contrato</label>
              <div class="relative">
                <select id="tipo_contrato_editar" name="tipo_contrato" required
                  class="select-usuario input-enterprise pr-10 appearance-none cursor-pointer">
                  <option value="" disabled selected>Seleccione tipo de contrato</option>
                  <option value="Planta">Planta</option>
                  <option value="Contratista">Contratista</option>
                </select>
                <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </div>
            </div>
          </div>

          <div class="grupoCoordinador hidden pt-4 border-t border-gray-100">
            <label for="area_editar" class="label-enterprise">Área del coordinador</label>
            <input type="text" id="area_editar" name="area_coordinador" required
              placeholder="Ingrese el área" class="input-enterprise">
          </div>
        </div>
      </div>

      <footer class="modal-usuario-footer flex justify-end gap-3">
        <button type="button" id="btnCancelarEditar" class="btn-modal-secondary">
          Cancelar
        </button>
        <button type="submit" class="btn-modal-primary">
          Guardar cambios
        </button>
      </footer>
    </form>
  </div>
</div>

<!-- ================= MODAL VER USUARIO ================= -->
<div id="modalVerUsuario" 
    class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-4">
    
    <div class="bg-white w-full max-w-xl max-h-[85vh] overflow-y-auto rounded-2xl shadow-xl border border-gray-200 px-8 py-10 relative">
        
        <button onclick="cerrarModal('modalVerUsuario')" 
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-lg">
            ✕
        </button>

        <h2 class="text-2xl font-bold text-[#0a3a57] mb-6">
            Detalles del Usuario
        </h2>

        <div id="errorModalVerUsuario" class="hidden alert-error mb-4" role="alert">
          <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
          </svg>
          <span class="alert-error-text"></span>
        </div>

        <!-- Avatar + Nombre + Tags (Rol, Estado) - Diseño alineado -->
        <div class="flex items-start gap-4 mb-8 pb-6 border-b border-gray-200">
            <div class="w-16 h-16 rounded-full flex items-center justify-center shrink-0 text-2xl font-semibold text-gray-500" id="verAvatar" style="background-color:#BFBFBF">—</div>
            <div class="flex-1 min-w-0 flex flex-col gap-2">
                <p id="verNombre" class="text-gray-900 font-bold text-lg leading-tight">Cargando...</p>
                <div class="flex flex-wrap gap-2 items-center">
                    <span id="verCargo" class="inline-block px-3 py-1.5 rounded-full text-xs font-semibold" style="background-color:#A8D4BA;color:#3F6278">Cargo</span>
                    <span id="verEstado" class="inline-block px-3 py-1.5 rounded-full text-xs font-semibold" style="background-color:#C5E7B5;color:#39A900">Estado</span>
                </div>
            </div>
        </div>

        <!-- Atributos -->
        <div class="space-y-5">
            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 tracking-wider">Tipo de documento</label>
                <p id="verTipoDoc" class="text-gray-800 mt-1">—</p>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 tracking-wider">Número documento</label>
                <p id="verNumDoc" class="text-gray-800 mt-1">—</p>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 tracking-wider">Correo electrónico</label>
                <p id="verCorreo" class="text-gray-800 mt-1">—</p>
            </div>

            <div id="verGrupoInstructor" class="grupoInstructor space-y-5 pt-4 border-t border-gray-100 hidden">
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 tracking-wider">Tipo instructor</label>
                    <p id="verTipoIns" class="text-gray-800 mt-1">—</p>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 tracking-wider">Tipo de contrato</label>
                    <p id="verContrato" class="text-gray-800 mt-1">—</p>
                </div>
            </div>

            <div id="verGrupoCoordinador" class="grupoCoordinador pt-4 border-t border-gray-100 hidden">
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 tracking-wider">Área coordinador</label>
                    <p id="verArea" class="text-gray-800 mt-1">—</p>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100">
                <label class="block text-xs font-bold uppercase text-gray-500 tracking-wider">Programas de Formación Vínculados</label>
                <div id="verProgramas" class="text-gray-800 mt-1 space-y-1">—</div>
            </div>
        </div>

        <div class="flex justify-end mt-10">
            <button type="button" id="btnCerrarVerUsuario"
                class="px-6 py-3 rounded-xl border border-gray-300 text-gray-600 hover:bg-gray-50 transition">
                Cerrar
            </button>
        </div>
    </div>
</div>  

<script>
window.API_USUARIO = "<?= BASE_URL ?>src/controllers/UsuarioController.php";
window.ICON_EDIT_USUARIO = "<?= BASE_URL ?>src/assets/img/pencil.svg";
window.ICON_VER_USUARIO = "<?= BASE_URL ?>src/assets/img/eye.svg";
</script>
<script src="<?= BASE_URL ?>src/assets/js/gestionUsuarios.js"></script>