<?php
require_once __DIR__ . '/../../includes/sesion.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Nexora - Login</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

  <link rel="stylesheet" href="<?= rutaBase() ?>/assets/css/login_nexora.css">
</head>

<body>

  <div class="container-fluid p-0 min-vh-100 d-flex">

    <div class="row g-0 w-100">

      <!-- PANEL IZQUIERDO -->
      <div class="col-lg-4 col-md-5 hero-panel p-4 p-xl-5 d-flex flex-column justify-content-between position-relative">

        <div class="hero-overlay"></div>

        <div class="brand-header position-relative z-1 d-flex align-items-center gap-2">
        </div>

        <div class="hero-content position-relative z-1 my-auto">

          <h1 class="display-5 text-white fw-semibold mb-3 border-start border-3 border-amber ps-3">
            BIENVENIDOS<br>
          </h1>

          <p class="text-secondary-custom small mb-0 ps-3">
            Ten manejo completo de lo que esta pasando.
          </p>

        </div>

        <div class="hero-footer position-relative z-1">

          <small class="text-muted-custom">
            &copy; 2026 Chrono.
          </small>

        </div>

      </div>


      <!-- PANEL DEL FORMULARIO -->
      <div class="col-lg-8 col-md-7 form-panel d-flex flex-column justify-content-center align-items-center p-4">

        <div class="login-card p-4 p-sm-5 w-100">

          <div class="text-center mb-4">

            <!-- LOGO -->
            <svg id="barcode"
              height="100"
              viewBox=".515 .76148307 73.922 72.10751693"
              width="150"
              xmlns="http://www.w3.org/2000/svg">

              <path
                d="m37.73.767a7.967 7.967 0 0 0 -7.957 7.975 7.967 7.967 0 0 0 7.972 7.96 7.967 7.967 0 0 0 7.963-7.968v-.015a7.967 7.967 0 0 0 -7.979-7.952zm-26.284 18.403c-1.4.008-2.824.086-4.159.422-.88.223-1.731.59-2.284 1.16-.694.714-.783 1.683-.444 2.51.302.73 1.116 1.276 2.023 1.589 1.95.668 4.117.81 6.168 1.23.378.093.796.195 1.03.453.243.307.1.73-.299.923-.666.339-1.517.341-2.293.33-.708-.03-1.486-.089-2.05-.444-.414-.255-.487-.689-.394-1.062h-4.57c-.012.696.123 1.423.633 2.02.426.51 1.114.87 1.854 1.086 1.183.348 2.464.453 3.725.485 1.711.029 3.456-.022 5.1-.41.98-.239 1.942-.625 2.57-1.251 1.113-1.118.851-2.889-.63-3.758-.737-.43-1.62-.696-2.52-.868-1.317-.273-2.658-.473-3.992-.693-.469-.091-.964-.172-1.358-.393-.412-.221-.445-.743-.025-.97.543-.307 1.266-.305 1.916-.305.685.012 1.43.055 1.994.376a.855.855 0 0 1 .446.758l4.345-.01c-.017-.55-.119-1.123-.521-1.598-.467-.585-1.288-.954-2.133-1.17-1.325-.335-2.74-.404-4.131-.41zm9.426.328-.002 10.382h12.67v-2.263h-8.023v-2.015h7.155v-2.213h-7.155l-.001-1.652 7.734-.002-.005-2.237zm20.875.004-5.872.001-.001 10.382h4.448l-.002-6.986 6.093 6.981 6.105.006-.002-10.383-4.452.001.005 6.936zm18.702.017s-4.795 6.926-7.204 10.384h4.669l1.12-1.873h7.214l1.046 1.874 5.186-.001-6.873-10.382zm2.334 2.478 2.214 3.761-4.577.007zm-62.268 10.82.04 5.65 21.12-.074c1.077.231 1.702.933 1.482 2.526l-12.99 22.739 4.232 3.955 20.118-34.797zm40.3.048 19.784 34.649 4.372-3.93-13.14-22.679c-.22-1.599.405-2.308 1.483-2.54l21.123.075-.006-5.558zm-3.338 5.733-18.553 31.846 4.928 2.401 12.38-20.924c.427-.349.859-.534 1.29-.552.456-.017.917.151 1.379.512l12.351 20.988 5.083-2.652z"
                fill="#fff" />

            </svg>

            <br><br>

            <h2 class="text-white fw-semibold mb-1">
              Login
            </h2>

            <p class="text-muted-custom small">
              Login to your account to continue
            </p>

          </div>


          <!-- ========================= -->
          <!-- FORMULARIO LOGIN -->
          <!-- ========================= -->

          <form
            action="<?= rutaBase() ?>/index.php"
            method="POST">

            <!-- CORREO -->
            <div class="mb-3">

              <label class="form-label text-light-custom small">
                Email Address
              </label>

              <div class="input-group dark-input">

                <span class="input-group-text border-0 bg-transparent text-muted-custom">
                  <i class="bi bi-envelope"></i>
                </span>

                <input
                  type="text"
                  name="documento"
                  class="form-control border-0 text-white bg-transparent"
                  placeholder="Documento o correo institucional"
                  required>

              </div>

            </div>


            <!-- CONTRASEÑA -->
            <div class="mb-2">

              <label class="form-label text-light-custom small">
                Password
              </label>

              <div class="input-group dark-input">

                <span class="input-group-text border-0 bg-transparent text-muted-custom">
                  <i class="bi bi-lock"></i>
                </span>

                <input
                  type="password"
                  name="clave"
                  id="password"
                  class="form-control border-0 text-white bg-transparent"
                  placeholder="••••••••"
                  required>

                <span
                  class="input-group-text border-0 bg-transparent text-muted-custom role-button"
                  id="mostrarPassword"
                  style="cursor: pointer;">

                  <i class="bi bi-eye-slash" id="iconoPassword"></i>

                </span>

              </div>

            </div>


            <!-- RECUPERAR CONTRASEÑA -->
            <div class="text-end mb-4">

              <a
                href="#"
                class="text-amber text-decoration-none small">

                Forgot Password?

              </a>

            </div>


            <!-- BOTÓN LOGIN -->
            <button
              type="submit"
              name="login"
              class="btn btn-amber w-100 fw-semibold d-flex align-items-center justify-content-center gap-2 py-2 mb-4">

              Login

              <i class="bi bi-arrow-right"></i>

            </button>

          </form>


          <!-- REGISTRO -->
          <p class="text-center text-muted-custom small mb-0">

            Don't have an account?

            <a
              href="#"
              class="text-amber text-decoration-none fw-semibold">

              Sign up

            </a>

          </p>

        </div>

      </div>

    </div>

  </div>


  <!-- ========================= -->
  <!-- MOSTRAR / OCULTAR PASSWORD -->
  <!-- ========================= -->

  <script>

    const mostrarPassword = document.getElementById("mostrarPassword");
    const password = document.getElementById("password");
    const iconoPassword = document.getElementById("iconoPassword");

    mostrarPassword.addEventListener("click", function () {

      if (password.type === "password") {

        password.type = "text";

        iconoPassword.classList.remove("bi-eye-slash");
        iconoPassword.classList.add("bi-eye");

      } else {

        password.type = "password";

        iconoPassword.classList.remove("bi-eye");
        iconoPassword.classList.add("bi-eye-slash");

      }

    });

  </script>

</body>

</html>