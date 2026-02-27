<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que tenga token en sesión
if (!isset($_SESSION['reset_token']) || !isset($_SESSION['reset_usuario_id'])) {
    header("Location: index.php?page=restablecerContrasenia");
    exit;
}

$error = $_SESSION['error_password'] ?? '';
unset($_SESSION['error_password']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - SENLOCK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .alert-transition {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-gray-50">
    
    <div class="w-full max-w-md">
        <!-- Tarjeta de cambio de contraseña -->
        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl p-8">
            <div class="mb-8">
                <h1 class="text-3xl text-center font-bold text-[#39A900] mb-6">Nueva Contraseña</h1>
                <p class="text-gray-600 mb-8">
                    Establece una nueva contraseña para tu cuenta. Asegúrate de que sea segura y fácil de recordar.
                </p>
            </div>

            <!-- Mensajes de error -->
            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg alert-transition">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Formulario -->
            <form method="POST" action="../controllers/RecuperarContrasenia.php?accion=cambiar" class="space-y-6">
                <!-- Nueva contraseña -->
                <div>
                    <label for="password" class="block text-sm font-bold text-black mb-2">
                        Nueva Contraseña
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="w-full px-4 py-3 rounded-2xl border border-gray-400 focus:ring-2 focus:ring-[#39A900] focus:border-[#39A900] transition-colors"
                        placeholder="••••••••"
                        minlength="8"
                        required
                    >
                    <p class="mt-2 text-sm text-gray-500">
                        Mínimo 8 caracteres
                    </p>
                </div>

                <!-- Confirmar contraseña -->
                <div>
                    <label for="confirmar_password" class="block text-sm font-bold text-black mb-2">
                        Confirmar Contraseña
                    </label>
                    <input 
                        type="password" 
                        id="confirmar_password" 
                        name="confirmar_password" 
                        class="w-full px-4 py-3 rounded-2xl border border-gray-400 focus:ring-2 focus:ring-[#39A900] focus:border-[#39A900] transition-colors"
                        placeholder="••••••••"
                        minlength="8"
                        required
                    >
                </div>

                <!-- Requisitos de contraseña -->
                <div class="bg-gray-50 p-4 rounded-xl">
                    <p class="text-xs font-semibold text-gray-700 mb-2">Tu contraseña debe tener:</p>
                    <ul class="text-xs text-gray-600 space-y-1">
                        <li class="flex items-center">
                            <svg class="h-4 w-4 text-[#39A900] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Mínimo 8 caracteres
                        </li>
                        <li class="flex items-center">
                            <svg class="h-4 w-4 text-[#39A900] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Al menos una mayúscula (recomendado)
                        </li>
                        <li class="flex items-center">
                            <svg class="h-4 w-4 text-[#39A900] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Al menos un número (recomendado)
                        </li>
                    </ul>
                </div>

                <!-- Botones de acción -->
                <div class="flex gap-6 pt-4 justify-center">
                    <button 
                        type="button" 
                        onclick="window.location.href='index.php?page=restablecerContrasenia'"
                        class="px-6 py-2.5 text-sm border-2 border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 hover:border-gray-400 transition-all duration-200"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="submit" 
                        class="px-6 py-2.5 text-sm bg-[#007831] text-white font-medium rounded-lg hover:bg-[#39A900] hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 focus:ring-4 focus:ring-[#39A900] focus:ring-opacity-50"
                    >
                        Cambiar Contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script para validar que las contraseñas coincidan en tiempo real -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const password = document.getElementById('password');
            const confirmar = document.getElementById('confirmar_password');
            
            function validateMatch() {
                if (confirmar.value.length > 0) {
                    if (password.value !== confirmar.value) {
                        confirmar.classList.add('border-red-500', 'focus:ring-red-500');
                        confirmar.classList.remove('border-gray-400', 'focus:ring-[#39A900]');
                    } else {
                        confirmar.classList.remove('border-red-500', 'focus:ring-red-500');
                        confirmar.classList.add('border-gray-400', 'focus:ring-[#39A900]');
                    }
                }
            }
            
            password.addEventListener('keyup', validateMatch);
            confirmar.addEventListener('keyup', validateMatch);
        });
    </script>
</body>
</html>