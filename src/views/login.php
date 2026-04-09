<?php
// login.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda - Iniciar Sesion</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="src/assets/css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        'verde-dark': '#1a8c37',
                        'verde-mid': '#148a2e',
                        'verde-bright': '#25a844',
                        'verde-light': '#4dca6a',
                        'verde-accent': '#2db84d',
                        'verde-deep': '#0e6b24',
                        'verde-text': '#1a6b2c',
                        'verde-muted': '#c5e8cd',
                        'dark-navy': '#1a2e3b',
                        'dark-navy-hover': '#253d4d',
                        'gray-off': '#f5f5f5',
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
         .otp-input {
            width: 38px;
            height: 42px;
            font-size: 16px;
            text-align: center;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            outline: none;
        }
        .otp-input:focus {
            border-color: #9ca3af;
            box-shadow: 0 0 0 2px rgba(0,0,0,0.05);
        }
    </style>
</head>

<body class="bg-white text-gray-800 antialiased h-screen overflow-hidden">

<main class="flex h-full w-full flex-col lg:flex-row overflow-hidden">

    <!-- PANEL IZQUIERDO -->
    <div class="hidden lg:block lg:w-1/2 h-full overflow-hidden">
        <img
            src="src/assets/img/agenda.png"
            alt="Agenda de Hoy"
            class="img-left-focus"
        >
    </div>

    <!-- PANEL DERECHO LOGIN -->
    <div class="flex flex-1 items-center justify-center bg-white px-6 py-12 lg:px-16">
        <div class="w-full max-w-sm">

        <div class="mb-6">
            <a href="index.php" class="inline-block">
                <img src="src/assets/img/flecha_izquierda.png"
                    alt="Volver"
                    class="w-6 h-6 opacity-70 hover:opacity-100 transition">
            </a>
        </div>

            <h1 class="mb-10 text-center text-4xl font-bold text-green-700">
                Bienvenido
            </h1>

            <form id="loginForm" method="POST" class="flex flex-col gap-6">

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700">Correo Electronico</label>
                    <input
                        name="correo"
                        type="email"
                        required
                        class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-600/30 focus:outline-none"
                        placeholder="correo_ejemplo@gmail.com"
                    >
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700">Contraseña</label>
                    <div class="relative">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            class="h-11 w-full rounded-lg border border-gray-300 px-4 pr-10 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-600/30 focus:outline-none"
                            placeholder="Contrasena"
                        >
                        <button type="button"
                            id="togglePasswordBtn"
                            onclick="togglePassword()"
                            class="absolute top-1/2 right-3 -translate-y-1/2 w-5 h-5 text-gray-500">
                            👁
                        </button>
                    </div>
                </div>

                <div class="flex justify-end">
                    <a href="src/views/restablecerContrasenia.php"
                    class="text-sm text-gray-500 hover:text-green-700 transition">
                        ¿Olvidaste la contraseña?
                    </a>
                </div>

                <div class="flex justify-center">
                    <button
                        type="submit"
                        name="login"
                        class="h-12 w-48 rounded-full bg-dark-navy text-sm font-semibold text-white hover:bg-dark-navy-hover">
                        Iniciar Sesion
                    </button>
                </div>
            </form>

            <?php if (isset($_GET['error'])): ?>
                <p class="mt-4 text-center text-sm text-red-500">
                    Credenciales invalidas. Intente de nuevo.
                </p>
            <?php endif; ?>

        </div>
    </div>

</main>

<!-- ================= MODAL OTP ================= -->
<div id="verificationModal" class="fixed inset-0 bg-black/70 hidden flex items-center justify-center z-50">
  <div class="bg-white w-[320px] rounded-xl p-5 relative text-center">

    <!-- cerrar -->
    <button onclick="cerrarModal('verificationModal')" class="absolute top-3 right-3 text-gray-400 text-sm">✕</button>

    <div class="flex items-center text-[11px] mb-5">

  <!-- Paso 1 -->
    <div class="flex items-center gap-2">
        <div class="w-5 h-5 bg-black text-white rounded-full flex items-center justify-center text-[10px]">
        1
        </div>
        <span class="text-black">Verificar</span>
    </div>

    <!-- línea -->
    <div class="flex-1 h-[1px] bg-gray-200 mx-3"></div>

    <!-- Paso 2 -->
    <div class="flex items-center gap-2">
        <div class="w-5 h-5 border border-gray-300 rounded-full flex items-center justify-center text-[10px] text-gray-400">
        2
        </div>
        <span class="text-gray-400">Contraseña</span>
    </div>

    </div>

    <!-- icono -->
    <div class="flex justify-center mb-3">
        <div class="w-10 h-10 rounded-full border border-gray-200 bg-gray-50 flex items-center justify-center">
            <img src="<?= BASE_URL ?>src/assets/img/icons/Security/Shield-Check.svg" class="w-5 h-5">
        </div>
    </div>

    <h2 class="text-sm font-semibold">Verifica tu correo</h2>
    <p class="text-[11px] text-gray-500 mb-4">
        Hemos enviado un código de 6 dígitos a<br>
        <span class="text-black" id="correoUsuario"></span>
    </p>

    <!-- OTP 3 + 3 -->
    <div class="flex justify-center items-center gap-3 mb-4">
        <div class="flex gap-1">
            <input class="otp-input" maxlength="1">
            <input class="otp-input" maxlength="1">
            <input class="otp-input" maxlength="1">
        </div>

        <div class="w-4 h-[1px] bg-gray-300"></div>

        <div class="flex gap-1">
            <input class="otp-input" maxlength="1">
            <input class="otp-input" maxlength="1">
            <input class="otp-input" maxlength="1">
        </div>
    </div>

    <!-- botón -->
    <button onclick="verificarCodigo()"
        class="w-full bg-gray-200 text-gray-500 py-2 rounded-md text-xs flex items-center justify-center gap-2">
        <img src="<?= BASE_URL ?>src/assets/img/icons/Security/Shield-Check.svg" class="w-4 h-4">
        Verificar correo
    </button>

    <p class="text-[10px] text-gray-400 mt-3">
        No recibiste el código?
        <button onclick="reenviarCodigo()" class="text-black">Reenviar código</button>
    </p>

  </div>
</div>

<!-- ================= PASSWORD ================= -->
<div id="passwordModal" class="fixed inset-0 bg-black/70 hidden flex items-center justify-center z-50">
  <div class="bg-white w-[320px] rounded-xl p-5 relative text-left">

    <!-- cerrar -->
    <button onclick="cerrarModal('passwordModal')" class="absolute top-3 right-3 text-gray-400 text-sm">✕</button>

    <div class="flex items-center text-[11px] mb-5">


  <!-- Paso 1 COMPLETADO -->
    <div class="flex items-center gap-2">

    <!-- bola verde con icono dentro -->
    <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
        <img src="<?= BASE_URL ?>src/assets/img/icons/Security/Shield-Check.svg" class="w-3.5 h-3.5 invert">
    </div>

    <span class="text-green-600">Verificar</span>

    </div>

    <!-- línea -->
    <div class="flex-1 h-[1px] bg-gray-200 mx-3"></div>

    <!-- Paso 2 ACTIVO -->
    <div class="flex items-center gap-2">
        <div class="w-5 h-5 bg-black text-white rounded-full flex items-center justify-center text-[10px]">
        2
        </div>
        <span class="text-black">Contraseña</span>
    </div>

    </div>

    <!-- icono -->
    <div class="flex justify-center mb-3">
        <div class="w-10 h-10 rounded-full border border-gray-200 bg-gray-50 flex items-center justify-center">
            <img src="<?= BASE_URL ?>src/assets/img/icons/Security/Shield-Check.svg" class="w-5 h-5">
        </div>
    </div>

    <h2 class="text-sm font-semibold text-center">Cambiar contraseña</h2>
    <p class="text-[11px] text-gray-500 text-center mb-4">
        Tu correo fue verificado correctamente. Ahora ingresa tu nueva contraseña.
    </p>

    <!-- input 1 -->
    <label class="text-[11px] text-gray-600">Nueva contraseña</label>
    <div class="relative mb-3">
        <input type="password" id="newPassword"
            class="w-full border rounded-md px-3 py-2 text-xs pr-8"
            placeholder="Ingresa tu nueva contraseña">
        <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400">👁</span>
    </div>

    <!-- input 2 -->
    <label class="text-[11px] text-gray-600">Confirmar contraseña</label>
    <div class="relative mb-4">
        <input type="password"
            class="w-full border rounded-md px-3 py-2 text-xs pr-8"
            placeholder="Ingresa la contraseña de nuevo">
        <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400">👁</span>
    </div>

    <!-- botón -->
    <button onclick="cambiarPassword()"
    class="w-full bg-gray-200 text-gray-500 py-2 rounded-md text-xs flex items-center justify-center gap-2">

    <img src="<?= BASE_URL ?>src/assets/img/icons/Security/lock.svg" class="w-4 h-4">

    Cambiar contraseña
    </button>

    <p class="text-[11px] text-gray-400 mt-3 text-center cursor-pointer"
       onclick="volverVerificacion()">
        ← Volver a verificación
    </p>

  </div>
</div>

<script>
function abrirModal(id){
    document.getElementById(id).classList.remove("hidden");
}
function cerrarModal(id){
    document.getElementById(id).classList.add("hidden");
}
function volverVerificacion(){
    cerrarModal("passwordModal");
    abrirModal("verificationModal");
}
</script>
<script>
document.getElementById("loginForm").addEventListener("submit", function(e) {
    e.preventDefault(); // evita que recargue

    const correo = document.querySelector("input[name='correo']").value;

    // guardar correo
    localStorage.setItem("correoUsuario", correo);

    // mostrar correo en el modal
    mostrarCorreo();

    // abrir modal
    abrirModal("verificationModal");
});
</script>
<script>
function mostrarCorreo() {
    const correo = localStorage.getItem("correoUsuario");

    if (!correo) return;

    const [usuario, dominio] = correo.split("@");

    const oculto = usuario.substring(0, 2) + "****@" + dominio;

    document.getElementById("correoUsuario").textContent = oculto;
}
</script>
<script src="./src/assets/js/login.js"></script>

</body>
</html>