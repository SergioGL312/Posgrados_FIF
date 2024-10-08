<?php
  include('../Header/MenuD.php');

// Verificar si ya hay una sesión activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir el archivo de conexión
include '../../conexion.php';

// Conectar a la base de datos
$Con = Conectar();

$clave_coordinador = $_SESSION['id'];

$SQL = "
    SELECT DISTINCT
        a.exp_alumno,
        e.nombre,
        e.a_paterno,
        e.a_materno,
        ev.aula,
        ev.fecha_evaluacion
    FROM 
        asignaciones a
    LEFT JOIN 
        estudiantes e ON a.exp_alumno = e.exp
    LEFT JOIN 
        evaluaciones ev ON a.exp_alumno = ev.exp_alumno
    LEFT JOIN 
        detalle_evaluaciones de ON (
            ev.id = de.id_evaluacion AND
            de.id_sinodo = $clave_coordinador
        )
    WHERE 
        (a.sinodo1 = $clave_coordinador OR a.sinodo2 = $clave_coordinador OR 
        a.sinodo3 = $clave_coordinador OR a.externo = $clave_coordinador)
        AND (de.id_evaluacion IS NULL OR ((de.calificacion IS NULL OR de.calificacion = 0) AND de.observacion IS NULL))
";
$Resultado = Ejecutar($Con, $SQL);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Header/styles.css">  
    <title>Evaluaciones Pendientes</title>
</head>

<style>
    body {
        margin: 0;
        padding: 0;
    }

    :root {
        --primary-color: rgb(26,115,232);
        --secondary-color: #aaa;
        --text-color: #3c4043;
        --background-color: #fafcff;
    }

    table {
        table-layout: fixed;
        border-collapse: collapse;
        margin-bottom: 5rem;
        width: 100%;
        max-width: 80%;
    }

    tr {
        border-top: 0.1rem solid var(--primary-color);
        border-bottom: 0.1rem solid var(--secondary-color);
    }

    th, td {
        border-bottom: 0.0625rem solid var(--secondary-color);
        padding: 1.25rem;
    }

    td {
        display: table-cell;
        text-align: center;
        font-family: "Google Sans", Roboto, Arial, sans-serif;
        font-size: 1.1rem;
        font-weight: 500;
        color: var(--text-color);
    }

    th {
        letter-spacing: .01785714em;
        font-family: system-ui;
        font-weight: 600;
        font-size: 1.5rem;
        color: var(--text-color);
        padding-bottom: 2rem;
        padding-top: 3.5rem;
    }

    h1 {
        font-family: "Google Sans", Roboto, Arial, sans-serif;
        text-align: center;
    }

    h3 {
        font-size: 2rem;
        font-family: "Google Sans", Roboto, Arial, sans-serif;
    }

    #title-container {
        display: flex;
        justify-content: space-between;
        width: 100%;
        align-items: center;
    }

    #table-container {
        display: flex !important;
        justify-content: center;
        overflow-x: auto;
    }

    .container-evaluaciones-pendientes {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 85vh;
        padding: 1rem;
    }

    .confirmar-icon {
        color: #123773;
        margin: auto;
        font-size: 1.5rem;
        padding: 0.5rem 0.9rem;
        background-color: #e0e0e0;
        border: none;
        cursor: pointer;
        border-radius: 0.4rem;
    }

    
    @media screen and (max-width: 1600px) {

        .container-agendar-evaluacion {
            height: 75vh;
        }

    }

    @media (max-width: 770px) {
        table {
            font-size: 0.9rem;
        }

        th, td {
            font-size: 1.1rem;
            padding: 0.75rem;
        }

        h3 {
            font-size: 1.5rem;
        }

        button {
            height: 2.5rem;
            font-size: 0.9rem;
        }
    }
</style>

<body>

<div class="container-evaluaciones-pendientes">
<h3>Evaluaciones pendientes:</h3>
    <div id="table-container">
        <table>
            <thead>
                <tr>
                    <th>Expediente</th>
                    <th>Nombre</th>
                    <th>Fecha</th>
                    <th>Aula</th>
                    <th>Calificación</th>
                    <th>Observaciones</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
            <?php
                if ($Resultado->num_rows > 0){
                    while ($Fila = $Resultado->fetch_assoc()){
                        $Nombre = $Fila["nombre"] . " " . $Fila["a_paterno"] . " " . $Fila["a_materno"];
                        echo "<tr data-expediente='" . $Fila['exp_alumno'] . "'>";
                        echo "<td>" . $Fila ["exp_alumno"] . "</td>";
                        echo "<td>" . $Nombre . "</td>";
                        echo "<td>" . (!empty($Fila["fecha_evaluacion"]) ? $Fila["fecha_evaluacion"] : "Pendiente") . "</td>";
                        echo "<td>" . (!empty($Fila["aula"]) ? $Fila["aula"] : "Pendiente") . "</td>";
                        
                        echo "<td>";
                        echo "<input type='number' name='calificacion_" . $Fila['exp_alumno'] . "' id='calificacion_" . $Fila['exp_alumno'] . "' step='0.01' min='0' max='10' placeholder='Calificación' required onchange='checkFields(\"" . $Fila['exp_alumno'] . "\")'>";
                        echo "</td>";
                    
                        echo "<td>";
                        echo "<textarea style='resize: none;' name='observacion_" . $Fila['exp_alumno'] . "' id='observacion_" . $Fila['exp_alumno'] . "' placeholder='Escribe observaciones aquí' rows='2' onchange='checkFields(\"" . $Fila['exp_alumno'] . "\")'></textarea>";
                        echo "</td>";
                        echo "<td><button class='confirmar-icon' id='btn_" . $Fila['exp_alumno'] . "' onclick='actualizarEvaluacion(\"" . $Fila['exp_alumno'] . "\")' disabled>&#x2714;</button></td>";
                    
                        echo "</tr>";
                    }
                    
                } else {
                    echo "<tr><td colspan='7'>No se encontraron evaluaiones pendientes</td></tr>";
                }
                Cerrar($Con);
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function checkFields(expediente) {
    const calificacion = document.getElementById('calificacion_' + expediente).value;
    const observacion = document.getElementById('observacion_' + expediente).value;
    const btn = document.getElementById('btn_' + expediente);

    // Obtener la fila correspondiente al expediente
    const fila = document.querySelector(`tr[data-expediente="${expediente}"]`);

    // Obtener el contenido de las celdas de fecha y aula
    const fecha = fila.querySelector('td:nth-child(3)').innerText;
    const aula = fila.querySelector('td:nth-child(4)').innerText;

    // Verificar que todos los campos estén llenos y que la fecha y aula no sean "Pendiente"
    if (calificacion && observacion && fecha !== "Pendiente" && aula !== "Pendiente") {
        btn.disabled = false;
    } else {
        btn.disabled = true;
    }
}



function actualizarEvaluacion(expediente) {
    const calificacion = document.getElementById('calificacion_' + expediente).value;
    const observacion = document.getElementById('observacion_' + expediente).value;

    // Crear una nueva instancia de XMLHttpRequest
    const xhr = new XMLHttpRequest();

    // Configurar la solicitud
    xhr.open('POST', 'actualizar_detalle_evaluaciones.php', true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    // Función a ejecutar cuando la solicitud cambie de estado
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var fila = document.querySelector(`tr[data-expediente="${expediente}"]`);
            console.log(fila);  // Para verificar si la fila fue seleccionada correctamente
            if (fila) {
                fila.remove();
                alert('Evaluación actualizada exitosamente');
            } else {
                console.error('No se encontró la fila para el expediente: ' + expediente);
            }
        }
    };


    // Enviar los datos a actualizar
    xhr.send("expediente=" + expediente + "&calificacion=" + calificacion + "&observacion=" + encodeURIComponent(observacion));

    // Configurar manejo de errores
    xhr.onerror = function() {
        console.error('Error de red');
        alert('Ocurrió un error al actualizar la evaluación');
    };
}

</script>

</body>

</html>