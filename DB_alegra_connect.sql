-- phpMyAdmin SQL Dump
-- version 4.6.5.1
-- https://www.phpmyadmin.net/
--
-- Servidor: mysql
-- Tiempo de generación: 29-11-2016 a las 17:40:11
-- Versión del servidor: 5.7.16
-- Versión de PHP: 5.6.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `DB_alegra_connect`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `customersMatch`
--

CREATE TABLE `customersMatch` (
  `id` int(11) NOT NULL,
  `idShopify` bigint(20) NOT NULL,
  `idAlegra` int(11) NOT NULL,
  `fkUserId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `invoicesMatch`
--

CREATE TABLE `invoicesMatch` (
  `id` int(11) NOT NULL,
  `idShopify` bigint(20) NOT NULL,
  `idAlegra` int(11) NOT NULL,
  `fkUserId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productsMatch`
--

CREATE TABLE `productsMatch` (
  `id` int(11) NOT NULL,
  `idShopify` bigint(20) NOT NULL,
  `idAlegra` int(11) NOT NULL,
  `fkUserId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `usernameShopify` varchar(200) COLLATE utf8_spanish_ci NOT NULL,
  `tokenShopify` varchar(200) COLLATE utf8_spanish_ci NOT NULL,
  `usenameAlegra` varchar(200) COLLATE utf8_spanish_ci DEFAULT NULL,
  `tokenAlegra` varchar(200) COLLATE utf8_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `customersMatch`
--
ALTER TABLE `customersMatch`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fkUserId` (`fkUserId`);

--
-- Indices de la tabla `invoicesMatch`
--
ALTER TABLE `invoicesMatch`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fkUserId` (`fkUserId`);

--
-- Indices de la tabla `productsMatch`
--
ALTER TABLE `productsMatch`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fkUserId` (`fkUserId`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `customersMatch`
--
ALTER TABLE `customersMatch`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `invoicesMatch`
--
ALTER TABLE `invoicesMatch`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `productsMatch`
--
ALTER TABLE `productsMatch`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `customersMatch`
--
ALTER TABLE `customersMatch`
  ADD CONSTRAINT `customersMatch_ibfk_1` FOREIGN KEY (`fkUserId`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `invoicesMatch`
--
ALTER TABLE `invoicesMatch`
  ADD CONSTRAINT `invoicesMatch_ibfk_1` FOREIGN KEY (`fkUserId`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `productsMatch`
--
ALTER TABLE `productsMatch`
  ADD CONSTRAINT `productsMatch_ibfk_1` FOREIGN KEY (`fkUserId`) REFERENCES `users` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
