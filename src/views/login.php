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
    </style>
</head>

<body class="bg-white text-gray-800 antialiased h-screen overflow-hidden">

<main class="flex h-full w-full flex-col lg:flex-row overflow-hidden">

    <!-- PANEL IZQUIERDO -->
    <div class="hidden lg:block lg:w-1/2 h-full overflow-hidden">
    <img 
        src="src/assets/img/agenda.png"
        alt="Agenda de Hoy"
        class="w-300rem h-600rem object-cover"
    >
</div>



    <!-- PANEL DERECHO LOGIN -->
    <div class="flex flex-1 items-center justify-center bg-white px-6 py-12 lg:px-16">
        <div class="w-full max-w-sm">

            <h1 class="mb-10 text-center text-4xl font-bold text-green-700">
                Bienvenido
            </h1>

            <form action="procesar_login.php" method="POST" class="flex flex-col gap-6">

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700">Correo Electronico</label>
                    <input
                        name="email"
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
                                onclick="togglePassword()"
                                class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            👁
                        </button>
                    </div>
                </div>

                <div class="flex justify-end">
                    <a href="#"
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

<script src="js/login.js"></script>

</body>
</html>
