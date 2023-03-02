<?php

use App\Tablas\Articulo;

session_start() ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../css/output.css" rel="stylesheet">
    <title>Modificar</title>
</head>

<body>
    <?php
    require '../../vendor/autoload.php';

    $id = obtener_get('id');

    $pdo = conectar();
    $sent = $pdo->prepare("SELECT * FROM articulos a JOIN categorias c ON a.categoria_id = c.id where a.id = :id");
    $sent->execute([':id' => $id]);
    $articulo = $sent->fetch();

    $codigo = obtener_post('codigo');
    $descripcion = obtener_post('descripcion');
    $precio = obtener_post('precio');
    $stock = obtener_post('stock');
    $descuento = obtener_post('descuento');
    $categoria = obtener_post('categoria');

    $cat_id = $articulo['categoria_id'];
    $pdo = conectar();
    $sent = $pdo->query("SELECT * FROM categorias WHERE id != $cat_id");


    //VALIDACIONES:

    if (!empty($codigo) && mb_strlen($codigo) == 13) {
        \App\Tablas\Articulo::modificar($id, $codigo, $descripcion, $precio, $stock, $descuento, $categoria, $pdo);
        $_SESSION['exito'] = 'El articulo se ha modificado correctamente.';
        return volver_admin();
    }

    require '../../src/_alerts.php';
    ?>

    <form class="mt-5 mr-96 ml-96" action="" method="POST">
        <div class="mb-6">
            <label for="departamento" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">Código</label>
            <input value="<?= hh($articulo['codigo']) ?>" type="text" id="codigo" name="codigo" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required="">

            <label for="descripcion" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">Nombre Articulo</label>
            <input value="<?= hh($articulo['descripcion']) ?>" type="text" id="descripcion" name="descripcion" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required="">


            <label for="precio" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">Precio</label>
            <input value="<?= hh($articulo['precio']) ?>" type="text" id="precio" name="precio" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required="">

            <label for="stok" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">Stock</label>
            <input value="<?= hh($articulo['stock']) ?>" type="text" id="stock" name="stock" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required="">

            <label for="descuento" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">Descuento:</label>
            <input value="<?= hh($articulo['descuento']) ?>" type="text" id="descuento" name="descuento" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">

            <label for="categoria" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">Categoría</label>
            <select name="categoria" id="categoria" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                <option selected="true" value="<?=hh($articulo['categoria_id'])?>"><?=hh($articulo['nombre'])?></option> 
                <?php foreach ($sent as $fila) : ?>
                    <option name="categoria" value="<?=hh($fila['id'])?>"><?=hh($fila['nombre'])?></option> 
                <?php endforeach ?>
            </select>
        </div>

        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">modificar</button>
    </form>

    <script src="../js/flowbite/flowbite.js"></script>

</body>

</html>