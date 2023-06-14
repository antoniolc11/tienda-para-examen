<?php session_start() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/css/output.css" rel="stylesheet">
    <title>Articuloshow</title>
</head>
<body>
    <?php 
        require '../vendor/autoload.php';
        $id =  obtener_get('id');

        $pdo = conectar();
        $sent = $pdo->prepare('SELECT a.id,
                                      a.descripcion, 
                                      a.stock, 
                                      a.descuento,
                                      a.precio,
                                      a.categoria_id,
                                      c.nombre 
                                 FROM articulos a JOIN categorias c ON a.categoria_id = c.id  
                                WHERE a.id = :id');
        $sent->execute([':id' => $id]);
        $articulo = $sent->fetch();     

    ?>
    <div class="container mx-auto">
        <?php require '../src/_menu.php' ?>
        <?php require '../src/_alerts.php' ?>
    <div class="flex">
            <main class="flex-1 grid grid-cols-3 gap-4 justify-center justify-items-center">
                    <div class="p-6 max-w-xs min-w-full bg-white rounded-lg border border-gray-200 shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <a href="#">
                            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white"><?= hh($articulo['descripcion']) ?></h5>
                        </a>
                        <p class="mb-3 font-normal text-gray-700 dark:text-gray-400"><?= hh($articulo['descripcion']) ?></p>
                        <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Categoria: <?= hh($articulo['nombre']) ?></p>
                        <?php if ($articulo['descuento'] != null) { ?>
                            <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Descuento: <?= hh($articulo['descuento']) ?>%</p>
                        <?php } ?>
                        <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Precio: <?= dinero(hh($articulo['precio'])) ?></p>
                        <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Existencias: <?= hh($articulo['stock']) ?></p>
                        <?php if ($articulo['stock'] > 0) : ?>
                            <a href="/insertar_en_carrito.php?id=<?= $articulo['id'] ?>&categoria=<?= $cat ?>" class="inline-flex items-center py-2 px-3.5 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                Añadir al carrito
                                <svg aria-hidden="true" class="ml-3 -mr-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </a>
                        <?php else : ?>
                            <a class="inline-flex items-center py-2 px-3.5 text-sm font-medium text-center text-white bg-gray-700 rounded-lg dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">
                                Sin existencias
                            </a>
                            
                        <?php endif ?>
                    
                        <?php
                                    $pdo = conectar();
                                    $sent = $pdo->prepare('SELECT *
                                                             FROM comentarios  
                                                            WHERE articulo_id = :id');
                                    $sent->execute([':id' => $id]);
                                    $comentarios = $sent->fetchAll(); 
                        ?>
                        <div class="py-4">
                            <form action="comentar.php" method="POST">
                                <input type="hidden" name="articulo_id" value="<?= $articulo['id'] ?>">
                                <textarea rows="5" cols="35" name="comentario"></textarea><br><br>
                                <input type="submit" value="Enviar comentario" class="inline-flex items-center py-2 px-3.5 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            </form>

                            <?php if (!empty($comentarios)) { ?>
                                <div>
    
                                    <h5  class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                        Comentarios: 
                                    </h5>

                                        <?php foreach ($comentarios as $comentario) { ?>
                                                <p><?= $comentario['comentario'] ?></p>
                                        <?php } ?>
                                </div>
                            <?php } ?>

                        </div>
                    </div>

            </main>
    </div>
    </div>
    <script src="/js/flowbite/flowbite.js"></script>
</body>
</html>