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
                            aria-label="Mostrar contraseña"
                            class="absolute top-1/2 right-3 -translate-y-1/2 w-5 h-5 flex items-center justify-center text-gray-500 hover:text-gray-700 transition focus:outline-none focus:ring-2 focus:ring-green-600/30 rounded">
                            <span id="iconEyeOpen" class="password-toggle-icon" aria-hidden="true">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </span>
                            <span id="iconEyeClosed" class="password-toggle-icon hidden" aria-hidden="true">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878a4.5 4.5 0 106.262 6.262M4.5 4.5l15 15"/></svg>
                            </span>
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
                        class="h-12 w-48 rounded-full bg-dark-navy text-sm font-semibold text-white hover:bg-dark-navy-hover focus:ring-2 focus:ring-green-600/30 focus:outline-none"
                    >
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

<!-- Modal verificación OTP -->
<div id="verificationModal"
     class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50">

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

<!-- Modal cambio de contraseña obligatoria -->
<div id="passwordModal"
     class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50">

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

<script src="./src/assets/js/login.js"></script>

</body>
</html>
