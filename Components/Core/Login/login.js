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
    // Check for saved theme using storage manager or fallback to localStorage
    let savedTheme;
    if (window.storageManager) {
        savedTheme = window.storageManager.getTheme();
    } else {
        savedTheme = localStorage.getItem('lego_theme');
    }
    
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const theme = savedTheme || (prefersDark ? 'dark' : 'dark'); // Default to dark
    
    // Apply theme
    if (theme === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
}

function toggleTheme() {
    console.log('toggleTheme function called');
    const isDark = document.documentElement.classList.contains('dark');
    console.log('Current theme is dark:', isDark);
    
    if (isDark) {
        console.log('Switching to light theme');
        document.documentElement.classList.remove('dark');
        if (window.storageManager) {
            window.storageManager.setTheme('light');
            console.log('Theme saved to storageManager: light');
        } else {
            localStorage.setItem('lego_theme', 'light');
            console.log('Theme saved to localStorage: light');
        }
    } else {
        console.log('Switching to dark theme');
        document.documentElement.classList.add('dark');
        if (window.storageManager) {
            window.storageManager.setTheme('dark');
            console.log('Theme saved to storageManager: dark');
        } else {
            localStorage.setItem('lego_theme', 'dark');
            console.log('Theme saved to localStorage: dark');
        }
    }
}

// Initialize theme immediately
initTheme();

// Add event listener to theme toggle button when DOM is loaded

console.log('DOM loaded, searching for theme toggle button');

// Wait a bit for all elements to be ready
setTimeout(() => {
    const themeToggle = document.getElementById('theme-toggle');
    console.log('Theme toggle button found:', themeToggle);
    console.log('Button properties:', {
        style: themeToggle?.style?.cssText,
        classes: themeToggle?.className,
        offsetParent: themeToggle?.offsetParent,
        clientWidth: themeToggle?.clientWidth,
        clientHeight: themeToggle?.clientHeight
    });
    
    if (themeToggle) {
        // Multiple event listeners for debugging
        themeToggle.addEventListener('click', function(event) {
            console.log('CLICK EVENT TRIGGERED!', event);
            event.preventDefault();
            event.stopPropagation();
            toggleTheme();
        });
        
        themeToggle.addEventListener('mousedown', function(event) {
            console.log('MOUSEDOWN EVENT TRIGGERED!', event);
        });
        
        themeToggle.addEventListener('mouseup', function(event) {
            console.log('MOUSEUP EVENT TRIGGERED!', event);
        });
        
        themeToggle.addEventListener('pointerdown', function(event) {
            console.log('POINTERDOWN EVENT TRIGGERED!', event);
        });
        
        // Force click handler as backup
        themeToggle.onclick = function(event) {
            console.log('ONCLICK HANDLER TRIGGERED!', event);
            event.preventDefault();
            toggleTheme();
        };
        
        console.log('All event listeners added to theme toggle button');
    } else {
        console.error('Theme toggle button not found');
    }
}, 100);


console.log("Login component loaded")