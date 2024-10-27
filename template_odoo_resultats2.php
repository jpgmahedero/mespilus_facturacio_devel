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
            $('#resultats_agrupats_per_cartid').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    //'copy', 'csv', 'excel', 'pdf', 'print'

                    'copy', 'csv', 'pdf', 'print'
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
<h3>ODOO</h3>

<table id="resultats_agrupats_per_cartid" class="display primary" cellspacing="0" width="100%">
    <thead>
    <tr>
        <!--
                <th style="width: 170px;"></th>
                <th style="width: 200px;"></th>
                -->
        <th>name</th>
        <th>partner_id</th>
        <th>invoice_date</th>
        <th>invoice_line_ids/account_id</th>
        <th>invoice_line_ids/quantity</th>
        <th>invoice_line_ids/price_unit</th>
        <th>invoice_line_ids/tax_ids/id</th>
        <th>invoice_line_ids/name</th>


    </tr>
    </thead>
    <tbody>

    <?php
        $iva_keys = get_iva_types();
    ?>
    <?php foreach ($factures as $results_comanda_resumida): ?>
        <tr>
            <td><?= $_POST['num_propera_factura']++; ?></td>
            <td><?= htmlspecialchars($results_comanda_resumida['nif']); ?></td>
            <td><?= htmlspecialchars($results_comanda_resumida['date_for_shop']); ?></td>
            <td>700000 Ventas de mercaderías en España</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>

        <?php foreach ($iva_types as $iva) : ?>

            <?php
            if ($results_comanda_resumida['base_iva'][$iva] == 0) {
                continue;
            }
            ?>
            <tr>
                <td><?= $_POST['num_propera_factura']++; ?></td>
                <td><?= htmlspecialchars($results_comanda_resumida['nif']); ?></td>
                <td><?= htmlspecialchars($results_comanda_resumida['date_for_shop']); ?></td>
                <td>700000 Ventas de mercaderías en España</td>
                <td>
                    <?= $results_comanda_resumida['base_iva'][$iva] == 0 ? 0 : 1; ?>
                </td>
                <td><?= htmlspecialchars($results_comanda_resumida['base_iva'][$iva]); ?></td>
                <td>account.1_account_tax_template_s_iva0b</td>
                <td>Productos facturados al <?= htmlspecialchars($iva); ?> de IVA</td>
            </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>


    </tbody>
</table>


<!--
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
-->
</body>
</html>


