<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener mensajes de sesión
$error = $_SESSION['error_recuperacion'] ?? '';
$success = $_SESSION['success_recuperacion'] ?? '';

// Limpiar mensajes de sesión después de obtenerlos
unset($_SESSION['error_recuperacion'], $_SESSION['success_recuperacion']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - SENLOCK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Estilos adicionales para animaciones */
        .alert-transition {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-gray-50">
    
    <div class="w-full max-w-md">
        <!-- Tarjeta de restablecimiento -->
        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl p-8">
            <div class="mb-8">
                <h1 class="text-3xl text-center font-bold text-[#39A900] mb-6">Restablecer Contraseña</h1>
                <p class="text-gray-600 mb-8">
                    No te preocupes, a todos nos pasa. Ingresa el correo electrónico asociado a tu cuenta 
                    y te enviaremos un enlace para que puedas crear una nueva.
                </p>
            </div>

            <!-- Mensajes de alerta -->
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

            <?php if ($success): ?>
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg alert-transition">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700"><?= htmlspecialchars($success) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Formulario -->
            <form method="POST" action="../controllers/RecuperarContrasenia.php?accion=solicitar" class="space-y-6">
                <!-- Campo de correo electrónico -->
                <div>
                    <label for="correo" class="block text-sm font-bold text-black mb-2">
                        Correo electrónico
                    </label>
                    <input 
                        type="email" 
                        id="correo" 
                        name="correo" 
                        class="w-full px-4 py-3 rounded-2xl border border-gray-400 focus:ring-2 focus:ring-[#39A900] focus:border-[#39A900] transition-colors"
                        placeholder="correo_ejemplo@gmail.com"
                        value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                        required
                    >
                    <p class="mt-2 text-sm text-gray-500">
                        Recibirás un enlace para restablecer tu contraseña
                    </p>
                </div>

                <!-- Botones de acción -->
                <div class="flex gap-6 pt-4 justify-center">
                    <button 
                        type="button" 
                        onclick="window.location.href='index.php?page=login'"
                        class="px-6 py-2.5 text-sm border-2 border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 hover:border-gray-400 transition-all duration-200"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="submit" 
                        class="px-6 py-2.5 text-sm bg-[#007831] text-white font-medium rounded-lg hover:bg-[#39A900] hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 focus:ring-4 focus:ring-[#39A900] focus:ring-opacity-50"
                    >
                        Enviar Enlace
                    </button>
                </div>
            </form>
        </div>

        <!-- Mensaje de seguridad -->
        <p class="text-center text-xs text-gray-500 mt-6">
            Por seguridad, el enlace expirará después de 30 minutos.
        </p>
    </div>

    <!-- Script para auto-ocultar mensajes después de 5 segundos -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert-transition');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>