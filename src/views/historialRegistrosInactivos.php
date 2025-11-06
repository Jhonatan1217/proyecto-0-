<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horarios Inactivos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Updated header to match green style from reference image */
        .header {
            background: white;
            padding: 1.5rem 2rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 1.75rem;
            font-weight: 600;
            color: #39b54a;
            margin: 0;
        }

        .header p {
            color: #666;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }

        /* Changed from table to card-based layout */
        .schedules-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .schedule-card {
            background: white;
            border-radius: 8px;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e0e0e0;
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .schedule-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .card-id {
            font-size: 0.85rem;
            color: #666;
            font-weight: 500;
        }

        .card-day {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-left: 1rem;
        }

        .card-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .info-label {
            font-size: 0.75rem;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        .info-value {
            font-size: 0.9rem;
            color: #333;
            font-weight: 500;
        }

        .card-footer {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid #f0f0f0;
        }

        /* Updated badge styles to match reference image */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.75rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge.inactive {
            background-color: #e8e8e8;
            color: #666;
        }

        .badge.active {
            background-color: #d4edda;
            color: #28a745;
        }

        .badge.id-badge {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .header h1 {
                font-size: 1.5rem;
            }

            .card-content {
                grid-template-columns: 1fr;
            }

            .card-header {
                flex-direction: column;
                gap: 0.5rem;
            }

            .card-day {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Horarios Inactivos</h1>
            <p>Visualice y edite los horarios inactivos del sistema</p>
        </div>
        
        <!-- Replaced table with card-based layout -->
        <div class="schedules-list">
            <div class="schedule-card">
                <div class="card-header">
                    <div style="display: flex; align-items: center;">
                        <span class="card-id">1001</span>
                        <span class="card-day">Lunes</span>
                    </div>
                </div>
                <div class="card-content">
                    <div class="info-item">
                        <span class="info-label">Hora Inicio y Fin</span>
                        <span class="info-value">08:00 - 10:00</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">ID Zona</span>
                        <span class="info-value">Z-01</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Area</span>
                        <span class="info-value">Area de ejemplo</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Ficha</span>
                        <span class="info-value">Ficha 308485</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Instructor</span>
                        <span class="info-value">Juan Manuel Gonzales Torres</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Competencia</span>
                        <span class="info-value">Desarrollo de ejemplo</span>
                    </div>
                </div>
                <div class="card-footer">
                    <span class="badge id-badge">Trimestre 1</span>
                    <span class="badge id-badge">RAE-001</span>
                    <span class="badge id-badge">Programa de ejemplo</span>
                </div>
            </div>

            <div class="schedule-card">
                <div class="card-header">
                    <div style="display: flex; align-items: center;">
                        <span class="card-id">1002</span>
                        <span class="card-day">Martes</span>
                    </div>
                </div>
                <div class="card-content">
                    <div class="info-item">
                        <span class="info-label">Horario</span>
                        <span class="info-value">10:00 - 12:00</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">ID Zona</span>
                        <span class="info-value">Z-02</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Area</span>
                        <span class="info-value">Area de ejemplo</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Ficha</span>
                        <span class="info-value">Ficha 308485</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Instructor</span>
                        <span class="info-value">Juan Manuel Gonzales Torres</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Competencia</span>
                        <span class="info-value">Desarrollo de ejemplo</span>
                    </div>
                </div>
                <div class="card-footer">
                    <span class="badge id-badge">Trimestre 2</span>
                    <span class="badge id-badge">RAE-002</span>
                    <span class="badge id-badge">Programa de ejemplo</span>
                </div>
            </div>

            <div class="schedule-card">
                <div class="card-header">
                    <div style="display: flex; align-items: center;">
                        <span class="card-id">1003</span>
                        <span class="card-day">Miércoles</span>
                    </div>
                </div>
                <div class="card-content">
                    <div class="info-item">
                        <span class="info-label">Horario</span>
                        <span class="info-value">14:00 - 16:00</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">ID Zona</span>
                        <span class="info-value">Z-01</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Area</span>
                        <span class="info-value">Area de ejemplo</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Ficha</span>
                        <span class="info-value">Ficha 308485</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Instructor</span>
                        <span class="info-value">Juan Manuel Gonzales Torres</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Competencia</span>
                        <span class="info-value">Desarrollo de ejemplo</span>
                    </div>
                </div>
                <div class="card-footer">
                    <span class="badge id-badge">Trimestre 1</span>
                    <span class="badge id-badge">RAE-003</span>
                    <span class="badge id-badge">Programa de ejemplo</span>
                </div>
            </div>

            <div class="schedule-card">
                <div class="card-header">
                    <div style="display: flex; align-items: center;">
                        <span class="card-id">1004</span>
                        <span class="card-day">Jueves</span>
                    </div>
                </div>
                <div class="card-content">
                    <div class="info-item">
                        <span class="info-label">Horario</span>
                        <span class="info-value">08:00 - 10:00</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">ID Zona</span>
                        <span class="info-value">Z-03</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Area</span>
                        <span class="info-value">Area de ejemplo</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Ficha</span>
                        <span class="info-value">Ficha 308485</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Instructor</span>
                        <span class="info-value">Juan Manuel Gonzales Torres</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Competencia</span>
                        <span class="info-value">Desarrollo de ejemplo</span>
                    </div>
                </div>
                <div class="card-footer">
                    <span class="badge id-badge">Trimestre 3</span>
                    <span class="badge id-badge">RAE-004</span>
                    <span class="badge id-badge">Programa de ejemplo</span>
                </div>
            </div>

            <div class="schedule-card">
                <div class="card-header">
                    <div style="display: flex; align-items: center;">
                        <span class="card-id">1005</span>
                        <span class="card-day">Viernes</span>
                    </div>
                </div>
                <div class="card-content">
                    <div class="info-item">
                        <span class="info-label">Horario</span>
                        <span class="info-value">16:00 - 18:00</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">ID Zona</span>
                        <span class="info-value">Z-02</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Area</span>
                        <span class="info-value">Area de ejemplo</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Ficha</span>
                        <span class="info-value">Ficha 308485</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Instructor</span>
                        <span class="info-value">Juan Manuel Gonzales Torres</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Competencia</span>
                        <span class="info-value">Desarrollo de ejemplo</span>
                    </div>
                </div>
                <div class="card-footer">
                    <span class="badge id-badge">Trimestre 2</span>
                    <span class="badge id-badge">RAE-005</span>
                    <span class="badge id-badge">Programa de ejemplo</span>
                </div>
            </div>

            <div class="schedule-card">
                <div class="card-header">
                    <div style="display: flex; align-items: center;">
                        <span class="card-id">1006</span>
                        <span class="card-day">Lunes</span>
                    </div>
                </div>
                <div class="card-content">
                    <div class="info-item">
                        <span class="info-label">Horario</span>
                        <span class="info-value">12:00 - 14:00</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">ID Zona</span>
                        <span class="info-value">Z-01</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Area</span>
                        <span class="info-value">Area de ejemplo</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Ficha</span>
                        <span class="info-value">Ficha 308485</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Instructor</span>
                        <span class="info-value">Juan Manuel Gonzales Torres</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Competencia</span>
                        <span class="info-value">Desarrollo de ejemplo</span>
                    </div>
                </div>
                <div class="card-footer">
                    <span class="badge id-badge">Trimestre 1</span>
                    <span class="badge id-badge">RAE-006</span>
                    <span class="badge id-badge">Programa de ejemplo</span>
                </div>
            </div>

            <div class="schedule-card">
                <div class="card-header">
                    <div style="display: flex; align-items: center;">
                        <span class="card-id">1007</span>
                        <span class="card-day">Martes</span>
                    </div>
                </div>
                <div class="card-content">
                    <div class="info-item">
                        <span class="info-label">Horario</span>
                        <span class="info-value">14:00 - 16:00</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">ID Zona</span>
                        <span class="info-value">Z-03</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Area</span>
                        <span class="info-value">Area de ejemplo</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Ficha</span>
                        <span class="info-value">Ficha 308485</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Instructor</span>
                        <span class="info-value">Juan Manuel Gonzales Torres</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Competencia</span>
                        <span class="info-value">Desarrollo de ejemplo</span>
                    </div>
                </div>
                <div class="card-footer">
                    <span class="badge id-badge">Trimestre 3</span>
                    <span class="badge id-badge">RAE-007</span>
                    <span class="badge id-badge">Programa de ejemplo</span>
                </div>
            </div>

            <div class="schedule-card">
                <div class="card-header">
                    <div style="display: flex; align-items: center;">
                        <span class="card-id">1008</span>
                        <span class="card-day">Miércoles</span>
                    </div>
                </div>
                <div class="card-content">
                    <div class="info-item">
                        <span class="info-label">Horario</span>
                        <span class="info-value">08:00 - 10:00</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">ID Zona</span>
                        <span class="info-value">Z-02</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Area</span>
                        <span class="info-value">Area de ejemplo</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Ficha</span>
                        <span class="info-value">Ficha 308485</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Instructor</span>
                        <span class="info-value">Juan Manuel Gonzales Torres</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Competencia</span>
                        <span class="info-value">Desarrollo de ejemplo</span>
                    </div>
                </div>
                <div class="card-footer">
                    <span class="badge id-badge">Trimestre 2</span>
                    <span class="badge id-badge">RAE-008</span>
                    <span class="badge id-badge">Programa de ejemplo</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>