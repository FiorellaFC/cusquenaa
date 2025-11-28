<?php
session_start();
session_unset();
session_destroy();
// Redirigir al home
header("Location: ../../../../frontend/pages/vistaCusquena.php"); // O index.php según uses
exit();
?>