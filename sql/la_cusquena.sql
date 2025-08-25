-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
-- Host: 127.0.0.1
-- Generation Time: May 07, 2025 at 05:35 PM (aproximado, basado en el primer script)
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12 (aproximado, basado en el primer script)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: la_cusquena
--

-- --------------------------------------------------------

--
-- Table structure for table alquiler
--

CREATE TABLE alquiler (
  idAlquiler int(11) NOT NULL,
  identificador varchar(30) DEFAULT NULL,
  nombre varchar(30) DEFAULT NULL,
  telefono varchar(10) DEFAULT NULL,
  tipo enum('local','cochera') DEFAULT NULL,
  fechaInicio date DEFAULT NULL,
  periodicidad enum('mensual','semanal','diario') DEFAULT NULL,
  pago decimal(10,2) DEFAULT NULL,
  ubicacion varchar(30) DEFAULT NULL,
  estado enum('activo','inactivo') NOT NULL,
  idSoat int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table categoria
--

CREATE TABLE categoria (
  idCategoria int(11) NOT NULL,
  descripcion varchar(100) DEFAULT NULL,
  estado enum('activo','inactivo') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table conductor
--

CREATE TABLE conductor (
  idConductor int(11) NOT NULL,
  nombre varchar(25) DEFAULT NULL,
  apellido varchar(25) DEFAULT NULL,
  tipoLicencia varchar(10) DEFAULT NULL,
  telefono varchar(10) DEFAULT NULL,
  direccion varchar(50) DEFAULT NULL,
  dni varchar(8) DEFAULT NULL,
  placa varchar(15) DEFAULT NULL,
  correo varchar(50) DEFAULT NULL,
  usuario varchar(25) DEFAULT NULL,
  clave varchar(255) DEFAULT NULL,
  estado enum('activo','inactivo') NOT NULL,
  detalle text DEFAULT NULL,
  idTipoConductor varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table cotizacion
--

CREATE TABLE cotizacion (
  idCotizacion int(11) NOT NULL,
  fechaCotizacion date DEFAULT NULL,
  subtotalProducto decimal(10,2) DEFAULT NULL,
  subtotalServicio decimal(10,2) DEFAULT NULL,
  total decimal(10,2) DEFAULT NULL,
  detalle text DEFAULT NULL COMMENT 'Cambiado a descripcion',
  nombre varchar(30) DEFAULT NULL,
  apellido varchar(100) NOT NULL,
  cotizacion varchar(100) NOT NULL,
  placa varchar(100) NOT NULL,
  idSoat int(11) DEFAULT NULL,
  idConductor int(11) DEFAULT NULL,
  idTipoConductor varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table gastos
--

CREATE TABLE gastos (
  id int(11) NOT NULL,
  descripcion varchar(255) NOT NULL,
  tipo enum('Empresa','Lubricentro') NOT NULL,
  monto decimal(10,2) NOT NULL,
  fecha date NOT NULL,
  detalle text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table historico_cambios
--

CREATE TABLE historico_cambios (
  id int(11) NOT NULL,
  usuario varchar(255) DEFAULT NULL,
  accion varchar(255) DEFAULT NULL,
  fecha datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table historico_cambios
--

INSERT INTO historico_cambios (id, usuario, accion, fecha) VALUES
(1, 'gian', 'Cambio de contraseña', '2025-04-14 12:32:54');

-- --------------------------------------------------------

--
-- Table structure for table producto
--

CREATE TABLE producto (
  idProducto int(11) NOT NULL,
  descripcion text NOT NULL,
  precioCompra decimal(10,2) NOT NULL,
  precioVenta decimal(10,2) NOT NULL,
  stock int(11) NOT NULL DEFAULT 0,
  idCategoria int(11) NOT NULL,
  presentacion text DEFAULT NULL,
  estado enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table ingresoproducto
--

CREATE TABLE `ingresoproducto` (
  idIngresoProducto int(11) NOT NULL,
  fechaIngreso date DEFAULT NULL,
  stock int(11) NOT NULL DEFAULT 0,
  precioCompra decimal(10,2) NOT NULL,
  idProducto int(11) DEFAULT NULL,
  detalle text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Table structure for table pwdreset
--

CREATE TABLE pwdreset (
  pwdResetId int(11) NOT NULL,
  pwdResetEmail text DEFAULT NULL,
  pwdResetSelector text DEFAULT NULL,
  pwdResetToken longtext DEFAULT NULL,
  pwdResetExpires datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table pwdreset
--

INSERT INTO pwdreset (pwdResetId, pwdResetEmail, pwdResetSelector, pwdResetToken, pwdResetExpires) VALUES
(1, 'gian94728@gmail.com', 'a0d5faf2008593dc', '$2y$10$V6rBekzOJNwZYesv54OlfeTg/zsWOR2CSmT7nsVlDUdVqrNcfXOyW', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table seguroplanilla
--

CREATE TABLE seguroplanilla (
  idSeguroPlanilla int(11) NOT NULL,
  socio varchar(30) DEFAULT NULL,
  montoTotal decimal(10,2) DEFAULT NULL,
  totalPagado decimal(10,2) DEFAULT NULL,
  pagoPendiente decimal(10,2) DEFAULT NULL,
  fechaEmision date DEFAULT NULL,
  fechaVencimiento date DEFAULT NULL,
  estado enum('Pendiente','Completo','activo','inactivo') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table servicio
--

CREATE TABLE servicio (
  idServicio int(11) NOT NULL,
  descripcion varchar(45) DEFAULT NULL,
  precioUnitario decimal(10,2) DEFAULT NULL,
  estado enum('activo','inactivo') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table soat
--

CREATE TABLE soat (
  idSoat int(11) NOT NULL,
  nombre varchar(30) DEFAULT NULL,
  fechaMantenimiento date DEFAULT NULL,
  fechaProxMantenimiento date DEFAULT NULL,
  estado enum('activo','inactivo') NOT NULL,
  idConductor int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table tipo_de_conductor
--

CREATE TABLE tipo_de_conductor (
  idTipoConductor varchar(100) NOT NULL,
  tipo_paga enum('diario','semanal') NOT NULL,
  monto_paga decimal(10,2) NOT NULL,
  descripcion varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table tipo_de_conductor
--

INSERT INTO tipo_de_conductor (idTipoConductor, tipo_paga, monto_paga, descripcion) VALUES
('afiliado', 'diario', 25.00, 'Pago diario fijo.'),
('alquiler', 'diario', 35.00, 'Alquiler diario de línea.'),
('contrato', 'diario', 25.00, 'Pago diario fijo.'),
('socio', 'semanal', 60.00, 'Cotización semanal entre 60 a 120. Además paga seguro mensual de 300.');

-- --------------------------------------------------------

--
-- Table structure for table usuarios
--

CREATE TABLE usuarios (
  id int(11) NOT NULL,
  usuario varchar(50) NOT NULL,
  contrasena varchar(255) NOT NULL,
  correo varchar(100) NOT NULL,
  rol enum('Administrador','Secretaria','admin','user') NOT NULL,
  estado enum('activo','inactivo') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table usuarios
--

INSERT INTO usuarios (id, usuario, contrasena, correo, rol, estado) VALUES
(1, 'sebasss', '$2y$10$NwAEYf8YyHJOApqpS.BFmOrxnFXnd/Bb7utEejB8wtdIctm.Bc6PK', 'sebastianth455@gmail.com', 'Administrador', 'activo'),
(7, 'gian', '$2y$10$5oc857xXlouwHmKAhmcdyuXEKAQ8ASOUZyQhnRzWED/aBqWcVO/4G', 'gian94728@gmail.com', 'admin', 'activo');

-- --------------------------------------------------------

--
-- Table structure for table ventaproducto
--

CREATE TABLE ventaproducto (
  idVentaProducto int(11) NOT NULL,
  descripcion varchar(255) NOT NULL,
  precioUnitario decimal(10,2) NOT NULL,
  cantidad int(11) NOT NULL,
  subtotal decimal(10,2) NOT NULL,
  fecha date NOT NULL,
  total decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table venta_servicio
--

CREATE TABLE venta_servicio (
  idVenta int(11) NOT NULL,
  idServicio int(11) NOT NULL,
  descripcion varchar(100) DEFAULT NULL,
  precioUnitario decimal(10,2) DEFAULT NULL,
  fechaVenta date DEFAULT NULL,
  total decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Indexes for dumped tables
--

--
-- Indexes for table alquiler
--
ALTER TABLE alquiler
  ADD PRIMARY KEY (idAlquiler),
  ADD KEY idSoat (idSoat);

--
-- Indexes for table categoria
--
ALTER TABLE categoria
  ADD PRIMARY KEY (idCategoria),
  ADD UNIQUE KEY descripcion (descripcion);

--
-- Indexes for table conductor
--
ALTER TABLE conductor
  ADD PRIMARY KEY (idConductor),
  ADD KEY fk_conductor_tipo (idTipoConductor);

--
-- Indexes for table cotizacion
--
ALTER TABLE cotizacion
  ADD PRIMARY KEY (idCotizacion),
  ADD KEY idSoat (idSoat),
  ADD KEY idConductor (idConductor),
  ADD KEY fk_cotizacion_tipo (idTipoConductor);


--
-- Indexes for table gastos
--
ALTER TABLE gastos
  ADD PRIMARY KEY (id);

--
-- Indexes for table historico_cambios
--
ALTER TABLE historico_cambios
  ADD PRIMARY KEY (id);

--
-- Indexes for table ingresoproducto
--
ALTER TABLE ingresoproducto
  ADD PRIMARY KEY (idIngresoProducto),
  ADD KEY idProducto (idProducto);

--
-- Indexes for table producto
--
ALTER TABLE producto
  ADD PRIMARY KEY (idProducto),
  ADD KEY idCategoria (idCategoria);

--
-- Indexes for table pwdreset
--
ALTER TABLE pwdreset
  ADD PRIMARY KEY (pwdResetId);

--
-- Indexes for table seguroplanilla
--
ALTER TABLE seguroplanilla
  ADD PRIMARY KEY (idSeguroPlanilla);

--
-- Indexes for table servicio
--
ALTER TABLE servicio
  ADD PRIMARY KEY (idServicio);

--
-- Indexes for table soat
--
ALTER TABLE soat
  ADD PRIMARY KEY (idSoat),
  ADD KEY idConductor (idConductor);

--
-- Indexes for table tipo_de_conductor
--
ALTER TABLE tipo_de_conductor
  ADD PRIMARY KEY (idTipoConductor);

--
-- Indexes for table usuarios
--
ALTER TABLE usuarios
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY usuario (usuario),
  ADD UNIQUE KEY correo (correo);

--
-- Indexes for table ventaproducto
--
ALTER TABLE ventaproducto
  ADD PRIMARY KEY (idVentaProducto);
--
-- Indexes for table venta_servicio
--
ALTER TABLE venta_servicio
  ADD PRIMARY KEY (idVenta),
  ADD KEY idServicio (idServicio);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table alquiler
--
ALTER TABLE alquiler
  MODIFY idAlquiler int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table categoria
--
ALTER TABLE categoria
  MODIFY idCategoria int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table conductor
--
ALTER TABLE conductor
  MODIFY idConductor int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table cotizacion
--
ALTER TABLE cotizacion
  MODIFY idCotizacion int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table gastos
--
ALTER TABLE gastos
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table historico_cambios
--
ALTER TABLE historico_cambios
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table ingresoproducto
--
ALTER TABLE ingresoproducto
  MODIFY idIngresoProducto int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table producto
--
ALTER TABLE producto
  MODIFY idProducto int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table pwdreset
--
ALTER TABLE pwdreset
  MODIFY pwdResetId int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table seguroplanilla
--
ALTER TABLE seguroplanilla
  MODIFY idSeguroPlanilla int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table servicio
--
ALTER TABLE servicio
  MODIFY idServicio int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table soat
--
ALTER TABLE soat
  MODIFY idSoat int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table usuarios
--
ALTER TABLE usuarios
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table ventaproducto
--
ALTER TABLE ventaproducto
  MODIFY idVentaProducto int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table venta_servicio
--
ALTER TABLE venta_servicio
  MODIFY idVenta int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table alquiler
--
ALTER TABLE alquiler
  ADD CONSTRAINT alquiler_ibfk_1 FOREIGN KEY (idSoat) REFERENCES soat (idSoat);

--
-- Constraints for table conductor
--
ALTER TABLE conductor
  ADD CONSTRAINT fk_conductor_tipo FOREIGN KEY (idTipoConductor) REFERENCES tipo_de_conductor (idTipoConductor);

--
-- Constraints for table cotizacion
--
ALTER TABLE cotizacion
  ADD CONSTRAINT cotizacion_ibfk_1 FOREIGN KEY (idSoat) REFERENCES soat (idSoat),
  ADD CONSTRAINT cotizacion_ibfk_2 FOREIGN KEY (idConductor) REFERENCES conductor (idConductor),
  ADD CONSTRAINT fk_cotizacion_tipo FOREIGN KEY (idTipoConductor) REFERENCES tipo_de_conductor(idTipoConductor);

--
-- Constraints for table ingresoproducto
--
ALTER TABLE ingresoproducto
  ADD CONSTRAINT ingresoproducto_ibfk_1 FOREIGN KEY (idProducto) REFERENCES producto (idProducto);


--
-- Constraints for table producto
--
ALTER TABLE producto
  ADD CONSTRAINT producto_ibfk_1 FOREIGN KEY (idCategoria) REFERENCES categoria (idCategoria);

--
-- Constraints for table soat
--
ALTER TABLE soat
  ADD CONSTRAINT soat_ibfk_1 FOREIGN KEY (idConductor) REFERENCES conductor (idConductor);

--
-- Constraints for table venta_servicio
--
ALTER TABLE venta_servicio
  ADD CONSTRAINT venta_servicio_ibfk_1 FOREIGN KEY (idServicio) REFERENCES servicio (idServicio);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
