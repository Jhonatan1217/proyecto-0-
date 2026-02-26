const API = window.API_USUARIO;
let usuarios = [];
let programas = [];
let tiposInstructor = [];

async function apiRequest(accion, method = "GET", body = null) {
    const config = {
        method,
        headers: { "Content-Type": "application/json" }
    };
    if (body) config.body = JSON.stringify(body);

    const res = await fetch(`${API}?accion=${accion}`, config);
    return await res.json();
}

/* =========================
    lISTAR USUARIOS
========================= */
async function cargarUsuarios() {
  const response = await apiRequest("listar");
  if (response.status === "success") {
      usuarios = response.data;
      renderTabla(usuarios);
  }
}

function renderTabla(data) {
  const tbody = document.getElementById("tbodyUsuarios");
  tbody.innerHTML = "";
  data.forEach(usuario => {
    tbody.innerHTML += `
      <tr class="hover:bg-gray-50">
        <td class="px-6 py-4 whitespace-nowrap">${usuario.numero_documento}</td>
        <td class="px-6 py-4 whitespace-nowrap">${usuario.nombre_completo}</td>
        <td class="px-6 py-4 whitespace-nowrap">${usuario.correo_electronico}</td>
        <td class="px-6 py-4 whitespace-nowrap text-right">
          <button onclick="editarUsuario(${usuario.id_usuario})" class="text-blue-500 hover:text-blue-700 mr-2">Editar</button>
          <button onclick="verUsuario(${usuario.id_usuario})" class="text-green-500 hover:text-green-700 mr-2">Activar</button>
          <!-- Switch -->
            <label class="relative inline-flex items-center cursor-pointer select-none">
              <input
                type="checkbox"
                class="sr-only peer toggleEstado"
                data-numero="${t.numero_trimestre}"
                ${t.estado == 1 ? "checked" : ""}
                aria-checked="${t.estado == 1 ? "true" : "false"}"
                aria-label="Activar trimestre ${t.numero_trimestre}"
              >
              <!-- Track -->
              <div
                class="w-11 h-6 rounded-full bg-gray-200 transition
                       peer-focus-visible:outline-none peer-focus-visible:ring-2 peer-focus-visible:ring-[#39A900]/60
                       peer-checked:bg-[#39A900] peer-disabled:opacity-60"
              ></div>
              <!-- Knob -->
              <div
                class="absolute left-0.5 top-0.5 w-5 h-5 rounded-full bg-white shadow
                       transition-transform duration-200 ease-out
                       peer-checked:translate-x-5"
              ></div>
            </label>
        </td>
      </tr>`;
  });
}

/* =========================
    DESACTIVAR Y ACTIVAR TOGGLE ESTADO USUARIO
========================= */

  document.querySelectorAll(".toggleEstado").forEach(chk => {
    chk.addEventListener("change", async (e) => {
      const numero = e.currentTarget.dataset.numero;
      const accion = e.currentTarget.checked ? "reactivar" : "suspender";
      try {
        const res = await fetch(API_URL, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ accion, numero_trimestre: numero })
        });
        const data = await res.json();

        if (data.mensaje?.includes("Error")) {
          toast("No se pudo actualizar el estado", "error");
          e.currentTarget.checked = !e.currentTarget.checked;
        } else {
          toast(data.mensaje, "success");
        }
      } catch (error) {
        toast("No se pudo conectar con el servidor", "error");
        e.currentTarget.checked = !e.currentTarget.checked;
      }
    });
  });



    
/* =========================
    MOSTRAR CAMPOS SEGÚN CARGO
========================= */
document.addEventListener('DOMContentLoaded', function() {
    const selectCargo = document.getElementById('selectCargoModal');
    const grupoInstructor = document.getElementById('grupoInstructor');
    const grupoCoordinador = document.getElementById('grupoCoordinador');

    selectCargo.addEventListener('change', function() {
        if (this.value === 'Instructor') {
            // Mostrar campos de instructor, ocultar coordinador
            grupoInstructor.classList.remove('hidden');
            grupoCoordinador.classList.add('hidden');
            
            // Opcional: Hacer que los inputs internos sean required o no
            grupoInstructor.querySelectorAll('select').forEach(s => s.required = true);
            grupoCoordinador.querySelector('input').required = false;
        } else {
            // Mostrar campos de coordinador, ocultar instructor
            grupoInstructor.classList.add('hidden');
            grupoCoordinador.classList.remove('hidden');
            
            grupoInstructor.querySelectorAll('select').forEach(s => s.required = false);
            grupoCoordinador.querySelector('input').required = true;
        }
    });
});