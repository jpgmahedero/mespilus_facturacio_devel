<?php
function get_connexio()
{
    $servername = "localhost";
    $username = "mespilus";
    $password = "M3sp1lus*";
    $dbname = "aixada";
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

function consulta_get_socis()
{

    $conn = get_connexio();
    $sql = "SELECT member.nif as name, member.name as x_mespilus_nom_complet, member.address as street, member.phone1 as phone, member.phone2 as mobile, user.email as email, member.nif as vat, uf.id as x_mespilus_num_cistella
FROM  aixada_member as member, aixada_uf as uf, aixada_user as user
WHERE member.participant = 1
	AND member.uf_id = uf.id";

    print_r($sql);
    die();
    $sql_result = $conn->query($sql);


    $results = [];

    if ($sql_result->num_rows > 0) {
        while ($row = $sql_result->fetch_assoc()) {
            $result = array(
                'name' => $row['name'],
                'x_mespilus_nom_complet' => $row['x_mespilus_nom_complet'],
                'uf_id' => $row['x_mespilus_num_cistella'],
                'street' => $row['street'],
                'phone1' => $row['phone'],
                'phone2' => $row['mobile'],
                'email' => $row['email'],
                'vat' => $row['vat'],
            );
            $result['uf_id'] = substr($row['email'], 0, strpos($row['email'], "@"));

            array_push($results, $result); // Append $result to $results
        }
    }

// Define the folder and file path
    $folderPath = __DIR__ . '/downloads';
    if (!file_exists($folderPath)) {
        mkdir($folderPath, 0777, true);
    }
    $csvFilePath = $folderPath . '/results.csv';

// Open the file for writing
    $file = fopen($csvFilePath, 'w');

    if ($file === false) {
        die('Error opening the file ' . $csvFilePath);
    }

// Write the header row to the CSV file
    $header = array('name', 'x_mespilus_nom_complet', 'uf_id', 'street', 'phone1', 'phone2', 'email', 'vat');
    fputcsv($file, $header);

// Write each row of $results to the CSV file
    foreach ($results as $row) {
        fputcsv($file, $row);
    }

// Close the file
    fclose($file);

// Generate the download link
    $downloadLink = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $csvFilePath;

    echo "CSV file has been successfully created. <a href=\"$downloadLink\">Download the file</a>";

    $conn->close();
    print_r($results[0]);
    return $results;
}

consulta_get_socis();
?>