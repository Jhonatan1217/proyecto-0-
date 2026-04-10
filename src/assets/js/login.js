const LOGIN_FETCH_ROOT =
  typeof window !== "undefined" && typeof window.LOGIN_FETCH_ROOT === "string"
    ? window.LOGIN_FETCH_ROOT.replace(/\/?$/, "/")
    : "";

function loginApiUrl(path) {
  const p = String(path || "").replace(/^\//, "");
  return LOGIN_FETCH_ROOT + p;
}

let usuarioPendiente = null;
let contador = 30;
let intervalo = null;

const EYE_SLASH =
  '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />';
const EYE_OPEN =
  '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />' +
  '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';

function setInlineMessage(elementId, text, variant) {
  const el = document.getElementById(elementId);
  if (!el) return;
  const base =
    "rounded-lg border px-3 py-2 text-sm" +
    (elementId === "loginFormMessage" ? " text-center" : "");
  el.className = base;
  if (!text) {
    el.textContent = "";
    el.classList.add("hidden");
    return;
  }
  el.textContent = text;
  el.classList.remove("hidden");
  if (variant === "success") {
    el.classList.add("border-green-200", "bg-green-50", "text-green-800");
  } else if (variant === "info") {
    el.classList.add("border-gray-200", "bg-gray-50", "text-gray-700");
  } else {
    el.classList.add("border-red-100", "bg-red-50", "text-red-600");
  }
}

function setVerificationMessage(text, variant) {
  const el = document.getElementById("verificationFormMessage");
  if (!el) return;
  if (!text) {
    el.textContent = "";
    el.classList.add("hidden");
    return;
  }
  el.textContent = text;
  el.classList.remove("hidden");
  el.className =
    "mb-3 rounded-lg border px-2 py-1.5 text-[11px] text-center" +
    (variant === "success"
      ? " border-green-200 bg-green-50 text-green-800"
      : variant === "info"
        ? " border-gray-200 bg-gray-50 text-gray-700"
        : " border-red-100 bg-red-50 text-red-600");
}

function setPasswordModalMessage(text, variant) {
  const el = document.getElementById("passwordFormMessage");
  if (!el) return;
  if (!text) {
    el.textContent = "";
    el.classList.add("hidden");
    return;
  }
  el.textContent = text;
  el.classList.remove("hidden");
  el.className =
    "mb-3 rounded-lg border px-2 py-1.5 text-[11px]" +
    (variant === "success"
      ? " border-green-200 bg-green-50 text-green-800"
      : " border-red-100 bg-red-50 text-red-600");
}

function mostrarCorreoEnModal(correo) {
  const dest = document.getElementById("correoUsuario");
  if (!dest || !correo) return;
  const [usuario, dominio] = String(correo).split("@");
  if (!dominio) {
    dest.textContent = correo;
    return;
  }
  const u = usuario.length > 2 ? usuario.substring(0, 2) + "****" : "****";
  dest.textContent = `${u}@${dominio}`;
}

function togglePasswordVisibility(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon = document.getElementById(iconId);
  if (!input || !icon) return;
  const btn = document.getElementById("togglePasswordBtn");
  if (input.type === "password") {
    input.type = "text";
    icon.innerHTML = EYE_OPEN;
    if (btn) btn.setAttribute("aria-label", "Ocultar contraseña");
  } else {
    input.type = "password";
    icon.innerHTML = EYE_SLASH;
    if (btn) btn.setAttribute("aria-label", "Mostrar contraseña");
  }
}

function toggleFirstLoginPassword(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon = document.getElementById(iconId);
  if (!input || !icon) return;
  if (input.type === "password") {
    input.type = "text";
    icon.innerHTML = EYE_OPEN;
  } else {
    input.type = "password";
    icon.innerHTML = EYE_SLASH;
  }
}

function iniciarContadorReenvio() {
  if (intervalo) {
    clearInterval(intervalo);
    intervalo = null;
  }

  contador = 30;
  const btn = document.getElementById("reenviarBtn");
  const txt = document.getElementById("contadorTexto");
  if (btn) {
    btn.classList.add("hidden");
    btn.disabled = true;
  }
  if (txt) txt.textContent = `Puedes reenviar el código en ${contador} s`;

  intervalo = setInterval(() => {
    contador -= 1;
    if (txt) {
      txt.textContent =
        contador > 0 ? `Puedes reenviar el código en ${contador} s` : "";
    }
    if (contador <= 0) {
      clearInterval(intervalo);
      intervalo = null;
      if (btn) {
        btn.classList.remove("hidden");
        btn.disabled = false;
      }
    }
  }, 1000);
}

/* ================= LOGIN ================= */
const loginForm = document.getElementById("loginForm");
if (loginForm) {
  loginForm.addEventListener("submit", function (e) {
    e.preventDefault();
    setInlineMessage("loginFormMessage", "", "error");

    const formData = new FormData(this);
    const correo = String(formData.get("correo") || "").trim();

    fetch(loginApiUrl("src/controllers/login_controller.php"), {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.status === "success") {
          window.location.href = "index.php?page=register_tables";
          return;
        }

        if (data.status === "require_verification") {
          usuarioPendiente = data.id_usuario;
          if (correo) {
            try {
              localStorage.setItem("correoUsuario", correo);
            } catch (_) {}
            mostrarCorreoEnModal(correo);
          } else {
            const saved = localStorage.getItem("correoUsuario");
            if (saved) mostrarCorreoEnModal(saved);
          }

          setVerificationMessage("", "info");
          document.querySelectorAll(".otp-input").forEach((input) => {
            input.value = "";
          });

          const modal = document.getElementById("verificationModal");
          if (modal) modal.classList.remove("hidden");

          iniciarContadorReenvio();
          const firstOtp = document.querySelector(".otp-input");
          if (firstOtp) firstOtp.focus();
          return;
        }

        if (data.status === "error_mail") {
          setInlineMessage(
            "loginFormMessage",
            data.message || "No se pudo enviar el correo de verificación. Intenta más tarde.",
            "error"
          );
          return;
        }

        if (data.status === "error") {
          setInlineMessage(
            "loginFormMessage",
            data.message || "Credenciales inválidas.",
            "error"
          );
        }
      })
      .catch(() => {
        setInlineMessage(
          "loginFormMessage",
          "No se pudo conectar con el servidor. Revisa tu conexión.",
          "error"
        );
      });
  });
}

/* ================= REENVIAR CODIGO ================= */
function reenviarCodigo() {
  if (!usuarioPendiente) return;
  if (contador > 0) return;

  setVerificationMessage("", "info");

  fetch(loginApiUrl("src/controllers/resend_token.php"), {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ id_usuario: usuarioPendiente }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "resent") {
        setVerificationMessage("Te enviamos un nuevo código al correo.", "success");
        iniciarContadorReenvio();
        document.querySelectorAll(".otp-input").forEach((input) => {
          input.value = "";
        });
        document.querySelector(".otp-input")?.focus();
        return;
      }
      if (data.status === "error_mail") {
        setVerificationMessage(
          data.message || "No se pudo reenviar el correo. Intenta más tarde.",
          "error"
        );
        return;
      }
      setVerificationMessage("No fue posible reenviar el código. Intenta de nuevo.", "error");
    })
    .catch(() => {
      setVerificationMessage("Error de conexión al reenviar el código.", "error");
    });
}

/* ================= OTP ================= */
document.querySelectorAll(".otp-input").forEach((input, index, inputs) => {
  input.addEventListener("input", function () {
    this.value = this.value.replace(/[^0-9]/g, "");
    if (this.value.length === 1 && index < inputs.length - 1) {
      inputs[index + 1].focus();
    }
  });

  input.addEventListener("keydown", function (e) {
    if (e.key === "Backspace" && this.value === "" && index > 0) {
      inputs[index - 1].focus();
    }
  });
});

/* ================= VERIFICAR CODIGO ================= */
function verificarCodigo() {
  setVerificationMessage("", "info");

  let codigo = "";
  document.querySelectorAll(".otp-input").forEach((input) => {
    codigo += input.value;
  });

  if (codigo.length !== 6) {
    setVerificationMessage("Ingresa los 6 dígitos del código.", "error");
    return;
  }

  fetch(loginApiUrl("src/controllers/verify_token.php"), {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      id_usuario: usuarioPendiente,
      token: codigo,
    }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "verified") {
        document.getElementById("verificationModal")?.classList.add("hidden");
        setPasswordModalMessage("", "error");
        document.getElementById("passwordModal")?.classList.remove("hidden");
        document.getElementById("newPassword")?.focus();
        return;
      }
      setVerificationMessage("Código inválido o expirado. Revisa e intenta de nuevo.", "error");
    })
    .catch(() => {
      setVerificationMessage("Error de conexión al verificar el código.", "error");
    });
}

/* ================= CAMBIAR PASSWORD ================= */
function cambiarPassword() {
  setPasswordModalMessage("", "error");

  const nueva = document.getElementById("newPassword")?.value || "";
  const confirma = document.getElementById("confirmNewPassword")?.value || "";

  if (nueva.length < 6) {
    setPasswordModalMessage("La contraseña debe tener mínimo 6 caracteres.", "error");
    return;
  }
  if (nueva !== confirma) {
    setPasswordModalMessage("Las contraseñas no coinciden.", "error");
    return;
  }

  fetch(loginApiUrl("src/controllers/change_password_first.php"), {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      id_usuario: usuarioPendiente,
      password: nueva,
    }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "password_changed") {
        window.location.href = "index.php?page=register_tables";
        return;
      }
      setPasswordModalMessage("No se pudo actualizar la contraseña. Intenta de nuevo.", "error");
    })
    .catch(() => {
      setPasswordModalMessage("Error de conexión al guardar la contraseña.", "error");
    });
}
