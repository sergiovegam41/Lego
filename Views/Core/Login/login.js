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
function initTheme() {
    // Check for saved theme or default to light
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const theme = savedTheme || (prefersDark ? 'dark' : 'light');
    
    // Apply theme
    if (theme === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
}

function toggleTheme() {
    const isDark = document.documentElement.classList.contains('dark');
    
    if (isDark) {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    } else {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    }
}

// Initialize theme immediately
initTheme();

// Add event listener to theme toggle button when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
        console.log('Theme toggle button initialized');
    } else {
        console.error('Theme toggle button not found');
    }
});

console.log("Login component loaded")