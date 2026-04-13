<?php defined('BASE_URL') || exit('BASE_URL no definido'); ?>
<!-- Modales Usuarios: Nuevo, Editar, Ver -->
<?php
$chevron = '<span class="select-usuario-chevron pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></span>';
$selTipoDoc = '<option value="" disabled selected>Seleccione tipo de documento</option><option value="CC">Cédula de Ciudadanía</option><option value="CE">Cédula de Extranjería</option><option value="PASAPORTE">Pasaporte</option>';
$selCargo = '<option value="" disabled selected>Seleccione cargo</option><option value="Instructor">Instructor</option><option value="Coordinador">Coordinador</option>';
$selModalidad = '<option value="" disabled selected>Seleccione tipo de instructor</option><option value="Técnico">Técnico</option><option value="Transversal">Transversal</option>';
$selContrato = '<option value="" disabled selected>Seleccione tipo de contrato</option><option value="Planta">Planta</option><option value="Contratista">Contratista</option>';
function grupoInstructorCoor($suf) {
  global $chevron, $selModalidad, $selContrato;
  $cbId = 'rol_trimestralizacion_' . $suf;
  $areaId = 'area_' . $suf;
  return '<div class="grupoInstructor hidden space-y-5 pt-4 border-t border-gray-100"><div><label for="modalidad_' . $suf . '" class="label-enterprise">Tipo de instructor</label><div class="relative"><select id="modalidad_' . $suf . '" name="modalidad" required class="select-styled select-usuario">' . $selModalidad . '</select>' . $chevron . '</div></div><div><label for="tipo_contrato_' . $suf . '" class="label-enterprise">Tipo de contrato</label><div class="relative"><select id="tipo_contrato_' . $suf . '" name="tipo_contrato" required class="select-styled select-usuario">' . $selContrato . '</select>' . $chevron . '</div></div><div class="pt-2"><label class="inline-flex items-center gap-2 cursor-pointer"><input type="checkbox" name="rol_encargado_trimestralizacion" id="' . $cbId . '" value="1" class="rounded border-gray-300 text-[#39A900] focus:ring-[#39A900]"><span class="text-sm text-gray-700">Encargado de trimestralización</span></label></div></div><div class="grupoCoordinador hidden pt-4 border-t border-gray-100"><label for="' . $areaId . '" class="label-enterprise">Área del coordinador (Opcional)</label><div class="relative"><select id="' . $areaId . '" name="area_coordinador" class="select-styled select-usuario"><option value="">Sin asignar área</option></select>' . $chevron . '</div></div>';
}
?>
<div id="contenedorModalesUsuarios">
<!-- Modal Nuevo Usuario -->
<div id="modalNuevoUsuario" role="dialog" aria-labelledby="modalNuevoUsuarioTitle" aria-modal="true" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 modal-usuario-overlay hidden">
  <div class="modal-usuario-box bg-white rounded-2xl overflow-hidden">
    <header class="modal-usuario-header flex items-center justify-between">
      <h2 id="modalNuevoUsuarioTitle" class="text-xl font-bold text-[#39A900] tracking-tight">Nuevo Usuario</h2>
      <button type="button" id="btnCerrarModal" aria-label="Cerrar" class="p-2 -m-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </header>
    <form id="formUsuario" class="flex flex-col flex-1 min-h-0" novalidate>
      <input type="hidden" name="estado" value="0">
      <div id="errorFormUsuario" class="hidden alert-error mx-6 mt-4" role="alert"><svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg><span class="alert-error-text"></span></div>
      <div class="modal-usuario-body flex-1"><div class="modal-usuario-form-content space-y-5">
        <div><label for="nombre_completo_nuevo" class="label-enterprise">Nombre completo</label><input type="text" id="nombre_completo_nuevo" name="nombre_completo" required placeholder="Ingrese el nombre completo" class="input-enterprise"><span class="error-input hidden block mt-1 text-xs text-red-600" data-field="nombre_completo"></span></div>
        <div><label for="tipo_documento_nuevo" class="label-enterprise">Tipo de documento</label><div class="relative"><select id="tipo_documento_nuevo" name="tipo_documento" required class="select-styled select-usuario"><?= $selTipoDoc ?></select><?= $chevron ?></div></div>
        <div><label for="numero_documento_nuevo" class="label-enterprise">Número de documento</label><input type="number" id="numero_documento_nuevo" name="numero_documento" required min="1" max="999999999999" data-max-digits="12" placeholder="Ingrese el número de documento" class="input-enterprise"><span class="error-input hidden block mt-1 text-xs text-red-600" data-field="numero_documento"></span></div>
        <div><label for="correo_nuevo" class="label-enterprise">Correo electrónico</label><input type="email" id="correo_nuevo" name="correo_electronico" required placeholder="correo@ejemplo.com" class="input-enterprise"><span class="error-input hidden block mt-1 text-xs text-red-600" data-field="correo_electronico"></span></div>
        <div><label for="cargo_nuevo" class="label-enterprise">Cargo</label><div class="relative"><select id="cargo_nuevo" name="cargo" class="select-styled selectCargoModal select-usuario" required><?= $selCargo ?></select><?= $chevron ?></div></div>
        <?= grupoInstructorCoor('nuevo') ?>
      </div></div>
      <footer class="modal-usuario-footer flex justify-end gap-3"><button type="button" id="btnCancelarNuevo" class="btn-modal-secondary">Cancelar</button><button type="submit" class="btn-modal-primary">Guardar Usuario</button></footer>
    </form>
  </div>
</div>
<!-- Modal Editar Usuario -->
<div id="modalEditarUsuario" role="dialog" aria-labelledby="modalEditarUsuarioTitle" aria-modal="true" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 modal-usuario-overlay hidden">
  <div class="modal-usuario-box bg-white rounded-2xl overflow-hidden">
    <header class="modal-usuario-header flex items-center justify-between">
      <h2 id="modalEditarUsuarioTitle" class="text-xl font-bold text-[#39A900] tracking-tight">Editar Usuario</h2>
      <button type="button" id="btnCerrarModalEditar" aria-label="Cerrar" class="p-2 -m-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </header>
    <form id="formEditarUsuario" class="flex flex-col flex-1 min-h-0" novalidate>
      <div id="errorFormEditarUsuario" class="hidden alert-error mx-6 mt-4" role="alert"><svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg><span class="alert-error-text"></span></div>
      <div class="modal-usuario-body flex-1"><div class="modal-usuario-form-content space-y-5">
        <div><label for="nombre_completo_editar" class="label-enterprise">Nombre completo</label><input type="text" id="nombre_completo_editar" name="nombre_completo" required placeholder="Ingrese el nombre completo" class="input-enterprise"><span class="error-input hidden block mt-1 text-xs text-red-600" data-field="nombre_completo"></span></div>
        <div><label for="tipo_documento_editar" class="label-enterprise">Tipo de documento</label><div class="relative"><select id="tipo_documento_editar" name="tipo_documento" required class="select-styled select-usuario"><?= $selTipoDoc ?></select><?= $chevron ?></div></div>
        <div><label for="numero_documento_editar" class="label-enterprise">Número de documento</label><input type="number" id="numero_documento_editar" name="numero_documento" required min="1" max="999999999999" placeholder="Máx. 12 dígitos" class="input-enterprise"><span class="error-input hidden block mt-1 text-xs text-red-600" data-field="numero_documento"></span></div>
        <div><label for="correo_editar" class="label-enterprise">Correo electrónico</label><input type="email" id="correo_editar" name="correo_electronico" required placeholder="correo@ejemplo.com" class="input-enterprise"><span class="error-input hidden block mt-1 text-xs text-red-600" data-field="correo_electronico"></span></div>
        <div><label for="cargo_editar" class="label-enterprise">Cargo</label><div class="relative"><select id="cargo_editar" name="cargo" class="select-styled selectCargoModal select-usuario" required><?= $selCargo ?></select><?= $chevron ?></div></div>
        <?= grupoInstructorCoor('editar') ?>
      </div></div>
      <footer class="modal-usuario-footer flex justify-end gap-3"><button type="button" id="btnCancelarEditar" class="btn-modal-secondary">Cancelar</button><button type="submit" class="btn-modal-primary">Guardar cambios</button></footer>
    </form>
  </div>
</div>
<!-- Modal Ver Usuario -->
<div id="modalVerUsuario" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 modal-usuario-overlay hidden">
  <div class="modal-usuario-box bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
    <header class="modal-usuario-header flex items-center justify-between"><h2 class="text-xl font-bold text-[#0a3a57] tracking-tight">Detalles del Usuario</h2><button type="button" id="btnCerrarVerUsuario" aria-label="Cerrar" class="btn-modal-secondary inline-flex items-center gap-2">Cerrar</button></header>
    <div class="modal-usuario-body flex-1 min-h-0 px-6 py-4 flex flex-col">
      <div id="errorModalVerUsuario" class="hidden alert-error mb-4 flex-shrink-0" role="alert"><svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg><span class="alert-error-text"></span></div>
      <div id="verUsuarioLoading" class="flex-1 flex flex-col items-center justify-center py-12 gap-4 min-h-[200px] hidden"><div class="w-10 h-10 border-2 border-[#39A900] border-t-transparent rounded-full animate-spin" role="status" aria-label="Cargando"></div><p class="text-gray-600 text-sm font-medium">Cargando datos del usuario...</p></div>
      <div id="verUsuarioContent" class="flex-1 min-h-0 overflow-y-auto hidden">
        <div class="flex items-start gap-4 mb-6 pb-6 border-b border-gray-200"><div class="w-16 h-16 rounded-full flex items-center justify-center shrink-0 text-2xl font-semibold text-gray-500" id="verAvatar" style="background-color:#BFBFBF">—</div><div class="flex-1 min-w-0 flex flex-col gap-2"><p id="verNombre" class="text-gray-900 font-bold text-lg leading-tight">—</p><div class="flex flex-wrap gap-2 items-center"><span id="verCargo" class="inline-block px-3 py-1.5 rounded-full text-xs font-semibold" style="background-color:#A8D4BA;color:#3F6278">—</span><span id="verEstado" class="inline-block px-3 py-1.5 rounded-full text-xs font-semibold" style="background-color:#C5E7B5;color:#39A900">—</span></div></div></div>
        <div class="space-y-5"><div><label class="block text-xs font-bold uppercase text-gray-500 tracking-wider">Tipo de documento</label><p id="verTipoDoc" class="text-gray-800 mt-1">—</p></div><div><label class="block text-xs font-bold uppercase text-gray-500 tracking-wider">Número documento</label><p id="verNumDoc" class="text-gray-800 mt-1">—</p></div><div><label class="block text-xs font-bold uppercase text-gray-500 tracking-wider">Correo electrónico</label><p id="verCorreo" class="text-gray-800 mt-1">—</p></div><div id="verGrupoInstructor" class="grupoInstructor space-y-5 pt-4 border-t border-gray-100 hidden"><div><label class="block text-xs font-bold uppercase text-gray-500 tracking-wider">Tipo instructor</label><p id="verTipoIns" class="text-gray-800 mt-1">—</p></div><div><label class="block text-xs font-bold uppercase text-gray-500 tracking-wider">Tipo de contrato</label><p id="verContrato" class="text-gray-800 mt-1">—</p></div><div id="verRolTrimestralizacion" class="hidden"><label class="block text-xs font-bold uppercase text-gray-500 tracking-wider">Rol funcional</label><p class="text-gray-800 mt-1">Encargado de trimestralización</p></div></div><div id="verGrupoCoordinador" class="grupoCoordinador pt-4 border-t border-gray-100 hidden"><div><label class="block text-xs font-bold uppercase text-gray-500 tracking-wider">Área coordinador</label><p id="verArea" class="text-gray-800 mt-1">—</p></div></div><div class="pt-4 border-t border-gray-100"><label class="block text-xs font-bold uppercase text-gray-500 tracking-wider">Programas de Formación Vínculados</label><div id="verProgramas" class="text-gray-800 mt-1 space-y-1">—</div></div></div>
      </div>
    </div>
  </div>
</div>
</div>
