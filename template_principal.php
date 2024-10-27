<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="stylesheet" href="css/estilos.css" type="text/css">
    <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="css/dataTables.bootstrap5.min.css" type="text/css">
    <link rel="stylesheet" href="css/buttons.bootstrap5.min.css" type="text/css">
    <link rel="stylesheet" href="css/select.dataTables.min.css" type="text/css">
    <script src="js/jquery-3.7.0.js"></script>
    <script src="js/jquery.dataTables.min.js"></script>

    <style>
        /* Center the text in all table cells */
        #example td {
            text-align: center;
        }
    </style>

    <title>FACTURACIO</title>
</head>
<body>
<h1>FACTURACIÓ MESPILUS+</H1>


<!-- NUEVO ui -->

<form class="form-check" action="principal.php" method="post">

        <h2>Facturació</h2>

        <label>
            <button type="button" class="btn btn-secondary btn-sm"
                    onclick="toggleSelection('facturar')">TOT visible
            </button>
            <input type="submit" class="btn btn-success" name="accio" value="facturar">
            <input type="submit" class="btn btn-success" name="accio" value="detalls">
            <br>
        </label>
        <br>
        <br>
        <br>




        <h2>Odoo</h2>

        <label>
            <button type="button" class="btn btn-secondary btn-sm" onclick="toggleSelection('odoo')">TOT
                visible
            </button>
            </label> <br>


        <?php
            $currentYear = date("Y");
            $startYear = $currentYear - 5; // 5 años antes
            $endYear = $currentYear + 5; // 5 años después
        ?>

        <label for="year">Selecciona l'any de facturaió</label>
        <select name="year" id="year" class="form-select select-narrow">
            <?php
                for ($year = $startYear; $year <= $endYear; $year++) {
                    echo "<option value='$year' " . ($year == $currentYear ? 'selected' : '') . ">$year</option>";
                }
            ?>
        </select>
    <label for="num_propera_factura">Següent num de factura</label>
    <input type="text" name="num_propera_factura" id="num_propera_factura">
    <br>
    Exportar a
    <input type="submit" class="btn btn-success" name="accio" value="odoo">


    <!-- FIN NUEVO ui -->




    <div style="width:80%; margin:auto;">
        <table id="example" class="table table-striped" style="width:100%">
            <thead>
            <tr role="row">
                <th class="text-center select-checkbox sorting_disabled" rowspan="1" colspan="1" style="width: 10px;"
                    aria-label="">
                    Facturar
                </th>
                <th class="text-center select-checkbox sorting_disabled" rowspan="1" colspan="1" style="width: 10px;"
                    aria-label="">
                    Odoo
                </th>
                <th class="text-center select-checkbox sorting_disabled" rowspan="1" colspan="1" style="width: 10px;"
                    aria-label="">
                    Veure detalls (albarà)
                </th>
                <th class="text-center sorting_asc" tabindex="0" aria-controls="example" rowspan="1" colspan="1"
                    style="width: 71px;" aria-sort="descending" aria-label="Name: activate to sort column descending">
                    Cart id
                </th>
                <th class="text-center sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1"
                    style="width: 95px;" aria-label="Position: activate to sort column ascending">
                    Cistella
                </th>
                <th class="text-center sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1"
                    style="width: 56px;" aria-label="Office: activate to sort column ascending">
                    Data de la compra
                </th>
                <th class="text-center sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1"
                    style="width: 27px;" aria-label="Age: activate to sort column ascending">
                    Validat
                </th>
                <th class="text-center sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1"
                    style="width: 56px;" aria-label="Salary: activate to sort column ascending">
                    Total
                </th>
            </tr>
            </thead>
            <tbody>

            <!-- $results comes from facturacio.php as a result of querying DB for all cart_ids -->
            <?php foreach ($results as $result): ?>
            <tr class="text-center">
                <td class="text-center"><input type="checkbox" name="<?= $result['c_id']; ?>" class="facturar"></td>
                <td class="text-center"><input type="checkbox" name="<?= $result['c_id']; ?>" class="odoo"></td>
                <td class="text-center"><input type="radio" name="cart_id" value="<?= $result['c_id']; ?>"
                                               class="seleccionable detalls"></td>
                <td class="text-center"><?= $result['c_id']; ?> </td>
                <td class="text-center"><?= $result['uf_name']; ?> </td>
                <td class="text-center"><?= $result['c_date_for_shop']; ?> </td>
                <td class="text-center"><?= $result['c_ts_validated']; ?> </td>
                <td class="text-center"><?= $result['total']; ?></td>
            </tr>
            <?php endforeach; ?>

            </tbody>
        </table>
    </div>
</form>

<script type="text/javascript">
    // Alternar selección de todos los checkboxes de la clase 'facturar'
    function toggleSelection(className) {
        var checkboxes = document.getElementsByClassName(className);
        var allChecked = true;

        // Verificar si todos están seleccionados
        for (var i = 0; i < checkboxes.length; i++) {
            if (!checkboxes[i].checked) {
                allChecked = false;
                break;
            }
        }

        // Si todos están seleccionados, desmarcar; de lo contrario, marcar todos
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = !allChecked;
        }
    }
</script>

<script type="text/javascript">
    $(document).ready(function () {
        let example = $('#example').DataTable({
            scrollX: true,
            scrollY: true,
            "select": {
                "style": "os",
                "selector": "td:first-child"
            },
            "lengthMenu": [
                [10, 25, 50, 100, 200, -1],
                [10, 25, 50, 100, 200, 'All'],
            ],
            "language": {
                "search": "Filtrar:",
                "lengthMenu": "Mostrar _MENU_ entrades",
            },
            "pageLength": 100,
            "order": [
                [5, 'desc'] // ordenar por columna [5]: data de la compra
            ]
        });
    });
</script>

</body>
</html>
