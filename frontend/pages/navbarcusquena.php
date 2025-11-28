<style>
    /* Navbar Transparente/Oscuro */
    .navbar-custom {
        background-color: rgba(26, 27, 30, 0.95) !important;
        backdrop-filter: blur(5px);
        border-bottom: 1px solid #333;
    }
    
    /* Botones y Links */
    .nav-link { color: #ccc !important; transition: 0.3s; }
    .nav-link:hover, .nav-link.active { color: #d4af37 !important; }
    
    .btn-login-nav {
        border: 1px solid #d4af37;
        color: #d4af37;
        border-radius: 50px;
        padding: 5px 20px;
        transition: 0.3s;
    }
    .btn-login-nav:hover {
        background-color: #d4af37;
        color: #000;
    }

    /* Modal Estilos */
    .nav-pills .nav-link.active { background-color: #d4af37 !important; color: black !important; }
    .nav-pills .nav-link { color: #ccc; }
    
    /* Dropdown Usuario */
    .dropdown-menu-dark { border-color: #444; }
    .dropdown-item:hover { background-color: #d4af37; color: #000 !important; }
</style>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top navbar-custom">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php" style="color: #d4af37; letter-spacing: 1px;">
            Lubricentro La Cusqueña
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="vistaServicios.html">Servicios</a></li>
                <li class="nav-item"><a class="nav-link" href="vistaPromociones.html">Promociones</a></li>
                <li class="nav-item"><a class="nav-link" href="vistaContacto.html">Contacto</a></li>

                <?php if (isset($_SESSION['cliente_id'])): ?>
                    <li class="nav-item dropdown ms-lg-3">
                        <a class="nav-link dropdown-toggle fw-bold text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1" style="color: #d4af37;"></i> 
                            <?php echo htmlspecialchars($_SESSION['cliente_nombre']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                            <li><a class="dropdown-item" href="#">Mi Perfil</a></li>
                            <li><a class="dropdown-item" href="#">Mis Citas</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" id="btn-logout">Cerrar Sesión</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-lg-3">
                        <button class="btn btn-login-nav btn-sm" data-bs-toggle="modal" data-bs-target="#authModal">
                            <i class="fas fa-sign-in-alt me-2"></i> Iniciar Sesión
                        </button>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="modal fade" id="authModal" tabindex="-1" aria-hidden="true" style="z-index: 10000;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: 1px solid #d4af37;">
            
            <div class="modal-header bg-dark text-white border-bottom-0" style="display: block;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="modal-title fw-bold" style="color: #d4af37;">Bienvenido</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <ul class="nav nav-pills nav-fill" id="authTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-panel" type="button" style="border-radius: 20px;">INGRESAR</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" id="register-tab" data-bs-toggle="tab" data-bs-target="#register-panel" type="button" style="border-radius: 20px;">REGISTRARSE</button>
                    </li>
                </ul>
            </div>

            <div class="modal-body p-4">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="login-panel">
                        <form id="formLogin">
                            <div class="mb-3">
                                <label class="form-label">Correo electrónico</label>
                                <input type="email" name="login_email" class="form-control" required placeholder="tu@email.com">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contraseña</label>
                                <input type="password" name="login_password" class="form-control" required placeholder="******">
                            </div>
                            <button type="submit" class="btn btn-dark w-100 fw-bold" style="border: 1px solid #d4af37;">INICIAR SESIÓN</button>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="register-panel">
                        <form id="formRegistro">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label small fw-bold">Nombres</label>
                                    <input type="text" name="reg_nombre" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label small fw-bold">Apellidos</label>
                                    <input type="text" name="reg_apellido" class="form-control form-control-sm" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label small fw-bold">DNI / RUC</label>
                                    <input type="text" name="reg_dni" class="form-control form-control-sm" required maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label small fw-bold">Teléfono</label>
                                    <input type="tel" name="reg_telefono" class="form-control form-control-sm" required maxlength="9" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Correo electrónico</label>
                                <input type="email" name="reg_email" class="form-control form-control-sm" required>
                            </div>
                            <button type="submit" class="btn btn-outline-dark w-100 fw-bold" style="border: 2px solid #198754; color: #198754;">REGISTRARME</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>