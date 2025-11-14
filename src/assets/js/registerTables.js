// ===============================
// REGISTER TABLES - 2025 FINAL (ÁREAS/ZONAS + EDICIÓN + TOASTS + PDF + ELIMINAR)
// ===============================

const urlParams = new URLSearchParams(window.location.search);
let id_zona = urlParams.get("id_zona");

// =======================
// CONFIG TOAST (SweetAlert2)
// =======================
const Toast = Swal.mixin({
  toast: true,
  position: "top-end",
  showConfirmButton: false,
  timer: 2500,
  timerProgressBar: true,
  background: "#fff",
  color: "#000",
});

// =======================
// Mostrar/Ocultar tabla y botones
// =======================
function toggleTabla(mostrar = true) {
  const tabla = document.querySelector("#tabla-horarios");
  const botones = document.querySelector("#botones-principales");
  if (tabla) tabla.style.display = mostrar ? "" : "none";
  if (botones) botones.style.display = mostrar ? "flex" : "none";
}

// =======================
// CARGAR ÁREAS Y ZONAS
// =======================
async function cargarAreasYZonas() {
  const selectArea = document.getElementById("selectArea");
  const selectZona = document.getElementById("selectZona");

  if (!selectArea || !selectZona) return;
  toggleTabla(false);

  try {
    // Cargar ÁREAS
    const resAreas = await fetch(`${BASE_URL}src/controllers/AreaController.php?accion=listar`);
    const dataAreas = await resAreas.json();
    

    if (dataAreas.status === "success" && Array.isArray(dataAreas.data)) {
      selectArea.innerHTML = `<option value="" hidden selected>SELECCIONE EL ÁREA</option>`;
      dataAreas.data.forEach((a) => {
        const opt = document.createElement("option");
        opt.value = a.id_area;
        opt.textContent = a.nombre_area;
        selectArea.appendChild(opt);
      });
    } else {
      Toast.fire({ icon: "warning", title: "No se encontraron áreas" });
    }

    // Cambiar área → cargar zonas
    selectArea.addEventListener("change", async (e) => {
      const id_area = e.target.value;
      selectZona.innerHTML = `<option value="" hidden selected>SELECCIONE LA ZONA</option>`;
      toggleTabla(false);
      if (!id_area) return;

      try {
        const resZonas = await fetch(`${BASE_URL}src/controllers/ZonaController.php?accion=listarPorArea&id_area=${id_area}`);
        const dataZonas = await resZonas.json();

        if (dataZonas.status === "success" && Array.isArray(dataZonas.data)) {
          if (dataZonas.data.length === 0) {
            Toast.fire({ icon: "info", title: "No hay zonas en esta área" });
            return;
          }
          dataZonas.data.forEach((z) => {
            const opt = document.createElement("option");
            opt.value = z.id_zona;
            opt.textContent = `Zona ${z.id_zona}`;
            selectZona.appendChild(opt);
          });
          Toast.fire({ icon: "success", title: "Zonas cargadas correctamente" });
        }
      } catch (err) {
        console.error("Error al cargar zonas:", err);
        Toast.fire({ icon: "error", title: "Error al cargar zonas" });
      }
    });

    selectZona.addEventListener("change", (e) => {
    id_zona = e.target.value;
    console.log("Zona seleccionada:", id_zona); 
    if (!id_zona) {
      toggleTabla(false);
      return;
    }
    const h1 = document.querySelector("#cabecera-trimestralizacion h1");
    if (h1) h1.innerHTML = `VISUALIZACIÓN DE REGISTRO TRIMESTRALIZACIÓN - ZONA ${id_zona}`;
    toggleTabla(true);
    cargarTrimestralizacion();
    Toast.fire({ icon: "info", title: `Zona ${id_zona} seleccionada` });
  });

  } catch (err) {
    console.error("Error en cargarAreasYZonas:", err);
    Toast.fire({ icon: "error", title: "Error al conectar con el servidor" });
  }
}

// =======================
// CARGAR TRIMESTRALIZACIÓN
// =======================
async function cargarTrimestralizacion() {
  const tbody = document.getElementById("tbody-horarios");
  const selectArea = document.getElementById("selectArea");
  const id_area = selectArea ? selectArea.value : "";

  if (!tbody) return;
  tbody.innerHTML = `<tr><td colspan="7" class="p-4 text-gray-500">Cargando datos...</td></tr>`;

  if (!id_zona || !id_area) {
    toggleTabla(false);
    return;
  }

  try {
    // Ahora enviamos también el área
    const res = await fetch(`${BASE_URL}src/controllers/TrimestralizacionController.php?accion=listar&id_zona=${id_zona}&id_area=${id_area}`);
    const data = await res.json();
    console.log("Datos recibidos del servidor:", data);
    tbody.innerHTML = "";

    // data debe ser un array; si tu controller devuelve estructura {status:..., data: [...]}
    const registrosServer = Array.isArray(data) ? data : (Array.isArray(data.data) ? data.data : []);

    const activos = registrosServer.filter((d) => d && (d.estado === 1 || d.estado === "1"));

    // Si no hay registros activos...
    if (!activos.length) {
      tbody.innerHTML = `<tr><td colspan="7" class="p-4 text-gray-500">No hay registros activos para esta zona y área.</td></tr>`;
      Toast.fire({ icon: "info", title: "Sin registros activos" });
      return;
    }

    // -------------------------
    // AGRUPAR POR id_horario
    // -------------------------
    // Creamos un Map para agrupar las RAEs de cada horario en una sola estructura
    const mapHorarios = new Map();

    activos.forEach(r => {
      const id = r.id_horario ?? (r.id_horario === 0 ? 0 : null);
      if (id === null) return; // proteger por si hay filas mal formadas

      if (!mapHorarios.has(id)) {
        // Hacemos una copia de los campos "únicos" que queremos mantener
        mapHorarios.set(id, {
          id_horario: id,
          dia: r.dia,
          hora_inicio: r.hora_inicio,
          hora_fin: r.hora_fin,
          id_zona: r.id_zona,
          id_area: r.id_area,
          numero_trimestre: r.numero_trimestre,
          estado: r.estado,
          numero_ficha: r.numero_ficha,
          nivel_ficha: r.nivel_ficha,
          nombre_instructor: r.nombre_instructor,
          tipo_instructor: r.tipo_instructor,
          id_competencia: r.id_competencia,
          nombre_competencia: r.nombre_competencia,
          descripcion_competencia: r.descripcion_competencia ?? r.descripcion,
          raesArray: []
        });
      }

      // Añadir RAE si existe y no está ya añadida
      const agr = mapHorarios.get(id);
      if (r.id_rae) {
        const textoRae = `${r.id_rae} - ${r.descripcion_rae ?? ""}`.trim();
        // evitar duplicados de RAE en el mismo horario
        if (textoRae && !agr.raesArray.includes(textoRae)) agr.raesArray.push(textoRae);
      }
    });

    // Convertir map a array usable por el render
    const horariosAgrupados = Array.from(mapHorarios.values());

    // Opcional: si quieres que las RAEs salgan como HTML con saltos de línea
    horariosAgrupados.forEach(h => {
      if (h.raesArray.length) {
        // crear HTML list (puedes cambiar a join(', ') si prefieres en línea)
        h.raesHtml = `<ul class="list-disc ml-5 mt-1">${h.raesArray.map(x => `<li>${x}</li>`).join('')}</ul>`;
      } else {
        h.raesHtml = `<span class="text-gray-500 italic">Sin especificar</span>`;
      }
    });

    // -------------------------
    // RENDERIZAR USANDO horariosAgrupados
    // -------------------------
    const dias = ["LUNES", "MARTES", "MIERCOLES", "JUEVES", "VIERNES", "SABADO"];
    const horas = Array.from({ length: 16 }, (_, i) => i + 6);

    horas.forEach((hora, idx) => {
      const fila = document.createElement("tr");
      fila.className = idx % 2 === 0 ? "bg-gray-50" : "bg-white";
      fila.innerHTML = `<td class="border border-gray-700 p-2 font-medium">${hora}:00-${hora + 1}:00</td>`;

      dias.forEach((dia) => {
        // ahora filtramos la lista agrupada
        const registros = horariosAgrupados.filter((r) => {
          if (!r.dia || r.dia.toUpperCase() !== dia) return false;
          const rStart = parseInt((r.hora_inicio || "0:00").split(":")[0], 10);
          const rEnd = r.hora_fin ? parseInt(r.hora_fin.split(":")[0], 10) : rStart + 1;
          return hora >= rStart && hora < rEnd;
        });

        let contenido = "";
        registros.forEach((r) => {
          const rStart = parseInt((r.hora_inicio || "0:00").split(":")[0], 10);
          const rEnd = r.hora_fin ? parseInt(r.hora_fin.split(":")[0], 10) : rStart + 1;
          if (hora === rStart) {
            // un solo bloque por horario (ya agrupado)
            contenido += `
              <div class="registro border-gray-300 pb-1 mb-1"
                  data-id="${r.id_horario || ""}"
                  data-id-instructor="${r.id_instructor ?? ""}">
                <div><strong>Instructor:</strong> ${r.nombre_instructor ?? ""} (${r.tipo_instructor ?? ""})</div>
                <div><strong>Ficha:</strong> <span class="ficha">${r.numero_ficha ?? ""}</span>
                  (<span class="nivel_ficha">${(r.nivel_ficha ?? "" ).toString().toUpperCase()}</span>)
                </div>
                <div><strong>Competencia:</strong> <span class="competencia">${r.id_competencia} -  ${r.nombre_competencia}  </span></div>
                <div><strong>RAE(s):</strong> ${r.raesHtml}</div>
              </div>`;
          } else if (hora > rStart && hora < rEnd) {
            // horas intermedias en rangos largos — mostramos solo instructor resumido
            contenido += `<div class="mb-1 border-gray-200 pb-1">
                <strong>Instructor:</strong> ${r.nombre_instructor ?? ""} (${r.tipo_instructor ?? ""})
              </div>`;
          }
        });

        fila.innerHTML += `
          <td class="border border-gray-700 p-2 text-sm text-left leading-tight">
            ${contenido || '<span class="text-gray-400 italic">zona libre</span>'}
          </td>`;
      });

      tbody.appendChild(fila);
    });

    Toast.fire({ icon: "success", title: "Trimestralización cargada correctamente" });
  } catch (error) {
    console.error("Error al cargar:", error);
    tbody.innerHTML = `<tr><td colspan="7" class="text-red-600 p-4">Error al conectar con el servidor.</td></tr>`;
    Toast.fire({ icon: "error", title: "Error al cargar trimestralización" });
  }
}

// =======================
// LISTAR INSTRUCTORES
// =======================
let listaInstructores = [];
let listaCompetencias = [];

async function cargarCompetencias() {
  try {
    const res = await fetch(`${BASE_URL}src/controllers/CompetenciaController.php?accion=listar`);
    const data = await res.json();

    console.log("Competencias recibidas:", data);

    const array = Array.isArray(data)
      ? data
      : (Array.isArray(data.data) ? data.data : []);

    listaCompetencias = array;

  } catch (error) {
    console.error("Error cargando competencias:", error);
    listaCompetencias = [];
  }
}

async function cargarInstructores() {
  try {
    const res = await fetch(`${BASE_URL}src/controllers/InstructorController.php?accion=listar`);
    const data = await res.json();

    console.log("Respuesta del servidor (Instructores):", data);

    const instructoresArray = Array.isArray(data)
      ? data
      : (Array.isArray(data.data) ? data.data : []);

    // 🔥 FILTRAR SOLO INSTRUCTORES ACTIVOS (estado = 1)
    listaInstructores = instructoresArray.filter(i => String(i.estado) === "1");

    if (listaInstructores.length > 0) {
      llenarSelectInstructores(listaInstructores);
    } else {
      Toast.fire({ icon: "warning", title: "No hay instructores activos" });
      listaInstructores = [];
    }

  } catch (error) {
    console.error("Error al cargar instructores:", error);
    Toast.fire({ icon: "error", title: "No se pudo cargar instructores" });
    listaInstructores = [];
  }
}

// opcional: si en alguna otra vista tienes un select con id="selectInstructor", esta función lo llenará.
// ahora protege si no existe.
function llenarSelectInstructores(instructores) {
  const selectInstructor = document.getElementById("selectInstructor");
  if (!selectInstructor) return; // protección: no hay select global, es normal
  selectInstructor.innerHTML = '<option value="">Seleccione un instructor</option>';

  instructores.forEach(i => {
    const option = document.createElement("option");
    option.value = i.id_instructor;
    option.textContent = `${i.nombre_instructor} - ${i.tipo_instructor}`;
    selectInstructor.appendChild(option);
  });
}

async function obtenerRoesPorCompetencia(id_competencia) {
  try {
    const res = await fetch(`${BASE_URL}src/controllers/RaeController.php?accion=porCompetencia&id_competencia=${id_competencia}`);
    const data = await res.json();

    console.log("RAEs de la BD:", data);

    // El controlador devuelve un array directo
    if (Array.isArray(data)) return data;

    return [];
  } catch (e) {
    console.error("Error obteniendo RAEs:", e);
    return [];
  }
}


// =======================
// MODO EDICIÓN
// =======================
async function activarEdicion() {
  try {
    await cargarInstructores();
    await cargarCompetencias();
  } catch (err) {
    console.error("Error al cargar instructores en activar Edicion:", err);
  }

  const registros = document.querySelectorAll("#tbody-horarios .registro");
  if (!registros.length) {
    Toast.fire({ icon: "warning", title: "No hay datos para editar" });
    return;
  }

  for (const reg of registros) {

    // -----------------------
    // 1. DATOS ORIGINALES
    // -----------------------
    const ficha = reg.querySelector(".ficha")?.textContent.trim() || "";
    const competenciaTexto = reg.querySelector(".competencia")?.textContent.trim() || "";
    const nivel_ficha = reg.querySelector(".nivel_ficha")?.textContent.trim() || "";
    const idInstructor = reg.getAttribute("data-id-instructor") || "";

    // separación del ID de la competencia
    const id_competencia = competenciaTexto.split("-")[0]?.trim();

    // Obtener RAEs ya asignados (li)
    const ul = reg.querySelector("ul");
    let raesExistentes = [];
    if (ul) raesExistentes = [...ul.querySelectorAll("li")].map(li => li.textContent.trim());

    // Obtener RAEs de BD
    const raesBD = await obtenerRoesPorCompetencia(id_competencia);

    // -----------------------
    // 2. LIMPIAR CONTENIDO
    // -----------------------
    reg.innerHTML = "";

    // select competencias
    const selCompetencia = document.createElement("select");
    selCompetencia.className = "competencia-select w-full mb-1 px-2 py-1 border border-gray-400 rounded text-sm";

    const placeholder = document.createElement("option");
    placeholder.value = "";
    placeholder.textContent = "Seleccione competencia";
    selCompetencia.appendChild(placeholder);

    // rellenar select desde la db
    listaCompetencias.forEach(c => {
      const opt = document.createElement("option");
      opt.value = c.id_competencia;
      opt.textContent = c.nombre_competencia;

     // seleccionar la competencia ya existente en la fila
     if (c.nombre_competencia.trim() === competenciaTexto.trim()) {
      opt.selected = true;
      }

      selCompetencia.appendChild(opt);
    });


    // select instructores
    const sel = document.createElement("select");
    sel.className = "instructor-select w-full mb-1 px-2 py-1 border border-gray-400 rounded text-sm";

    const placeholderOpt = document.createElement("option");
    placeholderOpt.value = "";
    placeholderOpt.textContent = "Seleccione instructor";
    sel.appendChild(placeholderOpt);

    listaInstructores.forEach((inst) => {
      const opt = document.createElement("option");
      opt.value = inst.id_instructor;
      opt.textContent = `${inst.nombre_instructor} (${inst.tipo_instructor})`;
      if (String(inst.id_instructor) === String(idInstructor)) opt.selected = true;
      sel.appendChild(opt);
    });

    // -----------------------
    // 4. FICHA
    // -----------------------
    const inputFicha = document.createElement("input");
    inputFicha.type = "text";
    inputFicha.value = ficha;
    inputFicha.placeholder = "Número de ficha";
    inputFicha.className = "ficha-input block w-full mb-1 px-2 py-1 border border-gray-400 rounded text-sm";

    // -----------------------
    // 5. COMPETENCIA
    // -----------------------
    const txt = document.createElement("textarea");
    txt.rows = 2;
    txt.className = "competencia-input w-full px-2 py-1 border border-gray-400 rounded text-sm resize-none";
    txt.textContent = competenciaTexto;

    // -----------------------
    // 6. CHECKBOX de RAEs
    // -----------------------
    const contRAE = document.createElement("div");
    contRAE.className = "rae-container mt-2 p-2 border rounded bg-gray-50";

    const labelRae = document.createElement("div");
    labelRae.textContent = "RAE(s):";
    labelRae.className = "font-semibold mb-1 text-sm";
    contRAE.appendChild(labelRae);

    // crear cada checkbox
    raesBD.forEach((rae) => {
      const descripcion = (rae.descripcion ?? rae.descripcion_rae ?? "").trim();
      const textoRae = `${rae.id_rae} - ${descripcion}`;

      const div = document.createElement("div");
      div.className = "flex items-center gap-2 mb-1";

      const chk = document.createElement("input");
      chk.type = "checkbox";
      chk.dataset.idRae = rae.id_rae;

      // ✔ MARCAR si esta rae EXISTE en la lista original
      chk.checked = raesExistentes.includes(textoRae);

      const lbl = document.createElement("label");
      lbl.textContent = textoRae;
      lbl.className = "text-sm";

      div.appendChild(chk);
      div.appendChild(lbl);
      contRAE.appendChild(div);
    });

    // -----------------------
    // 7. NIVEL
    // -----------------------
    const nivelDiv = document.createElement("div");
    nivelDiv.className = "text-xs text-gray-500 mt-1";
    nivelDiv.textContent = `Nivel: ${nivel_ficha}`;

    // -----------------------
    // 8. Ensamblar
    // -----------------------
    reg.appendChild(sel);
    reg.appendChild(inputFicha);
    reg.appendChild(txt);
    reg.appendChild(contRAE);
    reg.appendChild(nivelDiv);
  }

  document.getElementById("botones-principales").style.display = "none";
  mostrarBotonesEdicion();
}

function mostrarBotonesEdicion() {
  // si ya existe, no crear otro
  if (document.getElementById("botones-edicion")) return;

  const div = document.createElement("div");
  div.id = "botones-edicion";
  div.className = "mt-4 flex justify-center gap-4";

  const guardar = document.createElement("button");
  guardar.textContent = "Guardar cambios";
  guardar.className = "bg-[#39a900] text-white px-6 py-2 rounded-lg hover:bg-[#4ebe15] transition";
  guardar.onclick = guardarCambios;

  const cancelar = document.createElement("button");
  cancelar.textContent = "Cancelar edición";
  cancelar.className = "bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition";
  cancelar.onclick = cancelarEdicion;

  div.appendChild(guardar);
  div.appendChild(cancelar);
  document.querySelector("main").appendChild(div);
}

// =======================
// GUARDAR / CANCELAR EDICIÓN
// =======================
async function guardarCambios() {
  const registros = document.querySelectorAll("#tbody-horarios .registro");

  const filas = Array.from(registros).map((r) => {
    const id_horario = r.getAttribute("data-id");
    const numero_ficha = r.querySelector(".ficha-input")?.value || "";
    const descripcion = r.querySelector("select.competencia-select")?.value || "";
    const id_instructor = r.querySelector("select.instructor-select")?.value || "";

    // leer checkboxes RAE
    const raes = [...r.querySelectorAll(".rae-container input[type=checkbox]")]
      .filter(chk => chk.checked)
      .map(chk => chk.dataset.idRae);

    return {
      id_horario,
      numero_ficha,
      descripcion,
      id_instructor,
      raes
    };
  });
 

  try {
    const res = await fetch(`${BASE_URL}src/controllers/trimestralizacionController.php?accion=actualizar&id_zona=${id_zona}`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(filas),
    });

    const data = await res.json();
    if (data && (data.success || data.status === "success")) {
      Toast.fire({ icon: "success", title: "Cambios guardados correctamente" });
      document.getElementById("botones-edicion")?.remove();
      document.getElementById("botones-principales").style.display = "flex";
      cargarTrimestralizacion();
    } else {
      console.error("guardarCambios respuesta inesperada:", data);
      Toast.fire({ icon: "error", title: "Error al guardar cambios" });
    }
  } catch (err) {
    console.error("guardarCambios error:", err);
    Toast.fire({ icon: "error", title: "Error de conexión al guardar" });
  }
}


function cancelarEdicion() {
  Swal.fire({
    title: "¿Deseas cancelar los cambios realizados?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, cancelar",
    cancelButtonText: "No, continuar",
    reverseButtons: true,
    confirmButtonColor: "#39A900",
    cancelButtonColor: "#E53935"
  }).then((res) => {
    if (res.isConfirmed) {
      const be = document.getElementById("botones-edicion");
      if (be) be.remove();
      const bp = document.getElementById("botones-principales");
      if (bp) bp.style.display = "flex";
      cargarTrimestralizacion();
      Toast.fire({ icon: "info", title: "Edición cancelada" });
    }
  });
}

// =======================
// ELIMINAR TODO
// =======================
async function confirmarEliminar() {
  try {
    const res = await fetch(`${BASE_URL}src/controllers/trimestralizacionController.php?accion=eliminar&id_zona=${id_zona}`);
    const data = await res.json();
    Toast.fire({ icon: "success", title: data.message || "Trimestralización eliminada correctamente" });
    cargarTrimestralizacion();
  } catch (err) {
    console.error("confirmarEliminar error:", err);
    Toast.fire({ icon: "error", title: "Error al eliminar" });
  } finally {
    cerrarModal();
  }
}

function mostrarModalEliminar() {
  const modal = document.getElementById("modalEliminar");
  if (modal) modal.classList.remove("hidden");
}
function cerrarModal() {
  const modal = document.getElementById("modalEliminar");
  if (modal) modal.classList.add("hidden");
}

// =======================
// DESCARGAR PDF
// =======================
async function descargarPDF() {
  const { jsPDF } = window.jspdf;
  const elementoOriginal = document.querySelector("#tabla-horarios");

  if (!elementoOriginal) {
    Toast.fire({ icon: "error", title: "No se encontró la tabla para exportar" });
    return;
  }

  const elementoClonado = elementoOriginal.cloneNode(true);
  elementoClonado.style.maxHeight = "none";
  elementoClonado.style.overflow = "visible";
  elementoClonado.style.height = "auto";
  elementoClonado.style.width = "100%";
  elementoClonado.style.position = "absolute";
  elementoClonado.style.top = "0";
  elementoClonado.style.left = "-9999px";

  document.body.appendChild(elementoClonado);

  await new Promise(r => setTimeout(r, 300));

  const canvas = await html2canvas(elementoClonado, {
    scale: 1.5,
    useCORS: true,
    backgroundColor: "#ffffff",
    scrollX: 0,
    scrollY: 0,
    windowWidth: elementoClonado.scrollWidth,
    windowHeight: elementoClonado.scrollHeight,
    logging: false
  });

  document.body.removeChild(elementoClonado);

  // Convertir a JPEG con compresión en lugar de PNG
  const imgData = canvas.toDataURL("image/jpeg", 0.75);
  const pdf = new jsPDF({
    orientation: "landscape",
    unit: "mm",
    format: "a4",
    compress: true
  });

  const pdfWidth = pdf.internal.pageSize.getWidth();
  const pdfHeight = pdf.internal.pageSize.getHeight();

  // Márgenes configurables (en milímetros)
  const marginX = 10; // izquierda y derecha
  const marginY = 15; // arriba

  const imgWidth = pdfWidth - marginX * 2;
  const imgHeight = (canvas.height * imgWidth) / canvas.width;

  let position = marginY;
  let heightLeft = imgHeight;

  pdf.setFontSize(16);
  pdf.text(`Trimestralización - Zona ${id_zona}`, pdfWidth / 2, 10, { align: "center" });

  pdf.addImage(imgData, "jpeg", marginX, position, imgWidth, imgHeight);
  heightLeft -= pdfHeight - position;

  while (heightLeft > 0) {
    pdf.addPage();
    position = 0;
    pdf.addImage(imgData, "jpeg", marginX, position - heightLeft, imgWidth, imgHeight);
    heightLeft -= pdfHeight;
  }

  pdf.save(`trimestralizacion_zona_${id_zona}.pdf`);
}

// =======================
// INICIO
// =======================
document.addEventListener("DOMContentLoaded", () => {
  cargarAreasYZonas();
  if (id_zona) {
    toggleTabla(true);
    cargarTrimestralizacion();
  } else toggleTabla(false);

  document.getElementById("btn-actualizar")?.addEventListener("click", activarEdicion);
});
