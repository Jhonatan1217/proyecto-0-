let usuarioPendiente = null;
let contador = 60;
let intervalo = null;

/* ================= LOGIN ================= */
document.getElementById("loginForm").addEventListener("submit", function (e) {

    e.preventDefault();

    const formData = new FormData(this);

    fetch("src/controllers/login_controller.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {

        if (data.status === "success") {
            window.location.href = "index.php?page=register_tables";
        }

        if (data.status === "require_verification") {

            console.log("Código:", data.token_debug);

            usuarioPendiente = data.id_usuario;

            document.getElementById("verificationModal")
                .classList.remove("hidden");

            iniciarContador();
        }

        if (data.status === "error") {
            alert("Credenciales inválidas");
        }
    });
});

function togglePassword() {
    const input = document.getElementById("password");
    const btn = document.getElementById("togglePasswordBtn");
    const iconOpen = document.getElementById("iconEyeOpen");
    const iconClosed = document.getElementById("iconEyeClosed");
    if (!input || !btn || !iconOpen || !iconClosed) return;

    if (input.type === "password") {
        input.type = "text";
        iconOpen.classList.add("hidden");
        iconClosed.classList.remove("hidden");
        btn.setAttribute("aria-label", "Ocultar contraseña");
    } else {
        input.type = "password";
        iconOpen.classList.remove("hidden");
        iconClosed.classList.add("hidden");
        btn.setAttribute("aria-label", "Mostrar contraseña");
    }
}

/* ================= CONTADOR REENVIO ================= */
function iniciarContador() {

    contador = 60;

    document.getElementById("reenviarBtn").classList.add("hidden");

    intervalo = setInterval(() => {

        contador--;

        document.getElementById("contadorTexto").innerText =
            "Puedes reenviar en " + contador + "s";

        if (contador <= 0) {
            clearInterval(intervalo);
            document.getElementById("contadorTexto").innerText = "";
            document.getElementById("reenviarBtn").classList.remove("hidden");
        }

    }, 1000);
}


/* ================= REENVIAR CODIGO ================= */
function reenviarCodigo() {

    fetch("src/controllers/resend_token.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            id_usuario: usuarioPendiente
        })
    })
    .then(res => res.json())
    .then(data => {

        if (data.status === "resent") {

            console.log("Nuevo código:", data.token_debug);

            alert("Nuevo código enviado");

            iniciarContador();

            document.querySelectorAll(".otp-input").forEach(input => {
                input.value = "";
            });

            document.querySelector(".otp-input").focus();
        }
    });
}


/* ================= OTP INTELIGENTE ================= */
document.querySelectorAll(".otp-input").forEach((input, index, inputs) => {

    input.addEventListener("input", function () {

        this.value = this.value.replace(/[^0-9]/g, '');

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

    let codigo = "";

    document.querySelectorAll(".otp-input").forEach(input => {
        codigo += input.value;
    });

    if (codigo.length !== 6) {
        alert("Debe ingresar los 6 dígitos");
        return;
    }

    fetch("src/controllers/verify_token.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            id_usuario: usuarioPendiente,
            token: codigo
        })
    })
    .then(res => res.json())
    .then(data => {

        if (data.status === "verified") {

            document.getElementById("verificationModal")
                .classList.add("hidden");

            document.getElementById("passwordModal")
                .classList.remove("hidden");

        } else {
            alert("Código inválido o expirado");
        }
    });
}


/* ================= CAMBIAR PASSWORD ================= */
function cambiarPassword() {

    const nueva = document.getElementById("newPassword").value;

    if (nueva.length < 6) {
        alert("La contraseña debe tener mínimo 6 caracteres");
        return;
    }

    fetch("src/controllers/change_password_first.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            id_usuario: usuarioPendiente,
            password: nueva
        })
    })
    .then(res => res.json())
    .then(data => {

        if (data.status === "password_changed") {
            window.location.href = "index.php?page=register_tables";
        }
    });
}
