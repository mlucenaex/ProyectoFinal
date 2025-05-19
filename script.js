(function() {
  const params = new URLSearchParams(window.location.search);

  /* ---------------------------------------------------
    Pagina Login
  --------------------------------------------------- */
  const loginForm = document.getElementById('login-form');
  if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
      const email    = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value.trim();

      if (!email || !password) {
        alert('Por favor, ingrese correo y contraseña.');
        e.preventDefault();
        return;
      }
      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailPattern.test(email)) {
        alert('Por favor, ingrese un correo electrónico válido.');
        e.preventDefault();
      }
    });

    // Botón Google (simulado aún falta api)
    const googleBtn = document.getElementById('google-btn');
    if (googleBtn) {
      googleBtn.addEventListener('click', () => {
        alert('Inicio de sesión con Google no implementado.');
      });
    }

    // Mostrar mensajes en login.html
    window.addEventListener('load', () => {
      const err = document.getElementById('error-message');
      if (!err) return;

      if (params.get('error') === 'confirm') {
        err.textContent = 'Debes confirmar tu correo antes de iniciar sesión.';
        err.style.display = 'block';
      } else if (params.has('error')) {
        err.textContent = 'Correo o contraseña incorrectos.';
        err.style.display = 'block';
      }

      if (params.has('registered')) {
        alert('¡Registro exitoso! Ahora puedes iniciar sesión.');
      }
    });
  }

  /* ---------------------------------------------------
     Página registro
  --------------------------------------------------- */
  const registerForm = document.getElementById('register-form');
  const toast        = document.getElementById('toast');

  function showToast(msg) {
    toast.textContent = msg;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 4000);
  }

  if (registerForm) {
    registerForm.addEventListener('submit', function(e) {
      e.preventDefault();             
      const formData = new FormData(this);

      fetch('auth/registro.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(json => {
        if (json.success) {
          // Registro ok → redirigir a login
          window.location.href = json.redirect;
        } else {
          showToast(json.msg || 'Error desconocido.');
        }
      })
      .catch(err => {
        console.error(err);
        showToast('Fallo de red. Intenta de nuevo.');
      });
    });
  }

})();
