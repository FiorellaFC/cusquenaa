<?php
function verificarPermiso($permisosPermitidos) {
    // Inicia la sesión aquí. Si ya está iniciada en otro script, no hará nada.
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Verificar si el rol está definido en la sesión y si está en los permisos permitidos
    if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], $permisosPermitidos)) {
        // Podrías redirigir o mostrar un mensaje de error HTTP 403 Forbidden
        http_response_code(403);
        die("Acceso denegado. No tienes los permisos necesarios para esta acción.");
    }
}
?>
