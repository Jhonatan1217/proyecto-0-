<script>
document.addEventListener("DOMContentLoaded", function () {

  const btn = document.getElementById("userDropdownBtn");
  const menu = document.getElementById("userMenu");
  const chevron = document.getElementById("chevronIcon");
  const container = document.getElementById("userDropdownContainer");

  btn.addEventListener("click", function (e) {
    e.stopPropagation();
    menu.classList.toggle("hidden");
    chevron.classList.toggle("rotate-180");
  });

  document.addEventListener("click", function (e) {
    if (!container.contains(e.target)) {
      menu.classList.add("hidden");
      chevron.classList.remove("rotate-180");
    }
  });

});
</script>
<style>
    /* Toasts visibles por encima de los modales de perfil */
    .swal2-container.swal2-top-end {
      top: 5rem !important;
      z-index: 9999999 !important;
    }
    .modal-perfil {
      z-index: 999999 !important;
    }
    #modalSolicitarCambiosPerfil .modal-perfil-footer {
      flex-shrink: 0;
      background: #fff;
    }
    #modalCambiarContrasena #modalCambiarContrasenaTitle {
      display: block !important;
      visibility: visible !important;
      opacity: 1 !important;
      font-size: 1.25rem !important;
      font-weight: 700 !important;
      color: #111827 !important;
    }
    #modalCambiarContrasena .btn-cambiar-contrasena-lock {
      filter: brightness(0) invert(1);
    }
    .rotate-180 {
      transform: rotate(180deg);
    }
    .modal-perfil .modal-perfil-header {
      background: #fff;
      position: relative;
      z-index: 2;
      flex-shrink: 0;
      overflow: visible;
    }
    .modal-perfil .modal-perfil-header .modal-perfil-titulo,
    .modal-perfil .modal-perfil-header .modal-perfil-subtitulo {
      color: #111827;
      display: block !important;
      visibility: visible !important;
      opacity: 1 !important;
    }
    .modal-perfil .modal-perfil-header .modal-perfil-titulo { font-size: 1.25rem; font-weight: 700; }
    .modal-perfil .modal-perfil-header .modal-perfil-subtitulo { font-size: 0.875rem; color: #6b7280; }
    .modal-perfil .btn-cerrar-perfil {
      padding: 0.5rem;
      min-width: 2.5rem;
      min-height: 2.5rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-left: auto;
    }
    #verPerfilAvatar {
      width: 5rem;
      height: 5rem;
      min-width: 5rem;
      min-height: 5rem;
      max-width: 5rem;
      max-height: 5rem;
      border-radius: 50%;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }
    /* Selects estilo grupos/usuarios para modal Solicitar cambios */
    #modalSolicitarCambiosPerfil .select-perfil.input-enterprise {
      width: 100%; min-width: 0; box-sizing: border-box;
      padding-left: 0.75rem; padding-right: 2rem;
      appearance: none; border-radius: 12px; border: 1px solid #d1d5db;
      background: #fff; color: #1f2937;
      transition: border-color 0.15s, box-shadow 0.15s;
    }
    #modalSolicitarCambiosPerfil .select-perfil.input-enterprise:hover { border-color: #9ca3af; }
    #modalSolicitarCambiosPerfil .select-perfil.input-enterprise:focus {
      outline: none; border-color: #39A900; box-shadow: 0 0 0 3px rgba(57,169,0,0.2);
    }
    #modalSolicitarCambiosPerfil .custom-select-wrapper { position: relative; width: 100%; min-width: 0; }
    #modalSolicitarCambiosPerfil .custom-select-trigger {
      display: flex; align-items: center; justify-content: space-between; gap: 0.25rem;
      padding: 0.5rem 0.75rem; padding-right: 2rem; box-sizing: border-box; min-width: 0;
      border-radius: 12px; border: 1px solid #d1d5db; background: #fff;
      cursor: pointer; color: #374151; font-size: 0.875rem;
      transition: border-color 0.15s, box-shadow 0.15s;
    }
    #modalSolicitarCambiosPerfil .custom-select-trigger:hover { border-color: #9ca3af; }
    #modalSolicitarCambiosPerfil .custom-select-trigger:focus-within { border-color: #39A900; box-shadow: 0 0 0 3px rgba(57,169,0,0.2); }
    #modalSolicitarCambiosPerfil .custom-select-trigger .truncate { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    #modalSolicitarCambiosPerfil .custom-select-dropdown {
      position: absolute; left: 0; right: 0; top: 100%; margin-top: 8px;
      border-radius: 12px; border: 1px solid #e5e7eb;
      box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.05);
      background: white; max-height: 220px; overflow-y: auto; z-index: 100;
    }
    #modalSolicitarCambiosPerfil .custom-select-dropdown.dropdown-up {
      top: auto; bottom: 100%; margin-top: 0; margin-bottom: 8px;
      box-shadow: 0 -10px 25px -5px rgba(0,0,0,0.1), 0 -8px 10px -6px rgba(0,0,0,0.05);
    }
    #modalSolicitarCambiosPerfil .custom-select-dropdown .custom-option {
      padding: 10px 14px; cursor: pointer; transition: background 0.15s;
    }
    #modalSolicitarCambiosPerfil .custom-select-dropdown .custom-option:hover { background: #f3f4f6; }
    #modalSolicitarCambiosPerfil .custom-select-dropdown .custom-option.selected {
      background: rgba(57, 169, 0, 0.1); color: #0a3a57;
    }
    #modalSolicitarCambiosPerfil .modal-perfil-footer { flex-shrink: 0; overflow: visible; }
    #modalSolicitarCambiosPerfil .modal-perfil-footer .btn-enviar-solicitud { visibility: visible !important; opacity: 1 !important; }
    #academicos-submenu {
      padding-left: 1.5rem !important;
    }
    #academicos-submenu::before {
      content: '';
      position: absolute;
      left: 1.2rem;
      top: 3rem;
      bottom: 0;
      width: 1px;
      background-color: #000000;
    }
    #academicos-submenu li {
      position: relative;
      padding-left: 1.5rem !important;
    }
    #menu-lateral::-webkit-scrollbar {
      width: 8px;
    }
    #menu-lateral::-webkit-scrollbar-track {
      background: #E8E8E8;
    }
    #menu-lateral::-webkit-scrollbar-thumb {
      background: #BFBFBF;
      border-radius: 4px;
    }
  </style>

<!-- Header -->
<header class="flex items-center justify-between px-6 py-4 border-b shadow-sm bg-white">

  <!-- IZQUIERDA: LOGO -->
  <div class="flex items-center">
    <img src="<?= BASE_URL ?>src/assets/img/logoSena.png"
         alt="SENA Logo"
         class="h-10" />
  </div>

  <!-- DERECHA -->
  <div class="flex items-center gap-8">

    <?php
      $nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
      $correo = $_SESSION['usuario_correo'] ?? '';
      $cargo = $_SESSION['usuario_cargo'] ?? '';
      $iniciales = strtoupper(substr($nombre, 0, 2));
    ?>

    <!-- USER DROPDOWN -->
    <div class="relative" id="userDropdownContainer">

      <button id="userDropdownBtn"
        class="flex items-center space-x-3 focus:outline-none">

        <!-- AVATAR -->
       <div class="w-10 h-10 min-w-[40px] min-h-[40px]
            bg-gray-600 text-white
            rounded-full
            flex items-center justify-center
            font-semibold text-sm
            leading-none">
        <?= $iniciales ?>
      </div>

        <!-- TEXT -->
        <div class="text-left">
          <p class="text-sm font-semibold text-gray-800 leading-none">
            <?= htmlspecialchars($nombre) ?>
          </p>
          <p class="text-xs text-gray-500">
            <?= htmlspecialchars($cargo) ?>
          </p>
        </div>

        <!-- CHEVRON -->
        <svg id="chevronIcon"
             class="w-4 h-4 text-gray-600 transition-transform duration-200"
             fill="none"
             stroke="currentColor"
             viewBox="0 0 24 24"
             stroke-width="2">
          <path stroke-linecap="round"
                stroke-linejoin="round"
                d="M19 9l-7 7-7-7" />
        </svg>

      </button>

      <!-- DROPDOWN -->
      <div id="userMenu"
           class="hidden absolute right-0 mt-3 w-56
                  bg-white border border-gray-200
                  rounded-xl shadow-lg z-50 overflow-hidden">

        <button type="button" data-action="ver-perfil" class="user-widget-action w-full flex items-center gap-3 px-4 py-3 text-sm text-left hover:bg-gray-100 transition">
          <svg class="w-5 h-5 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          <span>Ver perfil</span>
        </button>

        <button type="button" data-action="editar-perfil" class="user-widget-action w-full flex items-center gap-3 px-4 py-3 text-sm text-left hover:bg-gray-100 transition">
          <svg class="w-5 h-5 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
          <span>Editar perfil</span>
        </button>

        <div class="border-t border-gray-200"></div>

        <button type="button" data-action="cerrar-sesion" class="user-widget-action w-full flex items-center gap-3 px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition text-left">
          <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
          <span>Cerrar sesión</span>
        </button>

      </div>
    </div>

    <!-- Botón imagen menú -->
    <img src="<?= BASE_URL ?>src/assets/img/menu.svg" alt="Menú" id="menu-hamburguesa" class="h-8 w-8 cursor-pointer" />

  </div>

</header>

<!-- Modales del widget de perfil -->
<div id="modalVerPerfil" class="modal-perfil fixed inset-0 hidden items-center justify-center p-4 bg-black/40" aria-modal="true" role="dialog" aria-labelledby="modalVerPerfilTitle">
  <div class="relative z-10 bg-white rounded-2xl shadow-xl max-w-md w-full max-h-[calc(100vh-2rem)] overflow-hidden flex flex-col">
    <div class="modal-perfil-header flex items-center justify-between gap-4 p-5 border-b border-gray-200">
      <div class="min-w-0 flex-1">
        <h2 id="modalVerPerfilTitle" class="modal-perfil-titulo text-gray-900">Perfil del usuario</h2>
        <p class="modal-perfil-subtitulo mt-0.5">Información de tu cuenta</p>
      </div>
      <button type="button" data-close="modalVerPerfil" class="btn-cerrar-perfil shrink-0 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition flex items-center justify-center" aria-label="Cerrar"><img src="<?= BASE_URL ?>src/assets/img/icons/Acction/x.svg" alt="" class="w-5 h-5" /></button>
    </div>
    <div class="p-5 overflow-y-auto flex-1">
      <div class="flex flex-col items-center mb-5">
        <div id="verPerfilAvatar" class="bg-gray-200 text-2xl font-bold text-gray-500 mb-3"></div>
        <p id="verPerfilNombre" class="text-lg font-bold text-gray-900"></p>
        <p id="verPerfilRol" class="text-sm text-gray-500"></p>
      </div>
      <div class="border-t border-gray-200 pt-4 space-y-3">
        <div><span class="text-xs font-semibold text-gray-500 uppercase">Nombre</span><p id="verPerfilNombreCampo" class="text-gray-900 mt-0.5"></p></div>
        <div><span class="text-xs font-semibold text-gray-500 uppercase">Número documento</span><p id="verPerfilDocumento" class="text-gray-900 mt-0.5"></p></div>
        <div><span class="text-xs font-semibold text-gray-500 uppercase">Correo electrónico</span><p id="verPerfilCorreo" class="text-gray-900 mt-0.5"></p></div>
        <div><span class="text-xs font-semibold text-gray-500 uppercase">Área del coordinador</span><p id="verPerfilArea" class="text-gray-900 mt-0.5">—</p></div>
      </div>
    </div>
  </div>
</div>

<div id="modalSolicitarCambiosPerfil" class="modal-perfil fixed inset-0 hidden items-center justify-center p-6 sm:p-8 bg-black/40" aria-modal="true" role="dialog" aria-labelledby="modalSolicitarCambiosTitle">
  <div class="relative z-10 bg-white rounded-2xl shadow-xl max-w-md w-full max-h-[55vh] sm:max-h-[60vh] overflow-hidden flex flex-col pt-6">
    <div class="modal-perfil-header flex items-center justify-between gap-4 px-5 pt-5 pb-0">
      <h2 id="modalSolicitarCambiosTitle" class="modal-perfil-titulo text-gray-900 min-w-0 flex-1">Solicitar cambios de perfil</h2>
      <button type="button" data-close="modalSolicitarCambiosPerfil" class="btn-cerrar-perfil shrink-0 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition flex items-center justify-center" aria-label="Cerrar"><img src="<?= BASE_URL ?>src/assets/img/icons/Acction/x.svg" alt="" class="w-5 h-5" /></button>
    </div>
    <p class="px-5 pt-1 pb-2 text-sm text-gray-500">Los cambios serán enviados al Administrador para su aprobación.</p>
    <form id="formSolicitarCambiosPerfil" class="p-5 overflow-y-auto flex-1 min-h-0 space-y-4">
      <div class="border-t border-gray-200 pt-4 -mt-1"><label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label><input type="text" name="nombre_completo" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-green-600/30 focus:border-green-600 outline-none" /></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Tipo de documento</label><div class="relative"><select name="tipo_documento" class="select-perfil input-enterprise py-2.5 text-sm pr-10 appearance-none cursor-pointer"><option value="">Seleccione tipo de documento</option><option value="CC">Cédula de Ciudadanía</option><option value="CE">Cédula de Extranjería</option><option value="PASAPORTE">Pasaporte</option></select></div></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Número de documento</label><input type="text" name="numero_documento" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-green-600/30 focus:border-green-600 outline-none" /></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label><input type="email" name="correo_electronico" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-green-600/30 focus:border-green-600 outline-none" /></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Tipo instructor</label><div class="relative"><select name="tipo_instructor" class="select-perfil input-enterprise py-2.5 text-sm pr-10 appearance-none cursor-pointer"><option value="">Seleccione tipo instructor</option><option value="Técnico">Técnico</option><option value="Transversal">Transversal</option></select></div></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Tipo contrato</label><div class="relative"><select name="tipo_contrato" class="select-perfil input-enterprise py-2.5 text-sm pr-10 appearance-none cursor-pointer"><option value="">Seleccione tipo contrato</option><option value="Planta">Planta</option><option value="Contratista">Contratista</option></select></div></div>
      <div class="pt-3 border-t border-gray-200">
        <p class="text-sm font-semibold text-gray-700 mb-2">Seguridad</p>
        <button type="button" id="btnAbrirCambiarContrasena" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 transition focus:ring-2 focus:ring-green-600/30 outline-none">
          <img src="<?= BASE_URL ?>src/assets/img/icons/Security/Key.svg" alt="" class="w-5 h-5" />
          Cambiar contraseña
        </button>
      </div>
    </form>
    <div class="modal-perfil-footer flex justify-end gap-3 p-5 border-t border-gray-200 bg-white shrink-0">
      <button type="button" data-close="modalSolicitarCambiosPerfil" class="px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 transition">Cancelar</button>
      <button type="submit" form="formSolicitarCambiosPerfil" class="btn-enviar-solicitud px-5 py-2.5 rounded-lg bg-[#0a3a57] text-white text-sm font-semibold hover:bg-[#00304D] transition border-0 shadow-sm">Enviar Solicitud</button>
    </div>
  </div>
</div>

<div id="modalCambiarContrasena" class="modal-perfil fixed inset-0 hidden items-center justify-center p-4 bg-black/40" aria-modal="true" role="dialog" aria-labelledby="modalCambiarContrasenaTitle">
  <div class="relative z-10 bg-white rounded-2xl shadow-xl max-w-md w-full max-h-[calc(100vh-2rem)] overflow-y-auto p-6 flex flex-col">
    <div class="modal-perfil-header flex items-center justify-between gap-4 pb-4 border-b border-gray-200 mb-4">
      <h2 id="modalCambiarContrasenaTitle" class="modal-perfil-titulo text-xl font-bold text-gray-900">Cambiar contraseña</h2>
      <button type="button" data-close="modalCambiarContrasena" class="btn-cerrar-perfil shrink-0 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition flex items-center justify-center" aria-label="Cerrar"><img src="<?= BASE_URL ?>src/assets/img/icons/Acction/x.svg" alt="" class="w-5 h-5" /></button>
    </div>
    <div class="flex flex-col items-center text-center mb-2">
      <div class="rounded-full flex items-center justify-center mb-3 shrink-0" style="background-color: #BFBFBF; width: 32px; height: 32px;">
        <img src="<?= BASE_URL ?>src/assets/img/icons/Security/Shield-Check.svg" alt="" class="w-5 h-5" style="width: 20px; height: 20px;" />
      </div>
      <p class="modal-perfil-subtitulo text-gray-500 mb-5">Tu correo fue verificado correctamente. Ahora ingresa tu nueva contraseña.</p>
    </div>
    <div class="mx-auto w-full max-w-sm">
    <form id="formCambiarContrasena" class="space-y-4 w-full">
      <div><label class="block text-sm font-medium text-gray-700 mb-1 text-left">Contraseña actual</label><div class="relative"><input type="password" name="password_actual" placeholder="Ingrese su contraseña actual" class="w-full rounded-lg border border-gray-300 px-3 py-2 pr-10 text-sm focus:ring-2 focus:ring-green-600/30 focus:border-green-600 outline-none" /><button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 toggle-pwd p-1 flex items-center justify-center" tabindex="-1" aria-label="Mostrar contraseña"><img src="<?= BASE_URL ?>src/assets/img/eye.svg" alt="" class="w-5 h-5 icon-eye-open" /><img src="<?= BASE_URL ?>src/assets/img/eye-off.svg" alt="" class="w-5 h-5 icon-eye-closed hidden" /></button></div></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1 text-left">Nueva contraseña</label><div class="relative"><input type="password" name="password_nueva" placeholder="Ingrese la nueva contraseña" class="w-full rounded-lg border border-gray-300 px-3 py-2 pr-10 text-sm focus:ring-2 focus:ring-green-600/30 focus:border-green-600 outline-none" /><button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 toggle-pwd p-1 flex items-center justify-center" tabindex="-1" aria-label="Mostrar contraseña"><img src="<?= BASE_URL ?>src/assets/img/eye.svg" alt="" class="w-5 h-5 icon-eye-open" /><img src="<?= BASE_URL ?>src/assets/img/eye-off.svg" alt="" class="w-5 h-5 icon-eye-closed hidden" /></button></div></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1 text-left">Confirmar contraseña</label><div class="relative"><input type="password" name="password_confirmar" placeholder="Ingrese la contraseña de nuevo" class="w-full rounded-lg border border-gray-300 px-3 py-2 pr-10 text-sm focus:ring-2 focus:ring-green-600/30 focus:border-green-600 outline-none" /><button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 toggle-pwd p-1 flex items-center justify-center" tabindex="-1" aria-label="Mostrar contraseña"><img src="<?= BASE_URL ?>src/assets/img/eye.svg" alt="" class="w-5 h-5 icon-eye-open" /><img src="<?= BASE_URL ?>src/assets/img/eye-off.svg" alt="" class="w-5 h-5 icon-eye-closed hidden" /></button></div></div>
    </form>
    <div class="mt-8 space-y-3 w-full">
      <button type="submit" form="formCambiarContrasena" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-gray-600 text-white text-sm font-medium hover:bg-gray-700 transition"><img src="<?= BASE_URL ?>src/assets/img/icons/Security/lock.svg" alt="" class="w-5 h-5 btn-cambiar-contrasena-lock" />Cambiar contraseña</button>
      <button type="button" id="btnVolverEditarPerfil" class="w-full inline-flex items-center justify-center gap-2 text-sm text-gray-600 hover:text-gray-900 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Volver editar perfil
      </button>
    </div>
    </div>
  </div>
</div>

<!-- Modal confirmación cerrar sesión -->
<div id="modalCerrarSesion" class="modal-perfil fixed inset-0 hidden items-center justify-center p-4 bg-black/40" aria-modal="true" role="dialog" aria-labelledby="modalCerrarSesionTitle">
  <div class="relative z-10 bg-white rounded-2xl shadow-xl max-w-sm w-full max-h-[calc(100vh-2rem)] overflow-y-auto p-6 flex flex-col items-center text-center">
    <img src="<?= BASE_URL ?>src/assets/img/triangle-alert.svg" alt="" class="w-14 h-14 mb-4 shrink-0" aria-hidden="true" />
    <h2 id="modalCerrarSesionTitle" class="modal-perfil-titulo text-gray-900 mb-2">Cerrar sesión</h2>
    <p class="text-sm text-gray-600 mb-6">¿Está seguro que quiere salir?</p>
    <div class="flex gap-3 w-full">
      <button type="button" data-close="modalCerrarSesion" class="flex-1 px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 transition">Cancelar</button>
      <button type="button" id="btnConfirmarCerrarSesion" class="flex-1 px-4 py-2.5 rounded-lg bg-red-600 text-white text-sm font-medium hover:bg-red-700 transition">Aceptar</button>
    </div>
  </div>
</div>

<script>
window.API_USUARIO = "<?= BASE_URL ?>src/controllers/UsuarioController.php";
window.USUARIO_ID = <?= json_encode((int)($_SESSION['usuario_id'] ?? 0)) ?>;
window.BASE_URL = <?= json_encode(BASE_URL) ?>;
</script>
<script src="<?= BASE_URL ?>src/assets/js/gestionPerfil.js"></script>

<!-- Menú lateral -->
  <nav id="menu-lateral" class="fixed top-0 right-0 h-full w-80 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out z-50 pointer-events-none overflow-y-auto">
    <div class="flex justify-between items-center p-4 border-b border-gray-400 mx-4">
      <h2 class="font-semibold text-gray-800 text-xl">Menú de navegación</h2>
      <button id="cerrar-menu" class="text-gray-600 text-2xl hover:text-black">×</button>
    </div>

    <ul class="p-4 space-y-4 text-gray-700">
      <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
        <img src="<?= BASE_URL ?>src/assets/img/calendar-days.svg" alt="Icono de Horarios">
        <a href="<?= BASE_URL ?>index.php?page=src/views/register_tables">Horarios</a>
      </li>

      <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
        <img src="<?= BASE_URL ?>src/assets/img/map-pin.svg" alt="Icono de Áreas">
        <a href="<?= BASE_URL ?>index.php?page=src/views/gestionAreas">Áreas</a>
      </li>
      
      <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
        <img src="<?= BASE_URL ?>src/assets/img/house-plus.svg" alt="Icono de Zonas">
        <a href="<?= BASE_URL ?>index.php?page=src/views/gestionZonas">Zonas</a>
      </li>

      <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
        <img src="<?= BASE_URL ?>src/assets/img/layers.svg" alt="Icono de Trimestres">
        <a href="<?= BASE_URL ?>index.php?page=src/views/gestionTrimestres">Trimestres</a>
      </li>

      <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
        <img src="<?= BASE_URL ?>src/assets/img/users.svg" alt="Icono de Usuarios">
        <a href="<?= BASE_URL ?>index.php?page=gestionUsuarios">Usuarios</a>
      </li>

      <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
        <img src="<?= BASE_URL ?>src/assets/img/folder-minus.svg" alt="Icono de Grupos">
        <a href="<?= BASE_URL ?>index.php?page=src/views/gestionGrupos">Grupos</a>
      </li>

      <li class="relative">
        <div class="flex items-center justify-between hover:text-[#39a900] cursor-pointer p-2" id="academicos-toggle">
          <div class="flex items-center space-x-2">
            <img src="<?= BASE_URL ?>src/assets/img/book-open.svg" alt="Icono de Académicos">
            <span>Académicos</span>
            <svg class="w-4 h-4 transition-transform duration-200" id="academicos-flecha" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>
        
        <!-- Submenú de Académicos (inicialmente oculto) -->
        <ul class="pl-12 mt-2 space-y-2 hidden" id="academicos-submenu">
          <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
            <img src="<?= BASE_URL ?>src/assets/img/upload.svg" alt="Icono de Carga Excel" class="w-5 h-5">
            <a href="<?= BASE_URL ?>index.php?page=academicos&tab=upload">Carga Excel</a>
          </li>
          <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
            <img src="<?= BASE_URL ?>src/assets/img/graduation-cap.svg" alt="Icono de Programas" class="w-5 h-5">
            <a href="<?= BASE_URL ?>index.php?page=academicos&tab=programs">Programas</a>
          </li>
          <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
            <img src="<?= BASE_URL ?>src/assets/img/book.svg" alt="Icono de Competencias" class="w-5 h-5">
            <a href="<?= BASE_URL ?>index.php?page=academicos&tab=competencies">Competencias</a>
          </li>
          <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
            <img src="<?= BASE_URL ?>src/assets/img/target.svg" alt="Icono de RAE" class="w-5 h-5">
            <a href="<?= BASE_URL ?>index.php?page=academicos&tab=raes">RAE</a>
          </li>
        </ul>
      </li>

      <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
        <img src="<?= BASE_URL ?>src/assets/img/clipboard-list.svg" alt="Icono de Solicitudes">
        <a href="#">Solicitudes</a>
      </li>

      <li class="flex items-center space-x-2 hover:text-[#39a900] cursor-pointer p-2">
        <img src="<?= BASE_URL ?>src/assets/img/history.svg" alt="Icono de Trimestres">
        <a href="<?= BASE_URL ?>index.php?page=src/views/historialRegistrosInactivos">Historial</a>
      </li>
    </ul>
  </nav>

  <script src="<?= BASE_URL ?>src/assets/js/header.js"></script>

