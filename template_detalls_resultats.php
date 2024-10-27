<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no">

    <title>Resultats</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>

</head>
<body>

<div id="divToExport">
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col">
                <img src="img/mespilus_logo.png"/>
            </div>
            <div class="col-1"></div>
            <div class="col">
                <h3>ALBAR&Agrave; DE LA COMPRA <?= $results_comanda_sencera[0]['c_id'] ?></h3>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <h1>Nom: <?= $results_comanda_resumida['member'] ?><h1>
            </div>
            <div class="col-4"><h3><?= $results_comanda_sencera[0]['c_date_for_shop'] ?></h3></div>
            <div class="col-2">
                <button id="exportPdfButton" onclick="generatePDF()">Export to PDF</button>
            </div>
        </div>
    </div>

    <br>
    <br>

    <h3>Nom: <?= $results_comanda_resumida['member'] ?></h3>
    <h3>Soci / Cistella: <?= $results_comanda_resumida['soci'] ?></h3>
    <table id="resultats_agrupats_per_cartid" class="table table-stripped" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th>producte</th>
            <th>proveidor</th>
            <th>quantity</th>
            <th>preu unitari</th>
            <th>IVA</th>
            <th>total</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($results_comanda_sencera as $result): ?>

            <tr>

                <td><?= $result['product_name'] ?></td>
                <td><?= $result['provider'] ?></td>
                <td><?= $result['quantity'] ?></td>
                <td><?= $result['unit_price'] ?></td>
                <td><?= $result['IVA'] ?></td>
                <td><?= $result['total'] ?></td>

            </tr>
        <?php endforeach; ?>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>TOTAL</b></td>
            <td><b><?= $results_comanda_resumida['total'] ?></b></td>
        </tr>


        <?php
        $iva_keys = get_iva_types();
        ?>
        <?php foreach ($iva_keys as $iva) : ?>
            <tr>
                <td></td>
                <td></td>
                <td><b>Base a IVA <?= $iva ?></b></td>
                <td><b><?= $results_comanda_resumida['base_iva'][$iva] ?></b></td>
                <td><b>IVA <?= $iva ?></b></td>
                <td><b><?= $results_comanda_resumida['iva'][$iva] ?></b></td>
            </tr>
        <?php endforeach; ?>


        </tbody>
    </table>
</div>

<script>


    function generatePDF() {

        // Choose the element id which you want to export.
        var element = document.getElementById('divToExport');
        var old_display = document.getElementById('exportPdfButton').style.display;
        document.getElementById('exportPdfButton').style.display = 'none';

        //       element.style.width = '700px';
        //     element.style.height = '900px';
        var opt = {
            margin: 0.5,
            filename: 'myfile.pdf',
            image: {type: 'jpeg', quality: 1},
            html2canvas: {scale: 1},
            jsPDF: {unit: 'in', format: 'letter', orientation: 'portrait', precision: '12'}
        };

        // choose the element and pass it to html2pdf() function and call the save() on it to save as pdf.
        html2pdf().set(opt).from(element).save();
        document.getElementById('exportPdfButton').style.display = old_display;
    }
</script>
</body>
</html>


