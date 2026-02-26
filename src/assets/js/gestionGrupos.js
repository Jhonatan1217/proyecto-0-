const API = window.API_FICHA;
let grupos = [];
let programas = [];
let lideres = [];

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
   CARGAR SELECTORES
========================= */
async function cargarSelectores() {
    const response = await apiRequest("obtener_datos_selectores");

    if (response.status !== "success") return;

    programas = response.data.programas;
    lideres = response.data.lideres;

    const selectPrograma = document.getElementById("selectProgramaModal");
    const selectLider = document.getElementById("selectLiderModal");
    const filtroPrograma = document.getElementById("filtroPrograma");

    programas.forEach(p => {
        const opt = new Option(p.nombre_programa, p.id_programa);
        selectPrograma?.appendChild(opt);
        filtroPrograma?.appendChild(opt.cloneNode(true));
    });

    lideres.forEach(l => {
        const opt = new Option(l.nombre, l.id_usuario);
        selectLider?.appendChild(opt);
    });
}

/* =========================
   LISTAR GRUPOS
========================= */
async function cargarGrupos() {
    const response = await apiRequest("listar");
    if (response.status === "success") {
        grupos = response.data;
        renderTabla(grupos);
    }
}

function renderTabla(data) {
    const tbody = document.getElementById("tbodyGrupos");
    tbody.innerHTML = "";

    data.forEach(g => {
        tbody.innerHTML += `
            <tr class="border-b">
                <td class="px-6 py-4">${g.numero_ficha}</td>
                <td class="px-6 py-4">${g.nombre_programa ?? ''}</td>
                <td class="px-6 py-4">${g.jornada}</td>
                <td class="px-6 py-4">${g.modalidad}</td>
                <td class="px-6 py-4">${g.nombre_lider ?? ''}</td>
                <td class="px-6 py-4 text-right">
                    <button onclick="eliminarGrupo(${g.id_ficha})"
                        class="text-red-500 hover:text-red-700">
                        Eliminar
                    </button>
                </td>
            </tr>
        `;
    });
}

/* =========================
   ELIMINAR
========================= */
window.eliminarGrupo = async function(id) {
    if (!confirm("¿Eliminar grupo?")) return;
    const response = await apiRequest("eliminar", "DELETE", { id_ficha: id });
    if (response.status === "success") cargarGrupos();
};

/* =========================
   MODAL
========================= */
function togglerModal(show = true) {
    const modal = document.getElementById("modalGrupo");
    if (show) {
        modal.classList.remove("hidden");
        modal.classList.add("flex");
        document.body.style.overflow = "hidden";
    } else {
        modal.classList.add("hidden");
        modal.classList.remove("flex");
        document.body.style.overflow = "auto";
        document.getElementById("formGrupo").reset();
    }
}

/* =========================
   INIT
========================= */
document.addEventListener("DOMContentLoaded", () => {

    cargarSelectores();
    cargarGrupos();

    document.getElementById("btnAbrirModalGrupo")?.addEventListener("click", () => togglerModal(true));
    document.getElementById("btnCerrarModal")?.addEventListener("click", () => togglerModal(false));
    document.getElementById("btnCancelar")?.addEventListener("click", () => togglerModal(false));

    document.getElementById("formGrupo")?.addEventListener("submit", async (e) => {
        e.preventDefault();

        const data = Object.fromEntries(new FormData(e.target).entries());

        const response = await apiRequest("crear", "POST", data);

        if (response.status === "success") {
            togglerModal(false);
            cargarGrupos();
        } else {
            alert(response.message);
        }
    });
});