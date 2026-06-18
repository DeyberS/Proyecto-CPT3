<?php
$id_pedido = (int)($_GET['id_pedido'] ?? 0);

if ($id_pedido === 0) {
    // Redirige al listado si no hay ID válido para evitar errores.
    header("Location: ../../pages/php/farmacia_pedidos_listado.php?status=error_pdf");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Imprimiendo Pedido...</title>
    <style>body { visibility: hidden; }</style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const id_pedido = <?php echo $id_pedido; ?>;
            const pdfUrl = 'pedido_pdf.php?Id=' + id_pedido;
            
            // 1. Abrir el PDF en una nueva pestaña
            const pdfWindow = window.open(pdfUrl, '_blank');
            
            // Si el navegador bloquea la ventana emergente, alertamos
            if (pdfWindow === null || typeof(pdfWindow) === 'undefined') {
                alert("La ventana de impresión fue bloqueada. Por favor, revisa la configuración de tu navegador.");
            }
            
            // 2. Redirigir la ventana actual al listado después de 2 segundos
            setTimeout(function() {
                window.location.href = '../../pages/php/farmacia_pedidos_listado.php?status=success';
            }, 2000); 
        });
    </script>
</head>
<body>
    <h1>Guardado Exitoso</h1>
    <p>La orden de pedido se está abriendo en una nueva pestaña. Serás redirigido al listado en 2 segundos.</p>
    <p>Si la nueva pestaña no se abre, <a href="pedido_pdf.php?Id=<?php echo $id_pedido; ?>" target="_blank">haz clic aquí</a> para imprimir.</p>
</body>
</html>