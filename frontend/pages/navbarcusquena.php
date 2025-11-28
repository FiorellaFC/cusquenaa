<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                <li class="nav-item"><a class="nav-link" href="vistaCusquena.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="vistaServicios.php">Servicios</a></li>
                <li class="nav-item"><a class="nav-link" href="vistaPromociones.php">Promociones</a></li>
                <li class="nav-item"><a class="nav-link" href="vistaContacto.php">Contacto</a></li>

                <?php if (isset($_SESSION['cliente_id'])): ?>
                    <li class="nav-item dropdown ms-lg-3">
                        <a class="nav-link dropdown-toggle fw-bold text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1" style="color: #d4af37;"></i> 
                            <?php echo htmlspecialchars($_SESSION['cliente_nombre']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                            <li><a class="dropdown-item" href="perfil_cliente.php">Mi Perfil</a></li>
                            <li><a class="dropdown-item" href="mis_citas.php">Mis Citas</a></li>
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

<script>
document.addEventListener('DOMContentLoaded', () => {

    // Configuración base de SweetAlert
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    // Ruta Base API
    const BASE_API = '../../backend/api/controllers/loginclientes/';

    // --- LÓGICA DE REGISTRO ---
    const formRegistro = document.getElementById('formRegistro');
    if (formRegistro) {
        formRegistro.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = formRegistro.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Enviando...';
            btn.disabled = true;

            const formData = new FormData(formRegistro);
            const data = Object.fromEntries(formData);

            try {
                const res = await fetch(BASE_API + 'registro.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(data)
                });
                const result = await res.json();

                if (result.success) {
                    // ÉXITO: Modal SweetAlert
                    Swal.fire({
                        icon: 'success',
                        title: '¡Registro Exitoso!',
                        text: result.message,
                        confirmButtonColor: '#d4af37'
                    });
                    formRegistro.reset();
                    const modalEl = document.getElementById('authModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    modal.hide();
                } else {
                    // ERROR: Toast
                    Toast.fire({ icon: 'error', title: result.error });
                }
            } catch (err) {
                console.error(err);
                Toast.fire({ icon: 'error', title: 'Error de conexión' });
            } finally {
                btn.textContent = originalText;
                btn.disabled = false;
            }
        });
    }

    // --- LÓGICA DE LOGIN ---
    const formLogin = document.getElementById('formLogin');
    if (formLogin) {
        formLogin.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = formLogin.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            btn.disabled = true;

            const formData = new FormData(formLogin);
            const data = Object.fromEntries(formData);

            try {
                const res = await fetch(BASE_API + 'login.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(data)
                });
                const result = await res.json();

                if (result.success) {
                    location.reload(); // Recargar para ver el usuario logueado
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Acceso',
                        text: result.error,
                        confirmButtonColor: '#d4af37'
                    });
                }
            } catch (err) {
                console.error(err);
                Toast.fire({ icon: 'error', title: 'Error de conexión' });
            } finally {
                btn.textContent = originalText;
                btn.disabled = false;
            }
        });
    }

    // --- LÓGICA DE LOGOUT ---
    const btnLogout = document.getElementById('btn-logout');
    if (btnLogout) {
        btnLogout.addEventListener('click', (e) => {
            e.preventDefault();
            Swal.fire({
                title: '¿Cerrar sesión?',
                text: "¿Estás seguro que deseas salir?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d4af37',
                cancelButtonColor: '#333',
                confirmButtonText: 'Sí, salir'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = BASE_API + 'logout.php';
                }
            })
        });
    }
});
</script>