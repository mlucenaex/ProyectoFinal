<?php


ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
 }
 
$userId   = 1;
$username = 'Prueba';

include 'includes/conexion.php';

$userId   = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Recuperor entradas previas
$entradas = [];
$sql = "SELECT id, titulo, contenido, fecha 
        FROM entrada 
        WHERE usuario_id = ? 
        ORDER BY fecha DESC";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id, $titulo, $contenido, $fecha);
    while (mysqli_stmt_fetch($stmt)) {
        $entradas[] = [
            'id'        => $id,
            'titulo'    => $titulo,
            'contenido' => $contenido,
            'fecha'     => $fecha
        ];
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>dIAario - Menú principal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
   
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
  <div class="container">
   <!-- Menú lateral -->
    <aside class="sidebar">
      <h1 class="logo">dIAario</h1>
      <nav>
        <ul>
          <li><a href="index.php" class="active">Menú principal</a></li>
          <li><a href="crear_entrada.php">Crear nueva entrada</a></li>
          <li><a href="ver_entradas.php">Ver entradas anteriores</a></li>
        </ul>
      </nav>
      <button id="btnAjustes" class="boton-ajustes">Ajustes ⚙️</button>
      <div id="menuAjustes" class="menu-ajustes oculto">
        <ul>
          <li><a href="cambiar_contrasena.php">Cambiar contraseña</a></li>
          <li><a href="logout.php">Logout</a></li>
          <li><a href="descargar_entradas.php">Descargar entradas</a></li>
        </ul>
      </div>
    </aside>

    <!-- Contenido principal -->
    <main class="contenido">
      <p class="usuario">Usuario: <?php echo htmlspecialchars($username); ?></p>

      <!-- Entradas previas -->
      <section class="entradas-previas">
        <h2>Entradas previas</h2>
        <?php if (empty($entradas)): ?>
          <p>No has creado entradas aún.</p>
        <?php else: ?>
          <?php foreach ($entradas as $e): ?>
            <article class="entrada">
              <h3><?php echo htmlspecialchars($e['titulo']); ?></h3>
              <small><?php echo date("d/m/Y H:i", strtotime($e['fecha'])); ?></small>
              <p>
                <?php
                  $res = substr($e['contenido'], 0, 100);
                  echo nl2br(htmlspecialchars($res));
                  if (strlen($e['contenido']) > 100) echo "...";
                ?>
              </p>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </section>

      <!-- Formulario para nueva entrada -->
      <section class="nueva-entrada">
        <h2>Crear entrada nueva</h2>
        <form action="guardar_entrada.php" method="post" enctype="multipart/form-data">
          <label for="titulo">Título:</label>
          <input type="text" id="titulo" name="titulo" required>

          <label for="contenido">Contenido:</label>
          <textarea id="contenido" name="contenido" rows="5" required></textarea>

          <label for="imagenes">Imágenes (jpg, png):</label>
          <input type="file" id="imagenes" name="imagenes[]" accept="image/*" multiple>

          <label for="video">Enlace YouTube (opcional):</label>
          <input type="text" id="video" name="video" placeholder="https://youtu.be/...">

          <button type="button" id="botonIA" class="boton-ia">Activar IA 🤖</button>
          <button type="submit">Guardar Entrada</button>
        </form>
      </section>
    </main>
  </div>

  <!-- Footer -->
  <footer class="footer">
    <a href="sobre.php">Sobre dIAario</a> | 
    <a href="contacto.php">Contacto</a>
    <span class="derechos">Copyright ???</span>
  </footer>

 
  <script src="js/main.js"></script>
</body>
</html>
