<?php /* views/grupos.php */ ?>
<?php if(!defined('BASE_URL')) { 
  $base = '/senlock/';
  define('BASE_URL', $base);
} ?>

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
            class="w-full appearance-none rounded-full border border-gray-300 bg-white
                   px-4 py-2.5 pr-10 text-sm
                   focus:ring-2 focus:ring-[#39A900]/20
                   focus:border-[#39A900] outline-none transition">
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
            class="w-full appearance-none rounded-full border border-gray-300 bg-white
                   px-4 py-2.5 pr-10 text-sm
                   focus:ring-2 focus:ring-[#39A900]/20
                   focus:border-[#39A900] outline-none transition">
            <option value="">Todos los roles</option>
            <option value="Instructor">Gestor de horario</option>
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

    <div id="errorTablaUsuarios" class="hidden text-red-600 text-sm p-3 bg-red-50 rounded-lg mx-6 mb-4"></div>

    <!-- Tabla -->
    <div class="overflow-x-auto">
      <table class="w-full text-left">
        <thead class="bg-gray-50 border-b border-gray-200 text-sm text-gray-600">
          <tr>
            <th class="px-6 py-3 font-medium">Número de Documento</th>
            <th class="px-6 py-3 font-medium">Nombre Completo</th>
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
<div id="modalNuevoUsuario"
  class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-4">

  <!-- Caja Modal -->
  <div class="bg-white w-full max-w-xl max-h-[80vh] overflow-y-auto rounded-2xl shadow-xl border border-gray-200
              px-8 py-10 relative">

    <!-- Botón cerrar -->
    <button id="btnCerrarModal"
      class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-lg">
      ✕
    </button>

    <h2 class="text-2xl font-bold text-[#39A900] mb-8 text-left">
      Nuevo Usuario
    </h2>

    <form id="formUsuario" class="space-y-6" novalidate>

      <div id="errorFormUsuario" class="hidden text-red-600 text-sm p-3 bg-red-50 rounded-lg mb-4"></div>

      <div class="flex flex-col gap-6">

        <!-- Nombre Completo -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Nombre Completo
          </label>
          <input type="text" name="nombre_completo" required
            placeholder="Ingrese el nombre completo"
            class="w-full border border-gray-300 rounded-xl px-4 py-3
                   focus:ring-2 focus:ring-[#39A900]/20
                   focus:border-[#39A900] outline-none transition">
          <span class="error-input hidden text-red-600 text-xs mt-1" data-field="nombre_completo"></span>
        </div>

        <!--  Tipo de documento -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Tipo de documento
          </label>
          <div class="relative">
            <select name="tipo_documento" required
              class="w-full border border-gray-300 rounded-xl px-4 py-3 pr-10
                     focus:ring-2 focus:ring-[#39A900]/20
                     focus:border-[#39A900] outline-none transition appearance-none">
              <option value="Cédula de Ciudadanía">Cédula de Ciudadanía</option>  
              <option value="Cédula de Extranjería">Cédula de Extranjería</option>
              <option value="Pasaporte">Pasaporte</option>
            </select>
            <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 flex items-center">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Número -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Número de Documento
          </label>
          <input type="number" name="numero_documento" required min="1" max="999999999"
            placeholder="Ingrese el número de documento (máx. 9 dígitos)"
            class="w-full border border-gray-300 rounded-xl px-4 py-3
                   focus:ring-2 focus:ring-[#39A900]/20
                   focus:border-[#39A900] outline-none transition">
          <span class="error-input hidden text-red-600 text-xs mt-1" data-field="numero_documento"></span>
        </div>                                                                                    

        <!-- Correo -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Correo electrónico
          </label>
          <input type="email" name="correo_electronico" required
            placeholder="Ingrese el correo electrónico"
            class="w-full border border-gray-300 rounded-xl px-4 py-3
                   focus:ring-2 focus:ring-[#39A900]/20
                   focus:border-[#39A900] outline-none transition">
          <span class="error-input hidden text-red-600 text-xs mt-1" data-field="correo_electronico"></span>
        </div>

        <!-- Cargo -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Cargo
          </label>
          <div class="relative">
            <select name="cargo" 
                    class="selectCargoModal w-full border border-gray-300 rounded-xl px-4 py-3 pr-10
                    focus:ring-2 focus:ring-[#39A900]/20
                    focus:border-[#39A900] outline-none transition appearance-none"
                    required>
              <option value="Instructor">Instructor</option>
              <option value="Coordinador">Coordinador</option>
            </select>
            <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 flex items-center">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </div>
        </div>

        <div class="grupoInstructor space-y-6">
          <!-- Tipo de instructor -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Tipo de instructor
            </label>
            <div class="relative">
              <select name="modalidad" required
                class="w-full border border-gray-300 rounded-xl px-4 py-3 pr-10
                      focus:ring-2 focus:ring-[#39A900]/20
                      focus:border-[#39A900] outline-none transition appearance-none">
                <option value="Técnico">Técnico</option>
                <option value="Transversal">Transversal</option>
              </select>
              <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 flex items-center">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </div>
          </div>
          <!-- Tipo de contrato -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Tipo de contrato
            </label>
            <div class="relative">
              <select name="tipo_contrato" required
                class="w-full border border-gray-300 rounded-xl px-4 py-3 pr-10
                      focus:ring-2 focus:ring-[#39A900]/20
                      focus:border-[#39A900] outline-none transition appearance-none">
                <option value="Planta">Planta</option>
                <option value="Contratista">Contratista</option>
              </select>
              <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 flex items-center">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Área del coordinador -->
        <div class="grupoCoordinador hidden">
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Área del coordinador
          </label>
          <input type="text" name="area_coordinador" required
            placeholder="Ingrese el área del coordinador"
            class="w-full border border-gray-300 rounded-xl px-4 py-3
                   focus:ring-2 focus:ring-[#39A900]/20
                   focus:border-[#39A900] outline-none transition">
        </div>

      <!-- Botones -->
      <div class="flex justify-end gap-4 pt-6">

        <button type="button" id="btnCancelarNuevo"
          class="px-6 py-3 rounded-xl border border-gray-300 text-gray-600
                 hover:bg-gray-50 transition">
          Cancelar
        </button>

        <button type="submit"
          class="px-6 py-3 rounded-xl bg-[#0a3a57] text-white
                 hover:bg-[#00304D] transition shadow-sm">
          Guardar Usuario
        </button>

      </div>

    </form>
  </div>
</div>
</div>
<!-- ================= MODAL EDITAR USUARIO ================= -->
<div id="modalEditarUsuario"
  class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-4">

  <!-- Caja Modal -->
  <div class="bg-white w-full max-w-xl max-h-[80vh] overflow-y-auto rounded-2xl shadow-xl border border-gray-200
              px-8 py-10 relative">

    <!-- Botón cerrar -->
    <button id="btnCerrarModalEditar"
      class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-lg">
      ✕
    </button>

    <h2 class="text-2xl font-bold text-[#39A900] mb-8 text-left">
      Editar Usuario
    </h2>

    <form id="formEditarUsuario" class="space-y-6" novalidate>

      <div id="errorFormEditarUsuario" class="hidden text-red-600 text-sm p-3 bg-red-50 rounded-lg mb-4"></div>

      <div class="flex flex-col gap-6">

        <!-- Nombre Completo -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Nombre Completo
          </label>
          <input type="text" name="nombre_completo" required
            placeholder="Ingrese el nombre completo"
            class="w-full border border-gray-300 rounded-xl px-4 py-3
                   focus:ring-2 focus:ring-[#39A900]/20
                   focus:border-[#39A900] outline-none transition">
          <span class="error-input hidden text-red-600 text-xs mt-1" data-field="nombre_completo"></span>
        </div>

        <!--  Tipo de documento -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Tipo de documento
          </label>
          <div class="relative">
            <select name="tipo_documento" required
              class="w-full border border-gray-300 rounded-xl px-4 py-3 pr-10
                     focus:ring-2 focus:ring-[#39A900]/20
                     focus:border-[#39A900] outline-none transition appearance-none">
              <option value="Cédula de Ciudadanía">Cédula de Ciudadanía</option>  
              <option value="Cédula de Extranjería">Cédula de Extranjería</option>
              <option value="Pasaporte">Pasaporte</option>
            </select>
            <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 flex items-center">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Número -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Número de Documento
          </label>
          <input type="number" name="numero_documento" required min="1" max="999999999"
            placeholder="Ingrese el número de documento (máx. 9 dígitos)"
            class="w-full border border-gray-300 rounded-xl px-4 py-3
                   focus:ring-2 focus:ring-[#39A900]/20
                   focus:border-[#39A900] outline-none transition">
          <span class="error-input hidden text-red-600 text-xs mt-1" data-field="numero_documento"></span>
        </div>
        <!-- Correo -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Correo electrónico
          </label>
          <input type="email" name="correo_electronico" required
            placeholder="Ingrese el correo electrónico"
            class="w-full border border-gray-300 rounded-xl px-4 py-3
                   focus:ring-2 focus:ring-[#39A900]/20
                   focus:border-[#39A900] outline-none transition">
          <span class="error-input hidden text-red-600 text-xs mt-1" data-field="correo_electronico"></span>
        </div>

          <!-- Cargo -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Cargo
          </label>
          <div class="relative">
            <select name="cargo" 
                    class="selectCargoModal w-full border border-gray-300 rounded-xl px-4 py-3 pr-10
                    focus:ring-2 focus:ring-[#39A900]/20
                    focus:border-[#39A900] outline-none transition appearance-none"
                    required>
              <option value="Instructor">Instructor</option>
              <option value="Coordinador">Coordinador</option>
            </select>
            <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 flex items-center">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </div>
        </div>

        <div class="grupoInstructor space-y-6">
          <!-- Tipo de instructor -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Tipo de instructor
            </label>
            <div class="relative">
              <select name="modalidad" required
                class="w-full border border-gray-300 rounded-xl px-4 py-3 pr-10
                      focus:ring-2 focus:ring-[#39A900]/20
                      focus:border-[#39A900] outline-none transition appearance-none">
                <option value="Técnico">Técnico</option>
                <option value="Transversal">Transversal</option>
              </select>
              <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 flex items-center">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </div>
          </div>
          <!-- Tipo de contrato -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Tipo de contrato
            </label>
            <div class="relative">
              <select name="tipo_contrato" required
                class="w-full border border-gray-300 rounded-xl px-4 py-3 pr-10
                      focus:ring-2 focus:ring-[#39A900]/20
                      focus:border-[#39A900] outline-none transition appearance-none">
                <option value="Planta">Planta</option>
                <option value="Contratista">Contratista</option>
              </select>
              <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 flex items-center">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Área del coordinador -->
        <div class="grupoCoordinador hidden">
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Área del coordinador
          </label>
          <input type="text" name="area_coordinador" required
            placeholder="Ingrese el área del coordinador"
            class="w-full border border-gray-300 rounded-xl px-4 py-3
                   focus:ring-2 focus:ring-[#39A900]/20
                   focus:border-[#39A900] outline-none transition">
        </div>

      <!-- Botones -->
      <div class="flex justify-end gap-4 pt-6">

        <button type="button" id="btnCancelarEditar"
          class="px-6 py-3 rounded-xl border border-gray-300 text-gray-600
                 hover:bg-gray-50 transition">
          Cancelar
        </button>

        <button type="submit"
          class="px-6 py-3 rounded-xl bg-[#0a3a57] text-white
                 hover:bg-[#00304D] transition shadow-sm">
          Guardar Usuario
        </button>

      </div>
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

        <div id="errorModalVerUsuario" class="hidden text-red-600 text-sm p-3 bg-red-50 rounded-lg mb-4"></div>

        <!-- Avatar + Nombre + Tags (Rol, Estado) - Diseño alineado -->
        <div class="flex items-start gap-4 mb-8 pb-6 border-b border-gray-200">
            <div class="w-16 h-16 rounded-full flex items-center justify-center shrink-0 text-2xl font-semibold text-gray-500 bg-[#D9D9D9]" id="verAvatar">
                —
            </div>
            <div class="flex-1 min-w-0 flex flex-col gap-2">
                <p id="verNombre" class="text-gray-900 font-bold text-lg leading-tight">Cargando...</p>
                <div class="flex flex-wrap gap-2 items-center">
                    <span id="verCargo" class="inline-block px-3 py-1.5 rounded-full text-xs font-semibold bg-blue-700/35 text-blue-900">Cargo</span>
                    <span id="verEstado" class="inline-block px-3 py-1.5 rounded-full text-xs font-semibold bg-[#39A900]/20 text-[#39A900]">Estado</span>
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