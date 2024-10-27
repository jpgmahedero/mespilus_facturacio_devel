<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no">

    <title>Resultats</title>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="css/buttons.dataTables.min.css">

    <script src="js/jquery-3.7.0.js"></script>
    <script src="js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" language="javascript" src="js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" language="javascript" src="js/jszip.min.js"></script>
    <script type="text/javascript" language="javascript" src="js/pdfmake.min.js"></script>
    <script type="text/javascript" language="javascript" src="js/vfs_fonts.js"></script>
    <script type="text/javascript" language="javascript" src="js/buttons.html5.min.js"></script>
    <script type="text/javascript" language="javascript" src="js/buttons.print.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            $('#table_agrupats).DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                "paging": false,
                "ordering": false,
                "info": false,
                "searching": false,
                "fixedColumns": true,
                 scrollX: true,
                 scrollY: true,

            });
        });
    </script>
<style>
        /* Custom CSS for dashed column borders */
        table.dataTable th,
        table.dataTable td {
            border: 1px solid #deddda66; /* Dashed border with light gray */
            padding: 8px; /* Add padding for better appearance */
        }
    </style>

</head>
<body>
    <h3>RESULTATS AGRUPATS SOCXI cart id</h3>
    <table id="table_agrupats" class="display primary" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th style="width: 170px;"></th> <!-- necessary for buttons CSV, PDF etc -->
                <th style="width: 200px;"></th> <!-- necessary for buttons CSV, PDF etc -->
                <?php foreach ($factures as $factura): ?>
                    <th></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td></td>
                <td>Detall compra<br/>(Cart id)</td>
                <?php foreach ($factures as $factura): ?>
                    <td><?= $factura['cart_id'] ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td>Despesa Total: <?= $total ?></td>
                <td>NUM SOCI:</td>
                <?php foreach ($factures as $factura): ?>
                    <td><?= $factura['soci'] ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td><?= count($factures) ?> comandes</td>
                <td></td>
                <?php foreach ($factures as $factura): ?>
                    <td></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td></td>
                <td>Total:</td>
                <?php foreach ($factures as $factura): ?>
                    <td><?= $factura['total'] ?></td>
                <?php endforeach; ?>
            </tr>
<!--            <tr>
                <td></td>
                <td>IVA 4%</td>
                <?php foreach ($factures as $factura): ?>
                    <td><?= $factura['sin_iva']['4%'] ?></td>
                <?php endforeach; ?>
            </tr>
-->


            <tr>
                <td></td>
                <td>IVA 5%</td>
                <?php foreach ($factures as $factura): ?>
                    <td><?= $factura['sin_iva']['5%'] ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td></td>
                <td>IVA 10%</td>
                <?php foreach ($factures as $factura): ?>
                    <td><?= $factura['sin_iva']['10%'] ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td></td>
                <td>IVA 21%</td>
                <?php foreach ($factures as $factura): ?>
                    <td><?= $factura['sin_iva']['21%'] ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td></td>
                <td>Sense IVA</td>
                <?php foreach ($factures as $factura): ?>
                    <td><?= $factura['sin_iva']['0%'] + $factura['sin_iva']['4%'] ?></td>
                <?php endforeach; ?>
            </tr>
        </tbody>
    </table>
<!--
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
-->
</body>
</html>


