-- 1. Crear y asegurar el uso de la Base de Datos
CREATE DATABASE IF NOT EXISTS portafolio_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE portafolio_db;

-- ============================================================
-- ELIMINACIÓN DE TABLAS EXISTENTES (Garantiza reinicio limpio)
-- ============================================================
DROP TABLE IF EXISTS proyectos;
DROP TABLE IF EXISTS tecnologias;
DROP TABLE IF EXISTS habilidades;
DROP TABLE IF EXISTS etiquetas;
DROP TABLE IF EXISTS biografia;

-- ============================================================
-- CREACIÓN DE LAS TABLAS DESDE CERO
-- ============================================================

-- 2. Tabla para Datos de Biografía (Fija - Usará siempre el ID 1)
CREATE TABLE biografia (
  id INT AUTO_INCREMENT NOT NULL,
  nombre VARCHAR(200) NOT NULL,
  titulo VARCHAR(200) NOT NULL,
  descripcion TEXT NOT NULL,
  foto_perfil VARCHAR(255) DEFAULT 'assets/img/placeholder.png',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabla para las Etiquetas de la Biografía
CREATE TABLE etiquetas (
  id INT AUTO_INCREMENT NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  destacada TINYINT(1) DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabla para Habilidades (Tarjetas de Especialidades)
CREATE TABLE habilidades (
  id INT AUTO_INCREMENT NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  icono VARCHAR(100) NOT NULL DEFAULT 'bi-code-slash',
  color VARCHAR(20) NOT NULL DEFAULT '#0d6efd',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabla para Tecnologías (Barras de Progreso)
CREATE TABLE tecnologias (
  id INT AUTO_INCREMENT NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  progreso INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Tabla para el Catálogo de Proyectos
CREATE TABLE proyectos (
  id INT AUTO_INCREMENT NOT NULL,
  titulo VARCHAR(200) NOT NULL,
  descripcion TEXT NOT NULL,
  url_demo VARCHAR(255) DEFAULT '#',
  url_github VARCHAR(255) DEFAULT '#',
  imagen VARCHAR(255) DEFAULT 'assets/img/placeholder.png',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- INSERCIÓN DE DATOS INICIALES (Muestra Académica Única)
-- ============================================================

-- Biografía Inicial Fija (ID = 1)
INSERT INTO biografia (nombre, titulo, descripcion, foto_perfil) VALUES
('Vicente Mauricio Ortiz Ortega', 
 'Desarrollador Web Full Stack', 
 'Estudiante apasionado por el desarrollo web con experiencia en tecnologías frontend y backend. Especializado en crear aplicaciones web modernas, responsivas y funcionales utilizando PHP, MySQL, JavaScript y frameworks.', 
 'assets/img/placeholder.png');

-- Etiquetas iniciales del diseño
INSERT INTO etiquetas (nombre, destacada) VALUES
('Desarrollo Web', 1),
('Full Stack', 0),
('Bases de Datos', 0);

-- Habilidades de tu portafolio con sus respectivos iconos y colores originales
INSERT INTO habilidades (nombre, icono, color) VALUES
('HTML', 'bi-filetype-html', '#e34f26'),
('CSS', 'bi-filetype-css', '#1572b6'),
('JavaScript', 'bi-filetype-js', '#f7df1e'),
('PHP', 'bi-filetype-php', '#777bb4'),
('MySQL', 'bi-database-fill', '#00758f'),
('Bootstrap', 'bi-bootstrap-fill', '#7952b3'),
('GitHub', 'bi-github', '#24292e');

-- Tecnologías para tus barras de progreso con tus porcentajes originales
INSERT INTO tecnologias (nombre, progreso) VALUES
('HTML/CSS', 90),
('JavaScript', 85),
('PHP', 80),
('MySQL', 75),
('Bootstrap', 88),
('Git/GitHub', 82);

-- Bloques de Proyectos Iniciales
INSERT INTO proyectos (titulo, descripcion, url_demo, url_github, imagen) VALUES
('Proyecto 1', 'Descripción breve de muestra para el proyecto número uno. Aquí se detallarán los objetivos y el stack técnico utilizado.', '#', '#', 'assets/img/placeholder.png'),
('Proyecto 2', 'Descripción breve de muestra para el proyecto número dos. Aquí se detallarán los objetivos y el stack técnico utilizado.', '#', '#', 'assets/img/placeholder.png'),
('Proyecto 3', 'Descripción breve de muestra para el proyecto número tres. Aquí se detallarán los objetivos y el stack técnico utilizado.', '#', '#', 'assets/img/placeholder.png');