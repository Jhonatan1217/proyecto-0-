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

    let texto = "CAMBIOS DEL HORARIO: \n\n";
    let hayCambios = false;
    const detalles = [];

    despues.forEach((nuevo, index) => {
      const viejo = antes[index];
      if (!viejo) return;

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
        hayCambios = true;
        texto += `ID: ${nuevo.id_horario}\n`;
        texto += `Anterior: ${viejo.dia} ${viejo.hora_inicio} - ${viejo.hora_fin}\n`;
        texto += `Nuevo Dia: ${nuevo.dia} ${nuevo.hora_inicio} - ${nuevo.hora_fin}\n`;
        texto += `Instructor: ${viejo.id_instructor} -> ${nuevo.id_instructor}\n`;
        texto += `Competencia: ${viejo.id_competencia} -> ${nuevo.id_competencia}\n\n`;

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

    if (!hayCambios || !detalles.length) {
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
