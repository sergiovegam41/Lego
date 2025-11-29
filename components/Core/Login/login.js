let context = {CONTEXT};

console.log(context);

document.getElementById('submit-button').addEventListener('click', async (event) => {


  // Evitar que el formulario envíe y recargue la página
  event.preventDefault();

  let email = document.getElementById('email').value;
  let password = document.getElementById('password').value;

  const myHeaders = new Headers();
  myHeaders.append("Content-Type", "application/json");

  const raw = JSON.stringify({
    "username": email,
    "password": password,
    "device_id": 1
  });

  const requestOptions = {
    method: "POST",
    headers: myHeaders,
    body: raw,
    redirect: "follow"
  };

  window.lego.loading(true,{
    withMenu:true
  });



  fetch("/api/auth/admin/login", requestOptions)
    .then(async (result) => {

    console.log(result);

      window.lego.loading(false,{
          success:true,
          message:"ok"
      });
      let data = await result.json();

      if (data.success) {
        // Guardar tokens usando storage manager unificado si está disponible
        if (window.storageManager) {
          window.storageManager.setSession(data.data);
        } else {
          // Fallback a localStorage
          localStorage.setItem('access_token', data.data.access_token);
          localStorage.setItem('expires_at', data.data.expires_at);
          localStorage.setItem('refresh_token', data.data.refresh_token);
          localStorage.setItem('refresh_expires_at', data.data.refresh_expires_at);
        }

        // Redirigir a la página de inicio
        window.location.href = "/admin";
      } else {
        // Manejo de error (puedes agregar mensajes aquí)
        console.error('Login failed:', data.message);
      }
    })
    .catch((error) => console.error('Error:', error));
});


document.getElementById('toggle-password').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');
    const eyeOffIcon = document.getElementById('eye-off-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.add('hidden');
        eyeOffIcon.classList.remove('hidden');
    } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('hidden');
        eyeOffIcon.classList.add('hidden');
    }
    });

// Theme toggle functionality
// LEGO Standard: Usar window.themeManager para consistencia con todo el framework

/**
 * Inicializa el toggle de tema usando el ThemeManager global
 * Patrón unificado igual que en HeaderComponent
 */
function initializeThemeToggle() {
    const themeToggle = document.getElementById('theme-toggle');
    
    if (!themeToggle) {
        console.error('[Login] Theme toggle button not found');
        return;
    }
    
    // Usar themeManager si está disponible, sino fallback local
    if (window.themeManager) {
        themeToggle.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            
            // Visual feedback
            this.style.transform = 'scale(0.9)';
            
            setTimeout(() => {
                window.themeManager.toggle();
                this.style.transform = '';
            }, 100);
        });
        
        // Hover effects
        themeToggle.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });
        
        themeToggle.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
        
        console.log('[Login] Theme toggle initialized with themeManager');
    } else {
        // Fallback: implementación local compatible con html.dark/html.light
        themeToggle.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            
            this.style.transform = 'scale(0.9)';
            
            setTimeout(() => {
                toggleThemeFallback();
                this.style.transform = '';
            }, 100);
        });
        
        console.log('[Login] Theme toggle initialized with fallback');
    }
}

/**
 * Fallback para toggle de tema si themeManager no está disponible
 * Mantiene consistencia con el estándar LEGO (html.dark/html.light)
 */
function toggleThemeFallback() {
    const html = document.documentElement;
    const body = document.body;
    const isDark = html.classList.contains('dark');
    const STORAGE_KEY = 'lego_theme';
    
    if (isDark) {
        // Cambiar a light
        html.classList.remove('dark');
        html.classList.add('light');
        body.classList.remove('dark');
        body.classList.add('light');
        html.style.colorScheme = 'light';
        localStorage.setItem(STORAGE_KEY, 'light');
    } else {
        // Cambiar a dark
        html.classList.remove('light');
        html.classList.add('dark');
        body.classList.remove('light');
        body.classList.add('dark');
        html.style.colorScheme = 'dark';
        localStorage.setItem(STORAGE_KEY, 'dark');
    }
}

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeThemeToggle);
} else {
    // DOM ya cargado, esperar un tick para que themeManager esté disponible
    setTimeout(initializeThemeToggle, 50);
}


console.log("Login component loaded");