-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-08-2025 a las 02:53:10
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `registrop6`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clase`
--

CREATE TABLE `clase` (
  `id_clase` int(11) NOT NULL,
  `nomProfe` varchar(222) NOT NULL,
  `nombreClase` varchar(222) NOT NULL,
  `codigoClase` varchar(222) NOT NULL,
  `Cuenta_Usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `clase`
--

INSERT INTO `clase` (`id_clase`, `nomProfe`, `nombreClase`, `codigoClase`, `Cuenta_Usuario`) VALUES
(4, 'MAMANI QUISPE HERMANA WANKA', 'Biologiazzz', '772918I', 9416306),
(5, 'MAMANI QUISPE HERMANA WANKA', 'Matematicazzz', '72110o', 9416306);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentario`
--

CREATE TABLE `comentario` (
  `id` int(11) NOT NULL,
  `contenido` varchar(500) NOT NULL,
  `fechaPub` datetime GENERATED ALWAYS AS (current_timestamp()) VIRTUAL,
  `fechaEdi` datetime NOT NULL,
  `Clase_id_clase` int(11) NOT NULL,
  `Cuenta_Usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `comentario`
--

INSERT INTO `comentario` (`id`, `contenido`, `fechaEdi`, `Clase_id_clase`, `Cuenta_Usuario`) VALUES
(1, 'jsq', '2025-08-03 19:21:15', 4, 9416161),
(2, 'hola', '0000-00-00 00:00:00', 4, 9416161),
(3, 'wdad', '2025-08-03 19:56:41', 4, 9416306);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuenta`
--

CREATE TABLE `cuenta` (
  `Usuario` int(11) NOT NULL,
  `Contraseña` varchar(20) NOT NULL,
  `Rol` varchar(45) DEFAULT NULL,
  `Bloqueado` varchar(22) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `cuenta`
--

INSERT INTO `cuenta` (`Usuario`, `Contraseña`, `Rol`, `Bloqueado`) VALUES
(1234321, '123321', 'Estudiante', NULL),
(9416161, '811I202219', 'Estudiante', ''),
(9416306, 'zzzsoyway', 'Profesor', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuenta_has_clase`
--

CREATE TABLE `cuenta_has_clase` (
  `Cuenta_Usuario` int(11) NOT NULL,
  `Clase_id_clase` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `cuenta_has_clase`
--

INSERT INTO `cuenta_has_clase` (`Cuenta_Usuario`, `Clase_id_clase`) VALUES
(9416161, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `informacion`
--

CREATE TABLE `informacion` (
  `CI` int(11) NOT NULL,
  `Nombres` varchar(222) NOT NULL,
  `Apellidos` varchar(222) NOT NULL,
  `Direccion` varchar(222) NOT NULL,
  `Nacimiento` date NOT NULL,
  `Telefono` varchar(222) NOT NULL,
  `Curso` varchar(222) DEFAULT NULL,
  `Rude` int(11) DEFAULT NULL,
  `Cuenta_Usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `informacion`
--

INSERT INTO `informacion` (`CI`, `Nombres`, `Apellidos`, `Direccion`, `Nacimiento`, `Telefono`, `Curso`, `Rude`, `Cuenta_Usuario`) VALUES
(1234321, 'wdawdaw', 'dwadadad', 'Av. Circunvalación', '1111-11-11', '85932413', '6to B ', 2147483647, 1234321),
(9416161, 'Jose Fabian', 'Zambrana Urquizu', 'Av. Circunvalación', '2007-11-19', '79734643', '6to B ', 2147483647, 9416161),
(9416306, 'MAMANI QUISPE', 'HERMANA WANKA', 'Av. TUPRIMA', '1997-11-11', '85932413', '', 0, 9416306);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clase`
--
ALTER TABLE `clase`
  ADD PRIMARY KEY (`id_clase`),
  ADD UNIQUE KEY `codigoClase_UNIQUE` (`codigoClase`),
  ADD UNIQUE KEY `id_clase_UNIQUE` (`id_clase`),
  ADD KEY `fk_Clase_Cuenta1_idx` (`Cuenta_Usuario`);

--
-- Indices de la tabla `comentario`
--
ALTER TABLE `comentario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_Comentario_Clase1_idx` (`Clase_id_clase`),
  ADD KEY `fk_Comentario_Cuenta1_idx` (`Cuenta_Usuario`);

--
-- Indices de la tabla `cuenta`
--
ALTER TABLE `cuenta`
  ADD PRIMARY KEY (`Usuario`),
  ADD UNIQUE KEY `Usuario_UNIQUE` (`Usuario`);

--
-- Indices de la tabla `cuenta_has_clase`
--
ALTER TABLE `cuenta_has_clase`
  ADD PRIMARY KEY (`Cuenta_Usuario`,`Clase_id_clase`),
  ADD KEY `fk_Cuenta_has_Clase_Clase1_idx` (`Clase_id_clase`),
  ADD KEY `fk_Cuenta_has_Clase_Cuenta1_idx` (`Cuenta_Usuario`);

--
-- Indices de la tabla `informacion`
--
ALTER TABLE `informacion`
  ADD PRIMARY KEY (`CI`),
  ADD UNIQUE KEY `CI_UNIQUE` (`CI`),
  ADD KEY `fk_Informacion_Cuenta_idx` (`Cuenta_Usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clase`
--
ALTER TABLE `clase`
  MODIFY `id_clase` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `comentario`
--
ALTER TABLE `comentario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `clase`
--
ALTER TABLE `clase`
  ADD CONSTRAINT `fk_Clase_Cuenta1` FOREIGN KEY (`Cuenta_Usuario`) REFERENCES `cuenta` (`Usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `comentario`
--
ALTER TABLE `comentario`
  ADD CONSTRAINT `fk_Comentario_Clase1` FOREIGN KEY (`Clase_id_clase`) REFERENCES `clase` (`id_clase`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Comentario_Cuenta1` FOREIGN KEY (`Cuenta_Usuario`) REFERENCES `cuenta` (`Usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `cuenta_has_clase`
--
ALTER TABLE `cuenta_has_clase`
  ADD CONSTRAINT `fk_Cuenta_has_Clase_Clase1` FOREIGN KEY (`Clase_id_clase`) REFERENCES `clase` (`id_clase`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Cuenta_has_Clase_Cuenta1` FOREIGN KEY (`Cuenta_Usuario`) REFERENCES `cuenta` (`Usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `informacion`
--
ALTER TABLE `informacion`
  ADD CONSTRAINT `fk_Informacion_Cuenta` FOREIGN KEY (`Cuenta_Usuario`) REFERENCES `cuenta` (`Usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
