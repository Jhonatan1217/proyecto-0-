<?php
/* ==========================================
   VISTA DE PROGRAMAS
   ========================================== */
?>
<!-- ========== PROGRAMAS ========== -->
<section data-tab="programs" class="tab-pane hidden mt-8">
    <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
        <div>
            <h2 class="text-3xl text-[#39a900] font-bold">Programas de Formación</h2>
            <p class="text-sm text-zinc-500">Gestione los programas de formación disponibles</p>
        </div>
        
        <div class="flex items-center gap-3 ">

            <?php if (($_SESSION['usuario_cargo'] ?? '') !== 'INSTRUCTOR'): ?>
                <button id="btnNewProgram" class="rounded-xl px-4 py-2 text-sm font-medium flex items-center gap-2 bg-[#0a3a57] text-white hover:bg-[#052433] transition-all whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Nuevo Programa
                </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="flex gap-3 flex-wrap mb-5">
        <select id="programTypeFilter" class="w-full sm:w-60 border border-zinc-300 rounded-xl px-3 py-2 text-sm select-nice">
            <option value="all">Todos los tipos de programa</option>
            <option value="tecnico">Técnico</option>
            <option value="tecnologo">Tecnólogo</option>
        </select>
        
        <input id="programSearchInput" type="text" placeholder="Buscar por nombre o código"
         class="w-full sm:w-72 border border-zinc-300 rounded-xl px-3 py-2 text-sm outline-none placeholder-zinc-400">
    </div>
    

    <!-- Grid de Programas -->
    <div id="programsGrid" class="space-y-4"></div>

    <!-- Mensaje cuando no hay programas -->
    <div id="programsEmpty" class="hidden rounded-2xl ring-1 ring-zinc-200 shadow-sm py-12 text-center text-zinc-500">
        No hay programas registrados
    </div>
</section>

<!-- ===== MODAL: Nuevo/Editar Programa ===== -->
<div id="modalProgramBackdrop" class="hidden fixed inset-0 z-40" style="background:rgba(0,0,0,.4)"></div>
<?php if (($_SESSION['usuario_cargo'] ?? '') !== 'INSTRUCTOR'): ?>

    <section id="modalProgram" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl" style="border:1px solid #e5e7eb">
            <div class="flex items-start justify-between p-6 pb-2">
                <div>
                    <h2 id="modalProgramTitle" class="text-2xl font-bold text-zinc-900">Nuevo Programa</h2>
                    <p class="text-sm text-zinc-500">Complete la información del programa de formación</p>
                </div>
                <button id="btnCloseProgram" class="p-2 rounded-lg hover:bg-zinc-100 transition text-zinc-500 hover:text-zinc-700">✕</button>
            </div>

            <form id="formProgramNew" class="p-6 pt-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Código <span class="text-red-500">*</span></label>
                    <input id="pg_code" type="number" min="1" placeholder="Ej: 2896365"
                        class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none focus:border-[#0a3a57] focus:ring-1 focus:ring-[#0a3a57] transition" required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Nombre <span class="text-red-500">*</span></label>
                    <input id="pg_name" type="text" placeholder="Ej: Análisis y Desarrollo de Software (ADSO)"
                        class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none focus:border-[#0a3a57] focus:ring-1 focus:ring-[#0a3a57] transition" required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Tipo de programa <span class="text-red-500">*</span></label>
                    <select id="pg_nivel" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm bg-white focus:border-[#0a3a57] focus:ring-1 focus:ring-[#0a3a57] transition" required>
                        <option value="">Seleccione tipo de programa</option>
                        <option value="tecnico">Técnico</option>
                        <option value="tecnologo">Tecnólogo</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Descripción</label>
                    <textarea id="pg_desc" rows="3" placeholder="Ej: Programa orientado al diseño y desarrollo de aplicaciones empresariales"
                            class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none focus:border-[#0a3a57] focus:ring-1 focus:ring-[#0a3a57] transition resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Duración (horas)</label>
                    <input id="pg_hours" type="number" min="0" placeholder="Ej: 2640"
                        class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none focus:border-[#0a3a57] focus:ring-1 focus:ring-[#0a3a57] transition">
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Instructor asignado</label>
                    <select id="pg_instructor" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm bg-white focus:border-[#0a3a57] focus:ring-1 focus:ring-[#0a3a57] transition">
                        <option value="">Seleccione un instructor</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" id="btnCancelProgram" class="rounded-xl border border-zinc-300 bg-white px-6 py-2.5 text-sm font-medium hover:bg-zinc-50 transition">Cancelar</button>
                    <button type="submit" id="btnSubmitProgram" class="rounded-xl px-6 py-2.5 text-sm font-medium bg-[#0a3a57] text-white hover:bg-[#052433] transition">Guardar</button>
                </div>
            </form>
        </div>
    </section>

<?php endif; ?>

<!-- ===== MODAL: Agregar Instructor ===== -->
<div id="modalInstructorBackdrop" class="hidden fixed inset-0 z-40" style="background:rgba(0,0,0,.4)"></div>
<section id="modalInstructor" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl" style="border:1px solid #e5e7eb">
        <div class="flex items-start justify-between p-6 pb-2">
            <div>
                <h2 id="modalInstructorTitle" class="text-2xl font-bold text-zinc-900">Agregar instructor(es)</h2>
                <p class="text-sm text-zinc-500">Seleccione los instructores para este programa</p>
            </div>
            <button id="btnCloseModalInstructores" class="p-2 rounded-lg hover:bg-zinc-100 transition text-zinc-500 hover:text-zinc-700">✕</button>
        </div>

        <div class="p-6 pt-4">
            <input type="hidden" id="modalInstructorProgramId">
            
            <!-- Instructores asignados actualmente -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Instructores asignados</label>
                <div id="instructoresAsignadosLista" class="space-y-2 max-h-32 overflow-y-auto p-2 bg-zinc-50 rounded-lg border border-zinc-200">
                    <div class="text-sm text-zinc-400 italic text-center">Cargando...</div>
                </div>
            </div>
            
            <!-- Buscador de instructores -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Agregar instructor</label>
                <input type="text" id="searchInstructorInput" placeholder="Ingrese el nombre del Instructor"
                       class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none focus:border-[#0a3a57] focus:ring-1 focus:ring-[#0a3a57] transition">
            </div>
            
            <!-- Resultados de búsqueda -->
            <div class="mb-4">
                <div id="instructoresResultados" class="space-y-2 max-h-40 overflow-y-auto p-2 bg-white rounded-lg border border-zinc-200">
                    <div class="text-sm text-zinc-400 italic text-center">Escriba para buscar instructores</div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button id="btnCancelarInstructores" class="rounded-xl border border-zinc-300 bg-white px-6 py-2.5 text-sm font-medium hover:bg-zinc-50 transition">Cancelar</button>
                <button id="btnGuardarInstructores" class="rounded-xl px-6 py-2.5 text-sm font-medium bg-[#0a3a57] text-white hover:bg-[#052433] transition">Guardar</button>
            </div>
        </div>
    </div>
</section>

<!-- ===== MODAL: Ver Lista de Instructores ===== -->
<div id="modalVerListaBackdrop" class="hidden fixed inset-0 z-40" style="background:rgba(0,0,0,.4)"></div>
<section id="modalVerLista" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl" style="border:1px solid #e5e7eb">
        <div class="flex items-start justify-between p-6 pb-2">
            <div>
                <h2 id="modalVerListaTitle" class="text-2xl font-bold text-zinc-900">Instructores asignados</h2>
                <p class="text-sm text-zinc-500">Lista de instructores del programa</p>
            </div>
            <button id="btnCloseVerLista" class="p-2 rounded-lg hover:bg-zinc-100 transition text-zinc-500 hover:text-zinc-700">✕</button>
        </div>

        <div class="p-6 pt-4">
            <div id="modalVerListaContent" class="max-h-96 overflow-y-auto custom-scroll pr-1">
                <div class="text-center py-8 text-zinc-500">Cargando instructores...</div>
            </div>

            <div class="flex justify-end mt-6">
                <button id="btnCerrarVerLista" class="rounded-xl border border-zinc-300 bg-white px-6 py-2.5 text-sm font-medium hover:bg-zinc-50 transition">Cerrar</button>
            </div>
        </div>
    </div>
</section>

<style>
.switch { font-size: 0; }
.switch .dot { transition: transform 0.2s ease; }
.custom-scroll::-webkit-scrollbar { width: 4px; }
.custom-scroll::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
.custom-scroll::-webkit-scrollbar-thumb { background: #0a3a57; border-radius: 10px; }
</style>