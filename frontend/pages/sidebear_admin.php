<nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
    <div class="sb-sidenav-menu">
        <div class="nav">
            <div class="sb-sidenav-menu-heading">Gestión</div>
            <a class="nav-link" href="dashboard.php">
                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                Dashboard
            </a>
            <a class="nav-link" href="balances.php">
                <div class="sb-nav-link-icon"><i class="fas fa-balance-scale"></i></div>
                Balances
            </a>
            <a class="nav-link" href="gestionCitas.php">
                <div class="sb-nav-link-icon"><i class="fas fa-coins"></i></div>
                citas
            </a>
            <a class="nav-link" href="gestionLubricentro.php">
                <div class="sb-nav-link-icon"><i class="fas fa-oil-can"></i></div>
                Lubricentros
            </a>
            <a class="nav-link" href="gestionPrestamos.php">
                <div class="sb-nav-link-icon"><i class="fas fa-hand-holding-usd"></i></div>
                Prestamos
            </a>
            <a class="nav-link" href="recaudos.php">
                <div class="sb-nav-link-icon"><i class="fas fa-money-check-alt"></i></div>
                Recaudos
            </a>
            <a class="nav-link" href="reportes.php">
                <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                Reportes
            </a>
            <a class="nav-link" href="Inicio - LA ROSA NÁUTICA.html">
                <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                Inicio - LA ROSA NÁUTICA
            </a>
        </div>frontend/pages/Inicio - LA ROSA NÁUTICA.html
    </div>
    <div class="sb-sidenav-footer">
        <div class="small">Sesion Inciada como:</div>
        Administrador
    </div>
</nav>
index.html
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