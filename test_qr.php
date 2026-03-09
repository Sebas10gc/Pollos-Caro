<?php
$numero_pedido = $_GET['pedido'] ?? 'P20260305-001';

$ip = gethostbyname(gethostname());

echo "<h2>🔍 Diagnóstico de URL para QR</h2>";

echo "<h3>URLs para probar:</h3>";
echo "<ul>";
echo "<li><strong>Local (solo PC):</strong> http://localhost/Pollos%20Caro/factura_publica.php?pedido=" . $numero_pedido . "</li>";
echo "<li><strong>Red local (para celular):</strong> http://" . $ip . "/Pollos%20Caro/factura_publica.php?pedido=" . $numero_pedido . "</li>";
echo "</ul>";

echo "<h3>Prueba cada URL:</h3>";
echo "1. Copia la URL de red local<br>";
echo "2. Pégala en el navegador del celular<br>";
echo "3. Si ves la factura, el QR funcionará<br>";

$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode("http://" . $ip . "/Pollos%20Caro/factura_publica.php?pedido=" . $numero_pedido);
echo "<h3>QR con IP local (funciona en celular):</h3>";
echo "<img src='" . $qr_url . "' style='border:1px solid #ccc; padding:10px;'>";
?>