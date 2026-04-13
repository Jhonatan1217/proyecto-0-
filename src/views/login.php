<?php
// login.php
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $project = preg_replace('#/src/.*$#', '', $scriptDir);
    define('BASE_URL', $protocol . $host . $project . '/');
}
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
                            onclick="togglePasswordVisibility('password', 'loginEyeIcon')"
                            class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none p-0.5"
                            aria-label="Mostrar contraseña">
                            <svg id="loginEyeIcon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex justify-end">
                    <a href="src/views/restablecerContrasenia.php"
                    class="text-sm text-gray-500 hover:text-green-700 transition">
                        ¿Olvidaste la contraseña?
                    </a>
                </div>

                <p id="loginFormMessage" class="hidden rounded-lg border px-3 py-2 text-sm text-center" role="alert"></p>

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
<div id="verificationModal" class="fixed inset-0 bg-black/70 hidden flex items-center justify-center z-50 p-4 sm:p-6">
  <div class="bg-white w-full max-w-[28rem] sm:max-w-[32rem] rounded-2xl p-6 sm:p-8 relative text-center shadow-2xl">

    <!-- cerrar: más separación del paso "Contraseña" -->
    <button type="button" onclick="cerrarModal('verificationModal')" class="absolute top-4 right-4 z-10 flex h-10 w-10 items-center justify-center rounded-xl text-gray-500 hover:bg-gray-100 hover:text-gray-800 text-xl leading-none transition" aria-label="Cerrar">✕</button>

    <div class="flex items-center text-sm sm:text-base font-medium mb-6 pr-14 sm:pr-16 text-left">

  <!-- Paso 1 -->
    <div class="flex items-center gap-2 shrink-0">
        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-black text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-semibold">
        1
        </div>
        <span class="text-black">Verificar</span>
    </div>

    <!-- línea -->
    <div class="flex-1 h-px bg-gray-200 mx-3 min-w-[1rem]"></div>

    <!-- Paso 2 -->
    <div class="flex items-center gap-2 shrink-0 min-w-0">
        <div class="w-7 h-7 sm:w-8 sm:h-8 border-2 border-gray-300 rounded-full flex items-center justify-center text-xs sm:text-sm text-gray-400 font-semibold">
        2
        </div>
        <span class="text-gray-400 truncate">Contraseña</span>
    </div>

    </div>

    <!-- icono -->
    <div class="flex justify-center mb-4">
        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full border border-gray-200 bg-gray-50 flex items-center justify-center">
            <img src="<?= BASE_URL ?>src/assets/img/icons/Security/Shield-Check.svg" class="w-7 h-7 sm:w-8 sm:h-8" alt="">
        </div>
    </div>

    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Verifica tu correo</h2>
    <p class="text-sm sm:text-base text-gray-600 mb-5 leading-relaxed">
        Hemos enviado un código de 6 dígitos a<br>
        <span class="text-gray-900 font-medium" id="correoUsuario"></span>
    </p>

    <p id="verificationFormMessage" class="hidden mb-4 rounded-xl border px-3 py-2 text-sm text-center" role="alert"></p>

    <!-- OTP 3 + 3 -->
    <div id="verificationOtpWrap" class="flex justify-center items-center gap-3 sm:gap-4 mb-6">
        <div class="flex gap-2">
            <input type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" class="otp-input h-14 w-12 sm:h-16 sm:w-14 rounded-xl border-2 border-gray-200 text-center text-xl sm:text-2xl font-semibold text-gray-900 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-600/25 focus:outline-none" maxlength="1" aria-label="Dígito 1">
            <input type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" class="otp-input h-14 w-12 sm:h-16 sm:w-14 rounded-xl border-2 border-gray-200 text-center text-xl sm:text-2xl font-semibold text-gray-900 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-600/25 focus:outline-none" maxlength="1" aria-label="Dígito 2">
            <input type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" class="otp-input h-14 w-12 sm:h-16 sm:w-14 rounded-xl border-2 border-gray-200 text-center text-xl sm:text-2xl font-semibold text-gray-900 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-600/25 focus:outline-none" maxlength="1" aria-label="Dígito 3">
        </div>

        <div class="w-6 text-gray-300 text-lg font-light select-none" aria-hidden="true">—</div>

        <div class="flex gap-2">
            <input type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" class="otp-input h-14 w-12 sm:h-16 sm:w-14 rounded-xl border-2 border-gray-200 text-center text-xl sm:text-2xl font-semibold text-gray-900 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-600/25 focus:outline-none" maxlength="1" aria-label="Dígito 4">
            <input type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" class="otp-input h-14 w-12 sm:h-16 sm:w-14 rounded-xl border-2 border-gray-200 text-center text-xl sm:text-2xl font-semibold text-gray-900 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-600/25 focus:outline-none" maxlength="1" aria-label="Dígito 5">
            <input type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" class="otp-input h-14 w-12 sm:h-16 sm:w-14 rounded-xl border-2 border-gray-200 text-center text-xl sm:text-2xl font-semibold text-gray-900 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-600/25 focus:outline-none" maxlength="1" aria-label="Dígito 6">
        </div>
    </div>

    <!-- botón -->
    <button type="button" id="btnVerificarCorreo" onclick="verificarCodigo()" disabled
        class="w-full py-3.5 rounded-xl text-sm sm:text-base font-semibold flex items-center justify-center gap-2 bg-gray-200 text-gray-500 cursor-not-allowed transition-colors shadow-sm">
        <img src="<?= BASE_URL ?>src/assets/img/icons/Security/Shield-Check.svg" class="w-5 h-5 opacity-60" alt="">
        Verificar correo
    </button>

    <p class="text-xs sm:text-sm text-gray-500 mt-4 leading-relaxed">
        <span id="contadorTexto" class="block min-h-[1.25em] text-gray-600 mb-2 text-center"></span>
        <span class="inline-flex flex-wrap items-center justify-center gap-1 w-full">
            <span>¿No recibiste el código?</span>
            <button type="button" id="reenviarBtn" onclick="reenviarCodigo()" class="text-[#0a3a57] underline font-semibold hover:text-green-700 hidden">Reenviar código</button>
        </span>
    </p>

  </div>
</div>

<!-- ================= PASSWORD ================= -->
<div id="passwordModal" class="fixed inset-0 bg-black/70 hidden flex items-center justify-center z-50 p-4 sm:p-6">
  <div class="bg-white w-full max-w-[28rem] sm:max-w-[32rem] rounded-2xl p-6 sm:p-8 relative text-left shadow-2xl">

    <!-- cerrar -->
    <button type="button" onclick="cerrarModal('passwordModal')" class="absolute top-4 right-4 z-10 flex h-10 w-10 items-center justify-center rounded-xl text-gray-500 hover:bg-gray-100 hover:text-gray-800 text-xl leading-none transition" aria-label="Cerrar">✕</button>

    <div class="flex items-center text-sm sm:text-base font-medium mb-6 pr-14 sm:pr-16">


  <!-- Paso 1 COMPLETADO -->
    <div class="flex items-center gap-2 shrink-0">

    <!-- bola verde con icono dentro -->
    <div class="w-7 h-7 sm:w-8 sm:h-8 bg-green-500 rounded-full flex items-center justify-center">
        <img src="<?= BASE_URL ?>src/assets/img/icons/Security/Shield-Check.svg" class="w-4 h-4 invert" alt="">
    </div>

    <span class="text-green-600">Verificar</span>

    </div>

    <!-- línea -->
    <div class="flex-1 h-px bg-gray-200 mx-3 min-w-[1rem]"></div>

    <!-- Paso 2 ACTIVO -->
    <div class="flex items-center gap-2 shrink-0 min-w-0">
        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-black text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-semibold">
        2
        </div>
        <span class="text-black truncate">Contraseña</span>
    </div>

    </div>

    <!-- icono -->
    <div class="flex justify-center mb-4">
        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full border border-gray-200 bg-gray-50 flex items-center justify-center">
            <img src="<?= BASE_URL ?>src/assets/img/icons/Security/Shield-Check.svg" class="w-7 h-7 sm:w-8 sm:h-8" alt="">
        </div>
    </div>

    <h2 class="text-xl sm:text-2xl font-bold text-center text-gray-900 mb-2">Cambiar contraseña</h2>
    <p class="text-sm sm:text-base text-gray-600 text-center mb-5 leading-relaxed">
        Tu correo fue verificado correctamente. Ahora ingresa tu nueva contraseña.
    </p>

    <p id="passwordFormMessage" class="hidden mb-4 rounded-xl border px-3 py-2 text-sm" role="alert"></p>

    <!-- Misma pila que el login: label + input h-11 + ojo w-5 -->
    <div class="flex flex-col gap-6">
      <div class="flex flex-col gap-1.5">
        <label for="newPassword" class="text-sm font-medium text-gray-700">Nueva contraseña</label>
        <div class="relative">
            <input type="password" id="newPassword"
                class="h-11 w-full rounded-lg border border-gray-300 px-4 pr-10 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-600/30 focus:outline-none"
                placeholder="Ingresa tu nueva contraseña"
                autocomplete="new-password">
            <button type="button" onclick="toggleFirstLoginPassword('newPassword', 'newPasswordEyeIcon')" class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none p-0.5" aria-label="Mostrar contraseña">
                <svg id="newPasswordEyeIcon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
            </button>
        </div>
      </div>

      <div class="flex flex-col gap-1.5">
        <label for="confirmNewPassword" class="text-sm font-medium text-gray-700">Confirmar contraseña</label>
        <div class="relative">
            <input type="password" id="confirmNewPassword"
                class="h-11 w-full rounded-lg border border-gray-300 px-4 pr-10 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-600/30 focus:outline-none"
                placeholder="Ingresa la contraseña de nuevo"
                autocomplete="new-password">
            <button type="button" onclick="toggleFirstLoginPassword('confirmNewPassword', 'confirmNewPasswordEyeIcon')" class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none p-0.5" aria-label="Mostrar contraseña">
                <svg id="confirmNewPasswordEyeIcon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
            </button>
        </div>
      </div>
    </div>

    <!-- botón -->
    <button type="button" onclick="cambiarPassword()"
    class="mt-6 w-full bg-gray-200 text-gray-600 py-3.5 rounded-xl text-sm sm:text-base font-semibold flex items-center justify-center gap-2 hover:bg-gray-300 transition-colors">

    <img src="<?= BASE_URL ?>src/assets/img/icons/Security/lock.svg" class="w-5 h-5" alt="">

    Cambiar contraseña
    </button>

    <p class="text-sm text-gray-500 mt-4 text-center cursor-pointer hover:text-gray-800 transition"
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
    if (typeof updateOtpVerifyButtonState === "function") updateOtpVerifyButtonState();
}
</script>
<script>window.LOGIN_FETCH_ROOT = <?= json_encode(BASE_URL) ?>;</script>
<script src="<?= BASE_URL ?>src/assets/js/login.js"></script>

</body>
</html>