<nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
    <div class="sb-sidenav-menu">
        <div class="nav">
            <div class="sb-sidenav-menu-heading">Gestión</div>
            <a class="nav-link" href="usuarios.php">
                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                Usuarios
            </a>
            <a class="nav-link" href="gestioncitas.php">
                <div class="sb-nav-link-icon"><i class="fas fa-balance-scale"></i></div>
                Reservas
            </a>
           
            <a class="nav-link" href="gestion_citas.php   ">
                <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                citas
            </a>
             <a class="nav-link" href="gestion_clientes.php   ">
                <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                clientes
            </a>
        </div>
    </div>
    <div class="sb-sidenav-footer">
        <div class="small">Sesion Inciada como:</div>
        administrador
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