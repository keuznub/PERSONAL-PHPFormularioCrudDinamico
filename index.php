<?php



$url = "localhost";
$username = "root";
$password = "";
$database = (isset($_POST["databases"])) ? $_POST["databases"] : "";
$nextPage = $_SERVER["PHP_SELF"];

session_start();
$con = getConnection($url, $username, $password, $database);

function getConnection($url, $username, $password, $database)
{
    try {
        if ($con = mysqli_connect($url, $username, $password, $database)) {
            return $con;
            
        }
    } catch (Exception $e) {
        die("Error en la conexion [datos de database erroneos] | [revisar conexion localhost]");
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['selectDatabase'])) {
        $nextPage = "tabla.php";
    }
    $_POST["database"] = $_POST["databases"];
}

function getDatabases()
{
    global $con;
    $statement = "SHOW DATABASES";
    $result = mysqli_query($con, $statement);
    while ($row = mysqli_fetch_assoc($result)) {
        foreach ($row as $k) {
            $databases[] = $k;
        }
    }
    return $databases;
}


function getTables()
{
    global $con;
    $statement = "SHOW TABLES";
    $result = mysqli_query($con, $statement);
    while ($row = mysqli_fetch_assoc($result)) {
        foreach ($row as $k) {
            $tablas[] = $k;
        }
    }
    return $tablas;
}






?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="formulario.css">
    <title>Buscador de bases de datos</title>

</head>
<div clas></div>
<form method="post" action='<?php $_SERVER["PHP_SELF"] ?>'>
    <h1>Lista de bases de datos</h1>
    <?php  ?>
    <select name="databases">
        <?php foreach (getDatabases() as $i) : ?>
            <option value="<?php echo $i ?>" <?php if ($i == $database) {
                                                    echo "selected";
                                                } ?>><?php echo $i ?></option>
        <?php endforeach; ?>
    </select>
    <input class="boton" type="submit" name="selectDatabase" value="Seleccionar Base de datos">
    <br>
</form>

<?php if ($_SERVER["REQUEST_METHOD"] == "POST") : ?>
    <form method="post" action='<?php echo $nextPage ?>'>
        <h1>Lista de tablas de <?php echo "$database" ?></h1>
        <select name="tablas">
            <?php foreach (getTables() as $i) : ?>
                <option value="<?php echo $i ?>"><?php echo $i ?></option>
            <?php endforeach; ?>
        </select>
        <br>
        <input class="boton" type="submit" name="selectTabla" value="Seleccionar Tabla">
        <input type="hidden" name="url" value='<?php echo "$url" ?>'>
        <input type="hidden" name="username" value='<?php echo "$username" ?>'>
        <input type="hidden" name="password" value='<?php echo "$password" ?>'>
        <input type="hidden" name="database" value='<?php echo "$database" ?>'>
        <br>
    </form>
<?php endif; ?>
<br>
<br>

</body>

</html>