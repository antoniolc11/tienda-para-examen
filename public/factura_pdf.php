<?php
session_start();

use App\Tablas\Factura;

require '../vendor/autoload.php';


//comprueba que el usuario está logueado
if (!($usuario = \App\Tablas\Usuario::logueado())) {
    return volver();
}

$id = obtener_get('id');

if (!isset($id)) {
    return volver();
}

$pdo = conectar();

$factura = Factura::obtener($id, $pdo);

if (!isset($factura)) {
    return volver();
}

if ($factura->getUsuarioId() != $usuario->id) {
    return volver();
}

$filas_tabla = '';
$total = 0;

foreach ($factura->getLineas($pdo) as $linea) {
    $articulo = $linea->getArticulo();
    $codigo = $articulo->getCodigo();
    $cantidad = $linea->getCantidad();
    $descripcion = $articulo->getDescripcion();
    
    if ($articulo->getDescuento() != null) { 
        $desc = $articulo->getDescuento() . '%';
        $precio = $articulo->getPrecio() * $cantidad;
        $descuento = ($articulo->getDescuento() / 100) * $precio;
        $precio = $precio - $descuento;
    }else {
        $desc = '';
        $precio = $articulo->getPrecio() * $cantidad;
    }

    $precioiva = $precio * 0.21;
    $importe = $precio + $precioiva;
    $total += $importe;

    $precio = dinero($precio);
    $precioiva = dinero($precioiva);
    $importe = dinero($importe);
    
    $filas_tabla .= <<<EOF
        <tr>
            <td>$codigo</td>
            <td>$descripcion</td>
            <td>$cantidad</td>
            <td>$desc</td>
            <td>$precio</td>
            <td>$precioiva</td>
            <td>$importe</td>
        </tr>
    EOF;
}

$total = dinero($total);

$res = <<<EOT
<p>Factura número: {$factura->id}</p>

<table border="1" class="font-sans mx-auto">
    <tr>
        <th>Código</th>
        <th>Descripción</th>
        <th>Cantidad</th>
        <th>Descuento</th>
        <th>Precio unitario</th>
        <th>IVA</th>
        <th>Importe</th>
    </tr>
    <tbody>
        $filas_tabla
    </tbody>
</table>

<p>Total: $total</p>
EOT;

// Create an instance of the class:
$mpdf = new \Mpdf\Mpdf();

// Write some HTML code:
$mpdf->WriteHTML(file_get_contents('css/output.css'), \Mpdf\HTMLParserMode::HEADER_CSS);
$mpdf->WriteHTML($res, \Mpdf\HTMLParserMode::HTML_BODY);

// Output a PDF file directly to the browser
$mpdf->Output();
