<?php
session_start();


require '../vendor/autoload.php';

$comentario =  obtener_post('comentario');
$articulo =  obtener_post('articulo_id');


if ($usuario = \App\Tablas\Usuario::logueado()) {
            $usuario =  (int) $usuario->id;
} else {
    return redirigir_login();
}

/* if (isset($comentario)) {
    return volver();
} 

if (isset($articulo)) {
    return volver();
} */

$pdo = $pdo ?? conectar();

$sent = $pdo->prepare('INSERT INTO comentarios (articulo_id, usuario_id, comentario)
VALUES (:articulo_id, :usuario_id, :comentario)');

$sent->execute([
                ':articulo_id' => $articulo,
                ':usuario_id' => $usuario,
                ':comentario' => $comentario
              ]); 

$_SESSION['exito'] = 'El comentario se ha añadido correctamente.';

volver();