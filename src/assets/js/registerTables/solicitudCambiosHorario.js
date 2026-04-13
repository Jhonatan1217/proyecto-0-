/**
 * Solicitud de cambios de horario al coordinador (diff vs snapshot + botón Enviar).
 */
(function (w) {
  "use strict";
  const RT = w.RegisterTables;
  const S = RT.state;
  const T = RT.Toast;

  const SOL = {};

  function setBotonEnviarVisible(visible) {
    const btnEnviar = document.querySelector("button[onclick='enviarHorario()']");
    if (!btnEnviar) return;
    btnEnviar.classList.toggle("hidden", !visible);
    btnEnviar.style.display = visible ? "inline-flex" : "none";
  }

  document.addEventListener("DOMContentLoaded", () => {
    setBotonEnviarVisible(false);
  });

  SOL.detectarCambios = function () {
    if (!S.horariosOriginal) {
      S.huboCambios = false;
      setBotonEnviarVisible(false);
      return;
    }

    const actual = JSON.stringify(S.horariosCache);
    S.huboCambios = actual !== S.horariosOriginal;
    setBotonEnviarVisible(S.huboCambios);
  };

  SOL.enviarHorario = async function () {
    if (!S.huboCambios) {
      T.fire({
        icon: "info",
        title: "No hay cambios activos para enviar",
      });
      return;
    }

    let antes;
    try {
      antes = JSON.parse(S.horariosOriginal || "[]");
    } catch (e) {
      console.error(e);
      T.fire({ icon: "error", title: "No se pudo leer el estado inicial del horario." });
      return;
    }

    const despues = S.horariosCache;
    const porIdAntes = new Map((antes || []).map((r) => [String(r.id_horario), r]));

    let texto = "CAMBIOS DE HORARIO SOLICITADOS:\n\n";
    const detalles = [];

    despues.forEach((nuevo) => {
      const viejo = porIdAntes.get(String(nuevo.id_horario));
      if (!viejo) {
        const descNuevo = String(nuevo.descripcion_jornada ?? nuevo.descripcion ?? "").trim();
        const raesNuevo = (nuevo.raes ?? nuevo.raesArray ?? []);
        texto += `Nuevo horario (${nuevo.dia} ${nuevo.hora_inicio} - ${nuevo.hora_fin})\n`;
        texto += `- Instructor: ${nuevo.id_instructor || "N/A"}\n`;
        texto += `- Competencia: ${nuevo.id_competencia || "N/A"}\n`;
        texto += `- Ficha: ${nuevo.numero_ficha || "N/A"}\n\n`;
        detalles.push({
          campo_modificado: "HORARIO_JSON",
          valor_anterior: "",
          valor_nuevo: JSON.stringify({
            es_nuevo: true,
            dia: nuevo.dia,
            hora_inicio: nuevo.hora_inicio,
            hora_fin: nuevo.hora_fin,
            numero_ficha: nuevo.numero_ficha,
            id_instructor: nuevo.id_instructor,
            id_competencia: nuevo.id_competencia,
            id_zona: nuevo.id_zona ?? null,
            id_area: nuevo.id_area ?? null,
            modalidad: nuevo.modalidad ?? null,
            descripcion_jornada: descNuevo,
            raes: raesNuevo,
          }),
        });
        return;
      }

      const descV = String(viejo.descripcion_jornada ?? viejo.descripcion ?? "").trim();
      const descN = String(nuevo.descripcion_jornada ?? nuevo.descripcion ?? "").trim();
      const cambioEnFila =
        viejo.dia !== nuevo.dia ||
        viejo.hora_inicio !== nuevo.hora_inicio ||
        viejo.hora_fin !== nuevo.hora_fin ||
        String(viejo.id_instructor) !== String(nuevo.id_instructor) ||
        String(viejo.id_competencia) !== String(nuevo.id_competencia) ||
        String(viejo.numero_ficha ?? "") !== String(nuevo.numero_ficha ?? "") ||
        descV !== descN ||
        JSON.stringify(viejo.raes ?? viejo.raesArray ?? []) !== JSON.stringify(nuevo.raes ?? nuevo.raesArray ?? []);

      if (cambioEnFila) {
        const cambiosFila = [];
        if (viejo.dia !== nuevo.dia) {
          cambiosFila.push(`- Dia: ${viejo.dia} -> ${nuevo.dia}`);
        }
        if (viejo.hora_inicio !== nuevo.hora_inicio || viejo.hora_fin !== nuevo.hora_fin) {
          cambiosFila.push(`- Horario: ${viejo.hora_inicio} - ${viejo.hora_fin} -> ${nuevo.hora_inicio} - ${nuevo.hora_fin}`);
        }
        if (String(viejo.id_instructor) !== String(nuevo.id_instructor)) {
          cambiosFila.push(`- Instructor: ${viejo.id_instructor} -> ${nuevo.id_instructor}`);
        }
        if (String(viejo.id_competencia) !== String(nuevo.id_competencia)) {
          cambiosFila.push(`- Competencia: ${viejo.id_competencia} -> ${nuevo.id_competencia}`);
        }
        if (String(viejo.numero_ficha ?? "") !== String(nuevo.numero_ficha ?? "")) {
          cambiosFila.push(`- Ficha: ${viejo.numero_ficha || "N/A"} -> ${nuevo.numero_ficha || "N/A"}`);
        }
        if (descV !== descN) {
          cambiosFila.push(`- Descripcion jornada: ${descV || "Sin descripcion"} -> ${descN || "Sin descripcion"}`);
        }
        if (JSON.stringify(viejo.raes ?? viejo.raesArray ?? []) !== JSON.stringify(nuevo.raes ?? nuevo.raesArray ?? [])) {
          const raesAnt = (viejo.raes ?? viejo.raesArray ?? []).join(", ");
          const raesNue = (nuevo.raes ?? nuevo.raesArray ?? []).join(", ");
          cambiosFila.push(`- RAEs: ${raesAnt || "Sin RAEs"} -> ${raesNue || "Sin RAEs"}`);
        }
        texto += `Horario ID ${nuevo.id_horario}\n${cambiosFila.join("\n")}\n\n`;

        detalles.push({
          campo_modificado: "HORARIO_JSON",
          valor_anterior: JSON.stringify({
            id_horario: viejo.id_horario,
            dia: viejo.dia,
            hora_inicio: viejo.hora_inicio,
            hora_fin: viejo.hora_fin,
            numero_ficha: viejo.numero_ficha,
            id_instructor: viejo.id_instructor,
            id_competencia: viejo.id_competencia,
            descripcion_jornada: descV,
            raes: viejo.raes ?? viejo.raesArray ?? [],
          }),
          valor_nuevo: JSON.stringify({
            id_horario: nuevo.id_horario,
            dia: nuevo.dia,
            hora_inicio: nuevo.hora_inicio,
            hora_fin: nuevo.hora_fin,
            numero_ficha: nuevo.numero_ficha,
            id_instructor: nuevo.id_instructor,
            id_competencia: nuevo.id_competencia,
            descripcion_jornada: descN,
            raes: nuevo.raes ?? nuevo.raesArray ?? [],
          }),
        });
      }
    });

    if (!detalles.length) {
      T.fire({
        icon: "info",
        title: "No hay cambios detectados",
      });
      return;
    }

    try {
      const id_instructor = w.USUARIO_ID || 1;
      const res = await fetch(`${RT.API_BASE}src/controllers/SolicitudController.php`, {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          accion: "crear",
          tipo_solicitud: "HORARIO",
          id_instructor_solicitante: id_instructor,
          detalles,
          cambios: texto,
        }),
      });

      const data = await res.json();
      if (data.status === "success") {
        T.fire({
          icon: "success",
          title: "Cambios realizados",
          html: "En espera de aprobación de coordinador/administrador",
        });

        S.horariosOriginal = JSON.stringify(S.horariosCache);
        S.huboCambios = false;
        setBotonEnviarVisible(false);
      } else {
        T.fire({
          icon: "error",
          title: data.message || "Error al enviar la solicitud",
        });
      }
    } catch (e) {
      console.error("Error:", e);
      T.fire({
        icon: "error",
        title: "Error al enviar solicitud de cambio",
      });
    }
  };

  Object.assign(RT.solicitud, SOL);
  setBotonEnviarVisible(false);
  w.enviarHorario = function () {
    return SOL.enviarHorario();
  };
})(window);
