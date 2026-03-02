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
                </div>

                <!-- Botones de acción -->
                <div class="flex gap-6 pt-4 justify-center">
                    <button 
                        type="button" 
                        onclick="window.location.href='login.php';"
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
    </div>
</body>
</html>