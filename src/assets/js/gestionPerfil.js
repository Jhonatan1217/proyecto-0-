/**
 * Módulo Gestión de Perfil – Widget de usuario y modales (Ver perfil, Solicitar cambios, Cambiar contraseña).
 * No modifica el módulo de Solicitudes (listado/aprobación).
 */
(function () {
  var API = window.API_USUARIO;
  var API_SOLICITUD = window.API_SOLICITUD || "";
  var USUARIO_ID = window.USUARIO_ID || 0;
  var USUARIO_ES_SISTEMA = Number(window.USUARIO_ES_SISTEMA || 0) === 1;

  function getEl(id) { return document.getElementById(id); }
  function qs(sel, ctx) { return (ctx || document).querySelector(sel); }
  function qsAll(sel, ctx) { return (ctx || document).querySelectorAll(sel); }
  function normalizeTxt(v) {
    return String(v || "")
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .trim()
      .toUpperCase();
  }
  function isRolEsSistema() {
    if (USUARIO_ES_SISTEMA) return true;
    var cargo = normalizeTxt(window.USUARIO_CARGO || "");
    return cargo === "ES SISTEMA" || cargo === "SISTEMA" || cargo.indexOf("SISTEMA") >= 0;
  }
  function isCuentaSistemaData(data) {
    if (!data || typeof data !== "object") return false;
    if (Number(data.es_sistema || 0) === 1) return true;
    var cargoData = normalizeTxt(data.cargo || "");
    return cargoData === "ES SISTEMA" || cargoData === "SISTEMA" || cargoData.indexOf("SISTEMA") >= 0;
  }
  function disableEditarPerfilAction() {
    var btn = qs('[data-action="editar-perfil"]');
    if (!btn) return;
    btn.setAttribute("disabled", "disabled");
    btn.setAttribute("aria-disabled", "true");
    btn.setAttribute("title", "No disponible para cuenta de sistema");
    btn.classList.add("opacity-50", "cursor-not-allowed");
    btn.classList.remove("hover:bg-gray-100");
  }

  var lastModalOpener = null;

  function fullyCloseModal(modal) {
    if (!modal) return;
    var activeEl = document.activeElement;
    if (activeEl && modal.contains(activeEl)) activeEl.blur();
    modal.classList.add("hidden");
    modal.classList.remove("flex", "block", "items-center", "justify-center");
    modal.style.display = "none";
    modal.style.pointerEvents = "none";
    modal.style.visibility = "hidden";
    modal.querySelectorAll(".absolute.inset-0, .fixed.inset-0, [class*='inset-0']").forEach(function (el) {
      el.style.pointerEvents = "none";
    });
    document.body.style.overflow = "";
    document.body.classList.remove("overflow-hidden");
    if (lastModalOpener && lastModalOpener.focus) {
      try { lastModalOpener.focus(); } catch (e) {}
      lastModalOpener = null;
    }
  }

  function closeUserMenu() {
    var menu = getEl("userMenu");
    var chevron = getEl("chevronIcon");
    if (menu) menu.classList.add("hidden");
    if (chevron) chevron.classList.remove("rotate-180");
  }

  function openModal(id) {
    var modal = getEl(id);
    if (!modal) return;
    lastModalOpener = document.activeElement;
    qsAll(".modal-perfil").forEach(fullyCloseModal);
    modal.classList.remove("hidden");
    modal.classList.add("flex", "items-center", "justify-center");
    modal.style.display = "";
    modal.style.pointerEvents = "";
    modal.style.visibility = "";
    modal.querySelectorAll(".absolute.inset-0, .fixed.inset-0, [class*='inset-0']").forEach(function (el) {
      el.style.pointerEvents = "";
    });
  }

  function closeModal(id) {
    var modal = id ? getEl(id) : null;
    if (modal) fullyCloseModal(modal);
  }

  function loadPerfil() {
    if (!USUARIO_ID) return Promise.resolve(null);
    return fetch(API + "?accion=obtener&id=" + USUARIO_ID)
      .then(function (r) { return r.json(); })
      .catch(function () { return null; });
  }

  function fillModalVerPerfil(data) {
    var nombre = (data && data.nombre_completo) || "—";
    var iniciales = nombre !== "—" ? nombre.trim().split(/\s+/).map(function (s) { return s[0]; }).slice(0, 2).join("").toUpperCase() : "—";
    getEl("verPerfilAvatar").textContent = iniciales;
    getEl("verPerfilNombre").textContent = nombre;
    getEl("verPerfilRol").textContent = (data && data.cargo) || "—";
    getEl("verPerfilNombreCampo").textContent = nombre;
    getEl("verPerfilDocumento").textContent = (data && data.numero_documento) || "—";
    getEl("verPerfilCorreo").textContent = (data && data.correo_electronico) || "—";
    getEl("verPerfilArea").textContent = (data && data.nombre_area) || "—";

    var areaContainer = getEl("verPerfilAreaContainer");
    if (areaContainer) {
        var cargoUsuario = window.USUARIO_CARGO || "";
        if (cargoUsuario.toUpperCase() === "COORDINADOR") {
            areaContainer.style.display = "";
        } else {
            areaContainer.style.display = "none";
        }
    }
  }

  var solicitarCambiosValoresIniciales = null;

  function getFormSolicitarCambiosValues(form) {
    if (!form) return {};
    return {
      nombre_completo: (form.querySelector("[name='nombre_completo']") || {}).value || "",
      tipo_documento: (form.querySelector("[name='tipo_documento']") || {}).value || "",
      numero_documento: (form.querySelector("[name='numero_documento']") || {}).value || "",
      correo_electronico: (form.querySelector("[name='correo_electronico']") || {}).value || "",
      tipo_instructor: (form.querySelector("[name='tipo_instructor']") || {}).value || "",
      tipo_contrato: (form.querySelector("[name='tipo_contrato']") || {}).value || ""
    };
  }

  function fillFormSolicitarCambios(data) {
    var form = getEl("formSolicitarCambiosPerfil");
    if (!form || !data) return;
    form.querySelector("[name='nombre_completo']").value = data.nombre_completo || "";
    form.querySelector("[name='numero_documento']").value = data.numero_documento || "";
    form.querySelector("[name='correo_electronico']").value = data.correo_electronico || "";
    ["tipo_documento", "tipo_instructor", "tipo_contrato"].forEach(function (name) {
      var sel = form.querySelector("[name='" + name + "']");
      if (sel) {
        sel.value = (data[name] || "").toString();
        sel.dispatchEvent(new Event("change", { bubbles: true }));
      }
    });
    solicitarCambiosValoresIniciales = getFormSolicitarCambiosValues(form);
  }

  function onVerPerfil() {
    closeUserMenu();
    loadPerfil().then(function (data) {
      fillModalVerPerfil(data);
      openModal("modalVerPerfil");
    });
  }

  function onEditarPerfil() {
    closeUserMenu();
    loadPerfil().then(function (data) {
      if (isRolEsSistema() || isCuentaSistemaData(data)) {
        disableEditarPerfilAction();
        if (window.Swal) {
          Swal.fire({
            toast: true,
            position: "top-end",
            icon: "info",
            title: "Editar perfil no está disponible para cuentas de sistema.",
            showConfirmButton: false,
            timer: 2800
          });
        }
        return;
      }
      fillFormSolicitarCambios(data);
      openModal("modalSolicitarCambiosPerfil");
    });
  }

  function enhanceSelectsPerfilModal() {
    var modal = getEl("modalSolicitarCambiosPerfil");
    if (!modal) return;
    var selects = qsAll(".select-perfil", modal);
    selects.forEach(function (select, idx) {
      var isLastSelect = idx === selects.length - 1;
      if (select.dataset.customDropdown) return;
      select.dataset.customDropdown = "1";

      var wrapper = document.createElement("div");
      wrapper.className = "custom-select-wrapper";
      select.parentNode.insertBefore(wrapper, select);
      wrapper.appendChild(select);

      var trigger = document.createElement("div");
      trigger.className = "custom-select-trigger py-2.5 text-sm w-full border border-gray-300 rounded-xl px-4 pr-10 bg-white text-gray-700 flex items-center justify-between cursor-pointer hover:border-gray-400 focus-within:ring-2 focus-within:ring-[#39A900]/20 focus-within:border-[#39A900]";
      trigger.setAttribute("tabindex", "0");
      trigger.setAttribute("aria-haspopup", "listbox");

      var dropdown = document.createElement("div");
      dropdown.className = "custom-select-dropdown hidden";
      dropdown.setAttribute("role", "listbox");

      var span = document.createElement("span");
      span.className = "truncate";

      function updateTrigger() {
        var opt = select.options[select.selectedIndex];
        span.textContent = opt ? opt.textContent : "";
      }

      trigger.appendChild(span);
      var arrow = document.createElement("span");
      arrow.className = "shrink-0 text-gray-400";
      arrow.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';
      trigger.appendChild(arrow);

      [].slice.call(select.options).forEach(function (opt, i) {
        var div = document.createElement("div");
        div.className = "custom-option" + (opt.value === select.value ? " selected" : "");
        div.textContent = opt.textContent;
        div.dataset.value = opt.value;
        div.dataset.index = String(i);
        div.setAttribute("role", "option");
        div.addEventListener("click", function (e) {
          e.stopPropagation();
          select.value = opt.value;
          select.dispatchEvent(new Event("change", { bubbles: true }));
          dropdown.querySelectorAll(".custom-option").forEach(function (o) { o.classList.remove("selected"); });
          div.classList.add("selected");
          updateTrigger();
          dropdown.classList.add("hidden");
        });
        dropdown.appendChild(div);
      });

      updateTrigger();

      select.classList.add("sr-only", "absolute", "inset-0", "w-full", "h-full", "opacity-0", "pointer-events-none");
      wrapper.appendChild(trigger);
      wrapper.appendChild(dropdown);

      trigger.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        var open = !dropdown.classList.contains("hidden");
        document.querySelectorAll(".custom-select-dropdown").forEach(function (d) { d.classList.add("hidden"); });
        if (!open) {
          var needsUp = isLastSelect;
          if (!needsUp) {
            var rect = wrapper.getBoundingClientRect();
            var dropdownMaxH = 220;
            var margin = 12;
            var spaceBelow = window.innerHeight - rect.bottom;
            needsUp = spaceBelow < dropdownMaxH + margin;
          }
          dropdown.classList.toggle("dropdown-up", needsUp);
          dropdown.classList.remove("hidden");
        } else {
          dropdown.classList.remove("dropdown-up");
        }
      });

      select.addEventListener("change", function () {
        updateTrigger();
        dropdown.querySelectorAll(".custom-option").forEach(function (o) {
          o.classList.toggle("selected", o.dataset.value === select.value);
        });
      });

      document.addEventListener("click", function (e) {
        if (!wrapper.contains(e.target)) dropdown.classList.add("hidden");
      }, true);
    });
  }

  function initTogglePassword(container) {
    if (!container) return;
    qsAll(".toggle-pwd", container).forEach(function (btn) {
      var iconOpen = btn.querySelector(".icon-eye-open");
      var iconClosed = btn.querySelector(".icon-eye-closed");
      btn.addEventListener("click", function () {
        var wrap = btn.closest(".relative");
        var input = wrap && wrap.querySelector("input");
        if (!input) return;
        var isPass = input.type === "password";
        input.type = isPass ? "text" : "password";
        btn.setAttribute("aria-label", isPass ? "Ocultar contraseña" : "Mostrar contraseña");
        if (iconOpen && iconClosed) {
          if (isPass) {
            iconOpen.classList.remove("hidden");
            iconClosed.classList.add("hidden");
          } else {
            iconOpen.classList.add("hidden");
            iconClosed.classList.remove("hidden");
          }
        }
      });
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    if (isRolEsSistema()) disableEditarPerfilAction();
    // Confirmación con datos reales del usuario (si la API los entrega).
    loadPerfil().then(function (data) {
      if (isCuentaSistemaData(data)) {
        USUARIO_ES_SISTEMA = true;
        disableEditarPerfilAction();
      }
    });

    var container = getEl("userDropdownContainer");
    if (container) {
      container.addEventListener("click", function (e) {
        var action = e.target.closest("[data-action]");
        if (!action) return;
        e.preventDefault();
        var act = action.getAttribute("data-action");
        if (act === "ver-perfil") onVerPerfil();
        else if (act === "editar-perfil") onEditarPerfil();
        else if (act === "cerrar-sesion") {
          closeUserMenu();
          openModal("modalCerrarSesion");
        }
      });
    }

    var btnConfirmarCerrar = getEl("btnConfirmarCerrarSesion");
    if (btnConfirmarCerrar) {
      btnConfirmarCerrar.addEventListener("click", function () {
        closeModal("modalCerrarSesion");
        var base = window.BASE_URL || "";
        window.location.href = base + "index.php?page=logout";
      });
    }

    qsAll("[data-close]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var id = btn.getAttribute("data-close");
        if (id) closeModal(id);
      });
    });

    var btnCambiarContrasena = getEl("btnAbrirCambiarContrasena");
    if (btnCambiarContrasena) {
      btnCambiarContrasena.addEventListener("click", function () {
        closeModal("modalSolicitarCambiosPerfil");
        openModal("modalCambiarContrasena");
      });
    }

    var btnVolver = getEl("btnVolverEditarPerfil");
    if (btnVolver) {
      btnVolver.addEventListener("click", function () {
        closeModal("modalCambiarContrasena");
        openModal("modalSolicitarCambiosPerfil");
      });
    }

    var formSolicitar = getEl("formSolicitarCambiosPerfil");
    if (formSolicitar) {
      formSolicitar.addEventListener("submit", function (e) {
        e.preventDefault();
        var actual = getFormSolicitarCambiosValues(formSolicitar);
        var inicial = solicitarCambiosValoresIniciales || {};
        var hayCambios = Object.keys(actual).some(function (key) {
          return String(actual[key] || "").trim() !== String(inicial[key] || "").trim();
        });
        if (!hayCambios) {
          if (window.Swal) {
            Swal.fire({ toast: true, position: "top-end", icon: "warning", title: "No se detectaron cambios", showConfirmButton: false, timer: 3000 });
          } else {
            alert("No se detectaron cambios.");
          }
          return;
        }
        var detalles = [];
        Object.keys(actual).forEach(function (key) {
          var vAnt = String(inicial[key] || "").trim();
          var vNuevo = String(actual[key] || "").trim();
          if (vNuevo !== vAnt) {
            detalles.push({ campo_modificado: key, valor_anterior: vAnt || null, valor_nuevo: vNuevo });
          }
        });
        var btnEnviar = formSolicitar.querySelector('button[type="submit"]');
        var textoOriginal = btnEnviar ? btnEnviar.innerHTML : "";
        if (btnEnviar) {
          btnEnviar.disabled = true;
          btnEnviar.innerHTML = "Enviando...";
        }
        var fd = new FormData();
        fd.append("accion", "crear");
        fd.append("tipo_solicitud", "DATOS");
        fd.append("id_instructor_solicitante", USUARIO_ID);
        fd.append("detalles", JSON.stringify(detalles));
        fetch(API_SOLICITUD, { method: "POST", body: fd })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (data && data.status === "success") {
              if (window.Swal) {
                Swal.fire({ toast: true, position: "top-end", icon: "success", title: data.message || "Solicitud enviada al administrador.", showConfirmButton: false, timer: 2500 });
              } else {
                alert(data.message || "Solicitud enviada al administrador.");
              }
              closeModal("modalSolicitarCambiosPerfil");
              solicitarCambiosValoresIniciales = null;
            } else {
              if (window.Swal) {
                Swal.fire({ toast: true, position: "top-end", icon: "error", title: data.message || data.error || "Error al enviar la solicitud.", showConfirmButton: false, timer: 3000 });
              } else {
                alert(data.message || data.error || "Error al enviar la solicitud.");
              }
            }
          })
          .catch(function () {
            if (window.Swal) {
              Swal.fire({ toast: true, position: "top-end", icon: "error", title: "Error de conexión.", showConfirmButton: false, timer: 2500 });
            } else {
              alert("Error de conexión.");
            }
          })
          .finally(function () {
            if (btnEnviar) {
              btnEnviar.disabled = false;
              btnEnviar.innerHTML = textoOriginal;
            }
          });
      });
    }

    var formContrasena = getEl("formCambiarContrasena");

if (formContrasena) {

  formContrasena.addEventListener("submit", function (e) {
    e.preventDefault();

    var actual = (formContrasena.querySelector("[name='password_actual']") || {}).value || "";
    var nueva = (formContrasena.querySelector("[name='password_nueva']") || {}).value || "";
    var confirmar = (formContrasena.querySelector("[name='password_confirmar']") || {}).value || "";

    // Validar contraseña actual
    if (!actual.trim()) {
      if (window.Swal) {
        Swal.fire({
          toast: true,
          position: "top-end",
          icon: "error",
          title: "Debe ingresar la contraseña actual.",
          showConfirmButton: false,
          timer: 2500,
          zIndex: 999999
        });
      } else {
        alert("Debe ingresar la contraseña actual.");
      }
      return;
    }

    // Validar coincidencia
    if (nueva !== confirmar) {
      if (window.Swal) {
        Swal.fire({
          toast: true,
          position: "top-end",
          icon: "error",
          title: "Las contraseñas no coinciden.",
          showConfirmButton: false,
          timer: 2500,
          zIndex: 999999
        });
      } else {
        alert("Las contraseñas no coinciden.");
      }
      return;
    }

    // Validar longitud
    if (nueva.length < 6) {
      if (window.Swal) {
        Swal.fire({
          toast: true,
          position: "top-end",
          icon: "error",
          title: "La nueva contraseña debe tener al menos 6 caracteres.",
          showConfirmButton: false,
          timer: 2500,
          zIndex: 999999
        });
      } else {
        alert("La nueva contraseña debe tener al menos 6 caracteres.");
      }
      return;
    }

    // Validar que no sea igual
    if (nueva === actual) {
      if (window.Swal) {
        Swal.fire({
          toast: true,
          position: "top-end",
          icon: "error",
          title: "La nueva contraseña no puede ser igual a la actual.",
          showConfirmButton: false,
          timer: 2500,
          zIndex: 999999
        });
      } else {
        alert("La nueva contraseña no puede ser igual a la actual.");
      }
      return;
    }

    // Botón loading
    var btn = formContrasena.querySelector('button[type="submit"]');
    var originalText = btn ? btn.innerHTML : "";

    if (btn) {
      btn.disabled = true;
      btn.innerHTML = `
        <span class="inline-block animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></span>
        Procesando...
      `;
    }

    // Enviar datos
    var fd = new FormData();
    fd.append("id_usuario", USUARIO_ID);
    fd.append("password_actual", actual);
    fd.append("password_nueva", nueva);

    fetch(API + "?accion=cambiarContrasena", {
      method: "POST",
      body: fd
    })
    .then(function (r) {
      return r.json();
    })
    .then(function (data) {

      if (data && data.success) {

        if (window.Swal) {
          Swal.fire({
            toast: true,
            position: "top-end",
            icon: "success",
            title: data.message || "Contraseña actualizada.",
            showConfirmButton: false,
            timer: 2500,
            zIndex: 999999
          });
        } else {
          alert(data.message || "Contraseña actualizada.");
        }

        closeModal("modalCambiarContrasena");
        formContrasena.reset();

      } else {

        if (window.Swal) {
          Swal.fire({
            toast: true,
            position: "top-end",
            icon: "error",
            title: data.error || "Error al cambiar contraseña.",
            showConfirmButton: false,
            timer: 2500,
            zIndex: 999999
          });
        } else {
          alert(data.error || "Error al cambiar contraseña.");
        }

      }

    })
    .catch(function () {

      if (window.Swal) {
        Swal.fire({
          toast: true,
          position: "top-end",
          icon: "error",
          title: "Error de conexión.",
          showConfirmButton: false,
          timer: 2500,
          zIndex: 999999
        });
      } else {
        alert("Error de conexión.");
      }

    })
    .finally(function () {

      if (btn) {
        btn.disabled = false;
        btn.innerHTML = originalText;
      }

    });

  });

}

    initTogglePassword(getEl("modalCambiarContrasena"));
    enhanceSelectsPerfilModal();
  });
})();
