<?php

use App\Tablas\Articulo;

require '../vendor/autoload.php';

session_start();

$id = obtener_get('id');

var_dump($id);

$articulo = Articulo::obtener($id);
$carrito = unserialize(carrito());


if (obtener_get('sumar')) 
{
    $carrito->insertar($id);
} elseif (obtener_get('restar')) 
{
    $carrito->eliminar($id);
}


$_SESSION['carrito'] = serialize($carrito);

header("Location: comprar.php");