// src/assets/js/gestionProgramas.js
document.addEventListener('DOMContentLoaded', function() {
    // ===============================
    // CONFIG
    // ===============================
    const API = (window.API_PROGRAMAS || (window.BASE_URL || '') + 'src/controllers/ProgramasController.php').replace(/\/+$/, '');

    // ===============================
    // SELECTORES PRINCIPALES
    // ===============================
    const grid = document.getElementById('programsGrid');
    const emptyBox = document.getElementById('programsEmpty');
    
    // Modal Programa
    const modalProgram = document.getElementById('modalProgram');
    const modalProgramBackdrop = document.getElementById('modalProgramBackdrop');
    const formProgram = document.getElementById('formProgramNew');
    const inpCode = document.getElementById('pg_code');
    const inpName = document.getElementById('pg_name');
    const inpNivel = document.getElementById('pg_nivel');
    const inpDesc = document.getElementById('pg_desc');
    const inpHours = document.getElementById('pg_hours');
    const selectInstructor = document.getElementById('pg_instructor');
    const btnCloseProgram = document.getElementById('btnCloseProgram');
    const btnCancelProgram = document.getElementById('btnCancelProgram');
    const btnNewProgram = document.getElementById('btnNewProgram');
    const modalProgramTitle = document.getElementById('modalProgramTitle');

    // Modal Instructor (para agregar)
    const modalInstructor = document.getElementById('modalInstructor');
    const modalInstructorBackdrop = document.getElementById('modalInstructorBackdrop');
    const modalInstructorProgramId = document.getElementById('modalInstructorProgramId');
    const instructoresAsignadosLista = document.getElementById('instructoresAsignadosLista');
    const searchInstructorInput = document.getElementById('searchInstructorInput');
    const instructoresResultados = document.getElementById('instructoresResultados');
    const btnCloseModalInstructor = document.getElementById('btnCloseModalInstructores');
    const btnCancelarInstructor = document.getElementById('btnCancelarInstructores');
    const btnGuardarInstructor = document.getElementById('btnGuardarInstructores');
    const modalInstructorTitle = document.getElementById('modalInstructorTitle');

    // Modal Ver Lista
    const modalVerLista = document.getElementById('modalVerLista');
    const modalVerListaBackdrop = document.getElementById('modalVerListaBackdrop');
    const modalVerListaTitle = document.getElementById('modalVerListaTitle');
    const modalVerListaContent = document.getElementById('modalVerListaContent');
    const btnCloseVerLista = document.getElementById('btnCloseVerLista');
    const btnCerrarVerLista = document.getElementById('btnCerrarVerLista');

    // Filtros
    const programTypeFilter = document.getElementById('programTypeFilter');
    const programSearchInput = document.getElementById('programSearchInput');

    // ===============================
    // VARIABLES GLOBALES
    // ===============================
    let editingId = null;
    let allPrograms = [];
    let allInstructores = [];
    let instructoresAsignados = [];
    let programaSeleccionadoId = null;
    let timeoutBusqueda = null;

    // ===============================
    // TOASTS
    // ===============================
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2500
    });

    function showToast(icon, title) {
        Toast.fire({ icon: icon, title: title });
    }

    // ===============================
    // FUNCIONES API
    // ===============================
    function apiRequest(accion, method, data, callback) {
        let url = API + '?accion=' + accion;
        let options = {
            method: method,
            credentials: 'same-origin',
            headers: method !== 'GET' ? { 'Content-Type': 'application/json' } : {}
        };
        
        if (method === 'GET' && data) {
            let params = new URLSearchParams(data);
            url += '&' + params.toString();
        }
        
        if (method !== 'GET' && data) {
            options.body = JSON.stringify(data);
        }
        
        fetch(url, options)
            .then(response => response.json())
            .then(result => {
                if (callback) callback(result);
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('error', 'Error en la petición');
            });
    }

    function listarProgramas() {
        apiRequest('listar', 'GET', null, function(data) {
            allPrograms = Array.isArray(data) ? data : [];
            aplicarFiltros();
        });
    }

    function listarTodosInstructores() {
        apiRequest('listarTodosInstructores', 'GET', null, function(data) {
            allInstructores = Array.isArray(data) ? data : [];
            cargarSelectInstructores();
        });
    }

    function listarInstructoresPrograma(id_programa, callback) {
        apiRequest('listarInstructores', 'GET', { id_programa: id_programa }, function(data) {
            instructoresAsignados = Array.isArray(data) ? data : [];
            if (callback) callback(instructoresAsignados);
        });
    }

    function asignarInstructores(id_programa, instructores, callback) {
        apiRequest('asignarInstructores', 'POST', { 
            id_programa: parseInt(id_programa), 
            instructores: instructores 
        }, function(res) {
            if (res && res.success) {
                showToast('success', res.success);
            } else if (res && res.error) {
                showToast('error', res.error);
            }
            if (callback) callback(res);
        });
    }

    function crearPrograma(datos, callback) {
        apiRequest('crear', 'POST', datos, callback);
    }

    function actualizarPrograma(datos, callback) {
        apiRequest('actualizar', 'POST', datos, callback);
    }

    function cambiarEstado(id_programa, estado, callback) {
        apiRequest('cambiarEstado', 'POST', { id_programa, estado }, callback);
    }

    // ===============================
    // FUNCIÓN PARA CARGAR SELECT DE INSTRUCTORES
    // ===============================
    function cargarSelectInstructores() {
        if (!selectInstructor) return;
        
        let valorActual = selectInstructor.value;
        selectInstructor.innerHTML = '<option value="">Seleccione un instructor</option>';
        
        if (allInstructores.length > 0) {
            for (let i = 0; i < allInstructores.length; i++) {
                let inst = allInstructores[i];
                let option = document.createElement('option');
                option.value = inst.id_instructor;
                option.textContent = inst.nombre_instructor;
                selectInstructor.appendChild(option);
            }
        }
        
        if (valorActual) {
            selectInstructor.value = valorActual;
        }
    }

    // ===============================
    // MODAL VER LISTA
    // ===============================
    function openModalVerLista(id_programa, nombre_programa) {
        if (!modalVerLista || !modalVerListaBackdrop) {
            showToast('error', 'Error al abrir la lista');
            return;
        }
        
        modalVerListaTitle.textContent = 'Instructores asignados - ' + (nombre_programa || 'Programa');
        modalVerListaContent.innerHTML = '<div class="text-center py-4 text-zinc-500">Cargando instructores...</div>';
        
        modalVerLista.classList.remove('hidden');
        modalVerListaBackdrop.classList.remove('hidden');
        
        listarInstructoresPrograma(id_programa, function(asignados) {
            renderListaInstructores(asignados);
        });
    }

    function closeModalVerLista() {
        if (modalVerLista && modalVerListaBackdrop) {
            modalVerLista.classList.add('hidden');
            modalVerListaBackdrop.classList.add('hidden');
        }
    }

    function renderListaInstructores(asignados) {
        if (!modalVerListaContent) return;
        
        if (!asignados || asignados.length === 0) {
            modalVerListaContent.innerHTML = '<div class="text-center py-8 text-zinc-500">No hay instructores asignados a este programa</div>';
            return;
        }
        
        let html = '<div class="space-y-3">';
        for (let i = 0; i < asignados.length; i++) {
            let inst = asignados[i];
            html += '<div class="flex items-center gap-3 p-3 bg-zinc-50 rounded-lg border border-zinc-200">';
            html += '  <div class="w-8 h-8 rounded-full bg-[#0a3a57] bg-opacity-20 flex items-center justify-center">';
            html += '    <span class="text-sm font-bold text-[#0a3a57]">' + (inst.nombre_instructor ? inst.nombre_instructor.charAt(0).toUpperCase() : '?') + '</span>';
            html += '  </div>';
            html += '  <div class="flex-1">';
            html += '    <p class="text-sm font-medium text-zinc-800">' + escapeHtml(inst.nombre_instructor) + '</p>';
            if (inst.correo_electronico) {
                html += '    <p class="text-xs text-zinc-500">' + escapeHtml(inst.correo_electronico) + '</p>';
            }
            html += '  </div>';
            html += '</div>';
        }
        html += '</div>';
        
        modalVerListaContent.innerHTML = html;
    }

    // ===============================
    // MODAL PROGRAMA
    // ===============================
    function openModalProgram(isCreate, data) {
        editingId = isCreate ? null : (data ? data.id_programa : null);
        
        inpCode.value = isCreate ? '' : (data ? data.id_programa : '');
        inpName.value = isCreate ? '' : (data ? data.nombre_programa : '');
        inpNivel.value = isCreate ? '' : (data ? data.nivel_formacion : '');
        inpDesc.value = isCreate ? '' : (data ? data.descripcion : '');
        inpHours.value = isCreate ? '' : (data ? data.duracion : '');
        
        modalProgramTitle.textContent = isCreate ? 'Nuevo Programa' : 'Editar Programa';
        
        if (!isCreate && data && data.id_programa) {
            cargarInstructorAsignado(data.id_programa);
        } else {
            if (selectInstructor) {
                selectInstructor.value = '';
            }
        }
        
        modalProgram.classList.remove('hidden');
        modalProgramBackdrop.classList.remove('hidden');
    }

    function cargarInstructorAsignado(id_programa) {
        listarInstructoresPrograma(id_programa, function(asignados) {
            if (asignados && asignados.length > 0 && selectInstructor) {
                selectInstructor.value = asignados[0].id_instructor;
            }
        });
    }

    function closeModalProgram() {
        modalProgram.classList.add('hidden');
        modalProgramBackdrop.classList.add('hidden');
        formProgram.reset();
        editingId = null;
    }

    // ===============================
    // MODAL INSTRUCTOR (para agregar)
    // ===============================
    function openModalInstructor(id_programa, nombre_programa) {
        if (!modalInstructor || !modalInstructorBackdrop || !modalInstructorProgramId) {
            showToast('error', 'Error al abrir el modal');
            return;
        }
        
        programaSeleccionadoId = id_programa;
        modalInstructorProgramId.value = id_programa;
        
        if (modalInstructorTitle) {
            modalInstructorTitle.textContent = 'Agregar instructor(es) - ' + (nombre_programa || 'Programa');
        }
        
        listarInstructoresPrograma(id_programa, function(asignados) {
            renderInstructoresAsignados(asignados);
        });
        
        if (searchInstructorInput) {
            searchInstructorInput.value = '';
        }
        
        if (instructoresResultados) {
            instructoresResultados.innerHTML = '<div class="text-sm text-zinc-400 italic text-center">Escriba para buscar instructores</div>';
        }
        
        modalInstructor.classList.remove('hidden');
        modalInstructorBackdrop.classList.remove('hidden');
    }

    function closeModalInstructor() {
        if (modalInstructor && modalInstructorBackdrop) {
            modalInstructor.classList.add('hidden');
            modalInstructorBackdrop.classList.add('hidden');
        }
        programaSeleccionadoId = null;
        instructoresAsignados = [];
    }

    function renderInstructoresAsignados(asignados) {
        if (!instructoresAsignadosLista) return;
        
        if (!asignados || asignados.length === 0) {
            instructoresAsignadosLista.innerHTML = '<div class="text-sm text-zinc-400 italic text-center">No hay instructores asignados</div>';
            return;
        }
        
        let html = '';
        for (let i = 0; i < asignados.length; i++) {
            let inst = asignados[i];
            html += '<div class="flex items-center justify-between bg-white p-2 rounded-lg border border-zinc-100">';
            html += '  <div class="flex items-center gap-2">';
            html += '    <div class="w-6 h-6 rounded-full bg-[#0a3a57] bg-opacity-10 flex items-center justify-center">';
            html += '      <span class="text-xs font-medium text-[#0a3a57]">' + (inst.nombre_instructor ? inst.nombre_instructor.charAt(0) : '?') + '</span>';
            html += '    </div>';
            html += '    <span class="text-sm text-zinc-700">' + escapeHtml(inst.nombre_instructor) + '</span>';
            html += '  </div>';
            html += '  <button class="text-red-500 hover:text-red-700 text-xs font-medium px-2 py-1 hover:bg-red-50 rounded transition quitar-instructor" data-id="' + inst.id_instructor + '">✕</button>';
            html += '</div>';
        }
        instructoresAsignadosLista.innerHTML = html;
        
        document.querySelectorAll('.quitar-instructor').forEach(btn => {
            btn.addEventListener('click', function() {
                let idInstructor = this.getAttribute('data-id');
                if (programaSeleccionadoId) {
                    quitarInstructor(programaSeleccionadoId, idInstructor);
                }
            });
        });
    }

    function buscarInstructores(termino) {
        if (!instructoresResultados) return;
        
        if (!termino || termino.length < 2) {
            instructoresResultados.innerHTML = '<div class="text-sm text-zinc-400 italic text-center">Escriba al menos 2 caracteres</div>';
            return;
        }
        
        termino = termino.toLowerCase();
        let filtrados = allInstructores.filter(inst => 
            inst.nombre_instructor && inst.nombre_instructor.toLowerCase().includes(termino)
        );
        
        let idsAsignados = instructoresAsignados.map(i => i.id_instructor);
        filtrados = filtrados.filter(inst => !idsAsignados.includes(inst.id_instructor));
        
        if (filtrados.length === 0) {
            instructoresResultados.innerHTML = '<div class="text-sm text-zinc-400 italic text-center">No se encontraron instructores</div>';
            return;
        }
        
        let html = '';
        for (let i = 0; i < filtrados.length; i++) {
            let inst = filtrados[i];
            html += '<div class="flex items-center justify-between p-2 hover:bg-zinc-50 rounded-lg cursor-pointer border border-transparent hover:border-zinc-200 agregar-instructor-item" data-id="' + inst.id_instructor + '" data-nombre="' + escapeHtml(inst.nombre_instructor) + '">';
            html += '  <span class="text-sm text-zinc-700">' + escapeHtml(inst.nombre_instructor) + '</span>';
            html += '  <span class="text-xs bg-[#0a3a57] text-white px-2 py-1 rounded-full">Agregar</span>';
            html += '</div>';
        }
        instructoresResultados.innerHTML = html;
        
        document.querySelectorAll('.agregar-instructor-item').forEach(item => {
            item.addEventListener('click', function() {
                let idInstructor = this.getAttribute('data-id');
                let nombreInstructor = this.getAttribute('data-nombre');
                if (programaSeleccionadoId) {
                    agregarInstructor(programaSeleccionadoId, idInstructor, nombreInstructor);
                }
            });
        });
    }

    function agregarInstructor(idPrograma, idInstructor, nombreInstructor) {
        let nuevosInstructores = instructoresAsignados.map(i => i.id_instructor);
        nuevosInstructores.push(parseInt(idInstructor));
        
        asignarInstructores(idPrograma, nuevosInstructores, function() {
            listarInstructoresPrograma(idPrograma, function(asignados) {
                renderInstructoresAsignados(asignados);
                if (searchInstructorInput) {
                    searchInstructorInput.value = '';
                }
                if (instructoresResultados) {
                    instructoresResultados.innerHTML = '<div class="text-sm text-zinc-400 italic text-center">Escriba para buscar instructores</div>';
                }
                if (selectInstructor) {
                    cargarInstructorAsignado(idPrograma);
                }
                listarProgramas(); // Actualizar la vista principal
            });
        });
    }

    function quitarInstructor(idPrograma, idInstructor) {
        let nuevosInstructores = instructoresAsignados
            .map(i => i.id_instructor)
            .filter(id => id != idInstructor);
        
        asignarInstructores(idPrograma, nuevosInstructores, function() {
            listarInstructoresPrograma(idPrograma, function(asignados) {
                renderInstructoresAsignados(asignados);
                if (selectInstructor) {
                    cargarInstructorAsignado(idPrograma);
                }
                listarProgramas(); // Actualizar la vista principal
            });
        });
    }

    // ===============================
    // TARJETAS
    // ===============================
    function escapeHtml(text) {
        if (!text) return '';
        let div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatHours(h) {
        return h ? h + ' horas' : '0 horas';
    }

    function crearTarjeta(programa) {
        const activo = programa.estado == 1;
        const numInstructores = programa.num_instructores || 0;

        const card = document.createElement('div');
        card.className = 'bg-white border border-zinc-200 rounded-2xl p-5 shadow-sm hover:shadow transition flex flex-col h-full';
        card.dataset.id = programa.id_programa;

        card.innerHTML = `
            <div class="flex justify-between gap-4">
                <div class="flex-1">
                    <div class="mb-2 text-sm font-medium text-zinc-600">Estado</div>
                    <h3 class="text-lg font-semibold text-zinc-800 mb-2">${escapeHtml(programa.nombre_programa)}</h3>
                    <div class="text-sm text-zinc-500 mb-2">Código: ${escapeHtml(programa.id_programa)}</div>
                    <div class="text-sm text-zinc-600 mb-2">Duración: ${formatHours(programa.duracion)}</div>
                    <div class="text-sm text-zinc-600 mb-3">Número de instructores: ${numInstructores}</div>
                    <div class="text-sm text-zinc-600">
                        <button class="text-[#39a900] hover:text-[#2d8200] font-medium underline underline-offset-2 btn-ver-lista" data-id="${programa.id_programa}" data-nombre="${escapeHtml(programa.nombre_programa)}">
                            Instructores asignados: ${numInstructores > 0 ? 'Ver lista' : 'Sin instructores'}
                        </button>
                    </div>
                </div>

                <div class="flex items-start gap-2">
                    <button class="p-2 rounded-lg hover:bg-zinc-100 editar-programa"
                        data-programa='${JSON.stringify(programa).replace(/'/g, '&apos;')}'>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 3l4 4-7 7H10v-4l7-7z"/>
                            <path d="M3 21h18"/>
                        </svg>
                    </button>

                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox"
                            class="peer sr-only cambiar-estado"
                            data-id="${programa.id_programa}"
                            ${activo ? 'checked' : ''}>
                        <span class="w-11 h-6 bg-zinc-300 rounded-full peer-checked:bg-[#39A900] transition"></span>
                        <span class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition peer-checked:translate-x-5"></span>
                    </label>
                </div>
            </div>

            <div class="mt-auto flex justify-end">
                <button class="text-sm px-5 py-2.5 rounded-lg bg-[#0a3a57] text-white hover:bg-[#052433] transition asignar-instructor">
                    Agregar instructor(es)
                </button>
            </div> 
        `;

        card.querySelector('.editar-programa').addEventListener('click', e => {
            e.stopPropagation();
            let programaData = JSON.parse(e.currentTarget.getAttribute('data-programa'));
            openModalProgram(false, programaData);
        });

        card.querySelector('.cambiar-estado').addEventListener('click', function (e) {
            e.stopPropagation();
            const checked = this.checked;

            cambiarEstado(programa.id_programa, checked ? 1 : 0, res => {
                if (res?.error) {
                    showToast('error', res.error);
                    this.checked = !checked;
                } else {
                    showToast('success', checked ? 'Programa activado' : 'Programa inhabilitado');
                    listarProgramas(); // Actualizar la vista
                }
            });
        });

        card.querySelector('.asignar-instructor').addEventListener('click', e => {
            e.stopPropagation();
            openModalInstructor(programa.id_programa, programa.nombre_programa);
        });

        card.querySelector('.btn-ver-lista').addEventListener('click', e => {
            e.stopPropagation();
            let id = e.currentTarget.getAttribute('data-id');
            let nombre = e.currentTarget.getAttribute('data-nombre');
            openModalVerLista(id, nombre);
        });

        return card;
    }

    function renderizarTarjetas(programas) {
        grid.innerHTML = '';
        
        if (!programas || programas.length === 0) {
            emptyBox.classList.remove('hidden');
            return;
        }
        
        emptyBox.classList.add('hidden');
        
        for (var i = 0; i < programas.length; i++) {
            grid.appendChild(crearTarjeta(programas[i]));
        }
    }

    // ===============================
    // FILTROS
    // ===============================
    function aplicarFiltros() {
        var tipo = programTypeFilter ? programTypeFilter.value : 'all';
        var busqueda = programSearchInput ? programSearchInput.value.toLowerCase() : '';
        
        var filtrados = [];
        
        for (var i = 0; i < allPrograms.length; i++) {
            var p = allPrograms[i];
            var cumple = true;
            
            if (tipo !== 'all' && p.nivel_formacion !== tipo) {
                cumple = false;
            }
            
            if (cumple && busqueda) {
                var nombre = (p.nombre_programa || '').toLowerCase();
                var codigo = String(p.id_programa || '');
                if (!nombre.includes(busqueda) && !codigo.includes(busqueda)) {
                    cumple = false;
                }
            }
            
            if (cumple) {
                filtrados.push(p);
            }
        }
        
        renderizarTarjetas(filtrados);
    }

    // ===============================
    // EVENTOS MODAL PROGRAMA
    // ===============================
    btnNewProgram.addEventListener('click', function() {
        openModalProgram(true, null);
    });

    btnCloseProgram.addEventListener('click', closeModalProgram);
    btnCancelProgram.addEventListener('click', function(e) {
        e.preventDefault();
        closeModalProgram();
    });

    modalProgramBackdrop.addEventListener('click', closeModalProgram);

    // ===============================
    // EVENTOS MODAL INSTRUCTOR
    // ===============================
    if (btnCloseModalInstructor) {
        btnCloseModalInstructor.addEventListener('click', closeModalInstructor);
    }
    
    if (btnCancelarInstructor) {
        btnCancelarInstructor.addEventListener('click', closeModalInstructor);
    }
    
    if (modalInstructorBackdrop) {
        modalInstructorBackdrop.addEventListener('click', closeModalInstructor);
    }

    if (searchInstructorInput) {
        searchInstructorInput.addEventListener('input', function() {
            let termino = this.value.trim();
            
            if (timeoutBusqueda) {
                clearTimeout(timeoutBusqueda);
            }
            
            timeoutBusqueda = setTimeout(function() {
                buscarInstructores(termino);
            }, 300);
        });
    }

    if (btnGuardarInstructor) {
        btnGuardarInstructor.addEventListener('click', function() {
            closeModalInstructor();
        });
    }

    // ===============================
    // EVENTOS MODAL VER LISTA
    // ===============================
    if (btnCloseVerLista) {
        btnCloseVerLista.addEventListener('click', closeModalVerLista);
    }
    
    if (btnCerrarVerLista) {
        btnCerrarVerLista.addEventListener('click', closeModalVerLista);
    }
    
    if (modalVerListaBackdrop) {
        modalVerListaBackdrop.addEventListener('click', closeModalVerLista);
    }

    // ===============================
    // EVENTOS FILTROS
    // ===============================
    programTypeFilter.addEventListener('change', aplicarFiltros);
    programSearchInput.addEventListener('input', aplicarFiltros);

    // ===============================
    // EVENTO FORM PROGRAMA
    // ===============================
    formProgram.addEventListener('submit', function(e) {
        e.preventDefault();

        var id_programa = inpCode.value.trim();
        var nombre_programa = inpName.value.trim();
        var nivel_formacion = inpNivel.value;
        var descripcion = inpDesc.value.trim();
        var duracion = inpHours.value.trim();
        var id_instructor = selectInstructor ? selectInstructor.value : '';

        if (!id_programa || !nombre_programa) {
            showToast('warning', 'Código y nombre son obligatorios');
            return;
        }

        if (!nivel_formacion) {
            showToast('warning', 'Debe seleccionar un tipo de programa');
            return;
        }

        var datos = {
            id_programa: parseInt(id_programa),
            nombre_programa: nombre_programa,
            nivel_formacion: nivel_formacion,
            descripcion: descripcion,
            duracion: duracion ? parseInt(duracion) : 0
        };

        if (editingId) {
            datos.id_programa = editingId;
            datos.nuevo_id_programa = parseInt(id_programa);
            
            actualizarPrograma(datos, function(res) {
                if (res && res.error) {
                    showToast('error', res.error);
                } else {
                    if (id_instructor) {
                        asignarInstructores(parseInt(id_programa), [parseInt(id_instructor)], function() {
                            closeModalProgram();
                            showToast('success', 'Programa actualizado');
                            listarProgramas(); // Actualizar la vista
                        });
                    } else {
                        closeModalProgram();
                        showToast('success', 'Programa actualizado');
                        listarProgramas(); // Actualizar la vista
                    }
                }
            });
        } else {
            crearPrograma(datos, function(res) {
                if (res && res.error) {
                    showToast('error', res.error);
                } else {
                    if (id_instructor) {
                        asignarInstructores(parseInt(id_programa), [parseInt(id_instructor)], function() {
                            closeModalProgram();
                            showToast('success', 'Programa creado');
                            listarProgramas(); // Actualizar la vista
                        });
                    } else {
                        closeModalProgram();
                        showToast('success', 'Programa creado');
                        listarProgramas(); // Actualizar la vista
                    }
                }
            });
        }
    });

    // ===============================
    // INICIO
    // ===============================
    listarProgramas();
    listarTodosInstructores();
});