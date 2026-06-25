-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-06-2026 a las 23:22:33
-- Versión del servidor: 10.1.35-MariaDB
-- Versión de PHP: 7.2.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cpt3db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alergias_conocidas`
--

CREATE TABLE `alergias_conocidas` (
  `Id_alergias_conocidas` int(11) NOT NULL,
  `nombre_alergia` varchar(35) NOT NULL,
  `estatus` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `alergias_conocidas`
--

INSERT INTO `alergias_conocidas` (`Id_alergias_conocidas`, `nombre_alergia`, `estatus`) VALUES
(6, 'Alergia al Mani', 1),
(7, 'Rinitis', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `antecedentes_familiares`
--

CREATE TABLE `antecedentes_familiares` (
  `Id` int(11) NOT NULL,
  `descripcion` varchar(150) COLLATE utf8_spanish_ci DEFAULT NULL,
  `estatus` enum('1','2') COLLATE utf8_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `antecedentes_familiares`
--

INSERT INTO `antecedentes_familiares` (`Id`, `descripcion`, `estatus`) VALUES
(18, 'a', '1'),
(19, 's', '1'),
(20, 'B', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `antecedentes_perinatales`
--

CREATE TABLE `antecedentes_perinatales` (
  `Id` int(11) NOT NULL,
  `descripcion` varchar(150) COLLATE utf8_spanish_ci DEFAULT NULL,
  `estatus` enum('1','2') COLLATE utf8_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `antecedentes_perinatales`
--

INSERT INTO `antecedentes_perinatales` (`Id`, `descripcion`, `estatus`) VALUES
(18, 's', '1'),
(19, 'A', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `antecedentes_sexuales_reproductivos`
--

CREATE TABLE `antecedentes_sexuales_reproductivos` (
  `Id` int(11) NOT NULL,
  `descripcion` varchar(150) COLLATE utf8_spanish_ci DEFAULT NULL,
  `estatus` enum('1','2') COLLATE utf8_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `antecedentes_sexuales_reproductivos`
--

INSERT INTO `antecedentes_sexuales_reproductivos` (`Id`, `descripcion`, `estatus`) VALUES
(16, 'b', '1'),
(17, 's', '1'),
(18, 'C', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id_cita` int(11) NOT NULL,
  `fecha_cita` date NOT NULL,
  `hora_cita` time NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `Id_paciente` int(11) NOT NULL,
  `Id_medico` int(11) NOT NULL,
  `Id_especialidad` int(11) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('Pendiente','Confirmada','Cancelada','Finalizada','Vencida','Inasistente','Reprogramada') DEFAULT 'Pendiente',
  `estatus` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consulta`
--

CREATE TABLE `consulta` (
  `Id_consulta` int(11) NOT NULL,
  `fecha_consulta` date NOT NULL,
  `motivo_consulta` text,
  `diagnostico` text,
  `tratamiento_indicado` text,
  `peso` decimal(5,2) DEFAULT NULL,
  `talla` decimal(5,2) DEFAULT NULL,
  `temperatura` int(11) DEFAULT NULL,
  `tension` int(11) DEFAULT NULL,
  `frecuencia_cardiaca` int(11) DEFAULT NULL,
  `saturacion` int(11) DEFAULT NULL,
  `frecuencia_respiratoria` int(11) DEFAULT NULL,
  `estado_paciente` varchar(45) DEFAULT NULL,
  `reaccion_adversa` varchar(45) DEFAULT NULL,
  `detalle_reaccion` varchar(45) DEFAULT NULL,
  `evolucion_resultado` varchar(100) DEFAULT NULL,
  `lectura_examenes` varchar(100) DEFAULT NULL,
  `examenes_solicitados` varchar(100) DEFAULT NULL,
  `entregado_a` varchar(100) DEFAULT NULL,
  `parentesco` varchar(25) DEFAULT NULL,
  `Id_historial` int(11) NOT NULL,
  `Id_medico` int(11) NOT NULL,
  `Id_paciente` int(11) NOT NULL,
  `estatus` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `consulta`
--

INSERT INTO `consulta` (`Id_consulta`, `fecha_consulta`, `motivo_consulta`, `diagnostico`, `tratamiento_indicado`, `peso`, `talla`, `temperatura`, `tension`, `frecuencia_cardiaca`, `saturacion`, `frecuencia_respiratoria`, `estado_paciente`, `reaccion_adversa`, `detalle_reaccion`, `evolucion_resultado`, `lectura_examenes`, `examenes_solicitados`, `entregado_a`, `parentesco`, `Id_historial`, `Id_medico`, `Id_paciente`, `estatus`) VALUES
(2, '2026-05-27', 'Algo', 'Dolor', 'Todas', '0.00', '0.00', 0, 0, 0, 0, 0, 'Primera Consulta', 'No', '', 'Paciente acude por primera vez. Se inicia protocolo.', '', '', 'Camilo Raul Montilla Perez', '', 86, 20, 328, 0),
(3, '2026-05-28', 'ALGO XX', 'QUIENS ABE', 'VERAG', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Primera Consulta', 'No', '', 'Paciente acude por primera vez. Se inicia protocolo.', '', '', 'sss sssd', '', 90, 20, 344, 0),
(5, '2026-06-13', 'MEXICO', 'DIOXIDO DE CARBONO', 'ALGo', '40.00', NULL, NULL, NULL, NULL, NULL, NULL, 'Primera Consulta', 'No', '', 'Paciente acude por primera vez. Se inicia protocolo.', '', '', 'Camilo Raul Montilla Perez', '', 86, 22, 328, 1),
(6, '2026-06-13', 'USA', 'QUE', 'XLR8', '20.00', NULL, NULL, NULL, NULL, NULL, NULL, 'Primera Consulta', 'No', '', 'Paciente acude por primera vez. Se inicia protocolo.', '', '', 'Mario Gomez', '', 87, 20, 331, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamento`
--

CREATE TABLE `departamento` (
  `Id_departamento` int(11) NOT NULL,
  `nombre_departamento` varchar(35) NOT NULL,
  `estatus` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `departamento`
--

INSERT INTO `departamento` (`Id_departamento`, `nombre_departamento`, `estatus`) VALUES
(1, 'Programa', 1),
(5, 'Farmacia', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `descripcion_medicamento`
--

CREATE TABLE `descripcion_medicamento` (
  `Id` int(11) NOT NULL,
  `via_aplicacion` varchar(25) COLLATE utf8_spanish_ci NOT NULL,
  `almacenamiento` varchar(45) COLLATE utf8_spanish_ci NOT NULL,
  `excipientes` varchar(100) COLLATE utf8_spanish_ci DEFAULT NULL,
  `stock_minimo` int(11) NOT NULL,
  `stock_maximo` int(11) NOT NULL,
  `codigo_barras` varchar(45) COLLATE utf8_spanish_ci NOT NULL,
  `contenido_neto` varchar(100) COLLATE utf8_spanish_ci NOT NULL,
  `cantidad_concentracion` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `Id_tipo_concentracion` int(11) NOT NULL,
  `Id_laboratorio` int(11) DEFAULT NULL,
  `Id_presentacion` int(11) NOT NULL,
  `Id_medicamento` int(11) NOT NULL,
  `estatus` enum('1','2') COLLATE utf8_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `descripcion_medicamento`
--

INSERT INTO `descripcion_medicamento` (`Id`, `via_aplicacion`, `almacenamiento`, `excipientes`, `stock_minimo`, `stock_maximo`, `codigo_barras`, `contenido_neto`, `cantidad_concentracion`, `Id_tipo_concentracion`, `Id_laboratorio`, `Id_presentacion`, `Id_medicamento`, `estatus`) VALUES
(81, 'Oral', '8_a_15', '', 1, 200, '27489824824742', '10 Tabletas', '10', 2, 2, 1, 94, '1'),
(82, 'Oral', '8_a_15', 'Fresa, Sal, Mantequilla', 1, 100, '234234235233', '10 Tabletas', '10', 2, 2, 1, 95, '1'),
(85, 'Oral', '8_a_15', '', 1, 200, '2748982482474', '10 Tabletas', '10', 2, 2, 1, 98, '1'),
(86, 'Oral', '8_a_15', '', 1, 100, '274898248247', '10 Tabletas', '10', 2, 2, 1, 99, '1'),
(87, 'Oral', '8_a_15', '', 0, 0, '2748982482475', '10 Tabletas', '10', 2, 2, 1, 100, '1'),
(88, 'Oral', '8_a_15', '', 1, 100, '23423423525', '10 Tabletas', '10', 2, 2, 1, 101, '1'),
(89, 'Oral', '8_a_15', '', 1, 20, '274898248245', '10 Tabletas', '10', 2, 2, 1, 102, '1'),
(90, 'Oral', '8_a_15', '', 15, 75, '274898248255', '10 Tabletas', '10', 2, 2, 1, 103, '1'),
(91, 'Oral', '8_a_15', '', 12, 204, '274898248200', '10 Tabletas', '10', 2, 2, 1, 104, '1'),
(92, 'Oral', '8_a_15', '', 10, 400, '274898248240', '10 Tabletas', '10', 2, 2, 1, 105, '1'),
(93, 'Oral', '8_a_15', '', 1, 100, '27489824827664', '10 Tabletas', '10', 2, 2, 1, 106, '1'),
(94, 'Oral', '8_a_15', '', 20, 30, '5325235236', '10 Tabletas', '10', 2, 2, 1, 107, '1'),
(95, 'Oral', '8_a_15', '', 1, 100, '274898263464', '10 Tabletas', '10', 2, 2, 1, 108, '1'),
(96, 'Oral', '8_a_15', '', 1, 200, '27489824', '10 Tabletas', '10', 2, 2, 1, 109, '1'),
(97, 'Oral', '8_a_15', '', 1, 34, '892389278', '10 Tabletas', '10', 2, 2, 1, 110, '1'),
(98, 'Oral', '8_a_15', '', 1, 340, '27489824825353', '10 Tabletas', '10', 2, 2, 1, 111, '1'),
(99, 'Oral', '8_a_15', '', 20, 200, '274898248634341', '10 Tabletas', '10', 2, 2, 1, 112, '1'),
(100, 'Oral', '8_a_15', '', 200, 300, '892389278433', '10 Tabletas', '10', 2, 2, 1, 113, '1'),
(101, 'Oral', '8_a_15', '', 1, 50, '89238927844', '10 Tabletas', '10', 2, 2, 1, 114, '1'),
(102, 'Oral', '8_a_15', '', 1, 100, '23423423053035', '10 Tabletas', '10', 2, 2, 1, 115, '1'),
(103, 'Oral', '8_a_15', '', 21, 422, '274898248243553', '10 Tabletas', '10', 2, 2, 1, 116, '1'),
(104, 'Oral', '8_a_15', '', 1, 20, '892389278437575', '10 Tabletas', '10', 2, 2, 1, 117, '1'),
(105, 'Oral', '8_a_15', '', 1, 500, '8923892777', '10 Tabletas', '10', 2, 2, 1, 118, '1'),
(106, 'Oral', '8_a_15', '', 2, 199, '27489824826464', '10 Tabletas', '10', 2, 2, 1, 119, '1'),
(107, 'Oral', '8_a_15', '', 2, 100, '2748982486464', '10 Tabletas', '10', 2, 2, 1, 120, '1'),
(108, 'Oral', '8_a_15', '', 1, 500, '666666666', '10 Tabletas', '10', 2, 2, 1, 121, '1'),
(109, 'Oral', '8_a_15', '', 2, 30, '274898248225252', '10 Tabletas', '10', 2, 2, 1, 122, '1'),
(110, 'Oral', '8_a_15', '', 2, 50, '2748982485335', '10 Tabletas', '10', 2, 2, 1, 123, '1'),
(111, 'Oral', '8_a_15', '', 1, 20, '8923892785353', '10 Tabletas', '10', 2, 2, 1, 124, '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_inventario`
--

CREATE TABLE `detalle_inventario` (
  `Id_detalle_inventario` int(11) NOT NULL,
  `Id_TipoMovimiento` int(11) NOT NULL,
  `Id_Persona` int(11) NOT NULL,
  `Id_receptor` int(11) DEFAULT NULL,
  `Id_prescripcion` int(11) DEFAULT NULL,
  `comprobante` text,
  `fecha` datetime NOT NULL,
  `observaciones` varchar(255) NOT NULL,
  `estado_movimiento` enum('Activo','Anulado') NOT NULL DEFAULT 'Activo',
  `fecha_registro` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `detalle_inventario`
--

INSERT INTO `detalle_inventario` (`Id_detalle_inventario`, `Id_TipoMovimiento`, `Id_Persona`, `Id_receptor`, `Id_prescripcion`, `comprobante`, `fecha`, `observaciones`, `estado_movimiento`, `fecha_registro`) VALUES
(2, 1, 189, 189, NULL, NULL, '2026-06-17 17:33:00', 'Algo', 'Activo', '0000-00-00 00:00:00'),
(3, 2, 189, NULL, NULL, NULL, '2026-06-17 17:43:51', 'Despacho a paciente externo: Deyber Deinner Silva Gallardo', 'Activo', '0000-00-00 00:00:00'),
(4, 2, 189, NULL, NULL, NULL, '2026-06-17 20:27:05', 'Despacho a paciente externo: Deyber Deinner Silva Gallardo', 'Activo', '0000-00-00 00:00:00'),
(5, 2, 189, NULL, NULL, NULL, '2026-06-17 20:28:22', 'Despacho a paciente externo: Deyber Deinner Silva Gallardo', 'Activo', '0000-00-00 00:00:00'),
(6, 2, 189, NULL, NULL, NULL, '2026-06-17 20:50:57', 'Despacho a paciente externo: Deyber Deinner Silva Gallardo', 'Activo', '0000-00-00 00:00:00'),
(7, 2, 189, NULL, NULL, NULL, '2026-06-17 20:51:24', 'Despacho a paciente externo: Deyber Deinner Silva Gallardo', 'Activo', '0000-00-00 00:00:00'),
(8, 7, 189, 189, NULL, NULL, '2026-06-18 03:49:00', 'Algo', 'Activo', '0000-00-00 00:00:00'),
(9, 2, 281, NULL, NULL, NULL, '2026-06-23 17:08:37', 'Despacho a paciente externo: Ezequiel Veroez', 'Activo', '0000-00-00 00:00:00'),
(10, 2, 189, NULL, NULL, NULL, '2026-06-25 11:56:23', 'Despacho a paciente externo: Ezequiel Veroez', 'Anulado', '0000-00-00 00:00:00'),
(11, 9, 189, NULL, NULL, NULL, '2026-06-25 17:19:51', 'ANULACIÓN DE MOV. #10 | Motivo: Error de registro detectado por administrador', 'Activo', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_medico`
--

CREATE TABLE `detalle_medico` (
  `Id_detalle_medico` int(11) NOT NULL,
  `cod_colegiatura` int(7) NOT NULL,
  `fecha_ingreso` date NOT NULL,
  `tipo_medico` enum('Interno','Externo') NOT NULL DEFAULT 'Interno',
  `Id_persona` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `detalle_medico`
--

INSERT INTO `detalle_medico` (`Id_detalle_medico`, `cod_colegiatura`, `fecha_ingreso`, `tipo_medico`, `Id_persona`) VALUES
(20, 8348234, '2026-04-28', 'Interno', 340),
(21, 0, '2026-05-14', 'Externo', 341),
(22, 0, '2026-05-24', 'Interno', 351),
(23, 0, '2026-06-06', 'Externo', 352),
(24, 0, '2026-06-06', 'Externo', 353),
(25, 0, '2026-06-13', 'Externo', 355),
(26, 0, '2026-06-13', 'Externo', 356),
(27, 1374280, '2026-06-14', 'Interno', 359),
(28, 0, '2026-06-15', 'Externo', 362),
(29, 0, '2026-06-23', 'Externo', 364),
(30, 0, '2026-06-23', 'Externo', 366);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_paciente`
--

CREATE TABLE `detalle_paciente` (
  `Id_detalle_paciente` int(11) NOT NULL,
  `situacion_conyugal` varchar(35) DEFAULT NULL,
  `etnia` enum('Si','No') NOT NULL,
  `tipo_etnia` varchar(35) DEFAULT NULL,
  `analfabeta` enum('Si','No') DEFAULT NULL,
  `seguro_social` enum('Si','No') DEFAULT NULL,
  `profesion` varchar(35) DEFAULT NULL,
  `ocupacion` varchar(45) DEFAULT NULL,
  `nivel_instruccion` varchar(35) DEFAULT NULL,
  `mision` varchar(35) DEFAULT NULL,
  `años_aprobados` int(11) DEFAULT NULL,
  `discapacidad` enum('Si','No') DEFAULT NULL,
  `tipo_discapacidad` varchar(45) DEFAULT NULL,
  `tipo_paciente` enum('Interno','Externo') NOT NULL DEFAULT 'Interno',
  `id_persona` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `detalle_paciente`
--

INSERT INTO `detalle_paciente` (`Id_detalle_paciente`, `situacion_conyugal`, `etnia`, `tipo_etnia`, `analfabeta`, `seguro_social`, `profesion`, `ocupacion`, `nivel_instruccion`, `mision`, `años_aprobados`, `discapacidad`, `tipo_discapacidad`, `tipo_paciente`, `id_persona`) VALUES
(69, '', 'No', '', 'No', '', '', '', 'sin_instruccion', '', 0, 'No', '', 'Interno', 328),
(70, '', 'No', '', 'No', '', '', '', '', '', 0, 'No', '', 'Interno', 332),
(71, '', 'No', '', 'No', '', '', '', 'sin_instruccion', NULL, NULL, 'No', '', 'Externo', 354),
(72, '', 'No', '', 'Si', '', '', '', 'sin_instruccion', NULL, NULL, 'No', '', 'Externo', 360),
(73, 'Soltero', 'No', '', 'No', '', '', '', 'sin_instruccion', NULL, NULL, 'No', '', 'Externo', 365);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_paciente_menor`
--

CREATE TABLE `detalle_paciente_menor` (
  `Id_detalle_paciente_menor` int(11) NOT NULL,
  `parentesco` varchar(25) NOT NULL,
  `etnia` varchar(35) DEFAULT NULL,
  `tipo_etnia` varchar(25) NOT NULL,
  `analfabeta` varchar(25) NOT NULL,
  `nivel_instruccion` varchar(35) DEFAULT NULL,
  `mision` varchar(35) DEFAULT NULL,
  `años_aprobados` int(11) DEFAULT NULL,
  `discapacidad` enum('Si','No') NOT NULL,
  `tipo_discapacidad` varchar(25) NOT NULL,
  `tipo_paciente` enum('Interno','Externo','','') NOT NULL DEFAULT 'Interno',
  `id_persona` int(11) NOT NULL,
  `id_representante` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `detalle_paciente_menor`
--

INSERT INTO `detalle_paciente_menor` (`Id_detalle_paciente_menor`, `parentesco`, `etnia`, `tipo_etnia`, `analfabeta`, `nivel_instruccion`, `mision`, `años_aprobados`, `discapacidad`, `tipo_discapacidad`, `tipo_paciente`, `id_persona`, `id_representante`) VALUES
(142, 'Padre', 'No', 'Ninguna', 'No', '', NULL, 0, 'No', 'Ninguna', 'Interno', 331, 330),
(143, 'Padre', 'No', 'Ninguna', 'No', '', NULL, 0, 'No', 'Ninguna', 'Interno', 344, 343),
(144, 'Padre', 'No', 'Ninguna', 'No', NULL, NULL, 0, 'No', 'Ninguna', 'Externo', 350, 349),
(145, 'Tío(a)', 'No', 'Ninguna', 'No', NULL, NULL, 0, 'No', 'Ninguna', 'Externo', 358, 357),
(146, 'Padre', 'No', 'Ninguna', 'No', NULL, NULL, 0, 'No', 'Ninguna', 'Externo', 361, 348);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_patologia_medicamento`
--

CREATE TABLE `detalle_patologia_medicamento` (
  `Id_detalle_patologia_medicamento` int(11) NOT NULL,
  `Id_patologia` int(11) NOT NULL,
  `Id_medicamento` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `detalle_patologia_medicamento`
--

INSERT INTO `detalle_patologia_medicamento` (`Id_detalle_patologia_medicamento`, `Id_patologia`, `Id_medicamento`) VALUES
(1, 35, 89);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_patologia_sintomas`
--

CREATE TABLE `detalle_patologia_sintomas` (
  `Id` int(11) NOT NULL,
  `Id_patologia` int(11) NOT NULL,
  `Id_sintoma` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `detalle_patologia_sintomas`
--

INSERT INTO `detalle_patologia_sintomas` (`Id`, `Id_patologia`, `Id_sintoma`) VALUES
(29, 32, 14),
(30, 33, 15),
(31, 34, 16);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedidos`
--

CREATE TABLE `detalle_pedidos` (
  `id_detalle` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_descripcion_medicamento` int(11) NOT NULL,
  `cantidad_solicitada` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Volcado de datos para la tabla `detalle_pedidos`
--

INSERT INTO `detalle_pedidos` (`id_detalle`, `id_pedido`, `id_descripcion_medicamento`, `cantidad_solicitada`) VALUES
(1, 1, 81, 200),
(2, 1, 82, 100),
(3, 1, 85, 100),
(4, 1, 86, 100),
(5, 1, 88, 100),
(6, 2, 82, 400);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_persona_rol`
--

CREATE TABLE `detalle_persona_rol` (
  `Id_detalle_persona_rol` int(11) NOT NULL,
  `Id_persona` int(11) NOT NULL,
  `Id_rol` int(11) NOT NULL,
  `estatus` enum('1','2') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `detalle_persona_rol`
--

INSERT INTO `detalle_persona_rol` (`Id_detalle_persona_rol`, `Id_persona`, `Id_rol`, `estatus`) VALUES
(66, 189, 1, '2'),
(110, 281, 6, '2'),
(112, 283, 2, '2'),
(113, 284, 8, '2'),
(137, 328, 3, '1'),
(139, 330, 5, '1'),
(140, 331, 3, '1'),
(141, 332, 3, '1'),
(148, 339, 9, '2'),
(149, 340, 7, '2'),
(150, 341, 7, '2'),
(152, 343, 5, '1'),
(153, 344, 3, '1'),
(155, 346, 5, '1'),
(157, 348, 5, '1'),
(158, 349, 5, '1'),
(159, 350, 3, '1'),
(161, 352, 7, '2'),
(162, 353, 7, '2'),
(163, 354, 3, '1'),
(164, 355, 7, '2'),
(165, 356, 7, '2'),
(166, 357, 5, '1'),
(167, 358, 3, '1'),
(168, 359, 7, '2'),
(169, 360, 3, '1'),
(170, 361, 3, '1'),
(171, 362, 7, '2'),
(172, 363, 2, '2'),
(173, 364, 7, '2'),
(174, 365, 3, '1'),
(175, 366, 7, '2');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_presentacion_medicamentos`
--

CREATE TABLE `detalle_presentacion_medicamentos` (
  `Id` int(11) NOT NULL,
  `Id_medicamento` int(11) NOT NULL,
  `Id_presentacion` int(11) NOT NULL,
  `descripcion` text COLLATE utf8_spanish_ci NOT NULL,
  `estatus` enum('1','2') COLLATE utf8_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_principio_medicamento`
--

CREATE TABLE `detalle_principio_medicamento` (
  `Id_principio_medicamento` int(11) NOT NULL,
  `id_medicamento` int(11) NOT NULL,
  `id_principio_activo` int(11) NOT NULL,
  `id_tipo_unidad_medida` int(11) NOT NULL,
  `cantidad_unidad_medida` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `detalle_principio_medicamento`
--

INSERT INTO `detalle_principio_medicamento` (`Id_principio_medicamento`, `id_medicamento`, `id_principio_activo`, `id_tipo_unidad_medida`, `cantidad_unidad_medida`) VALUES
(42, 82, 1, 1, 800),
(45, 81, 1, 1, 800),
(46, 81, 2, 2, 250),
(51, 85, 1, 1, 800),
(52, 85, 2, 2, 250),
(53, 86, 1, 1, 800),
(54, 86, 2, 2, 250),
(55, 87, 1, 1, 800),
(56, 87, 2, 2, 250),
(57, 88, 1, 1, 800),
(58, 89, 1, 1, 800),
(59, 89, 2, 2, 250),
(60, 90, 1, 1, 800),
(61, 90, 2, 2, 250),
(62, 91, 1, 1, 800),
(63, 91, 2, 2, 250),
(64, 92, 1, 1, 800),
(65, 92, 2, 2, 250),
(66, 93, 1, 1, 800),
(67, 93, 2, 2, 250),
(68, 94, 1, 1, 800),
(69, 94, 2, 2, 250),
(70, 95, 1, 1, 800),
(71, 95, 2, 2, 250),
(72, 96, 1, 1, 800),
(73, 96, 2, 2, 250),
(74, 97, 1, 1, 800),
(75, 97, 2, 2, 250),
(76, 98, 1, 1, 800),
(77, 98, 2, 2, 250),
(78, 99, 1, 1, 800),
(79, 99, 2, 2, 250),
(80, 100, 1, 1, 800),
(81, 100, 2, 2, 250),
(82, 101, 1, 1, 800),
(83, 101, 2, 2, 250),
(84, 102, 1, 1, 800),
(85, 103, 1, 1, 800),
(86, 103, 2, 2, 250),
(87, 104, 1, 1, 800),
(88, 104, 2, 2, 250),
(89, 105, 1, 1, 800),
(90, 105, 2, 2, 250),
(91, 106, 1, 1, 800),
(92, 106, 2, 2, 250),
(93, 107, 1, 1, 800),
(94, 107, 2, 2, 250),
(95, 108, 1, 1, 800),
(96, 108, 2, 2, 250),
(97, 109, 1, 1, 800),
(98, 109, 2, 2, 250),
(99, 110, 1, 1, 800),
(100, 110, 2, 2, 250),
(101, 111, 1, 1, 800),
(102, 111, 2, 2, 250);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_solicitud`
--

CREATE TABLE `detalle_solicitud` (
  `id_detalle` int(11) NOT NULL,
  `id_solicitud` int(11) NOT NULL,
  `id_medicamento` int(11) NOT NULL,
  `cantidad_recetada` int(11) NOT NULL,
  `cantidad_entregada` int(11) NOT NULL DEFAULT '0',
  `estatus_item` enum('Pendiente','Entregado','Parcialmente Entregado','Cancelado') DEFAULT 'Pendiente',
  `paciente_notificado` tinyint(1) NOT NULL DEFAULT '0',
  `motivo` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `detalle_solicitud`
--

INSERT INTO `detalle_solicitud` (`id_detalle`, `id_solicitud`, `id_medicamento`, `cantidad_recetada`, `cantidad_entregada`, `estatus_item`, `paciente_notificado`, `motivo`) VALUES
(1, 1, 81, 2, 2, 'Entregado', 1, ''),
(2, 2, 85, 3, 3, 'Entregado', 1, ''),
(3, 3, 81, 2, 2, 'Entregado', 1, ''),
(4, 4, 81, 2, 0, 'Pendiente', 1, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direccion`
--

CREATE TABLE `direccion` (
  `Id_Direccion` int(11) NOT NULL,
  `tiempo_residencia` varchar(35) NOT NULL,
  `tiempo` varchar(25) NOT NULL,
  `avenida_calle` varchar(25) NOT NULL,
  `referencia` varchar(50) NOT NULL,
  `Id_persona` int(11) DEFAULT NULL,
  `Id_sector` int(11) DEFAULT NULL,
  `estatus` enum('1','2') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `direccion`
--

INSERT INTO `direccion` (`Id_Direccion`, `tiempo_residencia`, `tiempo`, `avenida_calle`, `referencia`, `Id_persona`, `Id_sector`, `estatus`) VALUES
(295, '', 'dia/s', '', '', 328, 2, '1'),
(296, '', 'dia/s', '', '', 331, 1, '1'),
(297, '', 'dia/s', '', '', 332, 1, '1'),
(299, '', 'dia/s', '', '', 330, 1, '1'),
(300, '', 'dia/s', '', '', 349, NULL, '1'),
(301, '', 'dia/s', '', '', 344, 4, '1'),
(302, '', 'dia/s', '', 'Registrado Vía Rápida (Despacho)', 354, NULL, '1'),
(303, '', 'dia/s', '', 'Registrado Vía Rápida (Despacho)', 360, NULL, '1'),
(304, '', 'dia/s', '', 'Registrado Vía Rápida (Despacho)', 365, NULL, '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidad`
--

CREATE TABLE `especialidad` (
  `Id_especialidad` int(11) NOT NULL,
  `nombre_especialidad` varchar(100) NOT NULL,
  `estatus` int(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `especialidad`
--

INSERT INTO `especialidad` (`Id_especialidad`, `nombre_especialidad`, `estatus`) VALUES
(1, 'Medicina General', 1),
(2, 'Medicina Interna', 1),
(3, 'Neumologia', 1),
(4, 'Pediatria', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidades_medicos`
--

CREATE TABLE `especialidades_medicos` (
  `Id` int(11) NOT NULL,
  `Id_especialidad` int(11) NOT NULL,
  `Id_detalle_medico` int(11) NOT NULL,
  `estatus` enum('1','2') COLLATE utf8_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `especialidades_medicos`
--

INSERT INTO `especialidades_medicos` (`Id`, `Id_especialidad`, `Id_detalle_medico`, `estatus`) VALUES
(28, 1, 22, NULL),
(29, 4, 20, NULL),
(30, 1, 27, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado`
--

CREATE TABLE `estado` (
  `Id_Estado` int(11) NOT NULL,
  `nombre_estado` varchar(35) NOT NULL,
  `Id_Pais` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `estado`
--

INSERT INTO `estado` (`Id_Estado`, `nombre_estado`, `Id_Pais`) VALUES
(1, 'Portuguesa', 1),
(2, 'Lara', 1),
(3, 'Amazonas', 1),
(4, 'Anzoátegui', 1),
(5, 'Apure', 1),
(6, 'Aragua', 1),
(7, 'Barinas', 1),
(8, 'Bolívar', 1),
(9, 'Carabobo', 1),
(10, 'Cojedes', 1),
(11, 'Delta Amacuro', 1),
(12, 'Falcón', 1),
(13, 'Guárico', 1),
(14, 'Mérida', 1),
(15, 'Miranda', 1),
(16, 'Monagas', 1),
(17, 'Nueva Esparta', 1),
(18, 'Sucre', 1),
(19, 'Táchira', 1),
(20, 'Trujillo', 1),
(21, 'La Guaira', 1),
(22, 'Yaracuy', 1),
(23, 'Zulia', 1),
(24, 'Distrito Capital', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estilos_de_vida_paciente`
--

CREATE TABLE `estilos_de_vida_paciente` (
  `Id` int(11) NOT NULL,
  `Id_tipo` int(11) NOT NULL,
  `Id_Historial` int(11) NOT NULL,
  `estatus` enum('1','2') COLLATE utf8_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `estilos_de_vida_paciente`
--

INSERT INTO `estilos_de_vida_paciente` (`Id`, `Id_tipo`, `Id_Historial`, `estatus`) VALUES
(11, 14, 86, '1'),
(12, 15, 87, '1'),
(13, 16, 90, '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `existencias_stock`
--

CREATE TABLE `existencias_stock` (
  `Id_existencia` int(11) NOT NULL,
  `Id_descripcion_medicamento` int(11) NOT NULL,
  `Id_lote` int(11) NOT NULL,
  `cantidad_actual` int(11) NOT NULL DEFAULT '0',
  `ultima_actualizacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `existencias_stock`
--

INSERT INTO `existencias_stock` (`Id_existencia`, `Id_descripcion_medicamento`, `Id_lote`, `cantidad_actual`, `ultima_actualizacion`) VALUES
(1, 81, 2, 196, '2026-06-25 21:19:51'),
(2, 82, 3, 0, '2026-06-18 07:50:10'),
(3, 85, 4, 97, '2026-06-18 00:50:57'),
(4, 85, 5, 100, '2026-06-17 21:38:46'),
(5, 88, 6, 100, '2026-06-17 21:38:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_alergias`
--

CREATE TABLE `historial_alergias` (
  `Id` int(11) NOT NULL,
  `Id_persona` int(11) NOT NULL,
  `Id_alergia` int(11) NOT NULL,
  `Id_Historial` int(11) NOT NULL,
  `fecha_registro` date NOT NULL,
  `estatus` enum('1','2') COLLATE utf8_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `historial_alergias`
--

INSERT INTO `historial_alergias` (`Id`, `Id_persona`, `Id_alergia`, `Id_Historial`, `fecha_registro`, `estatus`) VALUES
(71, 344, 6, 90, '2026-05-28', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_antecedentes_familiares`
--

CREATE TABLE `historial_antecedentes_familiares` (
  `Id` int(11) NOT NULL,
  `Id_antecedente` int(11) NOT NULL,
  `Id_Historial` int(11) NOT NULL,
  `estatus` enum('1','2') COLLATE utf8_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `historial_antecedentes_familiares`
--

INSERT INTO `historial_antecedentes_familiares` (`Id`, `Id_antecedente`, `Id_Historial`, `estatus`) VALUES
(15, 18, 86, '1'),
(16, 19, 87, '1'),
(17, 20, 90, '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_antecedentes_perinatales`
--

CREATE TABLE `historial_antecedentes_perinatales` (
  `Id` int(11) NOT NULL,
  `Id_antecedente` int(11) NOT NULL,
  `Id_Historial` int(11) NOT NULL,
  `estatus` enum('1','2') COLLATE utf8_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `historial_antecedentes_perinatales`
--

INSERT INTO `historial_antecedentes_perinatales` (`Id`, `Id_antecedente`, `Id_Historial`, `estatus`) VALUES
(16, 18, 87, '1'),
(17, 19, 90, '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_antecedentes_sexuales_reproductivos`
--

CREATE TABLE `historial_antecedentes_sexuales_reproductivos` (
  `Id` int(11) NOT NULL,
  `Id_antecedente` int(11) NOT NULL,
  `Id_Historial` int(11) NOT NULL,
  `estatus` enum('1','2') COLLATE utf8_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `historial_antecedentes_sexuales_reproductivos`
--

INSERT INTO `historial_antecedentes_sexuales_reproductivos` (`Id`, `Id_antecedente`, `Id_Historial`, `estatus`) VALUES
(15, 16, 86, '1'),
(16, 17, 87, '1'),
(17, 18, 90, '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_medico`
--

CREATE TABLE `historial_medico` (
  `id_historial` int(11) NOT NULL,
  `grupo_sanguineo` varchar(3) NOT NULL,
  `fecha` datetime NOT NULL,
  `Id_persona` int(11) NOT NULL,
  `estatus` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `historial_medico`
--

INSERT INTO `historial_medico` (`id_historial`, `grupo_sanguineo`, `fecha`, `Id_persona`, `estatus`) VALUES
(86, 'A+', '2012-11-01 08:13:52', 328, 1),
(87, 'A+', '2012-11-01 14:00:06', 331, 1),
(88, 'A+', '2012-11-01 06:46:28', 332, 1),
(90, 'A+', '2026-05-15 23:25:44', 344, 1),
(91, 'A+', '2026-05-19 22:40:00', 350, 1),
(92, 'A+', '2026-06-13 21:36:29', 354, 1),
(93, 'A+', '2026-06-13 21:54:34', 358, 1),
(94, 'A+', '2026-06-15 02:22:27', 360, 1),
(95, 'A+', '2026-06-15 02:24:45', 361, 1),
(96, 'A+', '2026-06-23 19:52:27', 365, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_patologias`
--

CREATE TABLE `historial_patologias` (
  `Id` int(11) NOT NULL,
  `Id_persona` int(11) NOT NULL,
  `Id_patologia` int(11) NOT NULL,
  `Id_Historial` int(11) NOT NULL,
  `fecha_registro` date NOT NULL,
  `estatus` enum('1','2') COLLATE utf8_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `historial_patologias`
--

INSERT INTO `historial_patologias` (`Id`, `Id_persona`, `Id_patologia`, `Id_Historial`, `fecha_registro`, `estatus`) VALUES
(142, 331, 32, 87, '0000-00-00', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `laboratorio`
--

CREATE TABLE `laboratorio` (
  `Id_laboratorio` int(11) NOT NULL,
  `nombre_laboratorio` varchar(45) NOT NULL,
  `estatus` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `laboratorio`
--

INSERT INTO `laboratorio` (`Id_laboratorio`, `nombre_laboratorio`, `estatus`) VALUES
(1, 'Laboratorios Leti', 1),
(2, 'Behrens', 1),
(3, 'Calox International', 1),
(4, 'Laboratorios Farma', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lotes_medicamentos`
--

CREATE TABLE `lotes_medicamentos` (
  `Id` int(11) NOT NULL,
  `Id_descripcion_medicamento` int(11) NOT NULL,
  `Id_proveedor` int(11) NOT NULL,
  `Lote` varchar(45) COLLATE utf8_spanish_ci NOT NULL,
  `fecha_fabricacion` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `estado_lote` enum('Disponible','Cuarentena','Vencido','Retirado','Dañado') COLLATE utf8_spanish_ci NOT NULL,
  `estatus` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `lotes_medicamentos`
--

INSERT INTO `lotes_medicamentos` (`Id`, `Id_descripcion_medicamento`, `Id_proveedor`, `Lote`, `fecha_fabricacion`, `fecha_vencimiento`, `estado_lote`, `estatus`) VALUES
(2, 81, 1, 'A', '2026-06-17', '2026-07-22', 'Disponible', 1),
(3, 82, 1, 'XKSL', '2026-06-17', '2027-02-17', 'Disponible', 1),
(4, 85, 1, 'DKS', '2026-06-17', '2026-06-24', 'Vencido', 1),
(5, 85, 1, 'LOTE1', '2026-06-17', '2027-02-17', 'Disponible', 1),
(6, 88, 1, 'ACD', '2026-06-17', '2026-07-08', 'Disponible', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lugar_nacimiento`
--

CREATE TABLE `lugar_nacimiento` (
  `Id` int(11) NOT NULL,
  `Id_persona` int(11) NOT NULL,
  `Id_municipio` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `lugar_nacimiento`
--

INSERT INTO `lugar_nacimiento` (`Id`, `Id_persona`, `Id_municipio`) VALUES
(221, 328, 677),
(222, 331, 973),
(223, 332, 912),
(225, 330, 1),
(226, 349, NULL),
(227, 344, 944);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medicamento`
--

CREATE TABLE `medicamento` (
  `Id_medicamento` int(11) NOT NULL,
  `nombre_medicamento` varchar(100) NOT NULL,
  `estatus` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `medicamento`
--

INSERT INTO `medicamento` (`Id_medicamento`, `nombre_medicamento`, `estatus`) VALUES
(94, 'DARFF', 1),
(95, 'GERMEW', 1),
(98, 'AAA', 1),
(99, 'CCC', 1),
(100, 'SSS', 1),
(101, 'XL', 1),
(102, 'ZZZ', 1),
(103, 'CELOVEN', 1),
(104, 'PEGATANQUE', 1),
(105, 'ZTE', 1),
(106, 'PHP', 1),
(107, 'LIRYC', 1),
(108, 'AADOS', 1),
(109, 'ACINCO', 1),
(110, 'AATRES', 1),
(111, 'UN MEDICAMENTO CHAFA', 1),
(112, 'XLR', 1),
(113, 'AACUATRO', 1),
(114, 'ASIETE', 1),
(115, 'MECHICO', 1),
(116, 'XXX', 1),
(117, 'URUSAURIO', 1),
(118, 'MEDICAMENTO', 1),
(119, 'XC', 1),
(120, 'XLe', 1),
(121, 'TXT', 1),
(122, 'CHILE', 1),
(123, 'MMM', 1),
(124, 'AACUATROd', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medicamentos_detalle_inventario`
--

CREATE TABLE `medicamentos_detalle_inventario` (
  `Id` int(11) NOT NULL,
  `Id_detalle_inventario` int(11) NOT NULL,
  `Id_descripcion_medicamento` int(11) NOT NULL,
  `Id_lote` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `cantida_unidad` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `stock_momento` int(11) NOT NULL,
  `observacion` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `medicamentos_detalle_inventario`
--

INSERT INTO `medicamentos_detalle_inventario` (`Id`, `Id_detalle_inventario`, `Id_descripcion_medicamento`, `Id_lote`, `cantidad`, `cantida_unidad`, `stock_momento`, `observacion`) VALUES
(1, 2, 81, 2, 200, '', 200, NULL),
(2, 2, 82, 3, 100, '', 100, NULL),
(3, 2, 85, 4, 100, '', 100, NULL),
(4, 2, 85, 5, 100, '', 200, NULL),
(5, 2, 88, 6, 100, '', 100, NULL),
(6, 3, 81, 2, 1, '', 199, NULL),
(7, 4, 81, 2, 1, '', 198, NULL),
(8, 5, 85, 4, 1, '', 99, NULL),
(9, 6, 85, 4, 2, '', 97, NULL),
(10, 7, 81, 2, 1, '', 197, NULL),
(11, 8, 82, 3, 100, '', 0, 'Algo'),
(12, 9, 81, 2, 1, '', 196, NULL),
(13, 10, 81, 2, 1, '', 195, NULL),
(14, 11, 81, 2, 1, '', 196, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medicos_departamentos`
--

CREATE TABLE `medicos_departamentos` (
  `Id` int(11) NOT NULL,
  `Id_departamento` int(11) NOT NULL,
  `Id_detalle_medico` int(11) NOT NULL,
  `estatus` enum('1','2') COLLATE utf8_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `medicos_departamentos`
--

INSERT INTO `medicos_departamentos` (`Id`, `Id_departamento`, `Id_detalle_medico`, `estatus`) VALUES
(4, 1, 22, NULL),
(5, 5, 20, NULL),
(6, 5, 27, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `municipio`
--

CREATE TABLE `municipio` (
  `Id_Municipio` int(11) NOT NULL,
  `nombre_municipio` varchar(150) NOT NULL,
  `codigo_postal` smallint(11) NOT NULL,
  `Id_Estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `municipio`
--

INSERT INTO `municipio` (`Id_Municipio`, `nombre_municipio`, `codigo_postal`, `Id_Estado`) VALUES
(1, 'Agua Blanca', 3302, 1),
(2, 'Paez', 3301, 1),
(3, 'Barquisimeto', 1083, 2),
(676, 'Guanare', 1001, 1),
(677, 'Araure', 1002, 1),
(678, 'Páez', 1003, 1),
(679, 'Esteller', 1004, 1),
(680, 'Guanarito', 1005, 1),
(681, 'Sucre', 1006, 1),
(682, 'Ospino', 1007, 1),
(683, 'Monseñor José Vicente de Unda', 1008, 1),
(684, 'Papelón', 1009, 1),
(685, 'San Genaro de Boconoíto', 1010, 1),
(686, 'San Rafael de Onoto', 1011, 1),
(687, 'Santa Rosalía', 1012, 1),
(688, 'Turén', 1013, 1),
(689, 'Agua Blanca', 1014, 1),
(690, 'Iribarren', 1015, 2),
(691, 'Jiménez', 1016, 2),
(692, 'Morán', 1017, 2),
(693, 'Palavecino', 1018, 2),
(694, 'Torres', 1019, 2),
(695, 'Andrés Eloy Blanco', 1020, 2),
(696, 'Crespo', 1021, 2),
(697, 'Simón Planas', 1022, 2),
(698, 'Urdaneta', 1023, 2),
(699, 'Alto Orinoco', 1024, 3),
(700, 'Atabapo', 1025, 3),
(701, 'Atures', 1026, 3),
(702, 'Autana', 1027, 3),
(703, 'Manapiare', 1028, 3),
(704, 'Maroa', 1029, 3),
(705, 'Río Negro', 1030, 3),
(706, 'Simón Bolívar', 1031, 4),
(707, 'Anaco', 1032, 4),
(708, 'Aragua', 1033, 4),
(709, 'Diego Bautista Urbaneja', 1034, 4),
(710, 'Fernando de Peñalver', 1035, 4),
(711, 'Francisco de Carmen Carvajal', 1036, 4),
(712, 'Francisco de Miranda', 1037, 4),
(713, 'Guanta', 1038, 4),
(714, 'Independencia', 1039, 4),
(715, 'José Gregorio Monagas', 1040, 4),
(716, 'Juan Antonio Sotillo', 1041, 4),
(717, 'Juan Manuel Cajigal', 1042, 4),
(718, 'Libertad', 1043, 4),
(719, 'Manuel Ezequiel Bruzual', 1044, 4),
(720, 'Pedro María Freites', 1045, 4),
(721, 'Píritu', 1046, 4),
(722, 'San José de Guanipa', 1047, 4),
(723, 'San Juan de Capistrano', 1048, 4),
(724, 'Santa Ana', 1049, 4),
(725, 'Simón Rodríguez', 1050, 4),
(726, 'Sir Arthur McGregor', 1051, 4),
(727, 'San Fernando', 1052, 5),
(728, 'Achaguas', 1053, 5),
(729, 'Biruaca', 1054, 5),
(730, 'Muñoz', 1055, 5),
(731, 'Páez', 1056, 5),
(732, 'Pedro Camejo', 1057, 5),
(733, 'Rómulo Gallegos', 1058, 5),
(734, 'Girardot', 1059, 6),
(735, 'Bolívar', 1060, 6),
(736, 'Camatagua', 1061, 6),
(737, 'Francisco Linares Alcántara', 1062, 6),
(738, 'José Ángel Lamas', 1063, 6),
(739, 'José Félix Ribas', 1064, 6),
(740, 'José Rafael Revenga', 1065, 6),
(741, 'Libertador', 1066, 6),
(742, 'Mario Briceño Iragorry', 1067, 6),
(743, 'Ocumare de la Costa de Oro', 1068, 6),
(744, 'San Casimiro', 1069, 6),
(745, 'San Sebastián', 1070, 6),
(746, 'Santiago Mariño', 1071, 6),
(747, 'Santos Michelena', 1072, 6),
(748, 'Sucre', 1073, 6),
(749, 'Tovar', 1074, 6),
(750, 'Urdaneta', 1075, 6),
(751, 'Zamora', 1076, 6),
(752, 'Barinas', 1077, 7),
(753, 'Alberto Arvelo Torrealba', 1078, 7),
(754, 'Andrés Eloy Blanco', 1079, 7),
(755, 'Antonio José de Sucre', 1080, 7),
(756, 'Arismendi', 1081, 7),
(757, 'Bolívar', 1082, 7),
(758, 'Cruz Paredes', 1883, 7),
(759, 'Ezequiel Zamora', 1084, 7),
(760, 'Obispos', 1085, 7),
(761, 'Pedraza', 1086, 7),
(762, 'Rojas', 1087, 7),
(763, 'Sosa', 1088, 7),
(764, 'Angostura del Orinoco', 1089, 8),
(765, 'Caroní', 1090, 8),
(766, 'Cedeño', 1091, 8),
(767, 'El Callao', 1092, 8),
(768, 'Gran Sabana', 1093, 8),
(769, 'Piar', 1094, 8),
(770, 'Raúl Leoni', 1095, 8),
(771, 'Roscio', 1096, 8),
(772, 'Sifontes', 1097, 8),
(773, 'Sucre', 1098, 8),
(774, 'Angostura', 1099, 8),
(775, 'Valencia', 1100, 9),
(776, 'Bejuma', 1101, 9),
(777, 'Carlos Arvelo', 1102, 9),
(778, 'Diego Ibarra', 1103, 9),
(779, 'Guacara', 1104, 9),
(780, 'Juan José Mora', 1105, 9),
(781, 'Libertador', 1106, 9),
(782, 'Los Guayos', 1107, 9),
(783, 'Miranda', 1108, 9),
(784, 'Montalbán', 1109, 9),
(785, 'Naguanagua', 1110, 9),
(786, 'Puerto Cabello', 1111, 9),
(787, 'San Diego', 1112, 9),
(788, 'San Joaquín', 1113, 9),
(789, 'Tocuyito', 1114, 9),
(790, 'San Carlos', 1115, 10),
(791, 'Anzoátegui', 1116, 10),
(792, 'Tinaquillo', 1117, 10),
(793, 'Girardot', 1118, 10),
(794, 'Lima Blanco', 1119, 10),
(795, 'Pao de San Juan Bautista', 1120, 10),
(796, 'Ricaurte', 1121, 10),
(797, 'Rómulo Gallegos', 1122, 10),
(798, 'Ezequiel Zamora', 1123, 10),
(799, 'Tucupita', 1124, 11),
(800, 'Antonio Díaz', 1125, 11),
(801, 'Casacoima', 1126, 11),
(802, 'Pedernales', 1127, 11),
(803, 'Miranda', 1128, 12),
(804, 'Acosta', 1129, 12),
(805, 'Bolívar', 1130, 12),
(806, 'Buchivacoa', 1131, 12),
(807, 'Cacique Manaure', 1132, 12),
(808, 'Carirubana', 1133, 12),
(809, 'Colina', 1134, 12),
(810, 'Dabajuro', 1135, 12),
(811, 'Democracia', 1136, 12),
(812, 'Falcón', 1137, 12),
(813, 'Federación', 1138, 12),
(814, 'Iturriza', 1139, 12),
(815, 'Jacura', 1140, 12),
(816, 'Los Taques', 1141, 12),
(817, 'Mauroa', 1142, 12),
(818, 'Monseñor Iturriza', 1143, 12),
(819, 'Palmasola', 1144, 12),
(820, 'Petit', 1145, 12),
(821, 'Píritu', 1146, 12),
(822, 'San Francisco', 1147, 12),
(823, 'Silva', 1148, 12),
(824, 'Sucre', 1149, 12),
(825, 'Tocópero', 1150, 12),
(826, 'Unión', 1151, 12),
(827, 'Zamora', 1152, 12),
(828, 'Francisco de Miranda', 1153, 13),
(829, 'Camaguán', 1154, 13),
(830, 'Chaguaramas', 1155, 13),
(831, 'El Socorro', 1156, 13),
(832, 'Infante', 1157, 13),
(833, 'Las Mercedes', 1158, 13),
(834, 'Mellado', 1159, 13),
(835, 'Monagas', 1160, 13),
(836, 'Ortiz', 1161, 13),
(837, 'Ribas', 1162, 13),
(838, 'Roscio', 1163, 13),
(839, 'San Gerónimo de Guayabal', 1164, 13),
(840, 'San José de Guaribe', 1165, 13),
(841, 'Santa María de Ipire', 1166, 13),
(842, 'Zaraza', 1167, 13),
(843, 'Libertador', 1168, 14),
(844, 'Alberto Adriani', 1169, 14),
(845, 'Andrés Bello', 1170, 14),
(846, 'Antonio Pinto Salinas', 1171, 14),
(847, 'Aricagua', 1172, 14),
(848, 'Arzobispo Chacón', 1173, 14),
(849, 'Campo Elías', 1174, 14),
(850, 'Caracciolo Parra Olmedo', 1175, 14),
(851, 'Cardenal Quintero', 1176, 14),
(852, 'Guaraque', 1177, 14),
(853, 'Julio César Salas', 1178, 14),
(854, 'Justo Briceño', 1179, 14),
(855, 'Miranda', 1180, 14),
(856, 'Obispo Ramos de Lora', 1181, 14),
(857, 'Padre Noguera', 1182, 14),
(858, 'Pueblo Llano', 1183, 14),
(859, 'Rangel', 1184, 14),
(860, 'Rivas Dávila', 1185, 14),
(861, 'Santos Marquina', 1186, 14),
(862, 'Sucre', 1187, 14),
(863, 'Tovar', 1188, 14),
(864, 'Tulio Febres Cordero', 1189, 14),
(865, 'Zea', 1190, 14),
(866, 'Guaicaipuro', 1191, 15),
(867, 'Acevedo', 1192, 15),
(868, 'Andrés Bello', 1193, 15),
(869, 'Baruta', 1194, 15),
(870, 'Brión', 1195, 15),
(871, 'Buroz', 1196, 15),
(872, 'Carrizal', 1197, 15),
(873, 'Chacao', 1198, 15),
(874, 'Cristóbal Rojas', 1199, 15),
(875, 'El Hatillo', 1200, 15),
(876, 'Independencia', 1201, 15),
(877, 'Lander', 1202, 15),
(878, 'Los Salias', 1203, 15),
(879, 'Páez', 1204, 15),
(880, 'Paz Castillo', 1205, 15),
(881, 'Pedro Gual', 1206, 15),
(882, 'Plaza', 1207, 15),
(883, 'Simón Bolívar', 1208, 15),
(884, 'Sucre', 1209, 15),
(885, 'Urdaneta', 1210, 15),
(886, 'Zamora', 1211, 15),
(887, 'Maturín', 1212, 16),
(888, 'Acosta', 1213, 16),
(889, 'Aguasay', 1214, 16),
(890, 'Bolívar', 1215, 16),
(891, 'Caripe', 1216, 16),
(892, 'Cedeño', 1217, 16),
(893, 'Ezequiel Zamora', 1218, 16),
(894, 'Libertador', 1219, 16),
(895, 'Piar', 1220, 16),
(896, 'Punceres', 1221, 16),
(897, 'Santa Bárbara', 1222, 16),
(898, 'Sotillo', 1223, 16),
(899, 'Uracoa', 1224, 16),
(900, 'Arismendi', 1225, 17),
(901, 'Antolín del Campo', 1226, 17),
(902, 'Díaz', 1227, 17),
(903, 'García', 1228, 17),
(904, 'Gómez', 1229, 17),
(905, 'Maneiro', 1230, 17),
(906, 'Marcano', 1231, 17),
(907, 'Mariño', 1232, 17),
(908, 'Península de Macanao', 1233, 17),
(909, 'Tubores', 1234, 17),
(910, 'Villalba', 1235, 17),
(911, 'Sucre', 1236, 18),
(912, 'Andrés Eloy Blanco', 1237, 18),
(913, 'Andrés Mata', 1238, 18),
(914, 'Arismendi', 1239, 18),
(915, 'Benítez', 1240, 18),
(916, 'Bermúdez', 1241, 18),
(917, 'Bolívar', 1242, 18),
(918, 'Cajigal', 1243, 18),
(919, 'Cruz Salmerón Acosta', 1244, 18),
(920, 'Libertador', 1245, 18),
(921, 'Mariño', 1246, 18),
(922, 'Mejía', 1247, 18),
(923, 'Montes', 1248, 18),
(924, 'Ribero', 1249, 18),
(925, 'Valdez', 1250, 18),
(926, 'San Cristóbal', 1251, 19),
(927, 'Andrés Bello', 1252, 19),
(928, 'Antonio Rómulo Costa', 1253, 19),
(929, 'Ayacucho', 1254, 19),
(930, 'Bolívar', 1255, 19),
(931, 'Cárdenas', 1256, 19),
(932, 'Córdoba', 1257, 19),
(933, 'Fernández Feo', 1258, 19),
(934, 'Francisco de Miranda', 1259, 19),
(935, 'García de Hevia', 1260, 19),
(936, 'Guásimos', 1261, 19),
(937, 'Independencia', 1262, 19),
(938, 'Jáuregui', 1263, 19),
(939, 'José María Vargas', 1264, 19),
(940, 'Junín', 1265, 19),
(941, 'Libertad', 1266, 19),
(942, 'Libertador', 1267, 19),
(943, 'Lobatera', 1268, 19),
(944, 'Michelena', 1269, 19),
(945, 'Panamericano', 1270, 19),
(946, 'Pedro María Ureña', 1271, 19),
(947, 'Rafael Urdaneta', 1272, 19),
(948, 'Samuel Darío Maldonado', 1273, 19),
(949, 'San Judas Tadeo', 1274, 19),
(950, 'Seboruco', 1275, 19),
(951, 'Simón Rodríguez', 1276, 19),
(952, 'Sucre', 1277, 19),
(953, 'Torbes', 1278, 19),
(954, 'Uribante', 1279, 19),
(955, 'Trujillo', 1280, 20),
(956, 'Andrés Bello', 1281, 20),
(957, 'Boconó', 1282, 20),
(958, 'Bolívar', 1283, 20),
(959, 'Candelaria', 1284, 20),
(960, 'Carache', 1285, 20),
(961, 'Escuque', 1286, 20),
(962, 'José Felipe Márquez Cañizales', 1287, 20),
(963, 'Juan Vicente Campo Elías', 1288, 20),
(964, 'La Ceiba', 1289, 20),
(965, 'Miranda', 1290, 20),
(966, 'Monte Carmelo', 1291, 20),
(967, 'Motatán', 1292, 20),
(968, 'Pampán', 1293, 20),
(969, 'Pampanito', 1294, 20),
(970, 'Rafael Rangel', 1295, 20),
(971, 'San Rafael de Carvajal', 1296, 20),
(972, 'Sucre', 1297, 20),
(973, 'Urdaneta', 1298, 20),
(974, 'Valera', 1299, 20),
(975, 'Vargas', 1300, 21),
(976, 'San Felipe', 1301, 22),
(977, 'Aristides Bastidas', 1302, 22),
(978, 'Bolívar', 1303, 22),
(979, 'Bruzual', 1304, 22),
(980, 'Cocorote', 1305, 22),
(981, 'Independencia', 1306, 22),
(982, 'José Antonio Páez', 1307, 22),
(983, 'La Trinidad', 1308, 22),
(984, 'Manuel Monge', 1309, 22),
(985, 'Nirgua', 1310, 22),
(986, 'Peña', 1311, 22),
(987, 'Sucre', 1312, 22),
(988, 'Urachiche', 1313, 22),
(989, 'Veroes', 1314, 22),
(990, 'Maracaibo', 1315, 23),
(991, 'Almirante Padilla', 1316, 23),
(992, 'Baralt', 1317, 23),
(993, 'Cabimas', 1318, 23),
(994, 'Catatumbo', 1319, 23),
(995, 'Colón', 1320, 23),
(996, 'Francisco Javier Pulgar', 1321, 23),
(997, 'Jesús Enrique Lossada', 1322, 23),
(998, 'Jesús María Semprúm', 1323, 23),
(999, 'La Cañada de Urdaneta', 1324, 23),
(1000, 'Lagunillas', 1325, 23),
(1001, 'Machiques de Perijá', 1326, 23),
(1002, 'Mara', 1327, 23),
(1003, 'Miranda', 1328, 23),
(1004, 'Páez', 1329, 23),
(1005, 'Rosario de Perijá', 1330, 23),
(1006, 'San Francisco', 1331, 23),
(1007, 'Santa Rita', 1332, 23),
(1008, 'Simón Bolívar', 1333, 23),
(1009, 'Sucre', 1334, 23),
(1010, 'Valmore Rodríguez', 1335, 23),
(1011, 'Libertador', 1336, 24);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones_usuarios`
--

CREATE TABLE `notificaciones_usuarios` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL COMMENT 'Ej: cita, inventario_lote',
  `referencia_id` varchar(50) NOT NULL COMMENT 'Ej: cita_15, lote_42',
  `titulo` varchar(100) NOT NULL,
  `mensaje` text NOT NULL,
  `ruta` varchar(255) DEFAULT NULL,
  `leida` tinyint(1) NOT NULL DEFAULT '0',
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `notificaciones_usuarios`
--

INSERT INTO `notificaciones_usuarios` (`id`, `id_usuario`, `tipo`, `referencia_id`, `titulo`, `mensaje`, `ruta`, `leida`, `fecha_creacion`) VALUES
(376, 189, 'inventario_lote', 'lote_4_Proximo', 'Lote próximo a vencer', 'El lote DKS de AAA vence pronto (2026-06-24).', 'pages/php/farmacia_lotes_listado.php', 1, '2026-06-17 20:25:48'),
(377, 281, 'inventario_lote', 'lote_4_Proximo', 'Lote próximo a vencer', 'El lote DKS de AAA vence pronto (2026-06-24).', 'pages/php/farmacia_lotes_listado.php', 1, '2026-06-17 20:25:48'),
(378, 339, 'inventario_lote', 'lote_4_Proximo', 'Lote próximo a vencer', 'El lote DKS de AAA vence pronto (2026-06-24).', 'pages/php/farmacia_lotes_listado.php', 0, '2026-06-17 20:25:48'),
(379, 189, 'inventario_lote', 'lote_6_Proximo', 'Lote próximo a vencer', 'El lote ACD de XL vence pronto (2026-07-08).', 'pages/php/farmacia_lotes_listado.php', 1, '2026-06-17 20:25:48'),
(380, 281, 'inventario_lote', 'lote_6_Proximo', 'Lote próximo a vencer', 'El lote ACD de XL vence pronto (2026-07-08).', 'pages/php/farmacia_lotes_listado.php', 1, '2026-06-17 20:25:48'),
(381, 339, 'inventario_lote', 'lote_6_Proximo', 'Lote próximo a vencer', 'El lote ACD de XL vence pronto (2026-07-08).', 'pages/php/farmacia_lotes_listado.php', 0, '2026-06-17 20:25:48'),
(436, 189, 'receta_disponible', 'disp_Externa_2', 'Medicina Disponible para Despachar', 'Ya hay stock de AAA para la receta pendiente de Deyber Deinner Silva Gallardo.', 'pages/php/farmacia_prescripciones_listado.php?buscar=Deyber+Deinner+Silva+Gallardo', 1, '2026-06-17 20:28:25'),
(437, 281, 'receta_disponible', 'disp_Externa_2', 'Medicina Disponible para Despachar', 'Ya hay stock de AAA para la receta pendiente de Deyber Deinner Silva Gallardo.', 'pages/php/farmacia_prescripciones_listado.php?buscar=Deyber+Deinner+Silva+Gallardo', 1, '2026-06-17 20:28:25'),
(438, 339, 'receta_disponible', 'disp_Externa_2', 'Medicina Disponible para Despachar', 'Ya hay stock de AAA para la receta pendiente de Deyber Deinner Silva Gallardo.', 'pages/php/farmacia_prescripciones_listado.php?buscar=Deyber+Deinner+Silva+Gallardo', 0, '2026-06-17 20:28:26'),
(547, 189, 'receta_disponible', 'disp_Externa_3', 'Medicina Disponible para Despachar', 'Ya hay stock de DARFF para la receta pendiente de Deyber Deinner Silva Gallardo.', 'pages/php/farmacia_prescripciones_listado.php?buscar=Deyber+Deinner+Silva+Gallardo', 1, '2026-06-17 20:51:25'),
(548, 281, 'receta_disponible', 'disp_Externa_3', 'Medicina Disponible para Despachar', 'Ya hay stock de DARFF para la receta pendiente de Deyber Deinner Silva Gallardo.', 'pages/php/farmacia_prescripciones_listado.php?buscar=Deyber+Deinner+Silva+Gallardo', 1, '2026-06-17 20:51:26'),
(549, 339, 'receta_disponible', 'disp_Externa_3', 'Medicina Disponible para Despachar', 'Ya hay stock de DARFF para la receta pendiente de Deyber Deinner Silva Gallardo.', 'pages/php/farmacia_prescripciones_listado.php?buscar=Deyber+Deinner+Silva+Gallardo', 0, '2026-06-17 20:51:26'),
(592, 189, 'inventario_stock', 'stock_2_Agotado', 'Stock Agotado', '¡URGENTE! El medicamento GERMEW (Lote: XKSL) se ha agotado.', 'pages/php/farmacia_inventario_listado.php', 1, '2026-06-18 03:50:12'),
(593, 281, 'inventario_stock', 'stock_2_Agotado', 'Stock Agotado', '¡URGENTE! El medicamento GERMEW (Lote: XKSL) se ha agotado.', 'pages/php/farmacia_inventario_listado.php', 1, '2026-06-18 03:50:12'),
(594, 339, 'inventario_stock', 'stock_2_Agotado', 'Stock Agotado', '¡URGENTE! El medicamento GERMEW (Lote: XKSL) se ha agotado.', 'pages/php/farmacia_inventario_listado.php', 0, '2026-06-18 03:50:12'),
(676, 189, 'cita_medica', 'cita_2_hoy', 'Cita programada para hoy', 'Paciente: Steve  Rogers a las 10:30 AM', 'pages/php/citas_medicas_listado.php', 1, '2026-06-19 09:33:36'),
(677, 359, 'cita_medica', 'cita_2_hoy', 'Cita programada para hoy', 'Paciente: Steve  Rogers a las 10:30 AM', 'pages/php/citas_medicas_listado.php', 0, '2026-06-19 09:33:36'),
(855, 189, 'cita_medica', 'cita_4_hoy', 'Cita programada para hoy', 'Paciente: Deyber Deinner Silva Gallardo a las 10:00 AM', 'pages/php/citas_medicas_listado.php', 1, '2026-06-18 09:30:40'),
(856, 359, 'cita_medica', 'cita_4_hoy', 'Cita programada para hoy', 'Paciente: Deyber Deinner Silva Gallardo a las 10:00 AM', 'pages/php/citas_medicas_listado.php', 0, '2026-06-18 09:30:40'),
(943, 189, 'cita_medica', 'cita_4_vencida', 'Cita Vencida', 'Paciente: Deyber Deinner Silva Gallardo a las 10:00 AM', 'pages/php/citas_medicas_listado.php', 1, '2026-06-18 16:13:02'),
(944, 359, 'cita_medica', 'cita_4_vencida', 'Cita Vencida', 'Paciente: Deyber Deinner Silva Gallardo a las 10:00 AM', 'pages/php/citas_medicas_listado.php', 0, '2026-06-18 16:13:02'),
(1014, 189, 'inventario_lote', 'lote_2_Proximo', 'Lote próximo a vencer', 'El lote A de DARFF vence pronto (2026-07-22).', 'pages/php/farmacia_lotes_listado.php', 1, '2026-06-22 16:15:21'),
(1015, 281, 'inventario_lote', 'lote_2_Proximo', 'Lote próximo a vencer', 'El lote A de DARFF vence pronto (2026-07-22).', 'pages/php/farmacia_lotes_listado.php', 1, '2026-06-22 16:15:21'),
(1016, 339, 'inventario_lote', 'lote_2_Proximo', 'Lote próximo a vencer', 'El lote A de DARFF vence pronto (2026-07-22).', 'pages/php/farmacia_lotes_listado.php', 0, '2026-06-22 16:15:21'),
(1020, 189, 'inventario_lote', 'lote_4_Vencido', 'Lote Vencido Crítico', 'El lote DKS de AAA venció el 2026-06-24. Retirar de estantes.', 'pages/php/farmacia_lotes_listado.php', 1, '2026-06-25 11:25:24'),
(1021, 281, 'inventario_lote', 'lote_4_Vencido', 'Lote Vencido Crítico', 'El lote DKS de AAA venció el 2026-06-24. Retirar de estantes.', 'pages/php/farmacia_lotes_listado.php', 0, '2026-06-25 11:25:25'),
(1022, 339, 'inventario_lote', 'lote_4_Vencido', 'Lote Vencido Crítico', 'El lote DKS de AAA venció el 2026-06-24. Retirar de estantes.', 'pages/php/farmacia_lotes_listado.php', 0, '2026-06-25 11:25:25'),
(1437, 189, 'receta_disponible', 'disp_Externa_4', 'Medicina Disponible para Despachar', 'Ya hay stock de DARFF para la receta pendiente de Ezequiel Veroez.', 'pages/php/farmacia_prescripciones_listado.php?buscar=Ezequiel+Veroez', 1, '2026-06-25 11:56:28'),
(1438, 281, 'receta_disponible', 'disp_Externa_4', 'Medicina Disponible para Despachar', 'Ya hay stock de DARFF para la receta pendiente de Ezequiel Veroez.', 'pages/php/farmacia_prescripciones_listado.php?buscar=Ezequiel+Veroez', 0, '2026-06-25 11:56:28'),
(1439, 339, 'receta_disponible', 'disp_Externa_4', 'Medicina Disponible para Despachar', 'Ya hay stock de DARFF para la receta pendiente de Ezequiel Veroez.', 'pages/php/farmacia_prescripciones_listado.php?buscar=Ezequiel+Veroez', 0, '2026-06-25 11:56:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `observaciones_historial_medico`
--

CREATE TABLE `observaciones_historial_medico` (
  `id` int(11) NOT NULL,
  `Id_historial_medico` int(11) NOT NULL,
  `observacion` text COLLATE utf8_spanish_ci NOT NULL,
  `Id_medico` int(11) NOT NULL,
  `fecha` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pais`
--

CREATE TABLE `pais` (
  `Id_Pais` int(11) NOT NULL,
  `nombre_pais` varchar(35) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `pais`
--

INSERT INTO `pais` (`Id_Pais`, `nombre_pais`) VALUES
(1, 'Venezuela');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `patologias`
--

CREATE TABLE `patologias` (
  `Id_patologia` int(11) NOT NULL,
  `nombre_patologia` varchar(45) COLLATE utf8_spanish_ci NOT NULL,
  `descripcion` varchar(150) COLLATE utf8_spanish_ci DEFAULT NULL,
  `estatus` int(1) DEFAULT '1',
  `codigo_cie` varchar(10) COLLATE utf8_spanish_ci NOT NULL,
  `contagioso` enum('SI','NO') COLLATE utf8_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `patologias`
--

INSERT INTO `patologias` (`Id_patologia`, `nombre_patologia`, `descripcion`, `estatus`, `codigo_cie`, `contagioso`) VALUES
(32, 'Dengue', NULL, 1, 'A99', 'NO'),
(33, 'Hipertensión Arterial', NULL, 1, 'I10', 'NO'),
(34, 'Diabetes Mellitus Tipo 2', NULL, 1, 'E11.9', 'NO'),
(35, 'HLA', NULL, 1, 'A345', 'NO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int(11) NOT NULL,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_proveedor` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `estado` enum('Pendiente','Recibido','Cancelado') COLLATE utf8_bin DEFAULT 'Pendiente',
  `estatus` int(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `fecha_creacion`, `id_proveedor`, `id_usuario`, `estado`, `estatus`) VALUES
(1, '2026-06-17 17:14:00', 1, 189, 'Recibido', 1),
(2, '2026-06-22 17:55:00', 1, 189, 'Cancelado', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permiso`
--

CREATE TABLE `permiso` (
  `Id_permiso` int(11) NOT NULL,
  `nombre_permiso` varchar(100) NOT NULL,
  `descripcion` text,
  `estatus` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `permiso`
--

INSERT INTO `permiso` (`Id_permiso`, `nombre_permiso`, `descripcion`, `estatus`) VALUES
(1, 'Crear Medicos', 'Permite registrar los datos de los medicos.', 1),
(2, 'Editar Medicos', 'Permite modificar los datos de los medicos.', 1),
(3, 'Ver Medicos', 'Permite ver los datos de los medicos', 1),
(4, 'Desactivar Medicos', 'Permite desactivar los datos de los medicos', 1),
(5, 'Reactivar Medicos', 'Permite reactivar los datos de los medicos', 1),
(6, 'Eliminar Medicos', 'Permite eliminar los datos de los medicos', 1),
(7, 'Crear Areas', 'Permite registrar las areas de los medicos.', 1),
(8, 'Editar Areas', 'Permite modificar las areas de los medicos.', 1),
(9, 'Ver Areas', 'Permite ver los datos de las areas de los medicos', 1),
(10, 'Desactivar Areas', 'Permite desactivar los areas de los medicos', 1),
(11, 'Reactivar Areas', 'Permite reactivar los areas de los medicos', 1),
(12, 'Eliminar Areas', 'Permite eliminar los areas de los medicos', 1),
(13, 'Crear Especialidades', 'Permite registrar las especialidades de los medicos.', 1),
(14, 'Editar Especialidades', 'Permite modificar las especialidades de los medicos.', 1),
(15, 'Ver Especialidades', 'Permite Ver los datos de las especialidades de los medicos', 1),
(16, 'Desactivar Especialidades', 'Permite desactivar las especialidades de los medicos', 1),
(17, 'Reactivar Especialidades', 'Permite reactivar las especialidades de los medicos', 1),
(18, 'Eliminar Especialidades', 'Permite eliminar las especialidades de los medicos', 1),
(19, 'Crear Usuarios', 'Permite crear y registrar nuevos usuarios en el sistema.', 1),
(20, 'Editar Usuarios', 'Permite modificar datos, roles y permisos de cualquier usuario.', 1),
(21, 'Ver Usuarios', 'Permite ver los usuarios existentes', 1),
(22, 'Desactivar Usuarios', 'Permite desactivar cuentas de usuario.', 1),
(23, 'Reactivar Usuarios', 'Permite reactivar cuentas de usuario.', 1),
(24, 'Eliminar Usuarios', 'Permite eliminar cuentas de usuario.', 1),
(25, 'Crear Roles', 'Permite crear y registrar nuevos roles y sus permisos en el sistema.', 1),
(26, 'Editar Roles', 'Permite modificar los roles y sus permisos en el sistema.', 1),
(27, 'Ver Roles', 'Permite ver los roles de usuario', 1),
(28, 'Desactivar Roles', 'Permite desactivar los roles de usuario.', 1),
(29, 'Reactivar Roles', 'Permite reactivar los roles de usuario.', 1),
(30, 'Eliminar Roles', 'Permite eliminar los roles de usuario.', 1),
(31, 'Crear Permisos', 'Permite crear y registrar nuevos permisos y sus permisos en el sistema.', 1),
(32, 'Editar Permisos', 'Permite modificar los permisos y sus permisos en el sistema .', 1),
(33, 'Ver Permisos', 'Permite Ver los permisos de usuario', 1),
(34, 'Desactivar Permisos', 'Permite desactivar los permisos de usuario.', 1),
(35, 'Reactivar Permisos', 'Permite reactivar los permisos de usuario.', 1),
(36, 'Eliminar Permisos', 'Permite eliminar los permisos de usuario.', 1),
(37, 'Crear Pacientes', 'Permite crear nuevos registros demograficos de pacientes.', 1),
(38, 'Editar Pacientes', 'Permite modificar datos personales (telofono, direccion, etc.) del paciente.', 1),
(39, 'Ver Pacientes', 'Permite acceder al listado y perfil demograficos del paciente.', 1),
(40, 'Desactivar Pacientes', 'Permite desactivar datos personales (telofono, direccion, etc.) del paciente.', 1),
(41, 'Reactivar Pacientes', 'Permite reactivar datos personales (telofono, direccion, etc.) del paciente.', 1),
(42, 'Eliminar Pacientes', 'Permite eliminar datos personales (telofono, direccion, etc.) del paciente.', 1),
(43, 'Crear Pacientes Menores de Edad', 'Permite crear nuevos registros demograficos de pacientes menores de edad.', 1),
(44, 'Editar Pacientes Menores de Edad', 'Permite modificar datos personales (telofono, direccion, etc.) de pacientes menores de edad.', 1),
(45, 'Ver Pacientes Menores de Edad', 'Permite acceder al listado y perfil demograficos de pacientes menores de edad.', 1),
(46, 'Desactivar Pacientes Menores de Edad', 'Permite desactivar datos personales (telofono, direccion, etc.) de pacientes menores de edad.', 1),
(47, 'Reactivar Pacientes Menores de Edad', 'Permite reactivar datos personales (telofono, direccion, etc.) de pacientes menores de edad.', 1),
(48, 'Eliminar Pacientes Menores de Edad', 'Permite eliminar datos personales (telofono, direccion, etc.) de pacientes menores de edad.', 1),
(49, 'Crear Representantes', 'Permite registrar los datos del representante de un paciente menor.', 1),
(50, 'Editar Representantes', 'Permite modificar los datos del representante de un paciente menor.', 1),
(51, 'Ver Representantes', 'Permite Ver los datos de los representantes', 1),
(52, 'Desactivar Representantes', 'Permite desactivar los datos del representante de un paciente menor.', 1),
(53, 'Reactivar Representantes', 'Permite reactivar los datos del representante de un paciente menor.', 1),
(54, 'Eliminar Representantes', 'Permite eliminar los datos del representante de un paciente menor.', 1),
(55, 'Crear Antecedentes', 'Permite regitrar los campos de antecedentes del paciente (Familiares, Estilo de Vida, etc.)', 1),
(56, 'Editar Antecedentes', 'Permite modificar los campos de antecedentes del paciente (Familiares, Estilo de Vida, etc.)', 1),
(57, 'Ver Historial Clinico', 'Permite acceder a la pestaña de Historial Clinico y antecedentes.', 1),
(58, 'Desactivar Historial Clinico', 'Permite desactivar el historial clinico y sus antecedentes.', 1),
(59, 'Reactivar Historial Clinico', 'Permite reactivar el historial clinico y sus antecedentes.', 1),
(60, 'Eliminar Historial Clinico', 'Permite eliminar el historial clinico y sus antecedentes.', 1),
(61, 'Crear Patologias', 'Permite registrar patologias.', 1),
(62, 'Editar Patologias', 'Permite modificar patologias existentes.', 1),
(63, 'Ver Patologias', 'Permite Ver las patologias', 1),
(64, 'Desactivar Patologias', 'Permite desactivar patologias existentes', 1),
(65, 'Reactivar Patologias', 'Permite reactivar patologias existentes', 1),
(66, 'Eliminar Patologias', 'Permite eliminar patologias existentes', 1),
(67, 'Crear Sintomas', 'Permite registrar sintomas.', 1),
(68, 'Editar Sintomas', 'Permite modificar sintomas existentes.', 1),
(69, 'Ver Sintomas', 'Permite Ver los sintomas', 1),
(70, 'Desactivar Sintomas', 'Permite desactivar sintomas existentes', 1),
(71, 'Reactivar Sintomas', 'Permite reactivar sintomas existentes', 1),
(72, 'Eliminar Sintomas', 'Permite eliminar sintomas existentes', 1),
(73, 'Crear Alergias', 'Permite registrar alergias existentes.', 1),
(74, 'Editar Alergias', 'Permite modificar alergias existentes.', 1),
(75, 'Ver Alergias', 'Permite Ver las alergias', 1),
(76, 'Desactivar Alergias', 'Permite desactivar alergias existentes', 1),
(77, 'Reactivar Alergias', 'Permite reactivar alergias existentes', 1),
(78, 'Eliminar Alergias', 'Permite eliminar alergias existentes', 1),
(79, 'Crear Consultas', 'Permite iniciar y guardar una nueva consulta modica.', 1),
(80, 'Editar Consultas', 'Permite modificar datos de una consulta ya guardada (usualmente restringido).', 1),
(81, 'Ver Consultas', 'Permite ver el listado de consultas y acceder al detalle de consultas historicas.', 1),
(82, 'Desactivar Consultas', 'Permite desactivar datos de una consulta.', 1),
(83, 'Reactivar Consultas', 'Permite reactivar datos de una consulta.', 1),
(84, 'Eliminar Consultas', 'Permite eliminar datos de una consulta.', 1),
(85, 'Crear Citas', 'Permite iniciar y guardar una nueva cita modica.', 1),
(86, 'Editar Citas', 'Permite modificar datos de una cita medica.', 1),
(87, 'Ver Citas', 'Permite ver el listado de citas.', 1),
(88, 'Desactivar Citas', 'Permite desactivar datos de una cita medica.', 1),
(89, 'Reactivar Citas', 'Permite reactivar datos de una cita medica.', 1),
(90, 'Eliminar Citas', 'Permite eliminar datos de una cita medica.', 1),
(91, 'Generar Entradas de Inventario', 'Permite generar entradas y salidas del inventario', 1),
(92, 'Generar Salidas de Inventario', 'Permite generar entradas y salidas del inventario', 1),
(93, 'Ajustar Stock de Medicamentos', 'Permite modificar el stock minimo y maximo de un medicamento', 1),
(95, 'Ver Inventario', 'Permite ver el listado y stock actual del inventario.', 1),
(98, 'Anular Movimientos de Inventario', 'Permite anular movimientos en el inventario.', 1),
(99, 'Crear Medicamentos', 'Permite registrar nuevos medicamentos en el catologo.', 1),
(100, 'Editar Medicamentos', 'Permite modificar la informacion de medicamentos existentes.', 1),
(101, 'Ver Medicamentos', 'Permite ver el listado de medicamentos existentes.', 1),
(102, 'Desactivar Medicamentos', 'Permite desactivar la informacion de medicamentos existentes.', 1),
(103, 'Reactivar Medicamentos', 'Permite reactivar la informacion de medicamentos existentes.', 1),
(104, 'Eliminar Medicamentos', 'Permite eliminar la informacion de medicamentos existentes.', 1),
(105, 'Crear Lotes', 'Permite registrar nuevos lotes en el catologo.', 1),
(106, 'Editar Lotes', 'Permite modificar la informacion de los lotes existentes.', 1),
(107, 'Ver Lotes', 'Permite ver el listado de los lotes existentes.', 1),
(108, 'Desactivar Lotes', 'Permite desactivar la informacion de los lotes existentes.', 1),
(109, 'Reactivar Lotes', 'Permite reactivar la informacion de los lotes existentes.', 1),
(110, 'Eliminar Lotes', 'Permite eliminar la informacion de los lotes existentes.', 1),
(111, 'Gestionar Consultas', 'Permite administrar las consultass.', 1),
(112, 'Gestionar Citas', 'Permite administrar las citas.', 1),
(113, 'Gestionar Pacientes', 'Permite administrar los datos de los pacientes.', 1),
(114, 'Gestionar Salud', 'Permite administrar los datos de las patologias, alergias, etc.', 1),
(115, 'Gestionar RH', 'Permite administrar las areas, empleados, etc.', 1),
(116, 'Gestionar Farmacia', 'Permite ver el panel completo del inventario.', 1),
(117, 'Gestionar Configuraciones', 'Permite administrar las configuraciones.', 1),
(118, 'Generar Reportes de Pacientes', 'Permite crear reportes de pacientes.', 1),
(119, 'Generar Reportes de Pacientes Menores de Edad', 'Permite crear reportes de pacientes menores de edad.', 1),
(121, 'Generar Reportes de Citas', 'Permite crear reportes de citas.', 1),
(122, 'Generar Recipe Medico', 'Permite crear el recipe medico de un paciente.', 1),
(123, 'Generar Comprobante de Cita', 'Permite crear el comprobante de una cita de un paciente.', 1),
(124, 'Generar Reportes de Consultas', 'Permite crear reportes de consultas.', 1),
(125, 'Generar Reportes de Representantes', 'Permite crear reportes de representantes.', 1),
(126, 'Generar Reportes de Patologias', 'Permite crear reportes de patologias.', 1),
(127, 'Generar Reportes de Alergias', 'Permite crear reportes de alergias.', 1),
(128, 'Generar Reportes de Sintomas', 'Permite crear reportes de sintomas.', 1),
(129, 'Generar Reportes de Medicos', 'Permite crear reportes de medicos.', 1),
(130, 'Generar Reportes de Areas', 'Permite crear reportes de areas.', 1),
(131, 'Generar Reportes de Especialidades', 'Permite crear reportes de especialidades.', 1),
(132, 'Generar Reportes de Inventario', 'Permite crear reportes de inventario.', 1),
(133, 'Generar Reportes de Medicamentos', 'Permite crear reportes de medicamentos.', 1),
(134, 'Generar Reportes de Lotes', 'Permite crear reportes de lotes.', 1),
(135, 'Ver panel de administrador', 'Permite visualizar las informaciones principales en el dasboard.', 1),
(136, 'Ver panel de medicos', 'Permite visualizar las informaciones principales en el dasboard.', 1),
(137, 'Ver panel de farmaceutico', 'Permite visualizar las informaciones principales en el dasboard.', 1),
(138, 'Ver panel de recepcionista', 'Permite visualizar las informaciones principales en el dasboard.', 1),
(139, 'Ver panel de recursos humanos', 'Permite visualizar las informaciones principales en el dasboard.', 1),
(140, 'Ver panel de visitante', 'Permite visualizar las informaciones principales en el dasboard.', 1),
(141, 'Ver notificaciones de citas', 'Permite ver las notificaciones relacionadas con las citas.', 1),
(142, 'Ver notificaciones de inventario', 'Permite ver las notificaciones relacionadas con el inventario.', 1),
(143, 'Ver notificaciones de lotes', 'Permite ver las notificaciones relacionadas con el inventario.', 1),
(144, 'Ver papelera de pacientes', 'Permite administrar la papelera relacionada con los pacientes.', 1),
(145, 'Ver papelera de pacientes menores de edad', 'Permite administrar la papelera relacionada con los pacientes menores de edad.', 1),
(146, 'Ver papelera de representantes', 'Permite administrar la papelera relacionada con los representantantes.', 1),
(147, 'Ver papelera de citas', 'Permite administrar la papelera relacionada con las citas.', 1),
(148, 'Ver papelera de consultas', 'Permite administrar la papelera relacionada con las consultas.', 1),
(149, 'Ver papelera de patologias', 'Permite administrar la papelera relacionada con las patologias.', 1),
(150, 'Ver papelera de alergias', 'Permite administrar la papelera relacionada con las alergias.', 1),
(151, 'Ver papelera de sintomas', 'Permite administrar la papelera relacionada con los sintomas.', 1),
(153, 'Ver papelera de medicamentos', 'Permite administrar la papelera relacionada con los medicamentos.', 1),
(154, 'Ver papelera de lotes', 'Permite administrar la papelera relacionada con los lotes.', 1),
(155, 'Ver papelera de medicos', 'Permite administrar la papelera relacionada con los medicos.', 1),
(156, 'Ver papelera de areas', 'Permite administrar la papelera relacionada con las areas.', 1),
(157, 'Ver papelera de especialidades', 'Permite ve las papelera relacionada con las especialidades.', 1),
(158, 'Ver papelera de usuarios', 'Permite administrar la papelera relacionada con los usuarios.', 1),
(159, 'Ver papelera de roles', 'Permite administrar la papelera relacionada con los roles.', 1),
(160, 'Ver papelera de permisos', 'Permite administrar la papelera relacionada con los permisos.', 1),
(161, 'Gestionar acciones de pacientes', 'Permite realizar acciones en el modulo de pacientes (editar, eliminar, etc).', 1),
(162, 'Gestionar acciones de pacientes menores de edad', 'Permite realizar acciones en el modulo de pacientes menores de edad (editar, eliminar, etc).', 1),
(163, 'Gestionar acciones de representantes', 'Permite realizar acciones en el modulo de representantes (editar, eliminar, etc).', 1),
(164, 'Gestionar acciones de citas', 'Permite realizar acciones en el modulo de citas (editar, eliminar, etc).', 1),
(165, 'Gestionar acciones de consultas', 'Permite realizar acciones en el modulo de consultas (editar, eliminar, etc).', 1),
(166, 'Gestionar acciones de patologias', 'Permite realizar acciones en el modulo de patologias (editar, eliminar, etc).', 1),
(167, 'Gestionar acciones de alergias', 'Permite realizar acciones en el modulo de alergias (editar, eliminar, etc).', 1),
(168, 'Gestionar acciones de sintomas', 'Permite realizar acciones en el modulo de sintomas (editar, eliminar, etc).', 1),
(169, 'Gestionar acciones de inventario', 'Permite realizar acciones en el modulo de inventario (editar, eliminar, etc).', 1),
(170, 'Gestionar acciones de medicamentos', 'Permite realizar acciones en el modulo de medicamentos (editar, eliminar, etc).', 1),
(171, 'Gestionar acciones de lotes', 'Permite realizar acciones en el modulo de lotes (editar, eliminar, etc).', 1),
(172, 'Gestionar acciones de medicos', 'Permite realizar acciones en el modulo de medicos (editar, eliminar, etc).', 1),
(173, 'Gestionar acciones de areas', 'Permite realizar acciones en el modulo de areas (editar, eliminar, etc).', 1),
(174, 'Gestionar acciones de especialidades', 'Permite realizar acciones en el modulo de especialidades (editar, eliminar, etc).', 1),
(175, 'Gestionar acciones de usuarios', 'Permite realizar acciones en el modulo de usuarios (editar, eliminar, etc).', 1),
(176, 'Gestionar acciones de roles', 'Permite realizar acciones en el modulo de roles (editar, eliminar, etc).', 1),
(177, 'Gestionar acciones de permisos', 'Permite realizar acciones en el modulo de permisos (editar, eliminar, etc).', 1),
(178, 'Crear Proveedores', 'Permiso para crear proveedores', 1),
(179, 'Editar Proveedores', 'Permiso para editar proveedores', 1),
(180, 'Eliminar Proveedores', 'Permiso para eliminar proveedores', 1),
(181, 'Ver Proveedores', 'Permiso para ver proveedores', 1),
(182, 'Crear Laboratorios', 'Permiso para crear laboratorios', 1),
(183, 'Editar Laboratorios', 'Permiso para editar laboratorios', 1),
(184, 'Eliminar Laboratorios', 'Permiso para eliminar laboratorios', 1),
(185, 'Ver Laboratorios', 'Permiso para ver laboratorios', 1),
(186, 'Generar Despacho de Inventario', 'Permiso para despachar medicamentos', 1),
(187, 'Ver kardex de medicamentos', 'Permiso para ver el kardex de los medicamentos', 1),
(188, 'Desactivar Proveedores', 'Permiso para desactivar proveedores', 1),
(189, 'Desactivar Laboratorios', 'Permiso para desactivar laboratorios', 1),
(190, 'Reactivar Proveedores', 'Permiso para reactivar proveedores', 1),
(191, 'Reactivar Laboratorios', 'Permiso para reactivar laboratorios', 1),
(192, 'Gestionar acciones de proveedores', 'Permiso para gestionar las acciones de los proveedores', 1),
(193, 'Gestionar acciones de laboratorios', 'Permiso para gestionar las acciones de los laboratorios', 1),
(194, 'Gestionar acciones de recetas', 'Permiso para gestionar las acciones de las recetas', 1),
(195, 'Ver Movimientos de Inventario', 'Permite ver los movimientos de entrada y salida en el inventario', 1),
(196, 'Ver papelera de laboratorios', 'Permite ver los registros eliminados de los laboratorios', 1),
(197, 'Ver papelera de proveedores', 'Permite ver los registros eliminados de los proveedores', 1),
(198, 'Ver informacion de recetas', 'Permite ver la informacion de las recetas', 1),
(199, 'Ver Recetas', 'Permiso para ver el modulo de recetas', 1),
(200, 'Cancelar Recetas', 'Permite cancelar la orden actual de una receta.', 1),
(201, 'Generar reporte de kardex', 'Permite generar un reporte en PDF del kardex de medicamentos', 1),
(202, 'Crear Pedidos', 'Permite crear una orden para pedir medicamentos a un proveedor en especifico', 1),
(203, 'Gestionar acciones de pedidos', 'Permite ver las distintas opciones de los pedidos', 1),
(204, 'Ver Pedidos', 'Permite ver los pedidos', 1),
(205, 'Generar reportes de pedidos', 'Permite crear reportes generales de los pedidos', 1),
(206, 'Cancelar Pedidos', 'Permite cancelar un pedido', 1),
(207, 'Generar Pedidos', 'Permite generar pedidos o ir al crud', 1),
(208, 'Ver panel de despachador', 'Permite ver el panel del encargado de despachar medicamentos', 1),
(209, 'Generar Reporte de Recetas', 'Permite generar reportes de recetas', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `persona`
--

CREATE TABLE `persona` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `tipo_cedula` varchar(2) NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `fecha_nacimiento` date NOT NULL,
  `genero` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(100) NOT NULL,
  `login_attempts` int(11) DEFAULT '0',
  `last_login_attempt` datetime DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `estatus` int(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `persona`
--

INSERT INTO `persona` (`id`, `nombre`, `apellido`, `tipo_cedula`, `cedula`, `fecha_nacimiento`, `genero`, `email`, `password`, `login_attempts`, `last_login_attempt`, `reset_token`, `token_expiry`, `estatus`) VALUES
(189, 'Administrador', '', '', NULL, '0000-00-00', '', 'Admin@gmail.com', '$2y$10$EGos8b6SaoaFZchVovQeE.XKEiiIMFnMVlBNSr4yqYFSnC5Q5AlsK', 0, '2026-04-18 15:07:25', '786882', '2026-03-13 00:03:15', 1),
(281, 'Farmaceutico', '', '', NULL, '0000-00-00', '', 'farmacia1@gmail.com', '$2y$10$Z4ra3/9G2YevG8DqH9nOreaVP8zCN0mjxrIf5RnkJ0OeU1OplYNU6', 0, NULL, NULL, NULL, 1),
(283, 'Supervisor', '', '', NULL, '0000-00-00', '', 'supervisor@gmail.com', '$2y$10$LpUujaFYGLR8dh8TLbVeSOPfgEPOoSUhUBRPwDegm4vmCAjesma6K', 0, NULL, NULL, NULL, 2),
(284, 'Recursos Humanos', '', '', NULL, '0000-00-00', '', 'RH2026@gmail.com', '$2y$10$OZv49JoBe5QAdfDZSthS0.vAX2Z5P/vmcjo5YdNMYEL8K2vP5NMz.', 0, NULL, NULL, NULL, 2),
(328, 'Ezequiel', 'Veroez', 'V', '22333333', '2004-12-07', 'Masculino', 'Deybersilva12@gmail.com', '', 0, NULL, NULL, NULL, 1),
(329, 'Francisco', 'Perez Mendoza', 'V', '23456646', '1994-11-01', 'Masculino', 'Camilo@gmail.com', '', 0, NULL, NULL, NULL, 1),
(330, 'Mario', 'Gomez', 'V', '24244252', '1994-11-01', 'Masculino', '', '', 0, NULL, NULL, NULL, 1),
(331, 'Steve ', 'Rogers', 'V', '31306212', '2011-03-01', 'Masculino', NULL, '', 0, NULL, NULL, NULL, 1),
(332, 'Maximiliano', 'Vasquez', 'V', '34567890', '1994-11-01', 'Masculino', '', '', 0, NULL, NULL, NULL, 1),
(339, 'Despachador', NULL, '', NULL, '0000-00-00', '', 'DespachadorOffi@gmail.com', '$2y$10$Kkjf20ZHBHwn3f6CGKXXd.LLK9WJIFeXGFMxnVT1tRYsgq4tmAV9i', 0, '2026-04-19 22:16:37', NULL, NULL, 2),
(340, 'Mario ', 'Mario', 'V', '23445525', '1986-01-06', 'Masculino', 'mario12@gmail.com', '$2y$10$p8h4Ro6UFZaGZk2jJCMmmuwleZonrXg8EcTH13D2pZ6PUHU.Cu1fa', 0, NULL, NULL, NULL, 2),
(341, 'Maicol', 'Jackson', 'V', '33333333', '2008-05-14', 'Masculino', 'Deyber12@gmail.com', '', 0, NULL, NULL, NULL, 2),
(343, 'sss', 'sssd', 'V', '22433333', '2008-05-15', 'Masculino', '', '', 0, NULL, NULL, NULL, 1),
(344, 'XSD', 'DSD', 'V', '12222222', '2026-05-15', 'Masculino', NULL, '', 0, NULL, NULL, NULL, 1),
(346, 'ABCE', 'HSH', 'V', '44444444', '1995-08-13', 'Masculino', '', '', 0, NULL, NULL, NULL, 1),
(348, 'DDDDD', 'SSSSS', 'V', '77777777', '1988-01-18', 'Masculino', '', '', 0, NULL, NULL, NULL, 1),
(349, 'BYE', 'HELLO', 'V', '54384384', '2005-09-19', 'Masculino', '', '', 0, NULL, NULL, NULL, 1),
(350, 'HOLA', 'CHAO', 'PN', '20000000000000000000', '2026-05-19', 'Masculino', NULL, '', 0, NULL, NULL, NULL, 1),
(351, 'SELECT', 'XXX', 'V', '31306213', '2008-05-24', 'Masculino', 'XXX@gmail.com', '', 0, NULL, NULL, NULL, 0),
(352, 'ssss', '', 'V', '33333332', '2008-06-06', 'Masculino', '', '', 0, NULL, NULL, NULL, 2),
(353, 'XX', 'sjnds', 'V', '31306211', '2008-06-06', 'Masculino', '', '', 0, NULL, NULL, NULL, 2),
(354, 'Fernanda', 'Garcia', 'V', '34306215', '2008-06-13', 'Femenino', '', '', 0, NULL, NULL, NULL, 1),
(355, 'Daniel ', 'Veroez', 'V', '23475858', '2008-06-13', 'Masculino', '', '', 0, NULL, NULL, NULL, 2),
(356, 'Fermin', 'Lopez', 'V', '23434343', '2008-06-13', 'Masculino', '', '', 0, NULL, NULL, NULL, 2),
(357, 'Gabriel', 'Mendoza', 'V', '23553253', '2008-06-13', 'Masculino', '', '', 0, NULL, NULL, NULL, 1),
(358, 'David', 'Silva', 'RP', '31306211-1', '2025-10-13', 'Masculino', NULL, '', 0, NULL, NULL, NULL, 1),
(359, 'Carles', 'Puyol', 'V', '25353535', '2008-06-14', 'Masculino', 'CarlitoP@gmail.com', '', 0, NULL, NULL, NULL, 2),
(360, 'Manzana', '', 'V', '33244323', '2008-06-15', 'Masculino', '', '', 0, NULL, NULL, NULL, 1),
(361, 'Cebollin', '', 'PN', '52353532523523523532', '2026-06-14', 'Masculino', NULL, '', 0, NULL, NULL, NULL, 1),
(362, 'XL', '', 'V', '32423423', '2008-06-14', 'Masculino', '', '', 0, NULL, NULL, NULL, 2),
(363, 'Deybersito', NULL, '', NULL, '0000-00-00', '', 'silvadeyber0712@gmail.com', '$2y$10$fKO98WZgjZSKLc.FvqCNfOsZ5eJ1B.LGAfCNp6fnYV2ZwpBjQiOLe', 0, '2026-06-18 17:34:56', NULL, '2026-06-18 23:55:31', 2),
(364, 'Musuculoso', '', 'V', '22222222', '2006-06-23', 'Masculino', 'XL@gmail.com', '', 0, NULL, NULL, NULL, 2),
(365, 'Alex', '', 'V', '55555555', '2008-06-23', 'Masculino', 'AWW@gmaI.com', '', 0, NULL, NULL, NULL, 1),
(366, 'ZLATAN', '', 'V', '53535333', '2006-06-23', 'Masculino', 'XLW3@gmai.com', '', 0, NULL, NULL, NULL, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prefijos_telefonos`
--

CREATE TABLE `prefijos_telefonos` (
  `Id` int(11) NOT NULL,
  `prefijo` varchar(20) COLLATE utf8_spanish_ci NOT NULL,
  `estatus` enum('1','2') COLLATE utf8_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `prefijos_telefonos`
--

INSERT INTO `prefijos_telefonos` (`Id`, `prefijo`, `estatus`) VALUES
(1, '0412', '1'),
(2, '0422', '1'),
(3, '0414', '1'),
(4, '0424', '1'),
(5, '0416', '1'),
(6, '0426', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prescripcion_medicamentos`
--

CREATE TABLE `prescripcion_medicamentos` (
  `Id` int(11) NOT NULL,
  `Id_consulta` int(11) NOT NULL,
  `Id_descripcion_medicamento` int(11) NOT NULL,
  `estado_prescripcion` enum('pendiente','entregado','parcial','no entregado','cancelado') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'pendiente',
  `estatus` int(1) NOT NULL DEFAULT '1',
  `paciente_notificado` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presentacion`
--

CREATE TABLE `presentacion` (
  `Id_presentacion` int(11) NOT NULL,
  `nombre_presentacion` varchar(100) NOT NULL,
  `estatus` enum('1','2') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `presentacion`
--

INSERT INTO `presentacion` (`Id_presentacion`, `nombre_presentacion`, `estatus`) VALUES
(1, 'Tabletas', '1'),
(2, 'Comprimido', '1'),
(3, 'Gotas', '1'),
(4, 'Suspensión', '1'),
(5, 'Crema', '1'),
(6, 'Inyectable', '1'),
(7, 'Capsulas', '1'),
(8, 'Jbe. Pediátrico', '1'),
(9, 'Solución oral', '1'),
(10, 'Blandas', '1'),
(11, 'Grageas', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `principio_activo`
--

CREATE TABLE `principio_activo` (
  `id_principio_activo` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `principio_activo`
--

INSERT INTO `principio_activo` (`id_principio_activo`, `nombre`, `descripcion`) VALUES
(1, 'Ibuprofeno', ''),
(2, 'Amoxicilina', ''),
(5, 'Losartán', ''),
(6, 'Losartán Potásico', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

CREATE TABLE `proveedor` (
  `Id_proveedor` int(11) NOT NULL,
  `nombre_proveedor` varchar(50) NOT NULL,
  `estatus` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `proveedor`
--

INSERT INTO `proveedor` (`Id_proveedor`, `nombre_proveedor`, `estatus`) VALUES
(1, 'SUAF Portuguesa', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `Id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(25) NOT NULL,
  `estatus` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`Id_rol`, `nombre_rol`, `estatus`) VALUES
(1, 'Administrador', 1),
(2, 'Supervisor', 1),
(3, 'Paciente', 1),
(4, 'Medico (Viejo)', 0),
(5, 'Representante', 1),
(6, 'Encargado de Farmacia', 1),
(7, 'Medico', 1),
(8, 'Administrador - RH', 1),
(9, 'Encargado de Despacho', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_permiso`
--

CREATE TABLE `rol_permiso` (
  `Id_rol_permiso` int(11) NOT NULL,
  `Id_rol` int(11) NOT NULL,
  `Id_permiso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `rol_permiso`
--

INSERT INTO `rol_permiso` (`Id_rol_permiso`, `Id_rol`, `Id_permiso`) VALUES
(173, 2, 3),
(174, 2, 9),
(175, 2, 15),
(176, 2, 39),
(177, 2, 45),
(178, 2, 51),
(179, 2, 53),
(180, 2, 63),
(181, 2, 69),
(182, 2, 75),
(183, 2, 81),
(184, 2, 87),
(185, 2, 101),
(186, 2, 107),
(187, 2, 111),
(188, 2, 112),
(189, 2, 113),
(190, 2, 114),
(191, 2, 115),
(192, 2, 116),
(193, 2, 161),
(194, 2, 162),
(195, 2, 164),
(196, 2, 165),
(197, 2, 172),
(198, 2, 140),
(279, 8, 1),
(280, 8, 2),
(281, 8, 3),
(282, 8, 4),
(283, 8, 7),
(284, 8, 8),
(285, 8, 9),
(286, 8, 10),
(287, 8, 13),
(288, 8, 14),
(289, 8, 15),
(290, 8, 16),
(291, 8, 115),
(292, 8, 129),
(293, 8, 130),
(294, 8, 131),
(295, 8, 139),
(296, 8, 172),
(297, 8, 173),
(298, 8, 174),
(1883, 7, 73),
(1884, 7, 55),
(1885, 7, 79),
(1886, 7, 37),
(1887, 7, 43),
(1888, 7, 61),
(1889, 7, 49),
(1890, 7, 67),
(1891, 7, 74),
(1892, 7, 56),
(1893, 7, 80),
(1894, 7, 38),
(1895, 7, 44),
(1896, 7, 62),
(1897, 7, 50),
(1898, 7, 68),
(1899, 7, 123),
(1900, 7, 122),
(1901, 7, 127),
(1902, 7, 124),
(1903, 7, 118),
(1904, 7, 119),
(1905, 7, 126),
(1906, 7, 128),
(1907, 7, 167),
(1908, 7, 165),
(1909, 7, 161),
(1910, 7, 162),
(1911, 7, 166),
(1912, 7, 168),
(1913, 7, 111),
(1914, 7, 113),
(1915, 7, 114),
(1916, 7, 75),
(1917, 7, 81),
(1918, 7, 57),
(1919, 7, 39),
(1920, 7, 45),
(1921, 7, 136),
(1922, 7, 63),
(1923, 7, 51),
(1924, 7, 69),
(2228, 6, 93),
(2229, 6, 98),
(2230, 6, 206),
(2231, 6, 200),
(2232, 6, 182),
(2233, 6, 105),
(2234, 6, 99),
(2235, 6, 202),
(2236, 6, 178),
(2237, 6, 189),
(2238, 6, 108),
(2239, 6, 102),
(2240, 6, 188),
(2241, 6, 183),
(2242, 6, 106),
(2243, 6, 100),
(2244, 6, 179),
(2245, 6, 184),
(2246, 6, 110),
(2247, 6, 104),
(2248, 6, 180),
(2249, 6, 186),
(2250, 6, 91),
(2251, 6, 207),
(2252, 6, 201),
(2253, 6, 209),
(2254, 6, 132),
(2255, 6, 134),
(2256, 6, 133),
(2257, 6, 205),
(2258, 6, 92),
(2259, 6, 169),
(2260, 6, 193),
(2261, 6, 171),
(2262, 6, 170),
(2263, 6, 203),
(2264, 6, 192),
(2265, 6, 194),
(2266, 6, 116),
(2267, 6, 191),
(2268, 6, 109),
(2269, 6, 103),
(2270, 6, 190),
(2271, 6, 198),
(2272, 6, 95),
(2273, 6, 187),
(2274, 6, 185),
(2275, 6, 107),
(2276, 6, 101),
(2277, 6, 195),
(2278, 6, 142),
(2279, 6, 143),
(2280, 6, 137),
(2281, 6, 196),
(2282, 6, 154),
(2283, 6, 153),
(2284, 6, 197),
(2285, 6, 204),
(2286, 6, 181),
(2287, 6, 199),
(2288, 9, 200),
(2289, 9, 105),
(2290, 9, 108),
(2291, 9, 106),
(2292, 9, 186),
(2293, 9, 209),
(2294, 9, 132),
(2295, 9, 134),
(2296, 9, 169),
(2297, 9, 171),
(2298, 9, 194),
(2299, 9, 116),
(2300, 9, 198),
(2301, 9, 95),
(2302, 9, 187),
(2303, 9, 185),
(2304, 9, 107),
(2305, 9, 101),
(2306, 9, 195),
(2307, 9, 142),
(2308, 9, 143),
(2309, 9, 208),
(2310, 9, 181),
(2311, 9, 199),
(2312, 1, 93),
(2313, 1, 98),
(2314, 1, 206),
(2315, 1, 200),
(2316, 1, 73),
(2317, 1, 55),
(2318, 1, 7),
(2319, 1, 85),
(2320, 1, 79),
(2321, 1, 13),
(2322, 1, 182),
(2323, 1, 105),
(2324, 1, 99),
(2325, 1, 1),
(2326, 1, 37),
(2327, 1, 43),
(2328, 1, 61),
(2329, 1, 202),
(2330, 1, 31),
(2331, 1, 178),
(2332, 1, 49),
(2333, 1, 25),
(2334, 1, 67),
(2335, 1, 19),
(2336, 1, 76),
(2337, 1, 10),
(2338, 1, 88),
(2339, 1, 82),
(2340, 1, 16),
(2341, 1, 58),
(2342, 1, 189),
(2343, 1, 108),
(2344, 1, 102),
(2345, 1, 4),
(2346, 1, 40),
(2347, 1, 46),
(2348, 1, 64),
(2349, 1, 34),
(2350, 1, 188),
(2351, 1, 52),
(2352, 1, 28),
(2353, 1, 70),
(2354, 1, 22),
(2355, 1, 74),
(2356, 1, 56),
(2357, 1, 8),
(2358, 1, 86),
(2359, 1, 80),
(2360, 1, 14),
(2361, 1, 183),
(2362, 1, 106),
(2363, 1, 100),
(2364, 1, 2),
(2365, 1, 38),
(2366, 1, 44),
(2367, 1, 62),
(2368, 1, 32),
(2369, 1, 179),
(2370, 1, 50),
(2371, 1, 26),
(2372, 1, 68),
(2373, 1, 20),
(2374, 1, 78),
(2375, 1, 12),
(2376, 1, 90),
(2377, 1, 84),
(2378, 1, 18),
(2379, 1, 60),
(2380, 1, 184),
(2381, 1, 110),
(2382, 1, 104),
(2383, 1, 6),
(2384, 1, 42),
(2385, 1, 48),
(2386, 1, 66),
(2387, 1, 36),
(2388, 1, 180),
(2389, 1, 54),
(2390, 1, 30),
(2391, 1, 72),
(2392, 1, 24),
(2393, 1, 123),
(2394, 1, 186),
(2395, 1, 91),
(2396, 1, 207),
(2397, 1, 122),
(2398, 1, 201),
(2399, 1, 209),
(2400, 1, 127),
(2401, 1, 130),
(2402, 1, 121),
(2403, 1, 124),
(2404, 1, 131),
(2405, 1, 132),
(2406, 1, 134),
(2407, 1, 133),
(2408, 1, 129),
(2409, 1, 118),
(2410, 1, 119),
(2411, 1, 126),
(2412, 1, 205),
(2413, 1, 125),
(2414, 1, 128),
(2415, 1, 92),
(2416, 1, 167),
(2417, 1, 173),
(2418, 1, 164),
(2419, 1, 165),
(2420, 1, 174),
(2421, 1, 169),
(2422, 1, 193),
(2423, 1, 171),
(2424, 1, 170),
(2425, 1, 172),
(2426, 1, 161),
(2427, 1, 162),
(2428, 1, 166),
(2429, 1, 203),
(2430, 1, 177),
(2431, 1, 192),
(2432, 1, 194),
(2433, 1, 163),
(2434, 1, 176),
(2435, 1, 168),
(2436, 1, 175),
(2437, 1, 112),
(2438, 1, 117),
(2439, 1, 111),
(2440, 1, 116),
(2441, 1, 113),
(2442, 1, 115),
(2443, 1, 114),
(2444, 1, 77),
(2445, 1, 11),
(2446, 1, 89),
(2447, 1, 83),
(2448, 1, 17),
(2449, 1, 59),
(2450, 1, 191),
(2451, 1, 109),
(2452, 1, 103),
(2453, 1, 5),
(2454, 1, 41),
(2455, 1, 47),
(2456, 1, 65),
(2457, 1, 35),
(2458, 1, 190),
(2459, 1, 53),
(2460, 1, 29),
(2461, 1, 71),
(2462, 1, 23),
(2463, 1, 75),
(2464, 1, 9),
(2465, 1, 87),
(2466, 1, 81),
(2467, 1, 15),
(2468, 1, 57),
(2469, 1, 198),
(2470, 1, 95),
(2471, 1, 187),
(2472, 1, 185),
(2473, 1, 107),
(2474, 1, 101),
(2475, 1, 3),
(2476, 1, 195),
(2477, 1, 141),
(2478, 1, 142),
(2479, 1, 143),
(2480, 1, 39),
(2481, 1, 45),
(2482, 1, 135),
(2483, 1, 150),
(2484, 1, 156),
(2485, 1, 147),
(2486, 1, 148),
(2487, 1, 157),
(2488, 1, 196),
(2489, 1, 154),
(2490, 1, 153),
(2491, 1, 155),
(2492, 1, 144),
(2493, 1, 145),
(2494, 1, 149),
(2495, 1, 160),
(2496, 1, 197),
(2497, 1, 146),
(2498, 1, 159),
(2499, 1, 151),
(2500, 1, 158),
(2501, 1, 63),
(2502, 1, 204),
(2503, 1, 33),
(2504, 1, 181),
(2505, 1, 199),
(2506, 1, 51),
(2507, 1, 27),
(2508, 1, 69),
(2509, 1, 21);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sector`
--

CREATE TABLE `sector` (
  `Id_Sector` int(11) NOT NULL,
  `nombre_sector` varchar(150) NOT NULL,
  `Id_Municipio` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `sector`
--

INSERT INTO `sector` (`Id_Sector`, `nombre_sector`, `Id_Municipio`) VALUES
(1, 'Agua Blanca', 1),
(2, 'Centro', 1),
(3, 'Banco Obrero', 1),
(4, 'Barrio Ajuro', 1),
(5, '1ero de Mayo', 1),
(6, 'Santa Ana', 1),
(7, 'El Paraíso', 1),
(8, 'La Arboleda', 1),
(9, 'Che Guevara', 1),
(10, 'El Samán', 1),
(11, 'San José', 1),
(12, 'La Manguera', 1),
(13, 'El Cementerio', 1),
(14, 'La Planta', 1),
(15, 'Urbanización Santa Bárbara', 1),
(16, 'Los Algodones', 1),
(17, 'Quebrada Honda', 1),
(18, 'Jobalito', 1),
(19, 'Los Bancos', 1),
(20, 'Los Cañizos', 1),
(21, 'Los Hijitos', 1),
(22, 'Sabana Larga', 1),
(23, 'Las Majaguas', 1),
(24, 'Los Arroyos', 1),
(25, 'Las Quebraditas', 1),
(26, 'Quebrada de Leña', 1),
(27, 'Hato Los Aguacates', 1),
(602, 'Alpargatón', 780),
(1080, 'Guanare Centro', 676),
(1081, 'Las Ameriquitas', 676),
(1082, 'Medero', 676),
(1083, 'Zona Industrial', 676),
(1084, 'Araure Centro', 677),
(1085, 'Las Acacias', 677),
(1086, 'Río Acarigua', 677),
(1087, 'Villas del Pilar', 677),
(1088, '19 de Abril', 678),
(1089, 'Acarigua Centro', 678),
(1090, 'Baraure', 678),
(1091, 'La Ciudadela', 678),
(1092, 'Camburito', 679),
(1093, 'Píritu Centro', 679),
(1094, 'El Mamón', 680),
(1095, 'Guanarito Centro', 680),
(1096, 'Biscucuy Centro', 681),
(1097, 'La Concepción', 681),
(1098, 'La Estación', 682),
(1099, 'Ospino Centro', 682),
(1100, 'Chabasquén Centro', 683),
(1101, 'Chabasquencito', 683),
(1102, 'La Comunidad', 684),
(1103, 'Papelón Centro', 684),
(1104, 'Boconoíto Centro', 685),
(1105, 'Cedeño', 685),
(1106, 'San Nicolás', 686),
(1107, 'San Rafael Centro', 686),
(1108, 'El Playón Centro', 687),
(1109, 'La Montañita', 687),
(1110, 'Chaparral', 688),
(1111, 'Payara', 688),
(1112, 'Villa Bruzual Centro', 688),
(1113, '7 de Octubre', 689),
(1114, '9 de Marzo', 689),
(1115, 'Apamate', 689),
(1116, 'Barrio Obrero Este', 689),
(1117, 'Bicentenario', 689),
(1118, 'Caño de Arroz', 689),
(1119, 'Caramacate', 689),
(1120, 'Cementerio', 689),
(1121, 'Centro Plaza', 689),
(1122, 'Cerro El Loro', 689),
(1123, 'Chaparrito', 689),
(1124, 'Colinas de Santa Clara', 689),
(1125, 'Colombia', 689),
(1126, 'Curazao', 689),
(1127, 'El Corozo', 689),
(1128, 'El Hato', 689),
(1129, 'Hugo Chávez', 689),
(1130, 'Independencia', 689),
(1131, 'Indio Atapaima', 689),
(1132, 'Jobal', 689),
(1133, 'La Arboleda', 689),
(1134, 'La Ceiba', 689),
(1135, 'La Colonia', 689),
(1136, 'La Esperanza', 689),
(1137, 'La Lucía', 689),
(1138, 'La Manga', 689),
(1139, 'La Manguera', 689),
(1140, 'La Pedrera', 689),
(1141, 'La Plazuela', 689),
(1142, 'Manchuca', 689),
(1143, 'Manguera I', 689),
(1144, 'Manuelita Sáez', 689),
(1145, 'Masato Masatico', 689),
(1146, 'Morrocoy', 689),
(1147, 'Pirital', 689),
(1148, 'Prolongación 9 de Marzo', 689),
(1149, 'Pumarroso', 689),
(1150, 'Quebrada Honda', 689),
(1151, 'Quebrada Honda I', 689),
(1152, 'Quebreditas', 689),
(1153, 'Rodeo Atapaima', 689),
(1154, 'Samaria', 689),
(1155, 'San Francisco', 689),
(1156, 'Santa Ana', 689),
(1157, 'Santa Lucía', 689),
(1158, 'Simón Bolívar', 689),
(1159, 'Tocuyano', 689),
(1160, 'Tocuyano Centro', 689),
(1161, 'Tocuyano Las Afueras', 689),
(1162, 'Venezuela', 689),
(1163, 'Villa Hermosa', 689),
(1164, 'Zambrano Roa', 689),
(1165, 'Barquisimeto Centro', 690),
(1166, 'C.C.)', 690),
(1167, 'El Este (Av. Los Leones', 690),
(1168, 'El Oeste (La Carucieña', 690),
(1169, 'La Ruezga', 690),
(1170, 'Patarata', 690),
(1171, 'Pta. de Piedra)', 690),
(1172, 'Tamaca', 690),
(1173, 'Cubiro', 691),
(1174, 'Quíbor Centro', 691),
(1175, 'Tintorero', 691),
(1176, 'El Tocuyo Centro', 692),
(1177, 'Humocaro Alto', 692),
(1178, 'Humocaro Bajo', 692),
(1179, 'Agua Viva', 693),
(1180, 'Cabudare Centro', 693),
(1181, 'La Piedad', 693),
(1182, 'Valle Hondo', 693),
(1183, 'Aregue', 694),
(1184, 'Calicanto', 694),
(1185, 'Carora Centro', 694),
(1186, 'Siquisique', 694),
(1187, 'Sanare Centro', 695),
(1188, 'Yay', 695),
(1189, 'Duaca Centro', 696),
(1190, 'El Eneal', 696),
(1191, 'Las Tunas', 697),
(1192, 'Sarare Centro', 697),
(1193, 'San Miguel', 698),
(1194, 'Siquisique Centro', 698),
(1195, 'La Esmeralda', 699),
(1196, 'Mavaca', 699),
(1197, 'Ocamo', 699),
(1198, 'Platanal', 699),
(1199, 'Tamatama', 699),
(1200, 'Comunidad Yabarana', 700),
(1201, 'Picua', 700),
(1202, 'Sabanita', 700),
(1203, 'San Fernando de Atabapo', 700),
(1204, '5 de Julio', 701),
(1205, 'Barrio Ajuro', 701),
(1206, 'El Tobogán', 701),
(1207, 'La Florida', 701),
(1208, 'Las Mercedes', 701),
(1209, 'Limón de Parhueña', 701),
(1210, 'Media Agua', 701),
(1211, 'Monte Bello', 701),
(1212, 'Pintado', 701),
(1213, 'Puerto Ayacucho (Centro)', 701),
(1214, 'Comunidad Autana (Piaroa)', 702),
(1215, 'Isla Ratón', 702),
(1216, 'San José de Paria', 702),
(1217, 'San Pedro del Orinoco', 702),
(1218, 'Comunidad Cucuy', 705),
(1219, 'Comunidad Yeral', 705),
(1220, 'Río Baria', 705),
(1221, 'San Carlos de Río Negro', 705),
(1222, 'Solano', 705),
(1223, 'Boyacá I', 706),
(1224, 'El Espejo', 706),
(1225, 'El Viñedo', 706),
(1226, 'II', 706),
(1227, 'III y IV', 706),
(1228, 'Mesones', 706),
(1229, 'Terrazas del Mar', 706),
(1230, 'Tronconal V (y sus etapas)', 706),
(1231, 'Alí Primera', 707),
(1232, 'Campo Rojo', 707),
(1233, 'Cantaura (Cercano)', 707),
(1234, 'Centro', 708),
(1235, 'El Salto', 708),
(1236, 'Chorrerón', 713),
(1237, 'La Laguna', 713),
(1238, 'Volcadero', 713),
(1239, 'Casco Central (Soledad)', 714),
(1240, 'Múcura', 714),
(1241, 'El Centro', 718),
(1242, 'La Peña', 718),
(1243, 'Boca de Uchire (Cercano)', 721),
(1244, 'Píritu Centro', 721),
(1245, 'Pueblo Abajo', 721),
(1246, 'Boca de Uchire (Costa)', 723),
(1247, 'El Guapo (Cercano)', 723),
(1248, 'San Joaquín', 724),
(1249, 'Santa Ana Centro', 724),
(1250, 'Casco Viejo', 725),
(1251, 'El Tigrito', 725),
(1252, 'Las Mercedes (El Tigre)', 725),
(1253, 'San Miguel', 725),
(1254, 'Barrio Obrero', 727),
(1255, 'El Tocal', 727),
(1256, 'La Guasima', 727),
(1257, 'Merecure', 727),
(1258, 'Puerto Miranda', 727),
(1259, 'San Fernando II', 727),
(1260, 'Achaguas Centro', 728),
(1261, 'El Yagual', 728),
(1262, 'La Esperanza', 728),
(1263, 'Mantecal', 728),
(1264, 'Bocas de Arauca', 729),
(1265, 'Casco Central (Biruaca)', 729),
(1266, 'Las Margaritas', 729),
(1267, 'Bruzual (Capital de Parroquia)', 730),
(1268, 'Casco Central (Guasdalito)', 730),
(1269, 'El Amparo (Fronterizo)', 730),
(1270, 'Morrones', 730),
(1271, 'La Victoria', 731),
(1272, 'Palmarito', 731),
(1273, 'Urdaneta', 731),
(1274, 'Centro', 732),
(1275, 'Cunaviche', 732),
(1276, 'La Trinidad de Orichuna', 732),
(1277, 'Centro de Elorza', 733),
(1278, 'El Samán de Apure', 733),
(1279, 'Puerto de Nutrias', 733),
(1280, 'Alto Barinas', 752),
(1281, 'Ciudad Varyná', 752),
(1282, 'Los Pozones', 752),
(1283, 'Mi Jardín', 752),
(1284, 'Ramón Ignacio Méndez', 752),
(1285, 'Raúl Leoni', 752),
(1286, 'Casco Central (Sabaneta)', 753),
(1287, 'La Luz', 753),
(1288, 'El Cantón Centro', 754),
(1289, 'Santa Rosa', 754),
(1290, 'Ciudad Bolivia (Capital)', 755),
(1291, 'La Pedrera', 755),
(1292, 'Ticoporo', 755),
(1293, 'Arismendi Centro', 756),
(1294, 'La Morita', 756),
(1295, 'Altamira de Cáceres', 757),
(1296, 'Barinitas Centro', 757),
(1297, 'El Molino', 757),
(1298, 'Barrancas Centro', 758),
(1299, 'Cruz Paredes', 758),
(1300, 'Capitanejo', 759),
(1301, 'Santa Bárbara Centro', 759),
(1302, 'Obispos Centro', 760),
(1303, 'San Rafael de Obispos', 760),
(1304, 'Capitanejo', 761),
(1305, 'Ciudad Pedraza Centro', 761),
(1306, 'La Acequia', 761),
(1307, 'Dolores', 762),
(1308, 'Libertad Centro', 762),
(1309, 'Santa Rosa de Pagüey', 762),
(1310, 'Ciudad de Nutrias Centro', 763),
(1311, 'Puerto de Nutrias', 763),
(1312, '11 de Abril', 765),
(1313, 'Alta Vista', 765),
(1314, 'Castillito', 765),
(1315, 'Core 8', 765),
(1316, 'Los Olivos', 765),
(1317, 'San Félix (Casco Central)', 765),
(1318, 'Villa Asia', 765),
(1319, 'Caicara Centro', 766),
(1320, 'La Urbana', 766),
(1321, 'Las Bonitas', 766),
(1322, 'El Callao Centro', 767),
(1323, 'Nacupay', 767),
(1324, 'Paraitepuy de Roraima', 768),
(1325, 'San Francisco de Yuruaní', 768),
(1326, 'Santa Elena Centro', 768),
(1327, 'Centro (Upata)', 769),
(1328, 'El Palmar', 769),
(1329, 'La Armonía', 769),
(1330, 'Guasipati Centro', 771),
(1331, 'San José', 771),
(1332, 'El Dorado (Cercano)', 772),
(1333, 'Las Claritas (Cercano)', 772),
(1334, 'Tumeremo Centro', 772),
(1335, 'Barrancón', 773),
(1336, 'La Cruz Centro', 773),
(1337, 'La Reforma', 773),
(1338, 'La Segundera', 773),
(1339, 'Maripa Centro', 773),
(1340, 'Prados de Aragua', 773),
(1341, 'Taratara', 773),
(1342, 'Ciudad Piar (Centro)', 774),
(1343, 'Las Pijiguaos (Poblado cercano)', 774),
(1344, 'El Viñedo', 775),
(1345, 'La Isabelica', 775),
(1346, 'La Michelena', 775),
(1347, 'Prebo', 775),
(1348, 'Santa Rosa', 775),
(1349, 'Zona Industrial Norte', 775),
(1350, 'Bejuma Centro', 776),
(1351, 'Las Manzanas', 776),
(1352, 'Central Tacarigua', 777),
(1353, 'Güigüe Centro', 777),
(1354, 'Manuare', 777),
(1355, 'Aguas Calientes', 778),
(1356, 'Mariara Centro', 778),
(1357, 'Ciudad Alianza', 779),
(1358, 'El Toco', 779),
(1359, 'Valles de Guacara', 779),
(1360, 'Alpargatón', 780),
(1361, 'Morón Centro', 780),
(1362, '8 de Diciembre', 781),
(1363, 'Parque Residencial Tocuyito', 781),
(1364, 'Las Agüitas', 782),
(1365, 'Los Guayos Centro', 782),
(1366, 'Paraparos', 782),
(1367, 'Canoabo', 783),
(1368, 'Miranda Centro', 783),
(1369, 'Aguirre', 784),
(1370, 'Montalbán Centro', 784),
(1371, 'Colinas de Girardot', 785),
(1372, 'La Granja', 785),
(1373, 'Mañongo', 785),
(1374, 'Centro Histórico', 786),
(1375, 'El Palito', 786),
(1376, 'Rancho Grande', 786),
(1377, 'La Esmeralda', 787),
(1378, 'Morro I y II', 787),
(1379, 'Yuma', 787),
(1380, 'San Joaquín Centro', 788),
(1381, 'Villas del Centro', 788),
(1382, 'Casco Central (Tucupita)', 799),
(1383, 'Delfín Mendoza', 799),
(1384, 'El Cajobito', 799),
(1385, 'El Morichal', 799),
(1386, 'Kokorito', 799),
(1387, 'La Perimetral', 799),
(1388, 'Araguaimujo', 800),
(1389, 'Curiapo (Capital)', 800),
(1390, 'Sacupana', 800),
(1391, 'San Francisco de Guayo', 800),
(1392, 'Imataca Centro', 801),
(1393, 'Piacoa (Cercano)', 801),
(1394, 'Sierra Imataca', 801),
(1395, 'Capure', 802),
(1396, 'Pedernales Centro (Palafitos)', 802),
(1397, 'Río Araguaimujo', 802),
(1398, 'Camaguán Centro', 829),
(1399, 'Puerto de Nutrias (Cercano)', 829),
(1400, 'Chaguaramas Centro', 830),
(1401, 'Las Galeras', 830),
(1402, 'El Socorro Centro', 831),
(1403, 'Palenque', 831),
(1404, 'Tamanaco', 832),
(1405, 'El Totumo', 833),
(1406, 'Las Mercedes Centro', 833),
(1407, 'El Sombrero Centro', 834),
(1408, 'Las Mercedes', 834),
(1409, 'Altagracia Centro', 835),
(1410, 'La Candelaria', 835),
(1411, 'San Rafael de Orituco', 835),
(1412, 'Ortiz Centro', 836),
(1413, 'San Francisco de Tiznados', 836),
(1414, 'Banco de Guanape', 837),
(1415, 'Tucupido Centro', 837),
(1416, 'Camoruco', 838),
(1417, 'El Castrero', 838),
(1418, 'El Guafal', 838),
(1419, 'San Juan Centro', 838),
(1420, 'Corozopando', 839),
(1421, 'Guayabal Centro', 839),
(1422, 'La Peña', 840),
(1423, 'San José Centro', 840),
(1424, 'Paso Hondo', 841),
(1425, 'Santa María Centro', 841),
(1426, 'Alto Chama', 843),
(1427, 'Belén', 843),
(1428, 'Campo Claro', 843),
(1429, 'El Llano', 843),
(1430, 'Glorias Patrias', 843),
(1431, 'Buenos Aires', 844),
(1432, 'El Vigía Centro', 844),
(1433, 'La Pedregosa', 844),
(1434, 'La Azulita Centro', 845),
(1435, 'Mucumpís', 845),
(1436, 'Quebrada del Agua', 846),
(1437, 'Santa Cruz Centro', 846),
(1438, 'Aricagua Centro', 847),
(1439, 'Guaimaral', 847),
(1440, 'Canaguá Centro', 848),
(1441, 'Capurí', 848),
(1442, 'Ejido Centro', 849),
(1443, 'La Mata', 849),
(1444, 'Montaña Baja', 849),
(1445, 'El Charal', 850),
(1446, 'Tucaní Centro', 850),
(1447, 'Mucubají (Páramo)', 851),
(1448, 'Santo Domingo Centro', 851),
(1449, 'Guaraque Centro', 852),
(1450, 'Los Giros', 852),
(1451, 'Arapuey Centro', 853),
(1452, 'San Rafael de Alcázar', 853),
(1453, 'San Antonio de Torondoy', 854),
(1454, 'Torondoy Centro', 854),
(1455, 'La Venta', 855),
(1456, 'Timotes Centro', 855),
(1457, 'Guaimaral', 856),
(1458, 'Santa Elena Centro', 856),
(1459, 'Las Mesas', 857),
(1460, 'Santa María Centro', 857),
(1461, 'Los Corrales', 858),
(1462, 'Pueblo Llano Centro', 858),
(1463, 'Gavidia', 859),
(1464, 'Mucuchíes Centro', 859),
(1465, 'Bailadores Centro', 860),
(1466, 'Las Playitas', 860),
(1467, 'Mucuy Alta', 861),
(1468, 'Tabay Centro', 861),
(1469, 'Chiguará (Cercana)', 862),
(1470, 'Lagunillas Centro', 862),
(1471, 'El Llano', 863),
(1472, 'Tovar Centro', 863),
(1473, 'El Guaimaro', 865),
(1474, 'Zea Centro', 865),
(1475, 'El Tambor', 866),
(1476, 'Los Teques Centro', 866),
(1477, 'San Pedro de los Altos', 866),
(1478, 'Capaya', 867),
(1479, 'Caucagua Centro', 867),
(1480, 'Cumbo', 868),
(1481, 'San José Centro', 868),
(1482, 'Las Mercedes', 869),
(1483, 'Las Minas de Baruta', 869),
(1484, 'Prados del Este', 869),
(1485, 'Carenero', 870),
(1486, 'Higuerote Centro', 870),
(1487, 'Dos Caminos', 871),
(1488, 'Mamporal Centro', 871),
(1489, 'Carrizal Centro', 872),
(1490, 'Montaña Alta', 872),
(1491, 'Altamira', 873),
(1492, 'Bello Campo', 873),
(1493, 'El Rosal', 873),
(1494, 'Charallave Centro', 874),
(1495, 'Matalinda', 874),
(1496, 'El Hatillo (Pueblo)', 875),
(1497, 'La Lagunita Country Club', 875),
(1498, 'El Cartanal', 876),
(1499, 'Santa Teresa Centro', 876),
(1500, 'El Cerrito', 877),
(1501, 'Ocumare Centro', 877),
(1502, 'La Morita', 878),
(1503, 'San Antonio Centro', 878),
(1504, 'Paparo', 879),
(1505, 'Río Chico Centro', 879),
(1506, 'El Volcán', 880),
(1507, 'Santa Lucía Centro', 880),
(1508, 'Cúpira Centro', 881),
(1509, 'Machurucuto', 881),
(1510, 'Cloris', 882),
(1511, 'Los Naranjos', 882),
(1512, 'Las Yeguas', 883),
(1513, 'San Francisco Centro', 883),
(1514, 'José Félix Ribas', 884),
(1515, 'La Urbina', 884),
(1516, 'Petare Norte', 884),
(1517, 'Sur', 884),
(1518, 'La Morita', 885),
(1519, 'Nueva Cúa', 885),
(1520, 'Castillejo', 886),
(1521, 'El Ingenio', 886),
(1522, 'Juanico', 887),
(1523, 'La Floresta', 887),
(1524, 'Los Godos', 887),
(1525, 'Tipuro', 887),
(1526, 'Zona Industrial', 887),
(1527, 'La Pica', 888),
(1528, 'San Antonio Centro', 888),
(1529, 'Aguasay Centro', 889),
(1530, 'La Pica del Morocho', 889),
(1531, 'Caripito Centro', 890),
(1532, 'El Bajo', 890),
(1533, 'Caripe Centro (El Jardín de Oriente)', 891),
(1534, 'Teresén', 891),
(1535, 'Areo', 892),
(1536, 'Caicara Centro', 892),
(1537, 'Ezequiel Zamora', 893),
(1538, 'Punta de Mata Centro', 893),
(1539, 'Morichal', 894),
(1540, 'Temblador Centro', 894),
(1541, 'Aragua Centro', 895),
(1542, 'Guanipa', 895),
(1543, 'Jusepín', 896),
(1544, 'Quiriquire Centro', 896),
(1545, 'Potrerito', 897),
(1546, 'Santa Bárbara Centro', 897),
(1547, 'Barrancas Centro', 898),
(1548, 'San Rafael de Barrancas', 898),
(1549, 'Los Pozos', 899),
(1550, 'Uracoa Centro', 899),
(1551, 'La Asunción Centro (Casco Colonial)', 900),
(1552, 'Sabana de Guacuco', 900),
(1553, 'Paraguachí Centro', 901),
(1554, 'Playa El Tirano', 901),
(1555, 'Fuentidueño', 902),
(1556, 'San Juan Centro', 902),
(1557, 'El Valle Centro', 903),
(1558, 'Guatamare', 903),
(1559, 'El Cercado', 904),
(1560, 'La Vecindad', 904),
(1561, 'Santa Ana Centro', 904),
(1562, 'Costa Azul', 905),
(1563, 'Pampatar Centro', 905),
(1564, 'Playa El Ángel', 905),
(1565, 'Juan Griego Centro', 906),
(1566, 'La Galera', 906),
(1567, 'Centro (Porlamar)', 907),
(1568, 'Conejeros', 907),
(1569, 'Jorge Coll', 907),
(1570, 'Boca de Río Centro', 908),
(1571, 'Robledal', 908),
(1572, 'El Guamache', 909),
(1573, 'Punta de Piedras Centro', 909),
(1574, 'El Bichar', 910),
(1575, 'San Pedro Centro', 910),
(1576, 'Cumaná Centro (Casco Histórico)', 911),
(1577, 'Cumanacoa (Cercano)', 911),
(1578, 'El Peñón', 911),
(1579, 'Santa Catalina', 911),
(1580, 'Casanay Centro', 912),
(1581, 'San Francisco', 912),
(1582, 'Las Varas', 913),
(1583, 'San José Centro', 913),
(1584, 'Playa Medina', 914),
(1585, 'Río Caribe Centro', 914),
(1586, 'El Pilar Centro', 915),
(1587, 'Tunapuy', 915),
(1588, 'Carúpano Centro', 916),
(1589, 'El Tigre', 916),
(1590, 'Playa Grande', 916),
(1591, 'Boca de Uchire', 917),
(1592, 'Marigüitar Centro', 917),
(1593, 'Río Salado', 918),
(1594, 'Yaguaraparo Centro', 918),
(1595, 'Araya Centro', 919),
(1596, 'Punta de Araya', 919),
(1597, 'El Morro', 920),
(1598, 'Tunapuy Centro', 920),
(1599, 'Güiria', 921),
(1600, 'Irapa Centro', 921),
(1601, 'Guaca', 922),
(1602, 'San Antonio Centro', 922),
(1603, 'Cumanacoa Centro', 923),
(1604, 'San Lorenzo', 923),
(1605, 'Cariaco Centro', 924),
(1606, 'Río Casanay', 924),
(1607, 'Güiria Centro', 925),
(1608, 'Las Malvinas', 925),
(1609, 'Barrio Obrero', 926),
(1610, 'La Concordia', 926),
(1611, 'Pirineos', 926),
(1612, 'Pueblo Nuevo', 926),
(1613, 'Cordero Centro', 927),
(1614, 'La Honda', 927),
(1615, 'Las Mesas Centro', 928),
(1616, 'Pánaga', 928),
(1617, 'Colón Centro', 929),
(1618, 'La Jabonera', 929),
(1619, 'Barrio Miranda', 930),
(1620, 'San Antonio Centro', 930),
(1621, 'La Florida', 931),
(1622, 'Táriba Centro', 931),
(1623, 'La Fría Centro', 932),
(1624, 'San Félix', 932),
(1625, 'El Piñalito', 933),
(1626, 'San Rafael Centro', 933),
(1627, 'La Fría (Aeropuerto)', 935),
(1628, 'La Tendida', 935),
(1629, 'Palmira Centro', 936),
(1630, 'Tucapé', 936),
(1631, 'Barrio Bolívar', 937),
(1632, 'Capacho Nuevo Centro', 937),
(1633, 'La Grita Centro', 938),
(1634, 'Mesa de Aura', 938),
(1635, 'El Cobre Centro', 939),
(1636, 'Las Adjuntas', 939),
(1637, 'El Cafetal', 940),
(1638, 'Rubio Centro', 940),
(1639, 'Capacho Viejo Centro', 941),
(1640, 'Peracal (Fronterizo)', 941),
(1641, 'Abejales Centro', 942),
(1642, 'Borotá', 942),
(1643, 'Las Minas', 943),
(1644, 'Lobatera Centro', 943),
(1645, 'El Zumbador', 944),
(1646, 'Michelena Centro', 944),
(1647, 'Boca de Grita', 945),
(1648, 'Coloncito Centro', 945),
(1649, 'Aguas Calientes (Fronterizo)', 946),
(1650, 'Ureña Centro', 946),
(1651, 'Delicias Centro', 947),
(1652, 'Pata de Gallina', 947),
(1653, 'La Tendida Centro', 948),
(1654, 'San Simón', 948),
(1655, 'El Trapiche', 949),
(1656, 'Umuquena Centro', 949),
(1657, 'Los Pinos', 950),
(1658, 'Seboruco Centro', 950),
(1659, 'Caño Hondo', 951),
(1660, 'San Simón Centro', 951),
(1661, 'La Colorada', 952),
(1662, 'Queniquea Centro', 952),
(1663, 'El Corozo', 953),
(1664, 'San Josecito Centro', 953),
(1665, 'La Blanquita', 954),
(1666, 'Pregonero Centro', 954),
(1667, 'Santa Rosa', 955),
(1668, 'Trujillo Centro (Ciudad Portuaria)', 955),
(1669, 'El Dividive', 956),
(1670, 'Santa Isabel Centro', 956),
(1671, 'Boconó Centro', 957),
(1672, 'San Rafael de Pala', 957),
(1673, 'Tostós', 957),
(1674, 'El Paraíso', 958),
(1675, 'Sabana Grande Centro', 958),
(1676, 'Chejendé Centro', 959),
(1677, 'La Palma', 959),
(1678, 'Carache Centro', 960),
(1679, 'La Concepción', 960),
(1680, 'Escuque Centro', 961),
(1681, 'La Mesa de Escuque', 961),
(1682, 'La Ceiba Centro', 964),
(1683, 'Santa Apolonia', 964),
(1684, 'Agua Santa', 965),
(1685, 'El Dividive Centro', 965),
(1686, 'Miraflores', 966),
(1687, 'Monte Carmelo Centro', 966),
(1688, 'Jalisco', 967),
(1689, 'Motatán Centro', 967),
(1690, 'Monay', 968),
(1691, 'Pampán Centro', 968),
(1692, 'La Urbina', 969),
(1693, 'Pampanito Centro', 969),
(1694, 'Betijoque Centro', 970),
(1695, 'Los Cedros', 970),
(1696, 'Carvajal Centro', 971),
(1697, 'La Horqueta', 971),
(1698, 'La Libertad', 972),
(1699, 'Sabana de Mendoza Centro', 972),
(1700, 'Jajó', 973),
(1701, 'La Mesa Centro', 973),
(1702, 'Plata III', 974),
(1703, 'San Luis', 974),
(1704, 'Zona Industrial', 974),
(1705, '10 de Marzo', 975),
(1706, '12 de Febrero', 975),
(1707, 'Aeropuerto Internacional (Vía)', 975),
(1708, 'Camurí Grande', 975),
(1709, 'Caraballeda Centro', 975),
(1710, 'Carayaca Centro', 975),
(1711, 'Caribe', 975),
(1712, 'Caruao Centro', 975),
(1713, 'Casco Histórico (La Guaira)', 975),
(1714, 'Chichiriviche de la Costa', 975),
(1715, 'El Rincón', 975),
(1716, 'La Sabana', 975),
(1717, 'La Soublette', 975),
(1718, 'Las Salinas', 975),
(1719, 'Las Tunitas', 975),
(1720, 'Macuto Centro', 975),
(1721, 'Maiquetía Centro', 975),
(1722, 'Montesano', 975),
(1723, 'Naiguatá Centro', 975),
(1724, 'Osma', 975),
(1725, 'Palmar Oeste', 975),
(1726, 'Playa Grande', 975),
(1727, 'Tanaguarena', 975),
(1728, 'Tarmas', 975),
(1729, 'Todasana', 975),
(1730, 'Vía La Playa', 975),
(1731, 'Andrés Eloy Blanco', 976),
(1732, 'San Felipe Centro', 976),
(1733, 'San Javier', 976),
(1734, 'Cambural', 977),
(1735, 'San Pablo Centro', 977),
(1736, 'Aroa Centro', 978),
(1737, 'Las Tablas', 978),
(1738, 'Chivacoa Centro', 979),
(1739, 'El Matadero', 979),
(1740, 'Cocorote Centro', 980),
(1741, 'La Mosca', 980),
(1742, 'Brisas del Terminal', 981),
(1743, 'El Centro', 981),
(1744, 'Guatanquire', 982),
(1745, 'Sabana de Parra Centro', 982),
(1746, 'Boraure Centro', 983),
(1747, 'Campo Elías', 983),
(1748, 'El Copey', 984),
(1749, 'Yumare Centro', 984),
(1750, 'Nirgua Centro', 985),
(1751, 'Salom', 985),
(1752, 'Sabana Larga', 986),
(1753, 'Yaritagua Centro', 986),
(1754, 'Candelaria', 987),
(1755, 'Guama Centro', 987),
(1756, 'Guaremal', 988),
(1757, 'Urachiche Centro', 988),
(1758, 'Farriar Centro', 989),
(1759, 'Palmarejo', 989),
(1760, 'Circunvalación 2', 990),
(1761, 'El Milagro', 990),
(1762, 'La Candelaria', 990),
(1763, 'La Trinidad', 990),
(1764, 'Santa Lucía', 990),
(1765, 'El Toro Centro (Isla de Toas)', 991),
(1766, 'Isla de San Carlos', 991),
(1767, 'Mene Grande Centro', 992),
(1768, 'San Timoteo', 992),
(1769, 'Cabimas Centro', 993),
(1770, 'Carretera \"H\"', 993),
(1771, 'Concordia', 993),
(1772, 'El Guayabo', 994),
(1773, 'Encontrados Centro', 994),
(1774, 'San Carlos Centro', 995),
(1775, 'Santa Bárbara del Zulia', 995),
(1776, 'El Chivo Centro', 996),
(1777, 'Las Delicias', 996),
(1778, 'La Concepción Centro', 997),
(1779, 'La Paz', 997),
(1780, 'La Cañada Centro', 999),
(1781, 'Palmarejo', 999),
(1782, 'Ciudad Ojeda Centro', 1000),
(1783, 'Tía Juana', 1000),
(1784, 'Machiques Centro', 1001),
(1785, 'Tokuko (Indígena)', 1001),
(1786, 'El Moján Centro', 1002),
(1787, 'Tamare', 1002),
(1788, 'Los Puertos Centro', 1003),
(1789, 'Quisiro', 1003),
(1790, 'Paraguaipoa', 1004),
(1791, 'Sinamaica Centro', 1004),
(1792, 'Barranquitas', 1005),
(1793, 'La Villa Centro', 1005),
(1794, 'El Bajo', 1006),
(1795, 'San Francisco Centro', 1006),
(1796, 'Sierra Maestra', 1006),
(1797, 'El Mene', 1007),
(1798, 'Santa Rita Centro', 1007),
(1799, 'Campo Staff', 1008),
(1800, 'Tía Juana Centro', 1008),
(1801, 'Bobures Centro', 1009),
(1802, 'Gibralta', 1009),
(1803, 'Bachaquero Centro', 1010),
(1804, 'Mene Grande', 1010),
(1805, '23 de Enero (Bloques)', 1011),
(1806, 'Antímano Central', 1011),
(1807, 'Bello Monte (Parte)', 1011),
(1808, 'Carapita', 1011),
(1809, 'Caricuao (UD1 a UD5)', 1011),
(1810, 'Casco Central (Alrededores de la Basílica)', 1011),
(1811, 'Casco Central (El Conde)', 1011),
(1812, 'Casco Histórico (Plaza Bolívar)', 1011),
(1813, 'Catia (Centro)', 1011),
(1814, 'Ciudad Tiuna', 1011),
(1815, 'Ciudad Universitaria (UCV)', 1011),
(1816, 'Coche (Centro)', 1011),
(1817, 'El Junquito (Parte)', 1011),
(1818, 'El Paraíso', 1011),
(1819, 'El Silencio', 1011),
(1820, 'El Valle (Parte Alta y Baja)', 1011),
(1821, 'La Candelaria', 1011),
(1822, 'La Vega Central', 1011),
(1823, 'Lídice', 1011),
(1824, 'límite)', 1011),
(1825, 'Los Chaguaramos', 1011),
(1826, 'Los Magallanes de Catia', 1011),
(1827, 'Macarao Centro', 1011),
(1828, 'Montalbán', 1011),
(1829, 'Montalbán (Parte)', 1011),
(1830, 'Petare (Cercano', 1011),
(1831, 'Ruiz Pineda', 1011),
(1832, 'Sabana Grande', 1011),
(1833, 'San Agustín del Norte', 1011),
(1834, 'San Agustín del Sur', 1011),
(1835, 'San Bernardino', 1011),
(1836, 'San Juan Centro', 1011),
(1837, 'Sarría', 1011),
(1838, 'Monseñor Iturriza', 803),
(1839, 'Cruz Verde', 803),
(1840, 'Bobare', 803),
(1841, 'Casco Histórico (Coro)', 803),
(1842, 'Las Mercedes (Punto Fijo)', 808),
(1843, 'Punto Fijo Centro', 808),
(1844, 'Antiguo Aeropuerto', 808),
(1845, 'Pueblo Nuevo', 808),
(1846, 'Santa Cruz Centro', 816),
(1847, 'Amuay', 816),
(1848, 'El Pico', 816),
(1849, 'Pueblo Nuevo Centro', 812),
(1850, 'Adícora', 812),
(1851, 'El Supí', 812),
(1852, 'La Vela Centro', 809),
(1853, 'Curazaito', 809),
(1854, 'Puerto Cumarebo Centro', 827),
(1855, 'La Ciénaga', 827),
(1856, 'La Cruz Centro', 824),
(1857, 'Taratara', 824),
(1858, 'Tocópero Centro', 825),
(1859, 'El Volcán', 825),
(1860, 'Píritu Centro', 821),
(1861, 'El Guárico', 821),
(1862, 'Mirimire Centro', 822),
(1863, 'Agua Larga', 822),
(1864, 'Tucacas Centro', 823),
(1865, 'Chichiriviche (Cercano)', 823),
(1866, 'Palma Sola Centro', 819),
(1867, 'San Rafael', 819),
(1868, 'Chichiriviche Centro', 818),
(1869, 'Flamenco', 818),
(1870, 'San Juan de los Cayos Centro', 804),
(1871, 'Boca de Tocuyo', 804),
(1872, 'Jacura Centro', 815),
(1873, 'Guajaca', 815),
(1874, 'Cabure Centro', 820),
(1875, 'Curimagua', 820),
(1876, 'San Luis Centro', 805),
(1877, 'Baracara', 805),
(1878, 'Santa Cruz Centro', 826),
(1879, 'Santa Inés', 826),
(1880, 'Churuguara Centro', 813),
(1881, 'El Paují', 813),
(1882, 'Pedregal Centro', 811),
(1883, 'Agua Clara', 811),
(1884, 'Capatárida Centro', 806),
(1885, 'San Juan de la Costa', 806),
(1886, 'Dabajuro Centro', 810),
(1887, 'Las Delicias', 810),
(1888, 'Mene de Mauroa Centro', 817),
(1889, 'El Carrizal', 817),
(1890, 'Urumaco Centro', 814),
(1891, 'Barranquita', 814),
(1892, 'San Carlos Centro', 798),
(1893, 'La Candelaria', 798),
(1894, 'La Colonia', 798),
(1895, 'Lomas de San Carlos', 798),
(1896, 'Casco Central (Tinaco)', 793),
(1897, 'La Floresta', 793),
(1898, 'La Guamita', 793),
(1899, 'Las Vegas', 792),
(1900, 'Pueblo Nuevo', 792),
(1901, 'Tamanaco', 792),
(1902, 'Cojedes Centro', 791),
(1903, 'El Baúl (Capital de Parroquia)', 791),
(1904, 'San Rafael de Onoto (Cercano)', 791),
(1905, 'El Baúl Centro', 793),
(1906, 'La Blanquera', 793),
(1907, 'Macapo Centro', 794),
(1908, 'La Aguadita', 794),
(1909, 'El Pao Centro', 795),
(1910, 'Mata Oscura', 795),
(1911, 'Libertad Centro', 796),
(1912, 'Las Galeras', 796),
(1913, 'Las Vegas Centro', 797),
(1914, 'Mata de Agua', 797),
(1915, 'La Floresta', 887),
(1916, 'Los Godos', 887),
(1917, 'Tipuro', 887),
(1918, 'Zona Industrial', 887),
(1919, 'Juanico', 887),
(1920, 'La Pica', 888),
(1921, 'San Antonio Centro', 888),
(1922, 'Aguasay Centro', 889),
(1923, 'La Pica del Morocho', 889),
(1924, 'Caripito Centro', 890),
(1925, 'El Bajo', 890),
(1926, 'Caripe Centro (El Jardín de Oriente)', 891),
(1927, 'Teresén', 891),
(1928, 'Areo', 892),
(1929, 'Caicara Centro', 892),
(1930, 'Ezequiel Zamora', 893),
(1931, 'Punta de Mata Centro', 893),
(1932, 'Morichal', 894),
(1933, 'Temblador Centro', 894),
(1934, 'Aragua Centro', 895),
(1935, 'Guanipa', 895),
(1936, 'Jusepín', 896),
(1937, 'Quiriquire Centro', 896),
(1938, 'Potrerito', 897),
(1939, 'Santa Bárbara Centro', 897),
(1940, 'Barrancas Centro', 898),
(1941, 'San Rafael de Barrancas', 898),
(1942, 'Los Pozos', 899),
(1943, 'Uracoa Centro', 899),
(1944, 'Las Delicias', 734),
(1945, 'El Limón', 734),
(1946, 'San José', 734),
(1947, 'La Democracia', 734),
(1948, 'Caña de Azúcar', 734),
(1949, 'Base Aragua', 734),
(1950, 'La Encrucijada', 748),
(1951, 'Samán de Güere', 748),
(1952, 'San Joaquín de Turmero', 748),
(1953, 'Santa Rita', 737),
(1954, 'Paraparal', 737),
(1955, '1ro de Mayo', 737),
(1956, 'La Pica', 741),
(1957, 'Santa Ana', 741),
(1958, 'Casco Central (Palo Negro)', 741),
(1959, 'Prados de Aragua', 748),
(1960, 'La Segundera', 748),
(1961, 'Barrancón', 748),
(1962, 'El Castaño', 739),
(1963, 'El Centro', 739),
(1964, 'Las Tablitas', 739),
(1965, 'El Consejo Centro', 740),
(1966, 'Tiara', 740),
(1967, 'El Centro (Colonia Tovar)', 749),
(1968, 'La Planta', 749),
(1969, 'La Candelaria', 742),
(1970, 'El Limón (Centro)', 742),
(1971, 'Centro (Santa Cruz)', 738),
(1972, 'La Carrizalera', 738),
(1973, 'Centro', 735),
(1974, 'La Encrucijada de San Mateo', 735),
(1975, 'Centro de Barbacoas', 750),
(1976, 'Taguay', 750),
(1977, 'Villa de Cura Centro', 751),
(1978, 'Las Rosas', 751),
(1979, 'Magdaleno', 751),
(1980, 'San Casimiro Centro', 744),
(1981, 'Cumboto', 744),
(1982, 'San Sebastián Centro', 745),
(1983, 'Las Dos Bocas', 745),
(1984, 'Camatagua Centro', 736),
(1985, 'Tocorón', 736),
(1986, 'Ocumare Centro', 743),
(1987, 'Cata', 743),
(1988, 'Choroní (Cercano)', 743),
(1989, 'Lechería (Parte)', 716),
(1990, 'Cerro Amarillo', 716),
(1991, 'Pozuelos', 716),
(1992, 'El Pensil', 716),
(1993, 'Guamachito', 716),
(1994, 'Bella Vista', 716),
(1995, 'Casco Central (Lechería)', 709),
(1996, 'Cerro el Morro', 709),
(1997, 'Complejo Turístico El Morro', 709),
(1998, 'Urica', 720),
(1999, 'El Cumbre', 720),
(2000, 'Santa Rosa', 720),
(2001, 'Alí Primera', 707),
(2002, 'Campo Rojo', 707);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sintomas`
--

CREATE TABLE `sintomas` (
  `Id_sintomas` int(11) NOT NULL,
  `nombre_sintoma` varchar(45) NOT NULL,
  `estatus` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `sintomas`
--

INSERT INTO `sintomas` (`Id_sintomas`, `nombre_sintoma`, `estatus`) VALUES
(13, 'Tos', 1),
(14, 'Fiebre', 1),
(15, 'Dolor de cabeza', 1),
(16, 'Polidipsia', 1),
(17, 'Poliuria', 1),
(18, 'Fatiga', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitud_medicamento`
--

CREATE TABLE `solicitud_medicamento` (
  `id_solicitud` int(11) NOT NULL,
  `origen` enum('Interno','Externo') NOT NULL,
  `id_consulta` int(11) DEFAULT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_medico` int(11) NOT NULL,
  `entregado_a` varchar(45) NOT NULL,
  `estatus_general` enum('Pendiente','Parcial','Completado','Cancelado') DEFAULT 'Pendiente',
  `fecha_solicitud` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `solicitud_medicamento`
--

INSERT INTO `solicitud_medicamento` (`id_solicitud`, `origen`, `id_consulta`, `id_paciente`, `id_medico`, `entregado_a`, `estatus_general`, `fecha_solicitud`) VALUES
(1, 'Externo', NULL, 328, 21, 'Ezequiel Veroez', 'Completado', '2026-06-17 17:43:51'),
(2, 'Externo', NULL, 328, 21, 'Alguien', 'Completado', '2026-06-17 20:28:22'),
(3, 'Externo', NULL, 328, 21, 'Michigan', 'Completado', '2026-06-17 20:51:24'),
(4, 'Externo', NULL, 328, 21, 'Manuel', 'Pendiente', '2026-06-25 11:56:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `telefonos_personas`
--

CREATE TABLE `telefonos_personas` (
  `Id` int(11) NOT NULL,
  `Id_prefijo` int(11) NOT NULL,
  `telefono` varchar(20) COLLATE utf8_spanish_ci DEFAULT NULL,
  `Id_persona` int(11) NOT NULL,
  `estatus` enum('1','2') COLLATE utf8_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `telefonos_personas`
--

INSERT INTO `telefonos_personas` (`Id`, `Id_prefijo`, `telefono`, `Id_persona`, `estatus`) VALUES
(319, 6, '4363741', 328, '1'),
(321, 1, '2455252', 330, '1'),
(322, 1, '2333333', 332, '1'),
(328, 1, '3423534', 340, '2'),
(329, 1, '2422222', 341, '2'),
(331, 1, '2333333', 343, '1'),
(333, 2, '4222222', 346, '1'),
(335, 1, '2222222', 349, '1'),
(336, 1, '2422552', 351, '2'),
(337, 1, '9888898', 352, '2'),
(338, 1, '2222222', 353, '2'),
(339, 1, '9393232', 354, '1'),
(340, 3, '4242267', 355, '2'),
(341, 1, '3333333', 356, '2'),
(342, 3, '2444444', 357, '1'),
(343, 1, '2824848', 359, '2'),
(344, 1, '2342242', 360, '1'),
(345, 2, '6464646', 348, '1'),
(346, 1, '2424442', 362, '2'),
(347, 1, '3255322', 364, '2'),
(348, 1, '3555353', 365, '1'),
(349, 1, '5252525', 366, '2');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_estilos_de_vida`
--

CREATE TABLE `tipos_estilos_de_vida` (
  `Id` int(11) NOT NULL,
  `descripcion` varchar(150) COLLATE utf8_spanish_ci DEFAULT NULL,
  `estatus` enum('1','2') COLLATE utf8_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `tipos_estilos_de_vida`
--

INSERT INTO `tipos_estilos_de_vida` (`Id`, `descripcion`, `estatus`) VALUES
(14, 'c', '1'),
(15, 's', '1'),
(16, 'D', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_movimiento`
--

CREATE TABLE `tipo_movimiento` (
  `Id_tipo_movimiento` int(11) NOT NULL,
  `nombre` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `tipo_movimiento`
--

INSERT INTO `tipo_movimiento` (`Id_tipo_movimiento`, `nombre`) VALUES
(1, 'Entrada'),
(2, 'Salida por Despacho'),
(3, 'Salida por Vencimiento'),
(4, 'Salida por Dañado'),
(5, 'Salida por Pérdida o Robo'),
(6, 'Ajuste por Cuadre (Entrada)'),
(7, 'Ajuste por Cuadre (Salida)'),
(8, 'Reversión de Entrada (Anulación)'),
(9, 'Reversión de Salida (Anulación)');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `unidad_medida`
--

CREATE TABLE `unidad_medida` (
  `Id_unidad_medida` int(11) NOT NULL,
  `unidad` varchar(50) DEFAULT NULL,
  `estatus` enum('1','2') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `unidad_medida`
--

INSERT INTO `unidad_medida` (`Id_unidad_medida`, `unidad`, `estatus`) VALUES
(1, 'g', '1'),
(2, 'mg', '1'),
(3, 'mcg', '1'),
(4, 'l', '1'),
(5, 'ml', '1'),
(6, 'cc', '1');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alergias_conocidas`
--
ALTER TABLE `alergias_conocidas`
  ADD PRIMARY KEY (`Id_alergias_conocidas`);

--
-- Indices de la tabla `antecedentes_familiares`
--
ALTER TABLE `antecedentes_familiares`
  ADD PRIMARY KEY (`Id`);

--
-- Indices de la tabla `antecedentes_perinatales`
--
ALTER TABLE `antecedentes_perinatales`
  ADD PRIMARY KEY (`Id`);

--
-- Indices de la tabla `antecedentes_sexuales_reproductivos`
--
ALTER TABLE `antecedentes_sexuales_reproductivos`
  ADD PRIMARY KEY (`Id`);

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id_cita`),
  ADD KEY `fk_cita_paciente` (`Id_paciente`),
  ADD KEY `fk_cita_medico` (`Id_medico`),
  ADD KEY `fk_cita_especialidad` (`Id_especialidad`);

--
-- Indices de la tabla `consulta`
--
ALTER TABLE `consulta`
  ADD PRIMARY KEY (`Id_consulta`),
  ADD KEY `FK_consulta1` (`Id_historial`),
  ADD KEY `FK_consulta2` (`Id_medico`),
  ADD KEY `FK_consulta3` (`Id_paciente`);

--
-- Indices de la tabla `departamento`
--
ALTER TABLE `departamento`
  ADD PRIMARY KEY (`Id_departamento`);

--
-- Indices de la tabla `descripcion_medicamento`
--
ALTER TABLE `descripcion_medicamento`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Id_presentacion` (`Id_presentacion`),
  ADD KEY `id_medicamento` (`Id_medicamento`),
  ADD KEY `id_laboratorio` (`Id_laboratorio`),
  ADD KEY `Id_tipo_concentracion` (`Id_tipo_concentracion`);

--
-- Indices de la tabla `detalle_inventario`
--
ALTER TABLE `detalle_inventario`
  ADD PRIMARY KEY (`Id_detalle_inventario`),
  ADD KEY `FK_detalle_inventario1` (`Id_Persona`),
  ADD KEY `FK_detalle_inventario2` (`Id_TipoMovimiento`),
  ADD KEY `Id_prescripcion` (`Id_prescripcion`),
  ADD KEY `FK_detalle_inventario_receptor` (`Id_receptor`);

--
-- Indices de la tabla `detalle_medico`
--
ALTER TABLE `detalle_medico`
  ADD PRIMARY KEY (`Id_detalle_medico`),
  ADD KEY `FK_detalle_medico1` (`Id_persona`);

--
-- Indices de la tabla `detalle_paciente`
--
ALTER TABLE `detalle_paciente`
  ADD PRIMARY KEY (`Id_detalle_paciente`),
  ADD KEY `id_persona` (`id_persona`);

--
-- Indices de la tabla `detalle_paciente_menor`
--
ALTER TABLE `detalle_paciente_menor`
  ADD PRIMARY KEY (`Id_detalle_paciente_menor`),
  ADD UNIQUE KEY `id_persona_2` (`id_persona`),
  ADD KEY `id_persona` (`id_persona`),
  ADD KEY `id_representante` (`id_representante`);

--
-- Indices de la tabla `detalle_patologia_medicamento`
--
ALTER TABLE `detalle_patologia_medicamento`
  ADD PRIMARY KEY (`Id_detalle_patologia_medicamento`),
  ADD KEY `Id_patologia` (`Id_patologia`),
  ADD KEY `detalle_patologia_medicamento_ibfk_2` (`Id_medicamento`);

--
-- Indices de la tabla `detalle_patologia_sintomas`
--
ALTER TABLE `detalle_patologia_sintomas`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Id_patologia` (`Id_patologia`),
  ADD KEY `Id_sintoma` (`Id_sintoma`);

--
-- Indices de la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_pedido` (`id_pedido`),
  ADD KEY `id_descripcion_medicamento` (`id_descripcion_medicamento`);

--
-- Indices de la tabla `detalle_persona_rol`
--
ALTER TABLE `detalle_persona_rol`
  ADD PRIMARY KEY (`Id_detalle_persona_rol`),
  ADD KEY `FK_detalleRol1` (`Id_rol`),
  ADD KEY `FK_detalleRol2` (`Id_persona`);

--
-- Indices de la tabla `detalle_presentacion_medicamentos`
--
ALTER TABLE `detalle_presentacion_medicamentos`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `FK_presentaciones_medicamentos1` (`Id_medicamento`),
  ADD KEY `FK_presentaciones_medicamentos2` (`Id_presentacion`);

--
-- Indices de la tabla `detalle_principio_medicamento`
--
ALTER TABLE `detalle_principio_medicamento`
  ADD PRIMARY KEY (`Id_principio_medicamento`),
  ADD KEY `id_tipo_unidad_medida` (`id_tipo_unidad_medida`),
  ADD KEY `id_medicamento` (`id_medicamento`),
  ADD KEY `id_principio_activo` (`id_principio_activo`);

--
-- Indices de la tabla `detalle_solicitud`
--
ALTER TABLE `detalle_solicitud`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `FK_detalle_solicitud_principal` (`id_solicitud`),
  ADD KEY `FK_detalle_medicamento_catalogo` (`id_medicamento`);

--
-- Indices de la tabla `direccion`
--
ALTER TABLE `direccion`
  ADD PRIMARY KEY (`Id_Direccion`),
  ADD UNIQUE KEY `Id_persona` (`Id_persona`),
  ADD KEY `FK_direccion1` (`Id_persona`),
  ADD KEY `FK_direccion2` (`Id_sector`);

--
-- Indices de la tabla `especialidad`
--
ALTER TABLE `especialidad`
  ADD PRIMARY KEY (`Id_especialidad`);

--
-- Indices de la tabla `especialidades_medicos`
--
ALTER TABLE `especialidades_medicos`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `FK_especialidades_medicos1` (`Id_detalle_medico`),
  ADD KEY `FK_especialidades_medicos2` (`Id_especialidad`);

--
-- Indices de la tabla `estado`
--
ALTER TABLE `estado`
  ADD PRIMARY KEY (`Id_Estado`),
  ADD KEY `FK_Estado1` (`Id_Pais`);

--
-- Indices de la tabla `estilos_de_vida_paciente`
--
ALTER TABLE `estilos_de_vida_paciente`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `FK_estilos_de_vida_paciente1` (`Id_tipo`),
  ADD KEY `FK_estilos_de_vida_paciente2` (`Id_Historial`);

--
-- Indices de la tabla `existencias_stock`
--
ALTER TABLE `existencias_stock`
  ADD PRIMARY KEY (`Id_existencia`),
  ADD UNIQUE KEY `idx_lote_presentacion` (`Id_lote`,`Id_descripcion_medicamento`),
  ADD KEY `FK_Stock_Descripcion` (`Id_descripcion_medicamento`);

--
-- Indices de la tabla `historial_alergias`
--
ALTER TABLE `historial_alergias`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `FK_historial_patologias1` (`Id_alergia`),
  ADD KEY `FK_historial_patologias2` (`Id_Historial`),
  ADD KEY `Id_persona` (`Id_persona`);

--
-- Indices de la tabla `historial_antecedentes_familiares`
--
ALTER TABLE `historial_antecedentes_familiares`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `FK_historial_antecedentes_familiares1` (`Id_antecedente`),
  ADD KEY `FK_historial_antecedentes_familiares2` (`Id_Historial`);

--
-- Indices de la tabla `historial_antecedentes_perinatales`
--
ALTER TABLE `historial_antecedentes_perinatales`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `FK_historial_antecedentes_perinatales1` (`Id_antecedente`),
  ADD KEY `historial_antecedentes_perinatales2` (`Id_Historial`);

--
-- Indices de la tabla `historial_antecedentes_sexuales_reproductivos`
--
ALTER TABLE `historial_antecedentes_sexuales_reproductivos`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `historial_antecedentes_sexuales_reproductivos1` (`Id_antecedente`),
  ADD KEY `historial_antecedentes_sexuales_reproductivos2` (`Id_Historial`);

--
-- Indices de la tabla `historial_medico`
--
ALTER TABLE `historial_medico`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `FK_historial_medico1` (`Id_persona`);

--
-- Indices de la tabla `historial_patologias`
--
ALTER TABLE `historial_patologias`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `FK_historial_patologias1` (`Id_patologia`),
  ADD KEY `FK_historial_patologias2` (`Id_Historial`),
  ADD KEY `Id_persona` (`Id_persona`);

--
-- Indices de la tabla `laboratorio`
--
ALTER TABLE `laboratorio`
  ADD PRIMARY KEY (`Id_laboratorio`),
  ADD UNIQUE KEY `nombre_laboratorio` (`nombre_laboratorio`);

--
-- Indices de la tabla `lotes_medicamentos`
--
ALTER TABLE `lotes_medicamentos`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `FK_lotes_medicamentos` (`Id_descripcion_medicamento`),
  ADD KEY `Id_proveedor` (`Id_proveedor`);

--
-- Indices de la tabla `lugar_nacimiento`
--
ALTER TABLE `lugar_nacimiento`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Id_persona` (`Id_persona`),
  ADD KEY `Fk_lugarnacimiento1` (`Id_municipio`),
  ADD KEY `Fk_lugarnacimiento2` (`Id_persona`);

--
-- Indices de la tabla `medicamento`
--
ALTER TABLE `medicamento`
  ADD PRIMARY KEY (`Id_medicamento`);

--
-- Indices de la tabla `medicamentos_detalle_inventario`
--
ALTER TABLE `medicamentos_detalle_inventario`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `FK_medicamentos_detalle_inventario1` (`Id_detalle_inventario`),
  ADD KEY `FK_medicamentos_detalle_inventario2` (`Id_descripcion_medicamento`),
  ADD KEY `Id_lote` (`Id_lote`);

--
-- Indices de la tabla `medicos_departamentos`
--
ALTER TABLE `medicos_departamentos`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `FK_medicos_departamentos1` (`Id_departamento`),
  ADD KEY `FK_medicos_departamentos2` (`Id_detalle_medico`);

--
-- Indices de la tabla `municipio`
--
ALTER TABLE `municipio`
  ADD PRIMARY KEY (`Id_Municipio`),
  ADD UNIQUE KEY `codigo_postal` (`codigo_postal`),
  ADD KEY `FK_municipio1` (`Id_Estado`);

--
-- Indices de la tabla `notificaciones_usuarios`
--
ALTER TABLE `notificaciones_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_unica_notificacion` (`id_usuario`,`referencia_id`);

--
-- Indices de la tabla `observaciones_historial_medico`
--
ALTER TABLE `observaciones_historial_medico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_observaciones_historial_medico1` (`Id_historial_medico`),
  ADD KEY `FK_observaciones_historial_medico2` (`Id_medico`);

--
-- Indices de la tabla `pais`
--
ALTER TABLE `pais`
  ADD PRIMARY KEY (`Id_Pais`);

--
-- Indices de la tabla `patologias`
--
ALTER TABLE `patologias`
  ADD PRIMARY KEY (`Id_patologia`),
  ADD UNIQUE KEY `nombre_patologia` (`nombre_patologia`,`codigo_cie`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `id_proveedor` (`id_proveedor`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `permiso`
--
ALTER TABLE `permiso`
  ADD PRIMARY KEY (`Id_permiso`);

--
-- Indices de la tabla `persona`
--
ALTER TABLE `persona`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `prefijos_telefonos`
--
ALTER TABLE `prefijos_telefonos`
  ADD PRIMARY KEY (`Id`);

--
-- Indices de la tabla `prescripcion_medicamentos`
--
ALTER TABLE `prescripcion_medicamentos`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Id_consulta` (`Id_consulta`),
  ADD KEY `Id_descripcion_medicamento` (`Id_descripcion_medicamento`);

--
-- Indices de la tabla `presentacion`
--
ALTER TABLE `presentacion`
  ADD PRIMARY KEY (`Id_presentacion`);

--
-- Indices de la tabla `principio_activo`
--
ALTER TABLE `principio_activo`
  ADD PRIMARY KEY (`id_principio_activo`);

--
-- Indices de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD PRIMARY KEY (`Id_proveedor`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`Id_rol`);

--
-- Indices de la tabla `rol_permiso`
--
ALTER TABLE `rol_permiso`
  ADD PRIMARY KEY (`Id_rol_permiso`),
  ADD KEY `Id_rol` (`Id_rol`),
  ADD KEY `Id_permiso` (`Id_permiso`);

--
-- Indices de la tabla `sector`
--
ALTER TABLE `sector`
  ADD PRIMARY KEY (`Id_Sector`),
  ADD KEY `FK_Sector` (`Id_Municipio`);

--
-- Indices de la tabla `sintomas`
--
ALTER TABLE `sintomas`
  ADD PRIMARY KEY (`Id_sintomas`);

--
-- Indices de la tabla `solicitud_medicamento`
--
ALTER TABLE `solicitud_medicamento`
  ADD PRIMARY KEY (`id_solicitud`),
  ADD KEY `FK_solicitud_consulta` (`id_consulta`),
  ADD KEY `id_persona` (`id_paciente`),
  ADD KEY `id_medico` (`id_medico`);

--
-- Indices de la tabla `telefonos_personas`
--
ALTER TABLE `telefonos_personas`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `FK_TelefonoPersona1` (`Id_prefijo`),
  ADD KEY `FK_TelefonoPersona2` (`Id_persona`);

--
-- Indices de la tabla `tipos_estilos_de_vida`
--
ALTER TABLE `tipos_estilos_de_vida`
  ADD PRIMARY KEY (`Id`);

--
-- Indices de la tabla `tipo_movimiento`
--
ALTER TABLE `tipo_movimiento`
  ADD PRIMARY KEY (`Id_tipo_movimiento`);

--
-- Indices de la tabla `unidad_medida`
--
ALTER TABLE `unidad_medida`
  ADD PRIMARY KEY (`Id_unidad_medida`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alergias_conocidas`
--
ALTER TABLE `alergias_conocidas`
  MODIFY `Id_alergias_conocidas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `antecedentes_familiares`
--
ALTER TABLE `antecedentes_familiares`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `antecedentes_perinatales`
--
ALTER TABLE `antecedentes_perinatales`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `antecedentes_sexuales_reproductivos`
--
ALTER TABLE `antecedentes_sexuales_reproductivos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id_cita` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `consulta`
--
ALTER TABLE `consulta`
  MODIFY `Id_consulta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `departamento`
--
ALTER TABLE `departamento`
  MODIFY `Id_departamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `descripcion_medicamento`
--
ALTER TABLE `descripcion_medicamento`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT de la tabla `detalle_inventario`
--
ALTER TABLE `detalle_inventario`
  MODIFY `Id_detalle_inventario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `detalle_medico`
--
ALTER TABLE `detalle_medico`
  MODIFY `Id_detalle_medico` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `detalle_paciente`
--
ALTER TABLE `detalle_paciente`
  MODIFY `Id_detalle_paciente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT de la tabla `detalle_paciente_menor`
--
ALTER TABLE `detalle_paciente_menor`
  MODIFY `Id_detalle_paciente_menor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT de la tabla `detalle_patologia_medicamento`
--
ALTER TABLE `detalle_patologia_medicamento`
  MODIFY `Id_detalle_patologia_medicamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `detalle_patologia_sintomas`
--
ALTER TABLE `detalle_patologia_sintomas`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `detalle_persona_rol`
--
ALTER TABLE `detalle_persona_rol`
  MODIFY `Id_detalle_persona_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=176;

--
-- AUTO_INCREMENT de la tabla `detalle_presentacion_medicamentos`
--
ALTER TABLE `detalle_presentacion_medicamentos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_principio_medicamento`
--
ALTER TABLE `detalle_principio_medicamento`
  MODIFY `Id_principio_medicamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT de la tabla `detalle_solicitud`
--
ALTER TABLE `detalle_solicitud`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `direccion`
--
ALTER TABLE `direccion`
  MODIFY `Id_Direccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=305;

--
-- AUTO_INCREMENT de la tabla `especialidad`
--
ALTER TABLE `especialidad`
  MODIFY `Id_especialidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `especialidades_medicos`
--
ALTER TABLE `especialidades_medicos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `estado`
--
ALTER TABLE `estado`
  MODIFY `Id_Estado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `estilos_de_vida_paciente`
--
ALTER TABLE `estilos_de_vida_paciente`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `existencias_stock`
--
ALTER TABLE `existencias_stock`
  MODIFY `Id_existencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `historial_alergias`
--
ALTER TABLE `historial_alergias`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT de la tabla `historial_antecedentes_familiares`
--
ALTER TABLE `historial_antecedentes_familiares`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `historial_antecedentes_perinatales`
--
ALTER TABLE `historial_antecedentes_perinatales`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `historial_antecedentes_sexuales_reproductivos`
--
ALTER TABLE `historial_antecedentes_sexuales_reproductivos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `historial_medico`
--
ALTER TABLE `historial_medico`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT de la tabla `historial_patologias`
--
ALTER TABLE `historial_patologias`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT de la tabla `laboratorio`
--
ALTER TABLE `laboratorio`
  MODIFY `Id_laboratorio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `lotes_medicamentos`
--
ALTER TABLE `lotes_medicamentos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `lugar_nacimiento`
--
ALTER TABLE `lugar_nacimiento`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=228;

--
-- AUTO_INCREMENT de la tabla `medicamento`
--
ALTER TABLE `medicamento`
  MODIFY `Id_medicamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT de la tabla `medicamentos_detalle_inventario`
--
ALTER TABLE `medicamentos_detalle_inventario`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `medicos_departamentos`
--
ALTER TABLE `medicos_departamentos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `municipio`
--
ALTER TABLE `municipio`
  MODIFY `Id_Municipio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1012;

--
-- AUTO_INCREMENT de la tabla `notificaciones_usuarios`
--
ALTER TABLE `notificaciones_usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4860;

--
-- AUTO_INCREMENT de la tabla `observaciones_historial_medico`
--
ALTER TABLE `observaciones_historial_medico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pais`
--
ALTER TABLE `pais`
  MODIFY `Id_Pais` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `patologias`
--
ALTER TABLE `patologias`
  MODIFY `Id_patologia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `permiso`
--
ALTER TABLE `permiso`
  MODIFY `Id_permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=210;

--
-- AUTO_INCREMENT de la tabla `persona`
--
ALTER TABLE `persona`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=367;

--
-- AUTO_INCREMENT de la tabla `prefijos_telefonos`
--
ALTER TABLE `prefijos_telefonos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `prescripcion_medicamentos`
--
ALTER TABLE `prescripcion_medicamentos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `presentacion`
--
ALTER TABLE `presentacion`
  MODIFY `Id_presentacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `principio_activo`
--
ALTER TABLE `principio_activo`
  MODIFY `id_principio_activo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  MODIFY `Id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `Id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `rol_permiso`
--
ALTER TABLE `rol_permiso`
  MODIFY `Id_rol_permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2510;

--
-- AUTO_INCREMENT de la tabla `sector`
--
ALTER TABLE `sector`
  MODIFY `Id_Sector` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2003;

--
-- AUTO_INCREMENT de la tabla `sintomas`
--
ALTER TABLE `sintomas`
  MODIFY `Id_sintomas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `solicitud_medicamento`
--
ALTER TABLE `solicitud_medicamento`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `telefonos_personas`
--
ALTER TABLE `telefonos_personas`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=350;

--
-- AUTO_INCREMENT de la tabla `tipos_estilos_de_vida`
--
ALTER TABLE `tipos_estilos_de_vida`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `tipo_movimiento`
--
ALTER TABLE `tipo_movimiento`
  MODIFY `Id_tipo_movimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `unidad_medida`
--
ALTER TABLE `unidad_medida`
  MODIFY `Id_unidad_medida` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `fk_cita_especialidad` FOREIGN KEY (`Id_especialidad`) REFERENCES `especialidad` (`Id_especialidad`),
  ADD CONSTRAINT `fk_cita_medico` FOREIGN KEY (`Id_medico`) REFERENCES `detalle_medico` (`Id_detalle_medico`),
  ADD CONSTRAINT `fk_cita_paciente` FOREIGN KEY (`Id_paciente`) REFERENCES `persona` (`id`);

--
-- Filtros para la tabla `consulta`
--
ALTER TABLE `consulta`
  ADD CONSTRAINT `FK_consulta1` FOREIGN KEY (`Id_historial`) REFERENCES `historial_medico` (`id_historial`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_consulta3` FOREIGN KEY (`Id_paciente`) REFERENCES `persona` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `consulta_ibfk_1` FOREIGN KEY (`Id_medico`) REFERENCES `detalle_medico` (`Id_detalle_medico`);

--
-- Filtros para la tabla `descripcion_medicamento`
--
ALTER TABLE `descripcion_medicamento`
  ADD CONSTRAINT `descripcion_medicamento_ibfk_2` FOREIGN KEY (`Id_presentacion`) REFERENCES `presentacion` (`Id_presentacion`),
  ADD CONSTRAINT `descripcion_medicamento_ibfk_3` FOREIGN KEY (`Id_medicamento`) REFERENCES `medicamento` (`Id_medicamento`),
  ADD CONSTRAINT `descripcion_medicamento_ibfk_4` FOREIGN KEY (`Id_laboratorio`) REFERENCES `laboratorio` (`Id_laboratorio`);

--
-- Filtros para la tabla `detalle_inventario`
--
ALTER TABLE `detalle_inventario`
  ADD CONSTRAINT `FK_detalle_inventario1` FOREIGN KEY (`Id_Persona`) REFERENCES `persona` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_detalle_inventario2` FOREIGN KEY (`Id_TipoMovimiento`) REFERENCES `tipo_movimiento` (`Id_tipo_movimiento`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_detalle_inventario_receptor` FOREIGN KEY (`Id_receptor`) REFERENCES `persona` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `detalle_inventario_ibfk_1` FOREIGN KEY (`Id_prescripcion`) REFERENCES `prescripcion_medicamentos` (`Id`);

--
-- Filtros para la tabla `detalle_medico`
--
ALTER TABLE `detalle_medico`
  ADD CONSTRAINT `FK_detalle_medico1` FOREIGN KEY (`Id_persona`) REFERENCES `persona` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_paciente`
--
ALTER TABLE `detalle_paciente`
  ADD CONSTRAINT `detalle_paciente_ibfk_1` FOREIGN KEY (`id_persona`) REFERENCES `persona` (`id`);

--
-- Filtros para la tabla `detalle_paciente_menor`
--
ALTER TABLE `detalle_paciente_menor`
  ADD CONSTRAINT `detalle_paciente_menor_ibfk_1` FOREIGN KEY (`id_persona`) REFERENCES `persona` (`id`),
  ADD CONSTRAINT `detalle_paciente_menor_ibfk_2` FOREIGN KEY (`id_representante`) REFERENCES `persona` (`id`);

--
-- Filtros para la tabla `detalle_patologia_medicamento`
--
ALTER TABLE `detalle_patologia_medicamento`
  ADD CONSTRAINT `detalle_patologia_medicamento_ibfk_1` FOREIGN KEY (`Id_patologia`) REFERENCES `patologias` (`Id_patologia`),
  ADD CONSTRAINT `detalle_patologia_medicamento_ibfk_2` FOREIGN KEY (`Id_medicamento`) REFERENCES `descripcion_medicamento` (`Id`);

--
-- Filtros para la tabla `detalle_patologia_sintomas`
--
ALTER TABLE `detalle_patologia_sintomas`
  ADD CONSTRAINT `detalle_patologia_sintomas_ibfk_1` FOREIGN KEY (`Id_patologia`) REFERENCES `patologias` (`Id_patologia`),
  ADD CONSTRAINT `detalle_patologia_sintomas_ibfk_2` FOREIGN KEY (`Id_sintoma`) REFERENCES `sintomas` (`Id_sintomas`);

--
-- Filtros para la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  ADD CONSTRAINT `detalle_pedidos_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`),
  ADD CONSTRAINT `detalle_pedidos_ibfk_2` FOREIGN KEY (`id_descripcion_medicamento`) REFERENCES `descripcion_medicamento` (`Id`);

--
-- Filtros para la tabla `detalle_persona_rol`
--
ALTER TABLE `detalle_persona_rol`
  ADD CONSTRAINT `FK_detalleRol1` FOREIGN KEY (`Id_rol`) REFERENCES `rol` (`Id_rol`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_detalleRol2` FOREIGN KEY (`Id_persona`) REFERENCES `persona` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_principio_medicamento`
--
ALTER TABLE `detalle_principio_medicamento`
  ADD CONSTRAINT `detalle_principio_medicamento_ibfk_1` FOREIGN KEY (`id_medicamento`) REFERENCES `descripcion_medicamento` (`Id`),
  ADD CONSTRAINT `detalle_principio_medicamento_ibfk_2` FOREIGN KEY (`id_principio_activo`) REFERENCES `principio_activo` (`id_principio_activo`),
  ADD CONSTRAINT `detalle_principio_medicamento_ibfk_3` FOREIGN KEY (`id_tipo_unidad_medida`) REFERENCES `unidad_medida` (`Id_unidad_medida`);

--
-- Filtros para la tabla `detalle_solicitud`
--
ALTER TABLE `detalle_solicitud`
  ADD CONSTRAINT `FK_detalle_medicamento_catalogo` FOREIGN KEY (`id_medicamento`) REFERENCES `descripcion_medicamento` (`Id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_detalle_solicitud_principal` FOREIGN KEY (`id_solicitud`) REFERENCES `solicitud_medicamento` (`id_solicitud`) ON DELETE CASCADE;

--
-- Filtros para la tabla `direccion`
--
ALTER TABLE `direccion`
  ADD CONSTRAINT `FK_direccion1` FOREIGN KEY (`Id_persona`) REFERENCES `persona` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_direccion2` FOREIGN KEY (`Id_sector`) REFERENCES `sector` (`Id_Sector`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `especialidades_medicos`
--
ALTER TABLE `especialidades_medicos`
  ADD CONSTRAINT `FK_especialidades_medicos1` FOREIGN KEY (`Id_detalle_medico`) REFERENCES `detalle_medico` (`Id_detalle_medico`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_especialidades_medicos2` FOREIGN KEY (`Id_especialidad`) REFERENCES `especialidad` (`Id_especialidad`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `estado`
--
ALTER TABLE `estado`
  ADD CONSTRAINT `FK_Estado1` FOREIGN KEY (`Id_Pais`) REFERENCES `pais` (`Id_Pais`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `estilos_de_vida_paciente`
--
ALTER TABLE `estilos_de_vida_paciente`
  ADD CONSTRAINT `FK_estilos_de_vida_paciente1` FOREIGN KEY (`Id_tipo`) REFERENCES `tipos_estilos_de_vida` (`Id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_estilos_de_vida_paciente2` FOREIGN KEY (`Id_Historial`) REFERENCES `historial_medico` (`id_historial`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `existencias_stock`
--
ALTER TABLE `existencias_stock`
  ADD CONSTRAINT `FK_Stock_Descripcion` FOREIGN KEY (`Id_descripcion_medicamento`) REFERENCES `descripcion_medicamento` (`Id`),
  ADD CONSTRAINT `FK_Stock_Lote` FOREIGN KEY (`Id_lote`) REFERENCES `lotes_medicamentos` (`Id`);

--
-- Filtros para la tabla `historial_alergias`
--
ALTER TABLE `historial_alergias`
  ADD CONSTRAINT `historial_alergias_ibfk_1` FOREIGN KEY (`Id_alergia`) REFERENCES `alergias_conocidas` (`Id_alergias_conocidas`),
  ADD CONSTRAINT `historial_alergias_ibfk_2` FOREIGN KEY (`Id_Historial`) REFERENCES `historial_medico` (`id_historial`),
  ADD CONSTRAINT `historial_alergias_ibfk_3` FOREIGN KEY (`Id_persona`) REFERENCES `persona` (`id`);

--
-- Filtros para la tabla `historial_antecedentes_familiares`
--
ALTER TABLE `historial_antecedentes_familiares`
  ADD CONSTRAINT `FK_historial_antecedentes_familiares1` FOREIGN KEY (`Id_antecedente`) REFERENCES `antecedentes_familiares` (`Id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_historial_antecedentes_familiares2` FOREIGN KEY (`Id_Historial`) REFERENCES `historial_medico` (`id_historial`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `historial_antecedentes_perinatales`
--
ALTER TABLE `historial_antecedentes_perinatales`
  ADD CONSTRAINT `FK_historial_antecedentes_perinatales1` FOREIGN KEY (`Id_antecedente`) REFERENCES `antecedentes_perinatales` (`Id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `historial_antecedentes_perinatales2` FOREIGN KEY (`Id_Historial`) REFERENCES `historial_medico` (`id_historial`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `historial_antecedentes_sexuales_reproductivos`
--
ALTER TABLE `historial_antecedentes_sexuales_reproductivos`
  ADD CONSTRAINT `historial_antecedentes_sexuales_reproductivos1` FOREIGN KEY (`Id_antecedente`) REFERENCES `antecedentes_sexuales_reproductivos` (`Id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `historial_antecedentes_sexuales_reproductivos2` FOREIGN KEY (`Id_Historial`) REFERENCES `historial_medico` (`id_historial`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `historial_medico`
--
ALTER TABLE `historial_medico`
  ADD CONSTRAINT `FK_historial_medico1` FOREIGN KEY (`Id_persona`) REFERENCES `persona` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `historial_patologias`
--
ALTER TABLE `historial_patologias`
  ADD CONSTRAINT `FK_historial_patologias1` FOREIGN KEY (`Id_patologia`) REFERENCES `patologias` (`Id_patologia`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_historial_patologias2` FOREIGN KEY (`Id_Historial`) REFERENCES `historial_medico` (`id_historial`) ON UPDATE CASCADE,
  ADD CONSTRAINT `historial_patologias_ibfk_1` FOREIGN KEY (`Id_persona`) REFERENCES `persona` (`id`);

--
-- Filtros para la tabla `lotes_medicamentos`
--
ALTER TABLE `lotes_medicamentos`
  ADD CONSTRAINT `FK_lotes_medicamentos` FOREIGN KEY (`Id_descripcion_medicamento`) REFERENCES `descripcion_medicamento` (`Id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `lotes_medicamentos_ibfk_1` FOREIGN KEY (`Id_proveedor`) REFERENCES `proveedor` (`Id_proveedor`);

--
-- Filtros para la tabla `lugar_nacimiento`
--
ALTER TABLE `lugar_nacimiento`
  ADD CONSTRAINT `Fk_lugarnacimiento1` FOREIGN KEY (`Id_municipio`) REFERENCES `municipio` (`Id_Municipio`) ON UPDATE CASCADE,
  ADD CONSTRAINT `Fk_lugarnacimiento2` FOREIGN KEY (`Id_persona`) REFERENCES `persona` (`id`);

--
-- Filtros para la tabla `medicamentos_detalle_inventario`
--
ALTER TABLE `medicamentos_detalle_inventario`
  ADD CONSTRAINT `FK_medicamentos_detalle_inventario1` FOREIGN KEY (`Id_detalle_inventario`) REFERENCES `detalle_inventario` (`Id_detalle_inventario`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_medicamentos_detalle_inventario2` FOREIGN KEY (`Id_descripcion_medicamento`) REFERENCES `descripcion_medicamento` (`Id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `medicamentos_detalle_inventario_ibfk_1` FOREIGN KEY (`Id_lote`) REFERENCES `lotes_medicamentos` (`Id`);

--
-- Filtros para la tabla `medicos_departamentos`
--
ALTER TABLE `medicos_departamentos`
  ADD CONSTRAINT `FK_medicos_departamentos1` FOREIGN KEY (`Id_departamento`) REFERENCES `departamento` (`Id_departamento`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_medicos_departamentos2` FOREIGN KEY (`Id_detalle_medico`) REFERENCES `detalle_medico` (`Id_detalle_medico`);

--
-- Filtros para la tabla `municipio`
--
ALTER TABLE `municipio`
  ADD CONSTRAINT `FK_municipio1` FOREIGN KEY (`Id_Estado`) REFERENCES `estado` (`Id_Estado`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `observaciones_historial_medico`
--
ALTER TABLE `observaciones_historial_medico`
  ADD CONSTRAINT `FK_observaciones_historial_medico1` FOREIGN KEY (`Id_historial_medico`) REFERENCES `historial_medico` (`id_historial`) ON UPDATE CASCADE,
  ADD CONSTRAINT `observaciones_historial_medico_ibfk_1` FOREIGN KEY (`Id_medico`) REFERENCES `detalle_medico` (`Id_detalle_medico`);

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedor` (`Id_proveedor`),
  ADD CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `persona` (`id`);

--
-- Filtros para la tabla `prescripcion_medicamentos`
--
ALTER TABLE `prescripcion_medicamentos`
  ADD CONSTRAINT `prescripcion_medicamentos_ibfk_1` FOREIGN KEY (`Id_consulta`) REFERENCES `consulta` (`Id_consulta`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `prescripcion_medicamentos_ibfk_2` FOREIGN KEY (`Id_descripcion_medicamento`) REFERENCES `descripcion_medicamento` (`Id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `sector`
--
ALTER TABLE `sector`
  ADD CONSTRAINT `FK_Sector` FOREIGN KEY (`Id_Municipio`) REFERENCES `municipio` (`Id_Municipio`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `solicitud_medicamento`
--
ALTER TABLE `solicitud_medicamento`
  ADD CONSTRAINT `FK_solicitud_consulta` FOREIGN KEY (`id_consulta`) REFERENCES `consulta` (`Id_consulta`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `solicitud_medicamento_ibfk_1` FOREIGN KEY (`id_paciente`) REFERENCES `persona` (`id`),
  ADD CONSTRAINT `solicitud_medicamento_ibfk_2` FOREIGN KEY (`id_medico`) REFERENCES `detalle_medico` (`Id_detalle_medico`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
