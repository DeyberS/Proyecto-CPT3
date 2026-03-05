<?php

$id_consulta = (int)($_GET['id_consulta'] ?? 0);

if ($id_consulta === 0) {
    // Redirige al listado si no hay ID válido para evitar errores.
    header("Location: ../../pages/php/consulta_listado.php?status=error_pdf");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Imprimiendo Récipe...</title>
    <style>body { visibility: hidden; }</style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const id_consulta = <?php echo $id_consulta; ?>;
            const pdfUrl = 'consulta_receta_pdf.php?id_consulta=' + id_consulta;
            
            // 1. Abrir el PDF en una nueva pestaña (o ventana)
            const pdfWindow = window.open(pdfUrl, '_blank');
            
            // Si el navegador bloquea la ventana emergente, intentamos con un alert.
            if (pdfWindow === null || typeof(pdfWindow) === 'undefined') {
                alert("La ventana de impresión fue bloqueada. Por favor, revisa la configuración de tu navegador.");
            }
            
            // 2. Redirigir la ventana actual al listado después de un breve retraso (2 segundos)
            // Esto asegura que la nueva pestaña del PDF se abra antes de que la página actual desaparezca.
            setTimeout(function() {
                window.location.href = '../../pages/php/consulta_listado.php?status=success';
            }, 2000); 
        });
    </script>
</head>
<body>
    <h1>Guardado Exitoso</h1>
    <p>El récipe se está abriendo en una nueva pestaña. Serás redirigido al listado en 2 segundos.</p>
    <p>Si la nueva pestaña no se abre, <a href="consulta_receta_pdf.php?id_consulta=<?php echo $id_consulta; ?>" target="_blank">haz clic aquí</a> para imprimir.</p>
</body>
</html>


