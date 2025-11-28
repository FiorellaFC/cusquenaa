<nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
    <div class="sb-sidenav-menu">
        <div class="nav">
            <div class="sb-sidenav-menu-heading">Gestión</div>

            <!-- Usuarios -->
            <a class="nav-link" href="usuarios.php">
                <div class="sb-nav-link-icon"><i class="fas fa-users-cog"></i></div>
                Usuarios
            </a>

            <!-- Citas -->
            <a class="nav-link" href="gestion_citas.php">
                <div class="sb-nav-link-icon"><i class="fas fa-calendar-check"></i></div>
                Citas
            </a>

            <!-- Clientes -->
            <a class="nav-link" href="gestion_clientes.php">
                <div class="sb-nav-link-icon"><i class="fas fa-user-friends"></i></div>
                Clientes
            </a>

            <!-- Contacto -->
            <a class="nav-link" href="gestionContacto.php">
                <div class="sb-nav-link-icon"><i class="fas fa-envelope-open-text"></i></div>
                Contacto
            </a>

            <!-- Promociones -->
            <a class="nav-link" href="promociones_interno.php">
                <div class="sb-nav-link-icon"><i class="fas fa-tags"></i></div>
                Promociones
            </a>

            <!-- Reportes -->
            <a class="nav-link" href="reportes.php">
                <div class="sb-nav-link-icon"><i class="fas fa-file-chart-line"></i></div>
                Reportes
            </a>

                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Sesion Inciada como:</div>
                    Administrador
                </div>
            </nav>

<script>
    // Control del toggle
    document.getElementById('sidebarToggle')?.addEventListener('click', function(e) {
        e.preventDefault();
        document.body.classList.toggle('sb-sidenav-toggled');
        localStorage.setItem('sb-sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
    });

    // Estado inicial
    if (localStorage.getItem('sb-sidebar-toggle') === 'true') {
        document.body.classList.add('sb-sidenav-toggled');
    }

    // Asegurar clases necesarias
    document.body.classList.add('sb-nav-fixed');
</script>