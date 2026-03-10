<?php
// login.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Agenda - Iniciar Sesión</title>

<script src="https://cdn.tailwindcss.com"></script>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
body { font-family: 'Inter', sans-serif; }

/* OTP */
.otp-input {
    width: 50px;
    height: 55px;
    text-align: center;
    font-size: 22px;
    border: 2px solid #ccc;
    border-radius: 10px;
    outline: none;
    transition: 0.2s;
}
.otp-input:focus {
    border-color: #15803d;
}
</style>

</head>

<body class="bg-white text-gray-800 h-screen overflow-hidden">

<main class="flex h-full w-full flex-col lg:flex-row">

<div class="hidden lg:block lg:w-1/2 h-full">
    <img src="src/assets/img/agenda.png"
         class="w-full h-full object-cover"
         alt="Agenda">
</div>

<div class="flex flex-1 items-center justify-center bg-white px-6">
    <div class="w-full max-w-sm">

        <h1 class="mb-10 text-center text-4xl font-bold text-green-700">
            Bienvenido
        </h1>

        <!-- LOGIN -->
        <form id="loginForm" class="flex flex-col gap-6">

            <div>
                <label class="text-sm font-medium text-gray-700">Correo Electrónico</label>
                <input name="correo" type="email" required
                    class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-600/30 focus:outline-none"
                    placeholder="correo@gmail.com">
            </div>

            <!-- PASSWORD CON OJITO -->
            <div>
                <label class="text-sm font-medium text-gray-700">Contraseña</label>
                <div class="relative">
                    <input id="password"
                        name="password"
                        type="password"
                        required
                        class="h-11 w-full rounded-lg border border-gray-300 px-4 pr-12 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-600/30 focus:outline-none"
                        placeholder="Contraseña">

                    <button type="button"
                        onclick="togglePassword()"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                        👁
                    </button>
                </div>
            </div>

            <div class="flex justify-center">
                <button type="submit"
                    class="h-12 w-48 rounded-full bg-gray-800 text-sm font-semibold text-white hover:bg-gray-900">
                    Iniciar Sesión
                </button>
            </div>

        </form>

    </div>
</div>

</main>

<!-- ================= MODAL VERIFICACION ================= -->
<div id="verificationModal"
     class="fixed inset-0 bg-black/50 hidden flex items-center justify-center">

    <div class="bg-white p-8 rounded-xl w-96 text-center space-y-6">

        <h2 class="text-xl font-bold text-green-700">
            Código de Verificación
        </h2>

        <div class="flex justify-center gap-3">
            <input class="otp-input" maxlength="1">
            <input class="otp-input" maxlength="1">
            <input class="otp-input" maxlength="1">
            <input class="otp-input" maxlength="1">
            <input class="otp-input" maxlength="1">
            <input class="otp-input" maxlength="1">
        </div>

        <button onclick="verificarCodigo()"
            class="w-full bg-green-700 text-white py-2 rounded-lg">
            Verificar
        </button>

        <!-- REENVIAR -->
        <div class="text-sm text-gray-600">
            <span id="contadorTexto">Puedes reenviar en 60s</span>
            <button id="reenviarBtn"
                onclick="reenviarCodigo()"
                class="hidden text-green-700 font-semibold">
                Reenviar código
            </button>
        </div>

    </div>
</div>

<!-- ================= MODAL CAMBIO PASSWORD ================= -->
<div id="passwordModal"
     class="fixed inset-0 bg-black/50 hidden flex items-center justify-center">

    <div class="bg-white p-8 rounded-xl w-96 text-center space-y-6">

        <h2 class="text-xl font-bold text-green-700">
            Cambiar Contraseña Obligatoria
        </h2>

        <input type="password" id="newPassword"
            class="w-full border rounded-lg p-3 text-center"
            placeholder="Nueva contraseña segura">

        <button onclick="cambiarPassword()"
            class="w-full bg-green-700 text-white py-2 rounded-lg">
            Guardar y Continuar
        </button>

    </div>
</div>

<script>

let usuarioPendiente = null;

/* OJITO */
function togglePassword() {
    const input = document.getElementById("password");
    input.type = input.type === "password" ? "text" : "password";
}

/* LOGIN */
document.getElementById("loginForm").addEventListener("submit", function(e) {

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
        }

        if (data.status === "error") {
            alert("Credenciales inválidas");
        }
    });
});

/* OTP */
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

/* VERIFICAR CODIGO */
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

/* CAMBIAR PASSWORD */
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

</script>

</body>
</html>