<?php session_start() ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/css/output.css" rel="stylesheet">
    <title>Comprar</title>
</head>

<body>
    <?php require '../vendor/autoload.php';

    if (!\App\Tablas\Usuario::esta_logueado()) {
        return redirigir_login();
    }

    $carrito = unserialize(carrito());
   

    if (obtener_post('_testigo') !== null) {
        $pdo = conectar();
        $ids = implode(', ', $carrito->getIds());
        $where = "WHERE id IN ($ids)";
        $sent = $pdo->query("SELECT * FROM articulos $where");

        foreach ($sent->fetchAll() as $fila) {
            if ($fila['stock'] < $carrito->getLinea($fila['id'])->getCantidad()) {
                $_SESSION['error'] = 'No hay existencias suficientes para crear la factura.';
                return volver();
            } 
        }
        // Crear factura
        $usuario = \App\Tablas\Usuario::logueado();
        $usuario_id = $usuario->id;
        $pdo->beginTransaction();
        //Crea una nueva factura
        $sent = $pdo->prepare('INSERT INTO facturas (usuario_id)
                               VALUES (:usuario_id)
                               RETURNING id');
        $sent->execute([':usuario_id' => $usuario_id]);
        $factura_id = $sent->fetchColumn();
        $lineas = $carrito->getLineas();
        $values = [];
        $execute = [':f' => $factura_id];
        $i = 1;


        foreach ($lineas as $id => $linea) {
            $values[] = "(:a$i, :f, :c$i)";
            $execute[":a$i"] = $id;
            $execute[":c$i"] = $linea->getCantidad();
            $i++;
        }

        $values = implode(', ', $values);
        //Relaciona la tabla factura con los articulos
        $sent = $pdo->prepare("INSERT INTO articulos_facturas (articulo_id, factura_id, cantidad)
                               VALUES $values");
        $sent->execute($execute);
        foreach ($lineas as $id => $linea) {
            $cantidad = $linea->getCantidad();

            //Modifica la cantidad despues de haber creado la fatura
            $sent = $pdo->prepare('UPDATE articulos
                                      SET stock = stock - :cantidad
                                    WHERE id = :id');
            $sent->execute([':id' => $id, ':cantidad' => $cantidad]);
        }
        $pdo->commit();
        $_SESSION['exito'] = 'La factura se ha creado correctamente.';
        unset($_SESSION['carrito']);
        return volver();
    }

    ?>

    <div class="container mx-auto">
        <?php require '../src/_menu.php' ?>
        <div class="overflow-y-auto py-4 px-3 bg-gray-50 rounded dark:bg-gray-800">
            <table class="mx-auto text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <th scope="col" class="py-3 px-6">Código</th>
                    <th scope="col" class="py-3 px-6">Descripción</th>
                    <th scope="col" class="py-3 px-6">Cantidad</th>
                    <?php foreach ($carrito->getLineas() as $linea) : ?>
                        <?php
                        $articulo = $linea->getArticulo();
                        if ($articulo->getDescuento() != null) {
                            $descuento = '<th scope="col" class="py-3 px-6">Descuento</th>';
                            $validado = true;
                        }
                        ?>
                    <?php endforeach ?>

                    <?= $descuento ?>
                    <th scope="col" class="py-3 px-6">Precio unitario</th>
                    <th scope="col" class="py-3 px-6">IVA</th>
                    <th scope="col" class="py-3 px-6">Importe</th>
                </thead>
                <tbody>
                    <?php $total = 0 ?>
                    <?php foreach ($carrito->getLineas() as $id => $linea) : ?>
                        <?php
                        $articulo = $linea->getArticulo();
                        $codigo = $articulo->getCodigo();
                        $cantidad = $linea->getCantidad();
                        $descripcion = $articulo->getDescripcion();
                        if ($articulo->getDescuento() != null) { 
                            $precio = $articulo->getPrecio();
                            $descuento = ($articulo->getDescuento() / 100) * $precio;
                            $precio = ($precio - $descuento) * $cantidad;
                        }else {
                            $precio = $articulo->getPrecio() * $cantidad;
                        }
                        $precioiva = $precio*0.21;
                        $importe = $precio + $precioiva;
                        $total += $importe;
                        $articulo_id = $articulo->getId();
                       
                        ?>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="py-4 px-6"><?= $articulo->getCodigo() ?></td>
                            <td class="py-4 px-6"><?= $descripcion ?></td>
                            <td class="py-4 px-6 text-center">
                                    <form action="cantidad.php" name="" method=" get">
                                        <input type="hidden" name="id" value="<?= $articulo_id ?>">
                                        <button type="submit" name="restar" value="restar" class="mx-auto focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-2 py-0 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-900">-</button>
                                            <?= $cantidad ?>
                                        <button type="submit" name="sumar" value="sumar" class="mx-auto focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-2 py-0 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-900">+</button>
                                    </form>
                            </td>

                            
                                <?php if ($articulo->getDescuento() != null) { ?>
                                    <?php $existedescuento = true; ?>
                                    <td class="py-4 px-6 text-center"><?= $articulo->getDescuento() ?>%</td>
                                <?php } elseif (!$validado) { ?>
           
                                <?php } else { ?>
                                    <td class="py-4 px-6 text-center"></td>
                                <?php } ?>
                            

                            <td class="py-4 px-6 text-center">
                                <?= dinero($precio) ?>
                            </td>

                            <td class="py-4 px-6 text-center">
                                <?= dinero($precioiva) ?>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <?= dinero($importe) ?>
                            </td>
                            <td>
          
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
                <tfoot>
                    <td colspan="3"></td>
                    <td></td>
                    <td class="text-center font-semibold">TOTAL:</td>
                    <td class="text-center font-semibold"><?= dinero($total) ?></td>
                </tfoot>
            </table>
            <form action="" method="POST" class="mx-auto flex mt-4">
                <input type="hidden" name="_testigo" value="1">
                <button type="submit" href="" class="mx-auto focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-900">Realizar pedido</button>
            </form>
        </div>
    </div>
    <script src="/js/flowbite/flowbite.js"></script>
</body>

</html>