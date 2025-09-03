  <!-- header.php -->
     <style>
  /* Encabezado con fondo rojo oscuro, logo, título y menú */
header {
  background-color: #6b0014;
  color: white;
  padding: 16px 32px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
}

/* Contenedor del logo y nombre */
.logo-nombre {
  display: flex;
  align-items: center;
  gap: 15px;
}

/* Tamaño del logo */
.logo {
  height: 50px;
}

/* Título del encabezado */
header h1 {
  margin: 0;
  font-size: 24px;
}

/* Menú de navegación */
header nav {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
}

/* Enlaces del menú */
header nav a {
  color: white;
  text-decoration: none;
  font-weight: bold;
  transition: color 0.3s;
}

/* Efecto hover en los enlaces */
header nav a:hover {
  color: #ffcdd0;
}

/* Responsive para celulares */
@media (max-width: 600px) {
  header {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  .logo {
    height: 40px;
  }

  header h1 {
    font-size: 20px;
  }

  header nav {
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
  }

  header nav a {
    font-size: 14px;
  }
}
</style>
</head>
<body>
<header>
  <div class="logo-nombre">
    <img src="/1r Sprint-FMSDigital/Maquetacion/imagenes/logo.png" alt="Logo del colegio" class="logo">
    <h1>Julio Méndez</h1>
  </div>
  <nav>
    <a href="/1r Sprint-FMSDigital/Maquetacion/Pagina_Web/pagina_principal.php">Inicio</a>
    <a href="/1r Sprint-FMSDigital/Maquetacion/Pagina_Web/noticias.php">Noticias</a>
    <a href="/1r Sprint-FMSDigital/Maquetacion/Pagina_Web/galeria.php">Galería</a>
    <a href="/1r Sprint-FMSDigital/Maquetacion/Pagina_Web/Horario.php">Horarios</a>
    <a href="/1r Sprint-FMSDigital/Maquetacion/Pagina_Web/contacto.php">Contacto</a>
    <a href="/1r Sprint-FMSDigital/Maquetacion/cuentas_de_usuario/formulario_login.php">Iniciar sesión</a>
  </nav>
</header>