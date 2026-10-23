<?php
// Mostramos errores para enterarnos de si falla algo mientras probamos

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Variables iniciales para que no den fallos los inputs al cargar la pagina

$error = "";
$enviado = false;
$nombre = "";
$edad = "";
$tipo = "General";
$talleres = [];
$precioBase = 0;
$precioTotal = 0;

// Funcion para sacar el precio base de la entrada

function obtenerPrecioBase($tipoEntrada) {
    if ($tipoEntrada == "VIP") {
        return 60;
    } elseif ($tipoEntrada == "Infantil") {
        return 10;
    } else {
        return 30; // Si es General o cualquier otra cosa por defecto
    }
}

// Comprobamos si el usuario ha enviado el formulario

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Recogemos los datos que vienen del formulario

        $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''));
        $edad = $_POST['edad'] ?? '';
        $tipo = $_POST['tipo_entrada'] ?? 'General';
        $talleres = $_POST['talleres'] ?? []; // Si no marca nada, se queda vacio

        // Validacion 1: Que el nombre no este vacio
        if (empty($nombre)) {
            throw new Exception("El nombre no puede estar en blanco.");
        }

        // Validacion 2: Que la edad sea un numero valido y mayor que 0

        if (!is_numeric($edad) || intval($edad) <= 0) {
            throw new Exception("Tienes que poner una edad valida.");
        }
        $edad = intval($edad);

        // Validacion 3: Si pide VIP tiene que ser mayor de edad

        if ($tipo == "VIP" && $edad < 18) {
            throw new Exception("Ojo, las entradas VIP solo son para mayores de 18 años.");
        }

        // Calculamos el precio base llamando a la funcion

        $precioBase = obtenerPrecioBase($tipo);
        $precioTotal = $precioBase;

        // Recorremos los talleres con un bucle para sumar el extra

        foreach ($talleres as $taller) {
            if ($taller == "videojuegos") {
                $precioTotal += 10;
            } elseif ($taller == "cosplay") {
                $precioTotal += 15;
            } elseif ($taller == "rol") {
                $precioTotal += 5;
            }
        }

        // Si llega hasta aqui sin saltar al catch, es que todo esta bien

        $enviado = true;

    } catch (Exception $e) {
        // Guardamos el error para pintarlo en rojo en la pantalla

        $error = $e->getMessage();
    } finally {
        // Esto se ejecuta siempre al terminar, dejamos constancia en el log del servidor

        error_log("Peticion procesada en el formulario de la Comic-Con");
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inscripción Comic-Con</title>
    <style>
        /* CSS basico */
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f9f9f9;
        }
        .caja {
            width: 450px;
            background: #ffffff;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #999;
        }
        h1 {
            text-align: center;
            font-size: 22px;
        }
        .campo {
            margin-bottom: 12px;
        }
        label {
            display: block;
            margin-bottom: 4px;
            font-weight: bold;
        }
        input[type="text"], input[type="number"], select {
            width: 100%;
            padding: 6px;
            box-sizing: border-box;
        }
        .error-box {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
        }
        .resguardo-box {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin-top: 15px;
            border: 1px solid #c3e6cb;
        }
        button {
            width: 100%;
            padding: 8px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <div class="caja">
        <h1>Formulario Comic-Con 2026</h1>

        <!-- Si hay error, lo mostramos aqui arriba -->
        <?php if (!empty($error)): ?>
            <div class="error-box">
                <strong>Error:</strong> <?= $error ?>
            </div>
        <?php endif; ?>

        <!-- Si NO se ha enviado con exito, mostramos el formulario -->
        <?php if (!$enviado): ?>
            <form action="comicon.php" method="POST">
                <div class="campo">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" value="<?= $_POST['nombre'] ?? '' ?>">
                </div>

                <div class="campo">
                    <label for="edad">Edad:</label>
                    <input type="number" id="edad" name="edad" value="<?= $_POST['edad'] ?? '' ?>">
                </div>

                <div class="campo">
                    <label for="tipo_entrada">Tipo de entrada:</label>
                    <select id="tipo_entrada" name="tipo_entrada">
                        <option value="General" <?= (isset($_POST['tipo_entrada']) && $_POST['tipo_entrada'] == 'General') ? 'selected' : '' ?>>General - 30€</option>
                        <option value="VIP" <?= (isset($_POST['tipo_entrada']) && $_POST['tipo_entrada'] == 'VIP') ? 'selected' : '' ?>>VIP - 60€ (Solo mayores de edad)</option>
                        <option value="Infantil" <?= (isset($_POST['tipo_entrada']) && $_POST['tipo_entrada'] == 'Infantil') ? 'selected' : '' ?>>Infantil - 10€</option>
                    </select>
                </div>

                <div class="campo">
                    <label>Talleres opcionales:</label>
                    <input type="checkbox" name="talleres[]" value="videojuegos" <?= (isset($_POST['talleres']) && in_array('videojuegos', $_POST['talleres'])) ? 'checked' : '' ?>> Torneo Videojuegos (+10€)<br>
                    <input type="checkbox" name="talleres[]" value="cosplay" <?= (isset($_POST['talleres']) && in_array('cosplay', $_POST['talleres'])) ? 'checked' : '' ?>> Taller Cosplay (+15€)<br>
                    <input type="checkbox" name="talleres[]" value="rol" <?= (isset($_POST['talleres']) && in_array('rol', $_POST['talleres'])) ? 'checked' : '' ?>> Zona Rol (+5€)
                </div>

                <button type="submit">Enviar</button>
            </form>
        <?php endif; ?>

        <!-- Si ya se ha enviado bien, mostramos el resguardo -->
         
        <?php if ($enviado): ?>
            <div class="resguardo-box">
                <h3>¡Inscripción correcta!</h3>
                <p><strong>Nombre:</strong> <?= $nombre ?></p>
                <p><strong>Edad:</strong> <?= $edad ?></p>
                <p><strong>Tipo de entrada:</strong> <?= $tipo ?> (<?= $precioBase ?>€)</p>
                
                <p><strong>Talleres elegidos:</strong></p>
                <?php if (empty($talleres)): ?>
                    <p><i>Ninguno</i></p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($talleres as $t) {
                            if ($t == 'videojuegos') echo "<li>Torneo Videojuegos (10€)</li>";
                            elseif ($t == 'cosplay') echo "<li>Taller Cosplay (15€)</li>";
                            elseif ($t == 'rol') echo "<li>Zona Rol (5€)</li>";
                        } ?>
                    </ul>
                <?php endif; ?>

                <hr>
                <p><strong>Total a pagar: <?= $precioTotal ?> €</strong></p>
                
                <p style="text-align: center; margin-top: 15px;">
                    <a href="comicon.php">Volver al formulario</a>
                </p>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>