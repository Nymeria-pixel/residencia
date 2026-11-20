<?php
// Validar que se reciba un ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("ID de solicitud inválido o no especificado.");
}

// Requerir conexión
if (!file_exists('conexion.php')) {
    die("Error: No se encuentra el archivo de conexión.");
}
require_once 'conexion.php';

// Validar que la conexión exista y esté activa
if (!isset($conexion) || $conexion->connect_error) {
    die("Error al conectar con la base de datos.");
}

// Obtener los datos de la base de datos usando prepared statements
$sql = "SELECT * FROM solicitudes_residencia WHERE id = ?";
$stmt = $conexion->prepare($sql);
if (!$stmt) {
    die("Error al preparar la consulta de la base de datos.");
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("La solicitud con el ID especificado (#" . $id . ") no fue encontrada.");
}

$solicitud = $result->fetch_assoc();

$stmt->close();
$conexion->close();

// Auxiliar para formatear fecha a español legible
$timestamp = strtotime($solicitud['fecha_registro']);
$meses = [
    1 => 'enero',
    2 => 'febrero',
    3 => 'marzo',
    4 => 'abril',
    5 => 'mayo',
    6 => 'junio',
    7 => 'julio',
    8 => 'agosto',
    9 => 'septiembre',
    10 => 'octubre',
    11 => 'noviembre',
    12 => 'diciembre'
];
$dia = date('d', $timestamp);
$mes = $meses[intval(date('m', $timestamp))];
$anio = date('Y', $timestamp);
$fechaFormateada = "a los {$dia} días del mes de {$mes} de {$anio}";

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Constancia - Residencia #
        <?php echo $id; ?>
    </title>
    <style>
        :root {
            --text-color: #1a1a1a;
            --font-family: 'Times New Roman', Times, serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #f3f4f6;
            color: var(--text-color);
            font-family: var(--font-family);
            line-height: 1.6;
            font-size: 12pt;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .document-page {
            background-color: #ffffff;
            width: 100%;
            max-width: 800px;
            min-height: 1050px;
            /* Alto aproximado tipo carta */
            padding: 80px 70px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            position: relative;
        }

        /* Barra de control flotante en pantalla, oculta en impresión */
        .control-bar {
            width: 100%;
            max-width: 800px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 15px 25px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .btn {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s, color 0.2s;
            border: none;
        }

        .btn-print {
            background-color: #2563eb;
            color: #ffffff;
        }

        .btn-print:hover {
            background-color: #1d4ed8;
        }

        .btn-back {
            background-color: #e5e7eb;
            color: #374151;
        }

        .btn-back:hover {
            background-color: #d1d5db;
        }

        /* Cabecera del documento */
        .document-header {
            text-align: center;
            margin-bottom: 40px;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.4;
        }

        .document-header .subtitle {
            font-weight: normal;
            font-size: 10pt;
        }

        /* Fecha a la derecha */
        .date-location {
            text-align: right;
            margin-bottom: 35px;
            font-size: 11pt;
        }

        /* Destinatario */
        .recipient {
            margin-bottom: 40px;
            font-weight: bold;
        }

        .recipient .salutation {
            font-weight: normal;
            margin-top: 5px;
        }

        /* Asunto */
        .subject {
            text-align: right;
            font-weight: bold;
            margin-bottom: 35px;
            text-transform: uppercase;
            font-size: 11pt;
        }

        /* Cuerpo de la Carta */
        .letter-body {
            text-align: justify;
            text-indent: 1.5cm;
            margin-bottom: 60px;
            font-size: 12pt;
            line-height: 1.8;
        }

        .letter-body p {
            margin-bottom: 20px;
        }

        .highlight {
            font-weight: bold;
        }

        /* Firmas */
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            margin-top: 80px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 80%;
            margin: 0 auto 8px auto;
        }

        .signature-title {
            font-size: 10pt;
            font-weight: bold;
        }

        .signature-detail {
            font-size: 9pt;
            color: #374151;
        }

        /* Estilos de Impresión */
        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
                margin: 0;
            }

            .control-bar {
                display: none;
            }

            .document-page {
                box-shadow: none;
                padding: 0;
                width: 100%;
                max-width: 100%;
                min-height: auto;
            }

            /* Quitar márgenes de página por defecto en el navegador para ajustar */
            @page {
                size: letter;
                margin: 2.54cm;
                /* Margen estándar APA */
            }
        }
    </style>
</head>

<body>

    <!-- Barra de control en navegadores -->
    <div class="control-bar">
        <a href="index.html" class="btn btn-back">⬅ Volver al Formulario</a>
        <button onclick="window.print();" class="btn btn-print">🖨 Imprimir o Guardar PDF</button>
    </div>

    <!-- Página física de la Constancia / Solicitud -->
    <div class="document-page">

        <div class="document-header">
            <p>República Bolivariana de Venezuela</p>
            <p class="subtitle">Estado
                <?php echo htmlspecialchars($solicitud['estado']); ?> — Municipio / Ciudad
                <?php echo htmlspecialchars($solicitud['ciudad']); ?>
            </p>
            <p>Comunidad "Las Garras del León"</p>
        </div>

        <div class="date-location">
            <p>
                <?php echo htmlspecialchars($solicitud['ciudad']); ?>,
                <?php echo $fechaFormateada; ?>
            </p>
        </div>

        <div class="recipient">
            <p>Ciudadanos:</p>
            <p>VOCEROS Y MIEMBROS DEL CONSEJO COMUNAL "LAS GARRAS DEL LEÓN"</p>
            <p class="salutation">Presente.-</p>
        </div>

        <div class="subject">
            <p>Asunto: Solicitud de Constancia de Residencia</p>
        </div>

        <div class="letter-body">
            <p>
                Yo, <span class="highlight">
                    <?php echo htmlspecialchars($solicitud['nombre'] . ' ' . $solicitud['apellido']); ?>
                </span>,
                de nacionalidad venezolana, mayor de edad, civilmente hábil, titular del Documento de Identidad Nº
                <span class="highlight">
                    <?php echo htmlspecialchars($solicitud['documento']); ?>
                </span>, con número telefónico
                <span class="highlight">
                    <?php echo htmlspecialchars($solicitud['telefono']); ?>
                </span> y correo electrónico
                <span class="highlight">
                    <?php echo htmlspecialchars($solicitud['email']); ?>
                </span>, me dirijo respetuosamente ante ustedes con el
                fin de solicitar la emisión formal de mi **Constancia de Residencia**.
            </p>
            <p>
                A tales efectos, hago constar bajo fe de juramento que me encuentro residenciado actualmente en la
                siguiente dirección:
                casa/apartamento <span class="highlight">
                    <?php echo htmlspecialchars($solicitud['vivienda']); ?>
                </span>,
                sector <span class="highlight">
                    <?php echo htmlspecialchars($solicitud['sector']); ?>
                </span>, ubicado en la ciudad de
                <span class="highlight">
                    <?php echo htmlspecialchars($solicitud['ciudad']); ?>
                </span>, Estado
                <span class="highlight">
                    <?php echo htmlspecialchars($solicitud['estado']); ?>
                </span>. En dicha comunidad he habitado
                de manera ininterrumpida y pacífica durante un lapso de <span class="highlight">
                    <?php echo intval($solicitud['tiempo_residencia']); ?> años
                </span>.
            </p>
            <?php if (!empty($solicitud['motivo'])): ?>
                <p>
                    La presente solicitud obedece al siguiente motivo de trámite personal:
                    <em>“
                        <?php echo htmlspecialchars($solicitud['motivo']); ?>”
                    </em>.
                </p>
            <?php endif; ?>
            <p>
                Agradezco de antemano el estudio de esta solicitud, así como la receptividad de sus gestiones en
                beneficio de los habitantes y el fortalecimiento de nuestro sector. Sin otro particular a que hacer
                referencia, me despido atentamente de ustedes.
            </p>
        </div>

        <div class="signatures">
            <div>
                <div style="height: 60px;"></div>
                <div class="signature-line"></div>
                <p class="signature-title">Solicitante</p>
                <p class="signature-detail">
                    <?php echo htmlspecialchars($solicitud['nombre'] . ' ' . $solicitud['apellido']); ?>
                </p>
                <p class="signature-detail">C.I.
                    <?php echo htmlspecialchars($solicitud['documento']); ?>
                </p>
            </div>
            <div>
                <div style="height: 60px;"></div>
                <div class="signature-line"></div>
                <p class="signature-title">Recibido por el Consejo Comunal</p>
                <p class="signature-detail">Las Garras del León</p>
                <p class="signature-detail">Sello y Firma Autorizada</p>
            </div>
        </div>

    </div>

    <!-- Script para disparar automáticamente el diálogo de impresión -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            // Pequena espera para asegurar la carga completa de estilos y fuentes
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>

</body>

</html>