const API_URL =
  "http://localhost/cusquena/backend/api/controllers/gestionUsuario.php";

document.addEventListener("DOMContentLoaded", function () {
  listarUsuarios();

  // Buscar usuario
  document.querySelector(".btn-primary").addEventListener("click", function () {
    const termino = document.querySelector(
      'input[placeholder="Buscar usuario"]'
    ).value;
    listarUsuarios(termino);
  });

  // Agregar usuario
  document
    .querySelector("#modalAgregar form")
    .addEventListener("submit", function (e) {
      e.preventDefault();

      const usuario = document.getElementById("usuario").value.trim();
      const contrasena = document.getElementById("contrasena").value.trim();
      const correo = document.getElementById("correo").value.trim();
      const rol = document.getElementById("rol").value;
      const estado = document.querySelector(
        'input[name="estado"]:checked'
      )?.value;

      // Validación de correo
      if (!validarCorreo(correo)) {
        showToastEditar(
          "El correo electrónico no tiene un formato válido",
          "warning"
        );
        document.getElementById("correo").focus();
        return;
      }

      // Validación de campos obligatorios
      if (!usuario || !contrasena || !correo || !rol || !estado) {
        showToastEditar("Todos los campos son obligatorios", "warning");
        return;
      }

      const data = { usuario, contrasena, correo, rol, estado };

      fetch(API_URL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data),
      })
        .then((res) => res.json())
        .then((res) => {
          showToastAgregar(res.message, res.type);

          if (res.type == "success") {
            listarUsuarios();
            document.querySelector("#modalAgregar .btn-close").click();
            document.querySelector("#modalAgregar form").reset();
          } else if (res.type == "warning") {
            document.getElementById("correo").focus();
          }
        });
    });

  // Editar usuario
  document
    .querySelector("#modalEditar form")
    .addEventListener("submit", function (e) {
      e.preventDefault();
      const id = document.getElementById("editarId").value;
      const usuario = document.getElementById("editarUsuario").value.trim();
      const contrasena = document
        .getElementById("editarContrasena")
        .value.trim();
      const correo = document.getElementById("editarCorreo").value.trim();
      const rol = document.getElementById("editarRol").value;
      const estado = document.querySelector(
        'input[name="editarEstado"]:checked'
      )?.value;

      // Validación de correo
      if (!validarCorreo(correo)) {
        showToastEditar(
          "El correo electrónico no tiene un formato válido",
          "warning"
        );
        document.getElementById("correo").focus();
        return;
      }

      // Validación de campos obligatorios
      if (!usuario || !contrasena || !correo || !rol || !estado) {
        showToastEditar("Todos los campos son obligatorios", "warning");
        return;
      }

      const data = { id, usuario, contrasena, correo, rol, estado };

      fetch(API_URL, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data),
      })
        .then((res) => res.json())
        .then((res) => {
          showToastAgregar(res.message, res.type);

          if (res.type == "success") {
            listarUsuarios();
            document.querySelector("#modalEditar .btn-close").click();
            document.querySelector("#modalEditar form").reset();
          } else if (res.type == "warning") {
            document.getElementById("correo").focus();
          }
        });
    });
});

// Función para listar usuarios
function listarUsuarios(buscar = "") {
  const tbody = document.querySelector("tbody");
  tbody.innerHTML = "";

  const url = buscar
    ? `${API_URL}?buscar=${encodeURIComponent(buscar)}`
    : API_URL;

  fetch(url)
    .then((res) => res.json())
    .then((data) => {
      data.forEach((usuario) => {
        const fila = document.createElement("tr");

        const contrasenaOculta = usuario.contrasena
          ? "*".repeat(usuario.contrasena.length)
          : "";

        fila.innerHTML = `
              <td>${usuario.id}</td>
              <td>${usuario.usuario}</td>
              <td>${contrasenaOculta}</td>
              <td>${usuario.correo}</td>
              <td>${usuario.rol}</td>
              <td><span class="badge ${
                usuario.estado === "activo" ? "bg-success" : "bg-danger"
              }">${usuario.estado}</span></td>
              <td>
                <button class="btn btn-success btn-sm" onclick='llenarModalEditar(${JSON.stringify(
                  usuario
                )})' data-bs-toggle="modal" data-bs-target="#modalEditar">Editar</button>
                <button class="btn btn-danger btn-sm" onclick="confirmarEliminacion(${
                  usuario.id
                })">Eliminar</button>
              </td>
            `;
        tbody.appendChild(fila);
      });
    });
}

// Llenar modal de edición
function llenarModalEditar(usuario) {
  document.getElementById("editarId").value = usuario.id;
  document.getElementById("editarUsuario").value = usuario.usuario;
  document.getElementById("editarContrasena").value = usuario.contrasena;
  document.getElementById("editarCorreo").value = usuario.correo;
  document.getElementById("editarRol").value = usuario.rol;
  document.getElementById("editarEstadoActivo").checked =
    usuario.estado === "activo";
  document.getElementById("editarEstadoInactivo").checked =
    usuario.estado === "inactivo";
}

// Eliminar usuario
function eliminarUsuario(id) {
  fetch(API_URL, {
    method: "DELETE",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ id }),
  })
    .then((res) => res.json())
    .then((res) => {
      showToastEliminar(res.message);
      listarUsuarios();
    })
    .catch((error) => {
      console.error("Error al eliminar:", error);
    });
}
// toast message Agregar
function showToastAgregar(message, type = "success") {
  const toast = document.getElementById("toastAgregar");
  const toastMessage = document.getElementById("toastMessageAgregar");
  const toastHeader = document.getElementById("toastHeaderAgregar");
  const toastTitle = document.getElementById("toastTitleAgregar");

  toastHeader.className = "toast-header d-flex justify-content-between w-100";

  switch (type) {
    case "success":
      toastHeader.classList.add("bg-success", "text-white");
      toastTitle.textContent = "Registro Exitoso";
      break;
    case "warning":
      toastHeader.classList.add("bg-warning", "text-dark");
      toastTitle.textContent = "Advertencia";
      break;
    case "error":
    case "danger":
      toast.classList.add("text-bg-danger");
      break;
  }

  toastMessage.textContent = message;

  const bsToast = bootstrap.Toast.getOrCreateInstance(toast);
  bsToast.show();
}

// toast message Editar
function showToastEditar(message, type = "warning") {
  const toast = document.getElementById("toastEditar");
  const toastMessage = document.getElementById("toastMessageEditar");
  const toastHeader = document.getElementById("toastHeaderEditar");
  const toastTitle = document.getElementById("toastTitleEditar");

  toastHeader.className = "toast-header d-flex justify-content-between w-100";

  switch (type) {
    case "success":
      toastHeader.classList.add("bg-success", "text-white");
      toastTitle.textContent = "Actualización Exitosa";
      break;
    case "warning":
      toastHeader.classList.add("bg-warning", "text-dark");
      toastTitle.textContent = "Advertencia";
      break;
    case "error":
    case "danger":
      toast.classList.add("text-bg-danger");
      break;
  }

  toastMessage.textContent = message;

  const bsToast = bootstrap.Toast.getOrCreateInstance(toast);
  bsToast.show();
}

// toast message Eliminar
function showToastEliminar(message) {
  const toastElement = document.getElementById("toastEliminar");
  const toastMessage = document.getElementById("toastMessageEliminar");

  toastMessage.textContent = message;

  const toast = new bootstrap.Toast(toastElement);
  toast.show();
}

// ventana modal de confirmacion
function confirmarEliminacion(id) {
  const modalElement = document.getElementById("modalEliminarConfirmacion");
  const modal = new bootstrap.Modal(modalElement);
  modal.show();

  const btnConfirmar = document.getElementById("btnConfirmarEliminar");

  const nuevoBoton = btnConfirmar.cloneNode(true);
  btnConfirmar.parentNode.replaceChild(nuevoBoton, btnConfirmar);

  nuevoBoton.addEventListener("click", () => {
    eliminarUsuario(id);
    modal.hide();
  });
}
// validar correo
function validarCorreo(correo) {
  const regexCorreo = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  return regexCorreo.test(correo);
}

