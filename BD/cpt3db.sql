-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-04-2026 a las 19:37:42
-- Versión del servidor: 10.4.22-MariaDB
-- Versión de PHP: 7.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
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
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `alergias_conocidas`
--

INSERT INTO `alergias_conocidas` (`Id_alergias_conocidas`, `nombre_alergia`, `estatus`) VALUES
(6, 'Alergia al Mani', 1);

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
(19, 's', '1');

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
(18, 's', '1');

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
(17, 's', '1');

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
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('Pendiente','Confirmada','Cancelada','Finalizada','Vencida','Inasistente','Reprogramada') DEFAULT 'Pendiente',
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id_cita`, `fecha_cita`, `hora_cita`, `motivo`, `Id_paciente`, `Id_medico`, `Id_especialidad`, `fecha_registro`, `estado`, `estatus`) VALUES
(122, '0000-00-00', '00:00:00', 'Dolor', 331, 19, 2, '2026-04-06 12:43:30', 'Vencida', 0),
(123, '0000-00-00', '00:00:00', 'Dolor', 328, 19, 2, '2026-04-14 01:48:35', 'Vencida', 0),
(124, '0000-00-00', '00:00:00', 'Dolor', 328, 19, 2, '2026-04-16 12:59:37', 'Vencida', 0),
(125, '0000-00-00', '00:00:00', 'SDDS', 328, 19, 2, '2026-04-16 15:12:45', 'Vencida', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consulta`
--

CREATE TABLE `consulta` (
  `Id_consulta` int(11) NOT NULL,
  `fecha_consulta` date NOT NULL,
  `motivo_consulta` text DEFAULT NULL,
  `diagnostico` text DEFAULT NULL,
  `tratamiento_indicado` text DEFAULT NULL,
  `peso` decimal(5,2) DEFAULT NULL,
  `talla` decimal(5,2) DEFAULT NULL,
  `temperatura` int(11) DEFAULT NULL,
  `tension` int(11) DEFAULT NULL,
  `frecuencia_cardiaca` int(11) DEFAULT NULL,
  `saturacion` int(11) DEFAULT NULL,
  `frecuencia_respiratoria` int(11) DEFAULT NULL,
  `estado_paciente` varchar(45) NOT NULL,
  `reaccion_adversa` varchar(45) NOT NULL,
  `detalle_reaccion` varchar(45) NOT NULL,
  `evolucion_resultado` varchar(100) NOT NULL,
  `lectura_examenes` varchar(100) NOT NULL,
  `examenes_solicitados` varchar(100) NOT NULL,
  `entregado_a` varchar(100) NOT NULL,
  `parentesco` varchar(25) NOT NULL,
  `Id_historial` int(11) NOT NULL,
  `Id_medico` int(11) NOT NULL,
  `Id_paciente` int(11) NOT NULL,
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `consulta`
--

INSERT INTO `consulta` (`Id_consulta`, `fecha_consulta`, `motivo_consulta`, `diagnostico`, `tratamiento_indicado`, `peso`, `talla`, `temperatura`, `tension`, `frecuencia_cardiaca`, `saturacion`, `frecuencia_respiratoria`, `estado_paciente`, `reaccion_adversa`, `detalle_reaccion`, `evolucion_resultado`, `lectura_examenes`, `examenes_solicitados`, `entregado_a`, `parentesco`, `Id_historial`, `Id_medico`, `Id_paciente`, `estatus`) VALUES
(113, '2026-04-16', 'SDDS', 'SDADS', 'SDAD', '0.00', '0.00', 0, 0, 0, 0, 0, 'Primera Consulta', 'No', '', 'Paciente acude por primera vez. Se inicia protocolo.', '', '', 'Camilo Raul Montilla Perez', '', 86, 329, 328, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamento`
--

CREATE TABLE `departamento` (
  `Id_departamento` int(11) NOT NULL,
  `nombre_departamento` varchar(35) NOT NULL,
  `estatus` int(1) NOT NULL DEFAULT 1
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
  `excipientes` varchar(100) COLLATE utf8_spanish_ci NOT NULL,
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
(81, 'Oral', '8_a_15', 'Fresa, Sal, Mantequilla', 1, 100, '27489824824742', '10 Tabletas', '10', 2, 2, 1, 94, '1'),
(82, 'Oral', '8_a_15', 'Fresa, Sal, Mantequilla', 1, 100, '234234235233', '10 Tabletas', '10', 2, 2, 1, 95, '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_inventario`
--

CREATE TABLE `detalle_inventario` (
  `Id_detalle_inventario` int(11) NOT NULL,
  `Id_TipoMovimiento` int(11) NOT NULL,
  `Id_Persona` int(11) NOT NULL,
  `Id_prescripcion` int(11) DEFAULT NULL,
  `comprobante` varchar(255) DEFAULT NULL,
  `fecha` datetime NOT NULL,
  `observaciones` varchar(255) NOT NULL,
  `estado_movimiento` enum('Activo','Anulado') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `detalle_inventario`
--

INSERT INTO `detalle_inventario` (`Id_detalle_inventario`, `Id_TipoMovimiento`, `Id_Persona`, `Id_prescripcion`, `comprobante`, `fecha`, `observaciones`, `estado_movimiento`) VALUES
(226, 1, 189, NULL, NULL, '2026-04-16 11:13:46', 'Entrada', 'Activo'),
(230, 2, 189, NULL, NULL, '2026-04-16 11:33:29', 'Despacho a paciente externo: Deyber Silva', 'Anulado'),
(232, 9, 189, NULL, NULL, '2026-04-16 12:50:11', 'ANULACIÓN DE MOV. #230 | Motivo: Error de registro detectado por administrador', 'Activo'),
(233, 1, 189, NULL, NULL, '2026-04-16 13:16:48', 'Entrega', 'Activo'),
(234, 1, 189, NULL, NULL, '2026-04-16 13:18:14', 'Entrega', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_medico`
--

CREATE TABLE `detalle_medico` (
  `Id_detalle_medico` int(11) NOT NULL,
  `fecha_ingreso` date NOT NULL,
  `Id_persona` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `detalle_medico`
--

INSERT INTO `detalle_medico` (`Id_detalle_medico`, `fecha_ingreso`, `Id_persona`) VALUES
(19, '2012-11-01', 329);

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
  `id_persona` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `detalle_paciente`
--

INSERT INTO `detalle_paciente` (`Id_detalle_paciente`, `situacion_conyugal`, `etnia`, `tipo_etnia`, `analfabeta`, `seguro_social`, `profesion`, `ocupacion`, `nivel_instruccion`, `mision`, `años_aprobados`, `discapacidad`, `tipo_discapacidad`, `id_persona`) VALUES
(69, '', 'No', '', 'No', '', '', '', 'sin_instruccion', '', 0, 'No', '', 328),
(70, '', 'No', '', 'No', '', '', '', '', '', 0, 'No', '0', 332);

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
  `id_persona` int(11) NOT NULL,
  `id_representante` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `detalle_paciente_menor`
--

INSERT INTO `detalle_paciente_menor` (`Id_detalle_paciente_menor`, `parentesco`, `etnia`, `tipo_etnia`, `analfabeta`, `nivel_instruccion`, `mision`, `años_aprobados`, `discapacidad`, `tipo_discapacidad`, `id_persona`, `id_representante`) VALUES
(142, 'Padre', 'No', 'Ninguna', 'No', '', NULL, 0, 'No', 'Ninguna', 331, 330);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_patologia_medicamento`
--

CREATE TABLE `detalle_patologia_medicamento` (
  `Id_detalle_patologia_medicamento` int(11) NOT NULL,
  `Id_patologia` int(11) NOT NULL,
  `Id_medicamento` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
(28, 32, 14);

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
(111, 282, 7, '2'),
(112, 283, 2, '2'),
(113, 284, 8, '2'),
(137, 328, 3, '1'),
(138, 329, 4, '1'),
(139, 330, 5, '1'),
(140, 331, 3, '1'),
(141, 332, 3, '1');

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
(43, 81, 1, 1, 800),
(44, 81, 2, 2, 250);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_solicitud`
--

CREATE TABLE `detalle_solicitud` (
  `id_detalle` int(11) NOT NULL,
  `id_solicitud` int(11) NOT NULL,
  `id_medicamento` int(11) NOT NULL,
  `cantidad_recetada` int(11) NOT NULL,
  `cantidad_entregada` int(11) NOT NULL DEFAULT 0,
  `estatus_item` enum('Pendiente','Entregado','Parcialmente Entregado','Cancelado') DEFAULT 'Pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `detalle_solicitud`
--

INSERT INTO `detalle_solicitud` (`id_detalle`, `id_solicitud`, `id_medicamento`, `cantidad_recetada`, `cantidad_entregada`, `estatus_item`) VALUES
(35, 29, 81, 1, 0, 'Cancelado'),
(36, 29, 82, 1, 0, 'Cancelado'),
(37, 30, 82, 2, 0, 'Cancelado');

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
(299, '', 'dia/s', '', '', 330, NULL, '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidad`
--

CREATE TABLE `especialidad` (
  `Id_especialidad` int(11) NOT NULL,
  `nombre_especialidad` varchar(100) NOT NULL,
  `estatus` int(1) DEFAULT 1
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
(22, 2, 19, NULL);

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
(12, 15, 87, '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `existencias_stock`
--

CREATE TABLE `existencias_stock` (
  `Id_existencia` int(11) NOT NULL,
  `Id_descripcion_medicamento` int(11) NOT NULL,
  `Id_lote` int(11) NOT NULL,
  `cantidad_actual` int(11) NOT NULL DEFAULT 0,
  `ultima_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `existencias_stock`
--

INSERT INTO `existencias_stock` (`Id_existencia`, `Id_descripcion_medicamento`, `Id_lote`, `cantidad_actual`, `ultima_actualizacion`) VALUES
(74, 81, 60, 15, '2026-04-16 17:16:48'),
(75, 82, 61, 20, '2026-04-16 17:18:14');

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
(70, 328, 6, 86, '2012-11-01', '1');

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
(16, 19, 87, '1');

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
(16, 18, 87, '1');

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
(16, 17, 87, '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_medico`
--

CREATE TABLE `historial_medico` (
  `id_historial` int(11) NOT NULL,
  `grupo_sanguineo` varchar(3) NOT NULL,
  `fecha` datetime NOT NULL,
  `Id_persona` int(11) NOT NULL,
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `historial_medico`
--

INSERT INTO `historial_medico` (`id_historial`, `grupo_sanguineo`, `fecha`, `Id_persona`, `estatus`) VALUES
(86, 'A+', '2012-11-01 08:13:52', 328, 1),
(87, 'A+', '2012-11-01 14:00:06', 331, 1),
(88, 'A+', '2012-11-01 06:46:28', 332, 1);

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
(140, 332, 32, 88, '2012-11-01', '1'),
(142, 331, 32, 87, '0000-00-00', '1'),
(143, 328, 32, 86, '2010-07-01', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `laboratorio`
--

CREATE TABLE `laboratorio` (
  `Id_laboratorio` int(11) NOT NULL,
  `nombre_laboratorio` varchar(45) NOT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `laboratorio`
--

INSERT INTO `laboratorio` (`Id_laboratorio`, `nombre_laboratorio`, `estatus`) VALUES
(1, 'Laboratorios Leti', 1),
(2, 'Behrens', 1),
(3, 'Calox International', 1);

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
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `lotes_medicamentos`
--

INSERT INTO `lotes_medicamentos` (`Id`, `Id_descripcion_medicamento`, `Id_proveedor`, `Lote`, `fecha_fabricacion`, `fecha_vencimiento`, `estado_lote`, `estatus`) VALUES
(60, 81, 1, 'D-FGHR', '2026-04-12', '2026-04-29', 'Disponible', 1),
(61, 82, 1, 'L-2026B', '2026-04-12', '2026-04-29', 'Disponible', 1);

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
(221, 328, 972),
(222, 331, 973),
(223, 332, 912),
(225, 330, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medicamento`
--

CREATE TABLE `medicamento` (
  `Id_medicamento` int(11) NOT NULL,
  `nombre_medicamento` varchar(100) NOT NULL,
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `medicamento`
--

INSERT INTO `medicamento` (`Id_medicamento`, `nombre_medicamento`, `estatus`) VALUES
(94, 'DARFF', 1),
(95, 'GERMEW', 1);

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
  `stock_momento` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `medicamentos_detalle_inventario`
--

INSERT INTO `medicamentos_detalle_inventario` (`Id`, `Id_detalle_inventario`, `Id_descripcion_medicamento`, `Id_lote`, `cantidad`, `stock_momento`) VALUES
(220, 226, 81, 60, 10, 10),
(221, 226, 82, 61, 10, 10),
(222, 230, 82, 61, 1, 9),
(223, 232, 82, 61, 1, 10),
(224, 233, 81, 60, 5, 15),
(225, 234, 82, 61, 10, 20);

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
(22, 1, 19, NULL),
(23, 1, 19, NULL),
(24, 1, 19, NULL),
(25, 1, 19, NULL),
(26, 1, 19, NULL),
(27, 1, 19, NULL),
(28, 1, 19, NULL);

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
-- Estructura de tabla para la tabla `observaciones_historial_medico`
--

CREATE TABLE `observaciones_historial_medico` (
  `id` int(11) NOT NULL,
  `Id_historial_medico` int(11) NOT NULL,
  `observacion` text COLLATE utf8_spanish_ci NOT NULL,
  `Id_medico` int(11) NOT NULL,
  `fecha` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `observaciones_historial_medico`
--

INSERT INTO `observaciones_historial_medico` (`id`, `Id_historial_medico`, `observacion`, `Id_medico`, `fecha`) VALUES
(76, 86, '', 329, '2012-11-01 05:49:33');

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
  `estatus` int(1) DEFAULT 1,
  `codigo_cie` varchar(10) COLLATE utf8_spanish_ci NOT NULL,
  `contagioso` enum('SI','NO') COLLATE utf8_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `patologias`
--

INSERT INTO `patologias` (`Id_patologia`, `nombre_patologia`, `descripcion`, `estatus`, `codigo_cie`, `contagioso`) VALUES
(32, 'Dengue', NULL, 1, 'A00', 'NO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permiso`
--

CREATE TABLE `permiso` (
  `Id_permiso` int(11) NOT NULL,
  `nombre_permiso` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estatus` int(1) NOT NULL DEFAULT 1
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
(94, 'Editar Movimientos de Inventario', 'Permite editar entradas y salidas del inventario.', 1),
(95, 'Ver Inventario', 'Permite ver el listado y stock actual del inventario.', 1),
(96, 'Desactivar Movimientos de Inventario', 'Permite desactivar movimientos en el inventario.', 1),
(97, 'Reactivar Movimientos de Inventario', 'Permite reactivar movimientos en el inventario.', 1),
(98, 'Eliminar Movimientos de Inventario', 'Permite eliminar movimientos en el inventario.', 1),
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
(152, 'Ver papelera de inventario', 'Permite administrar la papelera relacionada con el inventario.', 1),
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
(186, 'Generar despachos de inventario', 'Permiso para despachar medicamentos', 1),
(187, 'Ver kardex de medicamentos', 'Permiso para ver el kardex de los medicamentos', 1),
(188, 'Desactivar Proveedores', 'Permiso para desactivar proveedores', 1),
(189, 'Desactivar Laboratorios', 'Permiso para desactivar laboratorios', 1),
(190, 'Reactivar Proveedores', 'Permiso para reactivar proveedores', 1),
(191, 'Reactivar Laboratorios', 'Permiso para reactivar laboratorios', 1),
(192, 'Gestionar acciones de proveedores', 'Permiso para gestionar las acciones de los proveedores', 1),
(193, 'Gestionar acciones de laboratorios', 'Permiso para gestionar las acciones de los laboratorios', 1),
(194, 'Gestionar acciones de recetas', 'Permiso para gestionar las acciones de las recetas', 1);

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
  `login_attempts` int(11) DEFAULT 0,
  `last_login_attempt` datetime DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `estatus` int(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `persona`
--

INSERT INTO `persona` (`id`, `nombre`, `apellido`, `tipo_cedula`, `cedula`, `fecha_nacimiento`, `genero`, `email`, `password`, `login_attempts`, `last_login_attempt`, `reset_token`, `token_expiry`, `estatus`) VALUES
(189, 'Administrador', '', '', NULL, '0000-00-00', '', 'Admin@gmail.com', '$2y$10$8yssf5zfDWJkyRMRT6ZKouSh.sjq44sfpX0TUmfa9lBiX66cjwABi', 0, '2026-04-05 09:11:21', '786882', '2026-03-13 00:03:15', 1),
(281, 'Farmaceutico', '', '', NULL, '0000-00-00', '', 'farmacia1@gmail.com', '$2y$10$p2/kzGwyEciijMTKsWZ.mehugciMAyuJQgrd.K9DqH8Z5dkCjkUke', 0, NULL, NULL, NULL, 2),
(282, 'Doctor Mario', '', '', NULL, '0000-00-00', '', 'DoctorM@gmail.com', '$2y$10$Vkl.uEJ6/eYrUH8zEUm1OOgFk6l2j8FwaZsftByZc2yn.G7E8QE/O', 0, NULL, NULL, NULL, 2),
(283, 'Vistante', '', '', NULL, '0000-00-00', '', 'Visitante@gmail.com', '$2y$10$LpUujaFYGLR8dh8TLbVeSOPfgEPOoSUhUBRPwDegm4vmCAjesma6K', 0, NULL, NULL, NULL, 2),
(284, 'Recursos Humanos', '', '', NULL, '0000-00-00', '', 'RH2026@gmail.com', '$2y$10$OZv49JoBe5QAdfDZSthS0.vAX2Z5P/vmcjo5YdNMYEL8K2vP5NMz.', 0, NULL, NULL, NULL, 2),
(328, 'Camilo Raul', 'Montilla Perez', 'V', '22333333', '1994-11-01', 'Masculino', '', '', 0, NULL, NULL, NULL, 1),
(329, 'Francisco', 'Perez Mendoza', 'V', '23456646', '1994-11-01', 'Masculino', 'Camilo@gmail.com', '', 0, NULL, NULL, NULL, 1),
(330, 'Mario', 'Gomez', 'V', '24244252', '1994-11-01', 'Masculino', '', '', 0, NULL, NULL, NULL, 1),
(331, 'Steve ', 'Rogers', 'V', '31306212', '2011-03-01', 'Masculino', NULL, '', 0, NULL, NULL, NULL, 1),
(332, 'dvds', '', 'V', '34567890', '1994-11-01', 'Masculino', '', '', 0, NULL, NULL, NULL, 1);

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
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `prescripcion_medicamentos`
--

INSERT INTO `prescripcion_medicamentos` (`Id`, `Id_consulta`, `Id_descripcion_medicamento`, `estado_prescripcion`, `estatus`) VALUES
(219, 113, 81, 'cancelado', 1),
(220, 113, 82, 'cancelado', 1);

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
(2, 'Amoxicilina', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

CREATE TABLE `proveedor` (
  `Id_proveedor` int(11) NOT NULL,
  `nombre_proveedor` varchar(50) NOT NULL,
  `estatus` int(1) NOT NULL DEFAULT 1
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
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`Id_rol`, `nombre_rol`, `estatus`) VALUES
(1, 'Administrador', 1),
(2, 'Supervisor', 1),
(3, 'Paciente', 1),
(4, 'Medico', 1),
(5, 'Representante', 1),
(6, 'Farmaceutico', 1),
(7, 'Medico - Usuario', 1),
(8, 'Administrador - RH', 1);

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
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 1, 4),
(5, 1, 5),
(6, 1, 6),
(7, 1, 7),
(8, 1, 8),
(9, 1, 9),
(10, 1, 10),
(11, 1, 11),
(12, 1, 12),
(13, 1, 13),
(14, 1, 14),
(15, 1, 15),
(16, 1, 16),
(17, 1, 17),
(18, 1, 18),
(19, 1, 19),
(20, 1, 20),
(21, 1, 21),
(22, 1, 22),
(23, 1, 23),
(24, 1, 24),
(25, 1, 25),
(26, 1, 26),
(27, 1, 27),
(28, 1, 28),
(29, 1, 29),
(30, 1, 30),
(31, 1, 31),
(32, 1, 32),
(33, 1, 33),
(34, 1, 34),
(35, 1, 35),
(36, 1, 36),
(37, 1, 37),
(38, 1, 38),
(39, 1, 39),
(40, 1, 40),
(41, 1, 41),
(42, 1, 42),
(43, 1, 43),
(44, 1, 44),
(45, 1, 45),
(46, 1, 46),
(47, 1, 47),
(48, 1, 48),
(49, 1, 49),
(50, 1, 50),
(51, 1, 51),
(52, 1, 52),
(53, 1, 53),
(54, 1, 54),
(55, 1, 55),
(56, 1, 56),
(57, 1, 57),
(58, 1, 58),
(59, 1, 59),
(60, 1, 60),
(61, 1, 61),
(62, 1, 62),
(63, 1, 63),
(64, 1, 64),
(65, 1, 65),
(66, 1, 66),
(67, 1, 67),
(68, 1, 68),
(69, 1, 69),
(70, 1, 70),
(71, 1, 71),
(72, 1, 72),
(73, 1, 73),
(74, 1, 74),
(75, 1, 75),
(76, 1, 76),
(77, 1, 77),
(78, 1, 78),
(79, 1, 79),
(80, 1, 80),
(81, 1, 81),
(82, 1, 82),
(83, 1, 83),
(84, 1, 84),
(85, 1, 85),
(86, 1, 86),
(87, 1, 87),
(88, 1, 88),
(89, 1, 89),
(90, 1, 90),
(91, 1, 91),
(92, 1, 92),
(93, 1, 93),
(94, 1, 94),
(95, 1, 95),
(96, 1, 96),
(97, 1, 97),
(98, 1, 98),
(99, 1, 99),
(100, 1, 100),
(101, 1, 101),
(102, 1, 102),
(103, 1, 103),
(104, 1, 104),
(105, 1, 105),
(106, 1, 106),
(107, 1, 107),
(108, 1, 108),
(109, 1, 109),
(110, 1, 110),
(111, 1, 111),
(112, 1, 112),
(113, 1, 113),
(114, 1, 114),
(115, 1, 115),
(116, 1, 116),
(117, 1, 117),
(118, 1, 118),
(119, 1, 119),
(120, 1, 120),
(121, 1, 121),
(122, 1, 122),
(123, 1, 123),
(124, 1, 124),
(125, 1, 125),
(126, 1, 126),
(127, 1, 127),
(128, 1, 128),
(129, 1, 129),
(130, 1, 130),
(131, 1, 131),
(132, 1, 132),
(133, 1, 133),
(134, 1, 134),
(135, 1, 135),
(136, 1, 141),
(137, 1, 142),
(138, 1, 143),
(139, 1, 144),
(140, 1, 145),
(141, 1, 146),
(142, 1, 147),
(143, 1, 148),
(144, 1, 149),
(145, 1, 150),
(146, 1, 151),
(147, 1, 152),
(148, 1, 153),
(149, 1, 154),
(150, 1, 155),
(151, 1, 156),
(152, 1, 157),
(153, 1, 158),
(154, 1, 159),
(155, 1, 160),
(156, 1, 161),
(157, 1, 162),
(158, 1, 163),
(159, 1, 164),
(160, 1, 165),
(161, 1, 166),
(162, 1, 167),
(163, 1, 168),
(164, 1, 169),
(165, 1, 170),
(166, 1, 171),
(167, 1, 172),
(168, 1, 173),
(169, 1, 174),
(170, 1, 175),
(171, 1, 176),
(172, 1, 177),
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
(199, 6, 91),
(200, 6, 92),
(201, 6, 93),
(202, 6, 94),
(203, 6, 95),
(204, 6, 96),
(205, 6, 97),
(206, 6, 98),
(207, 6, 99),
(208, 6, 110),
(210, 6, 132),
(211, 6, 133),
(212, 6, 134),
(213, 6, 142),
(214, 6, 143),
(215, 6, 169),
(216, 6, 170),
(217, 6, 171),
(218, 6, 137),
(219, 6, 116),
(220, 6, 100),
(221, 6, 101),
(222, 6, 102),
(223, 6, 103),
(224, 6, 104),
(225, 6, 105),
(226, 6, 106),
(227, 6, 107),
(228, 6, 108),
(229, 6, 109),
(230, 7, 37),
(231, 7, 38),
(232, 7, 39),
(233, 7, 43),
(234, 7, 44),
(235, 7, 45),
(236, 7, 55),
(237, 7, 56),
(238, 7, 57),
(239, 7, 61),
(240, 7, 62),
(241, 7, 63),
(242, 7, 67),
(243, 7, 68),
(244, 7, 69),
(245, 7, 73),
(246, 7, 74),
(247, 7, 75),
(248, 7, 79),
(249, 7, 80),
(250, 7, 81),
(251, 7, 85),
(252, 7, 86),
(253, 7, 87),
(254, 7, 118),
(255, 7, 119),
(256, 7, 120),
(257, 7, 121),
(258, 7, 122),
(259, 7, 123),
(260, 7, 124),
(261, 7, 125),
(262, 7, 126),
(263, 7, 127),
(264, 7, 128),
(265, 7, 136),
(266, 7, 141),
(267, 7, 161),
(268, 7, 162),
(269, 7, 163),
(270, 7, 164),
(271, 7, 165),
(272, 7, 166),
(273, 7, 167),
(274, 7, 168),
(275, 7, 111),
(276, 7, 112),
(277, 7, 113),
(278, 7, 114),
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
(298, 8, 174);

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
  `estatus` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `sintomas`
--

INSERT INTO `sintomas` (`Id_sintomas`, `nombre_sintoma`, `estatus`) VALUES
(13, 'Tos', 1),
(14, 'Fiebre', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitud_medicamento`
--

CREATE TABLE `solicitud_medicamento` (
  `id_solicitud` int(11) NOT NULL,
  `origen` enum('Interno','Externo') NOT NULL,
  `id_consulta` int(11) DEFAULT NULL,
  `tipo_cedula_externo` varchar(2) NOT NULL,
  `cedula_externo` int(11) NOT NULL,
  `datos_paciente_externo` varchar(150) DEFAULT NULL,
  `datos_medico_externo` varchar(150) DEFAULT NULL,
  `estatus_general` enum('Pendiente','Parcial','Completado','Cancelado') DEFAULT 'Pendiente',
  `fecha_solicitud` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `solicitud_medicamento`
--

INSERT INTO `solicitud_medicamento` (`id_solicitud`, `origen`, `id_consulta`, `tipo_cedula_externo`, `cedula_externo`, `datos_paciente_externo`, `datos_medico_externo`, `estatus_general`, `fecha_solicitud`) VALUES
(29, 'Interno', 113, '', 0, NULL, NULL, 'Cancelado', '2026-04-16 11:12:45'),
(30, 'Externo', NULL, 'V', 31306211, 'Deyber Silva', 'Mario', 'Cancelado', '2026-04-16 11:33:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `telefonos_emergencia`
--

CREATE TABLE `telefonos_emergencia` (
  `id_telefonos` int(11) NOT NULL,
  `Id_persona` int(11) DEFAULT NULL,
  `telefono` varchar(15) COLLATE utf8_spanish_ci DEFAULT NULL,
  `Id_prefijo` int(11) DEFAULT NULL,
  `estado` enum('1','2') COLLATE utf8_spanish_ci NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

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
(319, 1, '4363741', 328, '1'),
(320, 1, '5664534', 329, '1'),
(321, 1, '2455252', 330, '1'),
(322, 1, '2333333', 332, '1'),
(323, 1, '5664534', 329, '1'),
(324, 1, '5664534', 329, '1'),
(325, 1, '5664534', 329, '1'),
(326, 1, '5664534', 329, '1'),
(327, 1, '5664534', 329, '1');

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
(15, 's', '1');

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
  ADD KEY `Id_prescripcion` (`Id_prescripcion`);

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
  ADD KEY `FK_solicitud_consulta` (`id_consulta`);

--
-- Indices de la tabla `telefonos_emergencia`
--
ALTER TABLE `telefonos_emergencia`
  ADD PRIMARY KEY (`id_telefonos`),
  ADD KEY `fk_telefonos_prefijo_emergencia` (`Id_prefijo`),
  ADD KEY `fk_telefonos_persona_emergencia` (`Id_persona`);

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
  MODIFY `Id_alergias_conocidas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `antecedentes_familiares`
--
ALTER TABLE `antecedentes_familiares`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `antecedentes_perinatales`
--
ALTER TABLE `antecedentes_perinatales`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `antecedentes_sexuales_reproductivos`
--
ALTER TABLE `antecedentes_sexuales_reproductivos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id_cita` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT de la tabla `consulta`
--
ALTER TABLE `consulta`
  MODIFY `Id_consulta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT de la tabla `departamento`
--
ALTER TABLE `departamento`
  MODIFY `Id_departamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `descripcion_medicamento`
--
ALTER TABLE `descripcion_medicamento`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT de la tabla `detalle_inventario`
--
ALTER TABLE `detalle_inventario`
  MODIFY `Id_detalle_inventario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=235;

--
-- AUTO_INCREMENT de la tabla `detalle_medico`
--
ALTER TABLE `detalle_medico`
  MODIFY `Id_detalle_medico` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `detalle_paciente`
--
ALTER TABLE `detalle_paciente`
  MODIFY `Id_detalle_paciente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT de la tabla `detalle_paciente_menor`
--
ALTER TABLE `detalle_paciente_menor`
  MODIFY `Id_detalle_paciente_menor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=144;

--
-- AUTO_INCREMENT de la tabla `detalle_patologia_medicamento`
--
ALTER TABLE `detalle_patologia_medicamento`
  MODIFY `Id_detalle_patologia_medicamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `detalle_patologia_sintomas`
--
ALTER TABLE `detalle_patologia_sintomas`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `detalle_persona_rol`
--
ALTER TABLE `detalle_persona_rol`
  MODIFY `Id_detalle_persona_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT de la tabla `detalle_presentacion_medicamentos`
--
ALTER TABLE `detalle_presentacion_medicamentos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_principio_medicamento`
--
ALTER TABLE `detalle_principio_medicamento`
  MODIFY `Id_principio_medicamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `detalle_solicitud`
--
ALTER TABLE `detalle_solicitud`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `direccion`
--
ALTER TABLE `direccion`
  MODIFY `Id_Direccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=300;

--
-- AUTO_INCREMENT de la tabla `especialidad`
--
ALTER TABLE `especialidad`
  MODIFY `Id_especialidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `especialidades_medicos`
--
ALTER TABLE `especialidades_medicos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `estado`
--
ALTER TABLE `estado`
  MODIFY `Id_Estado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `estilos_de_vida_paciente`
--
ALTER TABLE `estilos_de_vida_paciente`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `existencias_stock`
--
ALTER TABLE `existencias_stock`
  MODIFY `Id_existencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT de la tabla `historial_alergias`
--
ALTER TABLE `historial_alergias`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT de la tabla `historial_antecedentes_familiares`
--
ALTER TABLE `historial_antecedentes_familiares`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `historial_antecedentes_perinatales`
--
ALTER TABLE `historial_antecedentes_perinatales`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `historial_antecedentes_sexuales_reproductivos`
--
ALTER TABLE `historial_antecedentes_sexuales_reproductivos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `historial_medico`
--
ALTER TABLE `historial_medico`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT de la tabla `historial_patologias`
--
ALTER TABLE `historial_patologias`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=144;

--
-- AUTO_INCREMENT de la tabla `laboratorio`
--
ALTER TABLE `laboratorio`
  MODIFY `Id_laboratorio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `lotes_medicamentos`
--
ALTER TABLE `lotes_medicamentos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT de la tabla `lugar_nacimiento`
--
ALTER TABLE `lugar_nacimiento`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=226;

--
-- AUTO_INCREMENT de la tabla `medicamento`
--
ALTER TABLE `medicamento`
  MODIFY `Id_medicamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT de la tabla `medicamentos_detalle_inventario`
--
ALTER TABLE `medicamentos_detalle_inventario`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=226;

--
-- AUTO_INCREMENT de la tabla `medicos_departamentos`
--
ALTER TABLE `medicos_departamentos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `municipio`
--
ALTER TABLE `municipio`
  MODIFY `Id_Municipio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1012;

--
-- AUTO_INCREMENT de la tabla `observaciones_historial_medico`
--
ALTER TABLE `observaciones_historial_medico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT de la tabla `pais`
--
ALTER TABLE `pais`
  MODIFY `Id_Pais` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `patologias`
--
ALTER TABLE `patologias`
  MODIFY `Id_patologia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `permiso`
--
ALTER TABLE `permiso`
  MODIFY `Id_permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=195;

--
-- AUTO_INCREMENT de la tabla `persona`
--
ALTER TABLE `persona`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=339;

--
-- AUTO_INCREMENT de la tabla `prefijos_telefonos`
--
ALTER TABLE `prefijos_telefonos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=427;

--
-- AUTO_INCREMENT de la tabla `prescripcion_medicamentos`
--
ALTER TABLE `prescripcion_medicamentos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=221;

--
-- AUTO_INCREMENT de la tabla `presentacion`
--
ALTER TABLE `presentacion`
  MODIFY `Id_presentacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `principio_activo`
--
ALTER TABLE `principio_activo`
  MODIFY `id_principio_activo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  MODIFY `Id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `Id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `rol_permiso`
--
ALTER TABLE `rol_permiso`
  MODIFY `Id_rol_permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=299;

--
-- AUTO_INCREMENT de la tabla `sector`
--
ALTER TABLE `sector`
  MODIFY `Id_Sector` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2003;

--
-- AUTO_INCREMENT de la tabla `sintomas`
--
ALTER TABLE `sintomas`
  MODIFY `Id_sintomas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `solicitud_medicamento`
--
ALTER TABLE `solicitud_medicamento`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `telefonos_emergencia`
--
ALTER TABLE `telefonos_emergencia`
  MODIFY `id_telefonos` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `telefonos_personas`
--
ALTER TABLE `telefonos_personas`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=328;

--
-- AUTO_INCREMENT de la tabla `tipos_estilos_de_vida`
--
ALTER TABLE `tipos_estilos_de_vida`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
  ADD CONSTRAINT `FK_consulta2` FOREIGN KEY (`Id_medico`) REFERENCES `persona` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_consulta3` FOREIGN KEY (`Id_paciente`) REFERENCES `persona` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `descripcion_medicamento`
--
ALTER TABLE `descripcion_medicamento`
  ADD CONSTRAINT `descripcion_medicamento_ibfk_2` FOREIGN KEY (`Id_presentacion`) REFERENCES `presentacion` (`Id_presentacion`),
  ADD CONSTRAINT `descripcion_medicamento_ibfk_3` FOREIGN KEY (`Id_medicamento`) REFERENCES `medicamento` (`Id_medicamento`),
  ADD CONSTRAINT `descripcion_medicamento_ibfk_4` FOREIGN KEY (`Id_laboratorio`) REFERENCES `laboratorio` (`id_laboratorio`);

--
-- Filtros para la tabla `detalle_inventario`
--
ALTER TABLE `detalle_inventario`
  ADD CONSTRAINT `FK_detalle_inventario1` FOREIGN KEY (`Id_Persona`) REFERENCES `persona` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_detalle_inventario2` FOREIGN KEY (`Id_TipoMovimiento`) REFERENCES `tipo_movimiento` (`Id_tipo_movimiento`) ON UPDATE CASCADE,
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
  ADD CONSTRAINT `FK_observaciones_historial_medico2` FOREIGN KEY (`Id_medico`) REFERENCES `persona` (`id`) ON UPDATE CASCADE;

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
  ADD CONSTRAINT `FK_solicitud_consulta` FOREIGN KEY (`id_consulta`) REFERENCES `consulta` (`Id_consulta`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
