<?php
session_start();
$_SESSION["database"] = isset($_POST["database"]) ? ($_POST["database"]) : $_SESSION["database"];
$_SESSION["username"] = isset($_POST["username"]) ? ($_POST["username"]) : $_SESSION["username"];
$_SESSION["password"] = isset($_POST["password"]) ? ($_POST["password"]) : $_SESSION["password"];
$_SESSION["url"] = isset($_POST["url"]) ? ($_POST["url"]) : $_SESSION["url"];
$_SESSION["tablas"] = isset($_POST["tablas"]) ? ($_POST["tablas"]) : $_SESSION["tablas"];

$database = $_SESSION["database"];
$username = $_SESSION["username"];
$password = $_SESSION["password"];
$url = $_SESSION["url"];
$tablas = $_SESSION["tablas"];
$con = getConnection($url, $username, $password, $database);
$columns = getColumns();
$columns = array_flip($columns);
$columns = array_fill_keys(array_keys($columns), "");
$mensajeError = "";

function getConnection($url, $username, $password, $database)
{
    try {
        if ($con = mysqli_connect($url, $username, $password, $database)) {
            return $con;
        }
    } catch (Exception $e) {
        die("Error en la conexion datos de database erroneos");
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST["btn"])) {
        foreach ($columns as $k => &$v) {
            $v = $_POST["$k"];
        }
        unset($v);

        if ($_POST["btn"] == "Insert") {
            if (!isFilledArray($columns)) {
                $mensajeError .= "FALTAN CAMPOS";
            } else {
                try {
                    $columnsSearch = implode(", ", array_keys($columns));
                    $columnsValues;
                    foreach ($columns as $k => $v) {
                        $columnsValues[] = '"' . $v . '"';
                    }
                    $columnsValues = implode(", ", array_values($columnsValues));
                    $statement = "INSERT INTO $tablas ($columnsSearch) VALUES($columnsValues)";
                    if ($result = mysqli_query($con, $statement)) {
                        $mensajeError .= "Inserccion Exitosa";
                    } else $mensajeError .= "Inserccion fallida";
                } catch (Exception $e) {
                    $mensajeError .= "Error al insertar";
                }
            }
            flushFields();
        }
        if ($_POST["btn"] == "Update") {

            if (!isFilledArray($columns) || !$idSearch = getID($columns)) {
                $mensajeError .= "FALTAN CAMPOS";
            } else {
                try {
                    $columnsValues;
                    foreach ($columns as $k => $v) {
                        $columnsValues[] = "$k" . '=' . '"' . $v . '"';
                    }
                    $columnsValues = implode(", ", array_values($columnsValues));
                    $statement = "UPDATE $tablas SET $columnsValues WHERE $idSearch = '$columns[$idSearch]'";
                    if ($result = mysqli_query($con, $statement)) {
                        $mensajeError .= "Modificacion Exitosa";
                    } else $mensajeError .= "Modificacion fallida";
                } catch (Exception $e) {
                    $mensajeError .= "Error al insertar";
                }
            }
            flushFields();
        }
        if ($_POST["btn"] == "Delete") {

            if (!($idSearch = getID($columns))) {
                $mensajeError .= "NO HAY ID";
            } else {
                try {
                    $columnsSearch = implode(", ", array_keys($columns));
                    $statement = "DELETE FROM $tablas WHERE $idSearch='$columns[$idSearch]'";
                    if ($result = mysqli_query($con, $statement)) {
                        $mensajeError .= "Eliminacion exitosa";
                    } else $mensajeError .= "Eliminacion fallida";
                } catch (Exception $e) {
                    $mensajeError .= "Error al eliminar";
                }
            }
            flushFields();
        }
        if ($_POST["btn"] == "Search") {
            if (!($idSearch = getID($columns))) {
                $mensajeError .= "NO HAY ID";
            } else {
                try {

                    $columnsSearch = implode(", ", array_keys($columns));
                    $statement = "SELECT $columnsSearch FROM $tablas WHERE $idSearch = '$columns[$idSearch]'";
                    $result = mysqli_query($con, $statement);
                    if ($row = $result->fetch_assoc()) {
                        foreach ($columns as $k => &$v) {
                            $v = $row[$k];
                        }
                        unset($v);
                    } else {
                        $mensajeError .= "No se han encontrado resultados";
                        flushFields();
                    }
                } catch (Exception $e) {
                    $mensajeError .= "Error al buscar";
                    flushFields();
                }
            }
        }
    }
}


function getColumns()
{
    global $con;
    global $tablas;
    $statement = "SHOW COLUMNS FROM " . $tablas;
    $result = mysqli_query($con, $statement);
    while ($row = mysqli_fetch_row($result)) {
        $columns[] = $row[0];
    }
    return $columns;
}

function getID($array)
{
    foreach ($array as $k => $v) {
        if ((str_contains($k, "id")) || (str_contains($k, "ID"))) {
            return $k;
        }
    }
    return false;
}

function flushFields()
{
    global $columns;
    foreach ($columns as $k => &$v) {
        $v = "";
    }
    unset($v);
}

function isFilledArray($array)
{
    foreach ($array as $k => $v) {
        if (empty($v) || $v == NULL || strlen(trim($v)) == 0) {
            return false;
        }
    }
    return true;
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="formulario.css">
    <title>Formulario Dinamico</title>

</head>

<body>
    <form method="post" action='<?php $_SERVER["PHP_SELF"] ?>'>
        <h1>Columnas de <?php echo $tablas ?></h1>
        
        <div class="campos">
        <?php foreach ($columns as $c => $v) : ?>
            <?php  ?>
            <label for='<?php echo $c ?>'><?php echo $c . " : " ?></label><input type="text" name="<?php echo $c ?>" value="<?php echo $v ?>">
            <br>
        <?php endforeach; ?>
        </div>
        <input class="boton" type="submit" name="btn" value="Insert">
        <input class="boton" type="submit" name="btn" value="Update">
        <input class="boton" type="submit" name="btn" value="Delete">
        <input class="boton" type="submit" name="btn" value="Search">
        <br>
        <?php if(strlen(trim($mensajeError))>0):?>
        <label class="mensaje"><?php echo $mensajeError ?></label>
        <?php  endif; ?>
    </form>
    <br>
    <br>
    <form method="get" action="index.php">
        <input class="boton" type="submit" name="btn" value="Volver a bases de datos">
    </form>
</body>

</html>