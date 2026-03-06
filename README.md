# Proyecto Z

Sistema web para la gestión de horarios, trimestralización, usuarios, grupos, áreas, zonas y contenidos académicos (programas, competencias, RAE). Incluye autenticación, perfil de usuario y flujo de solicitudes.

## Requisitos

- **PHP** 7.4 o superior (recomendado 8.x)
- **MySQL** 5.7 / MariaDB 10.x
- Servidor web (Apache, Nginx) o entorno integrado (XAMPP, WAMP, Laragon, etc.)
- Extensiones PHP: `pdo_mysql`, `json`, `mbstring`

## Instalación

1. Clonar el repositorio:
   ```bash
   git clone <https://github.com/Brandon-alt17/proyecto-z.git> proyecto-z
   cd proyecto-z
   ```

2. Configurar la base de datos:
   - Crear una base de datos MySQL (por ejemplo `proyecto-z`).
   - Copiar o crear el archivo de configuración en `config/database.php` con los datos de conexión:
     - `$host`, `$dbname`, `$user`, `$pass`
   - Importar el esquema SQL.

3. Dependencias (si el proyecto usa Composer):
   ```bash
   composer install
   ```

4. Punto de entrada: configurar el servidor para que el documento raíz sea la carpeta del proyecto y la URL base apunte a `index.php` (por ejemplo `http://localhost/proyecto-z/`).

## Estructura del proyecto

```
proyecto-z/
├── config/
│   └── database.php          # Conexión PDO a MySQL
├── index.php                 # Front controller (rutas por ?page=)
├── src/
│   ├── controllers/          # Lógica de negocio / API (Usuario, Solicitud, Ficha, etc.)
│   ├── models/               # Modelos (Usuario, Solicitud, etc.)
│   ├── views/                # Vistas PHP (login, register_tables, gestionUsuarios, etc.)
│   ├── includes/             # Header/footer (público y privado)
│   ├── assets/
│   │   ├── js/               # Scripts (gestionPerfil, gestionUsuarios, registerTables, etc.)
│   │   ├── css/
│   │   └── img/
│   └── helpers/
├── public/
│   └── css/                  # Estilos compilados (Tailwind: output.css, fonts.css)
├── scripts/                  # Utilidades (ej. desarrollo)
└── vendor/                   # Dependencias Composer (si aplica)
```

## Módulos principales

- **Autenticación**: login por correo y contraseña; sesión y redirección según rol.
- **Horarios / Trimestralización**: registro y visualización de horarios por zona/área (`register_tables`, `TrimestralizacionController`).
- **Usuarios**: listado, filtros, creación y edición (`gestionUsuarios`, `UsuarioController`).
- **Grupos**: gestión de grupos con programas, nivel, jornada, modalidad y líder (`gestionGrupos`).
- **Áreas y Zonas**: mantenimiento de áreas y zonas (`gestionAreas`, `gestionZonas`).
- **Académicos**: carga Excel, programas, competencias y RAE (`academicos`, controladores y modelos asociados).
- **Perfil de usuario**: ver perfil, solicitar cambios de datos y cambiar contraseña (widget en header, `gestionPerfil.js`, `UsuarioController`).
- **Solicitudes**: backend para crear y gestionar solicitudes (por ejemplo tipo DATOS/HORARIO) (`SolicitudController`, `Solicitud`).

## Tecnologías

- **Backend**: PHP (PDO, sesiones).
- **Frontend**: HTML, CSS (Tailwind), JavaScript (vanilla + SweetAlert2).
- **Base de datos**: MySQL/MariaDB.

## Uso

- **Público**: `index.php` (landing), `index.php?page=login` (inicio de sesión).
- **Privado**: tras el login se redirige a la vista por defecto (p. ej. registro de horarios). La navegación se hace desde el menú lateral (Horarios, Áreas, Zonas, Trimestres, Usuarios, Grupos, Académicos, Historial, etc.).

## Notas de seguridad

- No subir `config/database.php` con credenciales reales; usar variables de entorno o un archivo ignorado (por ejemplo `.config/database.php` en `.gitignore`).
- En producción, desactivar `display_errors` y ajustar `error_reporting`.
- Las contraseñas se almacenan con hash (por ejemplo `password_hash`/`password_verify`).

## Licencia

Copyright © 2026 SENLOCK . Uso según términos del proyecto.
