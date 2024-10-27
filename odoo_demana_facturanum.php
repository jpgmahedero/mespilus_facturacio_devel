<?php
// En pagina_intermedia.php

// Asegúrate de que solo procesas los datos si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Guarda los datos de $_POST en la sesión para usarlos después
    session_start();
    $_SESSION['datos_previos'] = $_POST;

    // Muestra un nuevo formulario solicitando el número de factura
    echo '<form action="template_odoo_resultats.php" method="post">';
    echo '<label for="numero_factura">Número de factura:</label>';
    echo '<input type="text" id="numero_factura" name="numero_factura" required>';
    echo '<input type="submit" value="Enviar">';
    echo '</form>';
} else {
    // Redirige o muestra un error si se accede a la página sin datos POST
    echo 'Acceso incorrecto.';
}
?>
