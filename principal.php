<?php

function get_connexio()
{
    $servername = "localhost";
    $username = "u370516707_aixada";
    $password = "B%Z>V8]F3NgDMy9";
    $dbname = "u370516707_aixada";
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
function consulta_general()
{
    $conn = get_connexio();
    $sql = "SELECT c.id as c_id, uf.name, uf.id as uf_id, c.date_for_shop, c.ts_validated,
                SUM(ROUND((si.quantity * si.unit_price_stamp), 2)) as total
            FROM  aixada_shop_item as si, aixada_cart as c, aixada_uf as uf
            WHERE
                c.uf_id = uf.id AND
                c.id = si.cart_id AND
                c.date_for_shop >= CONCAT(YEAR(CURRENT_DATE()), '-01-01')  -- Filtro para obtener datos desde el 1 de enero del año actual
            GROUP BY c.id
            ORDER BY c.id ASC";  // Ordenar por cart_id (c.id)

    $sql_result = $conn->query($sql);

    $results = array();
    $iva_types = get_iva_types();  // Obtener los tipos de IVA desde la función
    if ($sql_result->num_rows > 0) {
        while ($row = $sql_result->fetch_assoc()) {
            $result = array(
                'c_id' => $row['c_id'],
                'uf_name' => $row['name'],
                'uf_id' => $row['uf_id'],
                'c_date_for_shop' => $row['date_for_shop'],
                'c_ts_validated' => ($row['ts_validated'] == '0000-00-00 00:00:00') ? '<div style="background-color:red">NO</div>' : 'SI',
                'total' => $row['total'],
                'iva' => array()  // Crear el array vacío para los tipos de IVA
            );

            // Inicializar cada tipo de IVA con su porcentaje correspondiente
            foreach ($iva_types as $iva) {
                $result['iva'][$iva . '%'] = $iva;
            }

            array_push($results, $result); // Agregar el resultado al array de resultados
            /*
             $count++; // Incrementar el contador
            if ($count >= 50) { // Condición para limitar el procesamiento a las primeras 3 filas

                break;
            }
            */

        }
    }
    $conn->close();
    return $results;
}







/* Obtenir les dades de la comanda:

Detall compra(Cart id)
NUM SOCI:

Total:
IVA x
IVA y
IVA z
...
IVA 21
Sense IVA
*/
function get_comanda_resumida($cart_id)
{
    // prepatració del resultat
    $result = array();
    $result['cart_id'] = $cart_id;
    $result['con_iva'] = array(); // s'aniran afegint els camps corresponents  al IVA
    $result['base_iva'] = array(); // s'aniran afegint els camps corresponents  al IVA
    $result['iva'] = array(); // s'aniran afegint els camps corresponents  al IVA



    $conn = get_connexio();

    $consulta_uf = "SELECT c.id, u.id, u.name as uf_id, member.name as soci, member.nif, c.date_for_shop
                    FROM aixada_cart AS c, aixada_uf AS u, aixada_member as member
                    WHERE c.id = $cart_id
                      AND c.uf_id = u.id
                      AND u.id = member.uf_id
                      AND member.active=1";


    $result_uf = mysqli_query($conn, $consulta_uf);

    if ($result_uf) {
        $row = mysqli_fetch_row($result_uf);
        $result['soci'] = str_replace(array('Soci ', 'Cistella '), array('S', 'C'), $row[2]);
        $result['member'] = $row[3];
        $result['nif'] = $row[4];
        $result['date_for_shop'] = $row[5];
    }

    $iva_types = get_iva_types();
    $sql_template = "SELECT cart_id, iva_percent, SUM(ROUND((quantity * unit_price_stamp), 2)), SUM(quantity) FROM aixada_shop_item WHERE cart_id = $cart_id GROUP BY cart_id, iva_percent HAVING cart_id = $cart_id AND iva_percent = %f ORDER BY iva_percent ASC";

    foreach ($iva_types as $iva_type) {

        $sql = sprintf($sql_template, floatval($iva_type));
        $result['con_iva'][$iva_type] = 0;
        $result['base_iva'][$iva_type] = 0;
        $result['iva'][$iva_type] = 0;
        $mysqli_result = mysqli_query($conn, $sql);



    if ($mysqli_result && mysqli_num_rows($mysqli_result) > 0) {

        $row = mysqli_fetch_row($mysqli_result);
        $result['con_iva'][$iva_type] = $row[2];
        $result['base_iva'][$iva_type] = round($result['con_iva'][$iva_type]/(1 + $iva_type/100), 2);

        $result['iva'][$iva_type] = floatval($result['con_iva'][$iva_type]) - floatval($result['base_iva'][$iva_type]);

    }
}

    // Calcul del total
    // CONGTORLAT
    //$result['total'] = ['0%'] + $result['con_iva']['4%'] + $result['con_iva']['5%'] + $result['con_iva']['10%'] + $result['con_iva']['21%'];
    $result['total'] = 0;
    foreach ($iva_types as $iva_type){
        $result['total'] += $result['con_iva'][$iva_type];

    }
    mysqli_close($conn);
    return $result;
}


/*  A partir d'un cartd_id retorna els detalls d'aquesta compra

+-------+-------------------+-------+---------------+---------------------+----------------------+----------+----------+------+-------+
| c_id  | soci              | uf_id | date_for_shop | ts_validated        | product_name         | quantity | price    | IVA  | total |
+-------+-------------------+-------+---------------+---------------------+----------------------+----------+----------+------+-------+
| 19153 | Soci 5/Cistella 2 |     2 | 2024-01-03    | 0000-00-00 00:00:00 | Llimona - kg         |   0.2800 | 2.350000 | 0.00 |  0.66 |
| 19153 | Soci 5/Cistella 2 |     2 | 2024-01-03    | 0000-00-00 00:00:00 | Enciam roure unitat  |   1.0000 | 1.100000 | 0.00 |  1.10 |
| 19153 | Soci 5/Cistella 2 |     2 | 2024-01-03    | 0000-00-00 00:00:00 | Espinac - manat      |   1.0000 | 2.200000 | 0.00 |  2.20 |
...
...

*/

function get_comanda_sencera($cart_id)
{
    $conn = get_connexio();
    $sql = " SELECT c.id as c_id, uf.name as soci, uf.id as uf_id, provider.name as provider, c.date_for_shop, c.ts_validated, p.name as product_name,
            si.quantity as quantity, si.unit_price_stamp as price, si.iva_percent as IVA,
            ROUND ((si.quantity * si.unit_price_stamp), 2) as total

              FROM  aixada_shop_item as si, aixada_cart as c, aixada_uf as uf, aixada_product as p, aixada_provider as provider
             WHERE


                 c.id = $cart_id AND
                 c.uf_id  = uf.id AND
                 c.id = si.cart_id AND
                 si.product_id = p.id AND
                 p.provider_id = provider.id

             ORDER BY c.date_for_shop DESC
             LIMIT 400;";
    $sql_result = $conn->query($sql);
    // AND                 c.date_for_shop > DATE_SUB(CURRENT_DATE(), INTERVAL 3 MONTH)

        //print_r($sql);
    $results = array();
    $count = 0;
    if ($sql_result->num_rows > 0) {
        while ($row = $sql_result->fetch_assoc()) {
            $result = array(
                'c_id' => $row['c_id'],
                'uf_name' => $row['soci'],
                'uf_id' => $row['uf_id'],
                'provider' => $row['provider'],
                'c_date_for_shop' => $row['date_for_shop'],
                'c_ts_validated' => ($row['ts_validated'] == '0000-00-00 00:00:00') ? '<div style="background-color:red">NO</div>' : 'SI',
                'product_name' => $row['product_name'],
                'quantity' => $row['quantity'],
                'unit_price' => $row['price'],
                'IVA' => $row['IVA'],
                'total' => $row['total']
            );
            array_push($results, $result); // Append $result to $results

        }
        return $results;
    }


}

// Custom sorting function AS in old facturacio with python
// this ordering is done with alphanumeric sorting example
// S111/C3  S146/C13    S27/C27     S5/C2
function ordre_alphanumeric($a, $b)
{
    echo($a['soci'] . '<br>');
    return strcmp($a['soci'], $b['soci']);
}


// Custom sorting function AS in old facturacio with python
// this ordering is done with NUMERIC SORTING after the 'S'
// S5/C2 S27/C27 S111/C3 S146/C13
function ordre_numeric_de_soci($a, $b)
{
    $aNumericPart = substr($a['soci'], 1);
    $bNumericPart = substr($b['soci'], 1);

    return intval($aNumericPart) - intval($bNumericPart);
}



function findSociIndex($array, $soci) {
    foreach ($array as $index => $element) {
        if (isset($element['soci']) && $element['soci'] === $soci) {
            return $index;
        }
    }
    return -1;
}
function agrupar_factures_per_soci($factures)
{
    $factures_agrupades = array();
   /* $factura_agrupada = [
        'soci' => 'marta y jose',
        'total' => 1,  // Inicializamos el total en 0
        'base_iva' =>  Array ( '0.00' => 55.59, '4.00' => 120, 5.00 => 0, 10.00 => 5.03, 21.00=> 0 ) ,
        'con_iva' => 3,
        'cart_id' => 4
    ];
*/

    foreach ($factures as $factura) {

        $soci = $factura['soci'];

        $iva_keys = get_iva_types();
        $index_soci = findSociIndex($factures_agrupades, $soci);

            if ($index_soci === -1) {
                $factura_agrupada = [
                    'soci' => $factura['soci'],
                    'total' => $factura['total'],
                    'cart_id' => $factura['cart_id']
                ];

                foreach ($iva_keys as $iva) {
                    // Inicializar si no están inicializados
                    if (!isset($factura_agrupada['con_iva'][$iva])) {
                        $factura_agrupada['con_iva'][$iva] = 0;
                    }
                    if (!isset($factura_agrupada['base_iva'][$iva])) {
                        $factura_agrupada['base_iva'][$iva] = 0;
                    }

                    // Actualizar valores
                    $factura_agrupada['con_iva'][$iva] += $factura['con_iva'][$iva];
                    $factura_agrupada['base_iva'][$iva] += $factura['base_iva'][$iva];
                }

                array_push($factures_agrupades, $factura_agrupada);

            }else{

                $factures_agrupades[$index_soci]['total'] += $factura['total'];
                foreach ($iva_keys as $iva) {

                    $factures_agrupades[$index_soci]['con_iva'][$iva] += $factura['con_iva'][$iva];
                    $factures_agrupades[$index_soci]['base_iva'][$iva] += $factura['base_iva'][$iva];
                }

                $factura_agrupada = $factures_agrupades[$index_soci];



            }










    }

    return $factures_agrupades;
}

function comprovar_ultim_numero_factura()
{
    $message = '';
    if ($_POST['num_propera_factura'] == '') {
        $message = "Per poder facturar cal introduir el número de factura inicial";
    } else if (!is_numeric($_POST['num_propera_factura'])) { //if (!is_int($_POST['num_propera_factura'])){

        $message = "El número de factura ha de ser un enter";
    }


    return $message;


}

function generar_nou_numero_factura($num)
{
    return $num++;
}

/////////////////////////////////////////////////

function load_session()
{
    if (!isset($_SESSION)) {
        session_start();
    }
}


function validate_session()
{
    load_session();
    if (!isset($_SESSION['userdata'])) {
        throw new Exception("Not logged in");
    }
    // For compatibility with old versions the creation tate is forced if it does not exist.
    if (!isset($_SESSION['userdata']['t_saved'])) {
        $_SESSION['userdata']['t_saved'] = time();
        $_SESSION['userdata']['cli_addr'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['userdata']['cli_agent'] = $_SERVER['HTTP_USER_AGENT'];
    }

    // Check if the session is still valid.
    if ((time() - $_SESSION['userdata']['t_saved']) > 30 * 86400 || // More than 30 days inactive
        $_SESSION['userdata']['cli_addr'] !== $_SERVER['REMOTE_ADDR'] || // Client IP address is changed
        $_SESSION['userdata']['cli_agent'] !== $_SERVER['HTTP_USER_AGENT'] // Client browser is changed
    ) {
        logout_session();
        throw new Exception("Not logged in");
    }
    if ((time() - $_SESSION['userdata']['t_saved']) > 15 * 60) { // > 15 min
        save_session();
        load_session();
    }
}

/**
 * Logout Aixada session destroying php session.
 */
/*
function logout_session() {
    load_session();
    session_regenerate_id(true);
    session_unset();
    session_destroy();
}
*/

/**
 * Save Aixada session (only used in this general.php)
 */
function save_session() {
    $_SESSION['userdata']['t_saved'] = time();
    session_commit();
}
function get_current_role()
{
    return get_session_value('current_role');
}

function get_session_value($name)
{
    validate_session();
    return $_SESSION['userdata'][$name];
}


function get_iva_types()
{
    $iva_types = array();
    $sql = 'SELECT percent FROM aixada_iva_type';
    $conn = get_connexio();
    $sql_result = $conn->query($sql);
    if ($sql_result->num_rows > 0) {
        while ($row = $sql_result->fetch_assoc()) {


            array_push($iva_types, $row['percent']); // Append $result to $results

        }
    }
    sort($iva_types);
    return $iva_types;
}


//////////////////////////////// Main Entrypoint

$iva_types = get_iva_types();

/*
if (!isset($_SESSION)) {
    session_start();
    $_SESSION['aixada'] = true;
    session_commit(); // Force write session to create it and able to open $_SESSION faster.
}
*/


    try{
        $role = get_current_role();

    }catch (Exception $ex){
        header("Location: /aixada/login.php");
        exit;
    }
$isAdmin = ($role === "Hacker Commission" || $role ===  "Checkout");


if (!$isAdmin) {

    include_once('no_loguejat_missatge.php');
    exit; // Stops further processing
}

// Rest of the script continues here if the user is an admin



if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // Query  DB
    $results = consulta_general();

    // Render the template
    require('template_principal.php');
}

// Receive parameters from
// - facturar chekboxes or
// - veure detalls

if ($_SERVER["REQUEST_METHOD"] == "POST") {


    $total = 0;
    $factures = array();

    // POST parameter 'accio' is set via submit button in main form on 'template resultats.php'
    if (isset($_POST['accio'])) {

        switch (strtolower($_POST['accio'])) {
            case 'facturar':
                $total_totes_comandes=0;
                foreach ($_POST as $cart_id => $value) {
                    if (is_int($cart_id)) {
                        $comanda = get_comanda_resumida($cart_id);

                        array_push($factures, $comanda); // Append $comanda to $factures
                        $total_totes_comandes += $comanda['total'];
                    }
                }
                usort($factures, 'ordre_numeric_de_soci');

                $factures_agrupades = agrupar_factures_per_soci($factures);

                require('template_facturacio_resultats.php');
                break;

            case 'detalls':
                if (!isset($_POST['cart_id'])) {
                    echo('couldn\'t find $_POST[\'cart_id\']');
                } else {
                    $cart_id = $_POST['cart_id'];
                    //ºecho 'detalls';
                    $results_comanda_sencera = get_comanda_sencera($cart_id);
                    $results_comanda_resumida = get_comanda_resumida($cart_id);
                    require('template_detalls_resultats.php');
                }
                break;
            case 'odoo':

                print_r($_POST);
                $message = comprovar_ultim_numero_factura();
                if ($message != '') {

// Create the div with the message and the back button
                    echo '<div>';
                    echo '<p>' . htmlspecialchars($message) . '</p>'; // Display the message
                    echo '<button onclick="window.history.back();">Tornar</button>'; // Add a 'Go Back' button
                    echo '</div>';


                } else {
                    foreach ($_POST as $cart_id => $value) {
                        if (is_int($cart_id)) {
                            $comanda = get_comanda_resumida($cart_id);
                            array_push($factures, $comanda); // Append $comanda to $factures

                        }
                    }


                    require('template_odoo_resultats.php');
                }


                break;

            // should never happen
            default:
                echo 'El valor $_POST["accio"] = ' . $_POST['accio'] . ' no &eacute;s esperat. Mirar el formulari template_principal.php<br';


        } //with $_POST['accio']
    } // isset
} // POST

?>

