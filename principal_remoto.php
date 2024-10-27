<?php

function get_connexio(){
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
                SUM( ROUND ((si.quantity * si.unit_price_stamp), 2)) as total
            FROM  aixada_shop_item as si, aixada_cart as c, aixada_uf as uf
            WHERE
                c.uf_id  = uf.id AND
                c.id = si.cart_id 

            GROUP BY c.id
            ORDER BY c.date_for_shop DESC
            LIMIT 400";
            //AND                 c.date_for_shop > DATE_SUB(CURRENT_DATE(), INTERVAL 3 MONTH)

    $sql_result = $conn->query($sql);

    $results = array();
    $count =0;
    if ($sql_result->num_rows > 0) {
        while ($row = $sql_result->fetch_assoc()) {
            $result = array(
                'c_id' => $row['c_id'],
                'uf_name' => $row['name'],
                'uf_id' => $row['uf_id'],
                'c_date_for_shop' => $row['date_for_shop'],
                'c_ts_validated' => ($row['ts_validated'] == '0000-00-00 00:00:00') ? '<div style="background-color:red">NO</div>' : 'SI',
                'total' => $row['total'],
                'iva' => array(
                    '4%' => 4,
                    '5%' => 5,
                    '10%' => 10,
                    '21%' => 21
                )
            );
        array_push($results, $result); // Append $result to $results
        $count++;
        if ($count == 500)
            break;
        }
    }

    $conn->close();
    return $results;
}


/* Obtenir les dades de la comanda:

Detall compra(Cart id)
NUM SOCI:

Total:
IVA 4%
IVA 5%
IVA 10%
IVA 21%
Sense IVA
*/
function get_comanda_resumida($cart_id)
{
    // prepatració del resultat
    $result = array();
    $result['cart_id']=$cart_id;
    $result['con_iva']=array(); // s'aniran afegint els camps corresponents  al IVA
    // $result['con_iva']['0%']
    // $result['con_iva']['5%']
    // $result['con_iva']['10%']
    // $result['con_iva']['21%']
    // $result['total']


    $conn = get_connexio();

    $consulta_uf = "SELECT c.id, u.id, u.name as uf_id, member.name as soci, member.nif, c.date_for_shop 
                    FROM aixada_cart AS c, aixada_uf AS u, aixada_member as member 
                    WHERE c.id = $cart_id 
                      AND c.uf_id = u.id 
                      AND u.id = member.uf_id
                      AND member.active=1";
    //print_r('CONSULTA RESUMIDA: '.$consulta_uf.'<BR>');

    $result_uf = mysqli_query($conn, $consulta_uf);

    if ($result_uf) {
        $row = mysqli_fetch_row($result_uf);
        $result['soci'] = str_replace(array('Soci ', 'Cistella '), array('S', 'C'), $row[2]);
        $result['member'] = $row[3];
        $result['nif'] = $row[4];
        $result['date_for_shop'] = $row[5];
    }

    // TEMPLATE for all VAT types
    // Es van fent consltes pel mateix cart_id pero variant els IVAS
    $sql = "SELECT cart_id, iva_percent, SUM(ROUND((quantity * unit_price_stamp), 2)), SUM(quantity) FROM aixada_shop_item WHERE cart_id = $cart_id GROUP BY cart_id, iva_percent HAVING cart_id = $cart_id AND iva_percent = %s ORDER BY iva_percent ASC";
    $consulta_0_percent = sprintf($sql, 0);
    $result_0_percent = mysqli_query($conn, $consulta_0_percent);
    $result['con_iva']['4%'] = 0;
    $result['base_iva']['4%'] = 0;
    $result['iva']['4%'] = 0;
    if ($result_0_percent && mysqli_num_rows($result_0_percent)>0) {

        $row = mysqli_fetch_row($result_0_percent);
        $result['con_iva']['0%'] = $row[2];
        $result['base_iva']['0%'] = round(floatval($result['con_iva']['0%']) , 3);
        $result['iva']['0%'] = $result['con_iva']['0%'] - $result['base_iva']['0%'];
    }

    $consulta_4_percent = sprintf($sql, 4);
    $result_4_percent = mysqli_query($conn, $consulta_4_percent);

    $result['con_iva']['4%'] = 0;
    $result['base_iva']['4%'] = 0;
    $result['iva']['4%'] = 0;
    if ($result_4_percent && mysqli_num_rows($result_4_percent)>0) {
        $row = mysqli_fetch_row($result_4_percent);
        $result['con_iva']['4%'] = $row[2];
        $result['base_iva']['4%'] = round(floatval( $result['con_iva']['4%']) / 1.04, 3);
        $result['iva']['4%'] = $result['con_iva']['4%'] - $result['base_iva']['4%'];
    }

    $consulta_5_percent = sprintf($sql, 5);
    $result_5_percent = mysqli_query($conn, $consulta_5_percent);
    $result['con_iva']['5%'] = 0;
    $result['base_iva']['5%'] = 0;
    $result['iva']['5%'] = 0;
    if ($result_5_percent && mysqli_num_rows($result_5_percent)>0) {
        $row = mysqli_fetch_row($result_5_percent);
        $result['con_iva']['5%'] = $row[2];
        $result['base_iva']['5%'] = round(floatval ($result['con_iva']['5%']) / 1.05, 3);
        $result['iva']['5%'] = $result['con_iva']['5%'] - $result['base_iva']['5%'];
    }

    $consulta_10_percent = sprintf($sql, 10);
    $result_10_percent = mysqli_query($conn, $consulta_10_percent);
    $result['con_iva']['10%'] = 0;
    $result['base_iva']['10%'] = 0;
    $result['iva']['10%'] = 0;

    if ($result_10_percent && mysqli_num_rows($result_10_percent)>0) {
        $row = mysqli_fetch_row($result_10_percent);
        $result['con_iva']['10%'] = $row[2];
        $result['base_iva']['10%'] = round(floatval($result['con_iva']['10%']) / 1.10 , 3);
        $result['iva']['10%'] = $result['con_iva']['10%'] - $result['base_iva']['10%'];
    }

    $consulta_21_percent = sprintf($sql, 21);
    $result_21_percent = mysqli_query($conn, $consulta_21_percent);
    $result['con_iva']['21%'] = 0;
    $result['base_iva']['21%'] = 0;
    $result['iva']['21%'] = 0;

    if ($result_21_percent && mysqli_num_rows($result_21_percent)>0) {
        $row = mysqli_fetch_row($result_21_percent);
        $result['con_iva']['21%'] = $row[2];
        $result['base_iva']['21%'] = round(floatval($row[2]) / 1.21 , 3);
        $result['iva']['21%'] = $result['con_iva']['21%'] - $result['base_iva']['21%'];
    }

    // Calcul del total
    $result['total'] = $result['con_iva']['0%'] + $result['con_iva']['4%'] + $result['con_iva']['5%'] + $result['con_iva']['10%'] + $result['con_iva']['21%'];
    //$result['uf_id'] = $result['uf_id'];
//    $result['soci'] = $row['4'];
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

function get_comanda_sencera($cart_id){
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
    //print_r($sql);
        $sql_result = $conn->query($sql);
        // AND                 c.date_for_shop > DATE_SUB(CURRENT_DATE(), INTERVAL 3 MONTH)

    $results = array();
    $count =0;
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
                'quantity' =>  $row['quantity'],
                'unit_price' => $row['price'],
                'IVA' => $row['IVA'],
                'total' => $row['total']
            );
            array_push($results, $result); // Append $result to $results

            //print_r($results);
        }
        return $results;
    }


}

// Custom sorting function AS in old facturacio with python
// this ordering is done with alphanumeric sorting example
// S111/C3  S146/C13    S27/C27     S5/C2
function ordre_alphanumeric($a, $b) {
    echo ($a['soci']. '<br>');
    return strcmp($a['soci'], $b['soci']);
}


 // Custom sorting function AS in old facturacio with python
 // this ordering is done with NUMERIC SORTING after the 'S'
 // S5/C2 S27/C27 S111/C3 S146/C13
function ordre_numeric_de_soci($a, $b) {
    $aNumericPart = substr($a['soci'], 1);
    $bNumericPart = substr($b['soci'], 1);

    return intval($aNumericPart) - intval($bNumericPart);
}


function facturar(){
     foreach ($_POST as $cart_id => $value) {
        if (is_int($cart_id)){
            $comanda = get_comanda($cart_id);
            array_push($factures, $comanda); // Append $result to $results           
            $total += $comanda['total'];
        }
    break;
    }
    echo ("<h2>total: $total </h2>");
    usort($factures, 'ordre_numeric_de_soci');
    require('template_facturacio_resultats.php');


}

function agruparFacturasPorSoci($factures) {
    $factures_agrupades = [];

    foreach ($factures as $factura) {
        $soci = $factura['soci'];
        $con_iva = $factura['con_iva'];

        if (!isset($factures_agrupades[$soci])) {
            $factures_agrupades[$soci] = [
                'soci' => $soci,
                'total' => 0,  // Inicializamos el total en 0
                'con_iva' => [],
                'base_iva' => $factura['base_iva'],
            ];
        }




        // Sumar los valores de "con_iva" en la factura agrupada
        foreach ($con_iva as $impuesto => $valor) {
        if (!isset($factures_agrupades[$soci]['con_iva'][$impuesto])) {
            $factures_agrupades[$soci]['con_iva'][$impuesto]=0;
        }
            if (is_numeric($valor)) {
                $factures_agrupades[$soci]['con_iva'][$impuesto] += $valor;
            }

        }

        // Calcular el total sumando los valores de "con_iva"
        $factures_agrupades[$soci]['total'] += array_sum($con_iva);
    }

    return array_values($factures_agrupades);
}

function comprovar_ultim_numero_factura(){
    $message ='';
    if ($_POST['num_propera_factura']==''){
        $message = "Per poder facturar cal omplir l'últim número de factura";
    }else if (! is_numeric($_POST['num_propera_factura']))  { //if (!is_int($_POST['num_propera_factura'])){

        $message = "Lúltim número de factura ha de ser un enter";
    }


    return $message;


}
function generar_nou_numero_factura($num){
    return $num++;
}


//////////////////////////////// Main Entrypoint


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
    //echo 'detalls POST:'. print_r($_POST);
    // POST parameter 'accio' is set via submit button in main form on 'template resultats.php'
    if (isset($_POST['accio'])){
       // print_r('<br><br>POST:'.$_POST.'<br>');

        switch (strtolower($_POST['accio'])){
        // accio facturar
            case 'facturar':
                foreach ($_POST as $cart_id => $value) {
                    if (is_int($cart_id)){
                        $comanda = get_comanda_resumida($cart_id);
                        array_push($factures, $comanda); // Append $comanda to $factures
                        $total += $comanda['total'];
                    }
                }
                //echo ("<h2>total: $total </h2>");
                usort($factures, 'ordre_numeric_de_soci');
                $factures_agrupades = agruparFacturasPorSoci($factures);


                require('template_facturacio_resultats.php');
                break;

            case 'detalls':
                if (! isset($_POST['cart_id'])){
                    echo ('coulnd\'t find $_POST[\'cart_id\']');
                }else{
                    $cart_id = $_POST['cart_id'];
                    //ºecho 'detalls';
                    $results_comanda_sencera = get_comanda_sencera($cart_id);
                    $results_comanda_resumida = get_comanda_resumida($cart_id);
                    require('template_detalls_resultats.php');
                }
            case 'odoo':


                $message = comprovar_ultim_numero_factura();
                if ($message != '') {

// Create the div with the message and the back button
                        echo '<div>';
                        echo '<p>' . htmlspecialchars($message) . '</p>'; // Display the message
                        echo '<button onclick="window.history.back();">Tornar</button>'; // Add a 'Go Back' button
                        echo '</div>';


                    }else {
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
                echo 'El valor $_POST["accio"] = ' . $_POST['accio'] .' no &eacute;s esperat. Mirar el formulari template_principal.php<br';



        } //with $_POST['accio']
    } // isset
} // POST    

?>

