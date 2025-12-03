-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-12-2025 a las 02:25:26
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
-- Base de datos: `redblog`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `upvotes` int(11) DEFAULT 0,
  `downvotes` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `parent_id`, `content`, `upvotes`, `downvotes`, `created_at`) VALUES
(1, 1, 2, NULL, '¡Excelente iniciativa! Estoy emocionado de ser parte de esta comunidad.', 0, 0, '2025-12-01 22:00:48'),
(2, 1, 3, NULL, 'Bienvenidos todos. ¿Hay normas de la comunidad que debamos conocer?', 0, 0, '2025-12-01 22:00:48'),
(3, 1, 2, 1, 'Totalmente de acuerdo, ¡vamos a crear una gran comunidad!', 0, 0, '2025-12-01 22:00:48'),
(4, 2, 1, NULL, 'Recomiendo empezar con JavaScript si quieres desarrollo web, y Python si te interesa más la ciencia de datos.', 0, 0, '2025-12-01 22:00:48'),
(5, 3, 4, NULL, 'Que interesante', 1, 0, '2025-12-01 23:51:52'),
(6, 2, 4, NULL, 'Yo recomiendo empezar con JavaScript porque es más versátil. Puedes hacer frontend y backend con el mismo lenguaje.', 0, 0, '2025-12-02 00:28:26'),
(7, 2, 5, NULL, 'Depende de tus objetivos. ¿Quieres hacer análisis de datos? Python. ¿Desarrollo web? JavaScript.', 0, 0, '2025-12-02 00:28:26'),
(8, 2, 6, 1, 'Estoy de acuerdo, Node.js ha madurado mucho y ahora es una opción sólida para backend.', 0, 0, '2025-12-02 00:28:26'),
(9, 3, 4, NULL, 'Este descubrimiento podría acelerar la computación cuántica en décadas. ¡Impresionante!', 0, 0, '2025-12-02 00:28:26'),
(10, 3, 7, NULL, '¿Alguien tiene enlaces a los papers de investigación? Me encantaría leer más sobre esto.', 0, 0, '2025-12-02 00:28:26'),
(11, 4, 8, NULL, 'Zelda TOTK es una obra maestra, pero no descartes a Baldur Gate 3 para el GOTY.', 0, 0, '2025-12-02 00:28:26'),
(12, 4, 9, NULL, 'Mi voto es para Final Fantasy VII Rebirth. La jugabilidad, historia y gráficos son increíbles.', 0, 0, '2025-12-02 00:28:26'),
(13, 5, 4, NULL, 'Taylor Swift es un fenómeno único. Su capacidad para reinventarse es admirable.', 0, 1, '2025-12-02 00:28:26'),
(14, 6, 5, NULL, 'Messi tiene más talento natural, pero Cristiano tiene una ética de trabajo incomparable.', 0, 0, '2025-12-02 00:28:26'),
(15, 6, 6, NULL, '¿Por qué tenemos que elegir? Ambos han sido increíbles y han elevado el deporte.', 0, 0, '2025-12-02 00:28:26'),
(16, 7, 7, NULL, 'The Last of Us temporada 2 ha sido increíble. La adaptación está a la altura del juego.', 0, 0, '2025-12-02 00:28:26'),
(17, 7, 8, NULL, 'No olviden Shogun. Es una de las mejores series históricas que he visto.', 0, 0, '2025-12-02 00:28:26'),
(18, 7, 4, NULL, 'Personalmente me gusta mucho mas el DLC del elden ring y ojala se lo gane XD.', 0, 0, '2025-12-02 01:56:19'),
(19, 5, 10, NULL, 'Hola si me encanta programar', 0, 0, '2025-12-02 02:08:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comment_votes`
--

CREATE TABLE `comment_votes` (
  `id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vote_type` enum('up','down') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `comment_votes`
--

INSERT INTO `comment_votes` (`id`, `comment_id`, `user_id`, `vote_type`, `created_at`) VALUES
(1, 5, 4, 'up', '2025-12-01 23:51:54'),
(3, 13, 4, 'down', '2025-12-02 01:55:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `author_id` int(11) NOT NULL,
  `theme` enum('Ciencia y Tecnologia','Programacion','Videojuegos','Musica','Cine y Television','Deporte','Otros') DEFAULT 'Otros',
  `upvotes` int(11) DEFAULT 0,
  `downvotes` int(11) DEFAULT 0,
  `comment_count` int(11) DEFAULT 0,
  `is_public` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `posts`
--

INSERT INTO `posts` (`id`, `title`, `content`, `author_id`, `theme`, `upvotes`, `downvotes`, `comment_count`, `is_public`, `created_at`, `updated_at`) VALUES
(1, '¡Bienvenidos a RedBlog! 🎉', 'RedBlog es una plataforma comunitaria donde puedes compartir tus ideas, conocimientos y pasiones con personas de todo el mundo. ¡Únete a la conversación!', 1, 'Otros', 25, 2, 3, 1, '2025-12-01 22:00:48', '2025-12-01 22:03:52'),
(2, 'Python vs JavaScript: ¿Cuál aprender en 2024?', 'Ambos lenguajes tienen sus ventajas. Python es excelente para ciencia de datos y backend, mientras que JavaScript es esencial para desarrollo web frontend y con Node.js también para backend. ¿Cuál recomiendan?', 2, 'Programacion', 43, 5, 8, 1, '2025-12-01 22:00:48', '2025-12-01 22:16:28'),
(3, 'Nuevo descubrimiento en física cuántica', 'Científicos han logrado observar por primera vez el fenómeno de entrelazamiento cuántico a temperatura ambiente, abriendo nuevas posibilidades para la computación cuántica.', 3, 'Ciencia y Tecnologia', 89, 3, 12, 1, '2025-12-01 22:00:48', '2025-12-01 22:00:48'),
(4, 'Review: The Legend of Zelda: Tears of the Kingdom', 'Después de 100 horas de juego, puedo decir que es una de las mejores experiencias de videojuegos que he tenido. La física, la exploración y la creatividad son increíbles.', 1, 'Videojuegos', 156, 8, 24, 1, '2025-12-01 22:00:48', '2025-12-01 23:00:43'),
(5, 'React vs Vue: Comparativa 2024', 'Ambos frameworks son excelentes, pero ¿cuál elegir para un nuevo proyecto? React tiene una comunidad más grande, pero Vue es más fácil de aprender. ¿Qué opinan?', 4, 'Programacion', 33, 3, 15, 1, '2025-12-02 00:28:26', '2025-12-02 02:07:59'),
(6, 'Descubren nueva partícula subatómica', 'El Gran Colisionador de Hadrones ha detectado una nueva partícula que podría cambiar nuestro entendimiento de la física fundamental.', 5, 'Ciencia y Tecnologia', 78, 1, 22, 1, '2025-12-02 00:28:26', '2025-12-02 00:28:26'),
(7, 'GOTY 2024: Nuestras predicciones', 'Este año viene cargado de lanzamientos. ¿Cuál creen que se llevará el premio al Juego del Año? ¿Final Fantasy VII Rebirth? ¿Elden Ring DLC? ¿O algo inesperado?', 6, 'Videojuegos', 92, 5, 38, 1, '2025-12-02 00:28:26', '2025-12-02 00:28:26'),
(8, 'Taylor Swift: El impacto de The Eras Tour', 'No solo es un tour, es un fenómeno cultural. Analizamos cómo está impactando la economía, la música y la cultura pop a nivel global.', 7, 'Musica', 45, 3, 18, 1, '2025-12-02 00:28:26', '2025-12-02 00:28:26'),
(9, 'Messi vs Cristiano: El debate eterno', 'Con ambos jugadores en el ocaso de sus carreras, ¿quién ha tenido un mayor impacto en el fútbol? Analizamos estadísticas, títulos y legado.', 8, 'Deporte', 120, 15, 47, 1, '2025-12-02 00:28:26', '2025-12-02 00:28:26'),
(10, 'Las mejores series de 2024 hasta ahora', 'Desde reinvenciones de clásicos hasta nuevas franquicias, este año está siendo increíble para la televisión. ¿Cuáles son sus favoritas?', 9, 'Cine y Television', 56, 2, 21, 1, '2025-12-02 00:28:26', '2025-12-02 00:28:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `post_votes`
--

CREATE TABLE `post_votes` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vote_type` enum('up','down') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `post_votes`
--

INSERT INTO `post_votes` (`id`, `post_id`, `user_id`, `vote_type`, `created_at`) VALUES
(1, 1, 2, 'up', '2025-12-01 22:00:48'),
(2, 1, 3, 'up', '2025-12-01 22:00:48'),
(3, 2, 1, 'up', '2025-12-01 22:00:48'),
(4, 2, 3, 'up', '2025-12-01 22:00:48'),
(5, 3, 1, 'up', '2025-12-01 22:00:48'),
(6, 3, 2, 'up', '2025-12-01 22:00:48'),
(7, 4, 2, 'up', '2025-12-01 22:00:48'),
(8, 4, 3, 'up', '2025-12-01 22:00:48'),
(14, 2, 4, 'up', '2025-12-01 22:15:59'),
(23, 5, 5, 'up', '2025-12-02 00:28:26'),
(24, 5, 6, 'up', '2025-12-02 00:28:26'),
(25, 5, 7, 'up', '2025-12-02 00:28:26'),
(26, 5, 8, 'down', '2025-12-02 00:28:26'),
(27, 6, 4, 'up', '2025-12-02 00:28:26'),
(28, 6, 5, 'up', '2025-12-02 00:28:26'),
(29, 6, 6, 'up', '2025-12-02 00:28:26'),
(30, 6, 7, 'up', '2025-12-02 00:28:26'),
(31, 6, 8, 'up', '2025-12-02 00:28:26'),
(32, 7, 4, 'up', '2025-12-02 00:28:26'),
(33, 7, 5, 'up', '2025-12-02 00:28:26'),
(34, 7, 6, 'up', '2025-12-02 00:28:26'),
(35, 7, 7, 'down', '2025-12-02 00:28:26'),
(36, 7, 8, 'up', '2025-12-02 00:28:26'),
(37, 8, 4, 'up', '2025-12-02 00:28:26'),
(38, 8, 5, 'up', '2025-12-02 00:28:26'),
(39, 8, 6, 'up', '2025-12-02 00:28:26'),
(40, 8, 7, 'up', '2025-12-02 00:28:26'),
(41, 8, 8, 'up', '2025-12-02 00:28:26'),
(42, 9, 4, 'up', '2025-12-02 00:28:26'),
(43, 9, 5, 'up', '2025-12-02 00:28:26'),
(44, 9, 6, 'down', '2025-12-02 00:28:26'),
(45, 9, 7, 'up', '2025-12-02 00:28:26'),
(46, 9, 8, 'up', '2025-12-02 00:28:26'),
(47, 10, 4, 'up', '2025-12-02 00:28:26'),
(48, 10, 5, 'up', '2025-12-02 00:28:26'),
(49, 10, 6, 'up', '2025-12-02 00:28:26'),
(50, 10, 7, 'up', '2025-12-02 00:28:26'),
(51, 10, 8, 'up', '2025-12-02 00:28:26'),
(52, 5, 10, 'down', '2025-12-02 02:07:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `preferred_theme` enum('Ciencia y Tecnologia','Programacion','Videojuegos','Musica','Cine y Television','Deporte','Otros') DEFAULT 'Otros',
  `country` varchar(100) DEFAULT 'No especificado',
  `bio` text DEFAULT NULL,
  `avatar_url` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `is_active`, `created_at`, `preferred_theme`, `country`, `bio`, `avatar_url`) VALUES
(1, 'admin', 'admin@redblog.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, '2025-12-01 22:00:48', 'Otros', 'No especificado', NULL, NULL),
(2, 'usuario1', 'usuario1@redblog.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 1, '2025-12-01 22:00:48', 'Otros', 'No especificado', NULL, NULL),
(3, 'juan_dev', 'juan@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 1, '2025-12-01 22:00:48', 'Otros', 'No especificado', NULL, NULL),
(4, 'jose', 'jose.orozco1@utp.edu.co', '$2y$10$Td.gSE9BYXWYZe8LvbAKkOTcawmuHMaZETB/Lf5A/XFVui/YQeS3O', 'user', 1, '2025-12-01 22:02:38', 'Musica', 'Colombia', '', NULL),
(5, 'maria_dev', 'maria@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 1, '2025-12-02 00:28:26', 'Programacion', 'España', NULL, NULL),
(6, 'carlos_gamer', 'carlos@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 1, '2025-12-02 00:28:26', 'Videojuegos', 'México', NULL, NULL),
(7, 'ana_music', 'ana@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 1, '2025-12-02 00:28:26', 'Musica', 'Colombia', NULL, NULL),
(8, 'luis_sports', 'luis@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 1, '2025-12-02 00:28:26', 'Deporte', 'Argentina', NULL, NULL),
(9, 'sofia_tech', 'sofia@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 1, '2025-12-02 00:28:26', 'Ciencia y Tecnologia', 'Chile', NULL, NULL),
(10, 'SERGIO08', 'sergio.gonzalez2@utp.edu.co', '$2y$10$mIcH9YLQk.191yNQ5pkh7OHwvlD4isXPckB7Lh2DA15I8RQmG.6VK', 'user', 1, '2025-12-02 02:07:33', 'Videojuegos', 'Colombia', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_follows`
--

CREATE TABLE `user_follows` (
  `id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `followed_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_post_id` (`post_id`),
  ADD KEY `idx_parent_id` (`parent_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_comments_post` (`post_id`);

--
-- Indices de la tabla `comment_votes`
--
ALTER TABLE `comment_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_comment` (`user_id`,`comment_id`),
  ADD KEY `comment_id` (`comment_id`);

--
-- Indices de la tabla `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_author_id` (`author_id`),
  ADD KEY `idx_theme` (`theme`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_posts_author` (`author_id`),
  ADD KEY `idx_posts_created` (`created_at`);

--
-- Indices de la tabla `post_votes`
--
ALTER TABLE `post_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_post` (`user_id`,`post_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_users_username` (`username`),
  ADD KEY `idx_users_email` (`email`);

--
-- Indices de la tabla `user_follows`
--
ALTER TABLE `user_follows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_follow` (`follower_id`,`followed_id`),
  ADD KEY `followed_id` (`followed_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `comment_votes`
--
ALTER TABLE `comment_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `post_votes`
--
ALTER TABLE `post_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `user_follows`
--
ALTER TABLE `user_follows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `comment_votes`
--
ALTER TABLE `comment_votes`
  ADD CONSTRAINT `comment_votes_ibfk_1` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comment_votes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `post_votes`
--
ALTER TABLE `post_votes`
  ADD CONSTRAINT `post_votes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_votes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user_follows`
--
ALTER TABLE `user_follows`
  ADD CONSTRAINT `user_follows_ibfk_1` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_follows_ibfk_2` FOREIGN KEY (`followed_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
