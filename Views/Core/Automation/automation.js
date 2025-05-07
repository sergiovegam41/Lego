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
        // Guardar en localStorage
        localStorage.setItem('access_token', data.data.access_token);
        localStorage.setItem('expires_at', data.data.expires_at);
        localStorage.setItem('refresh_token', data.data.refresh_token);
        localStorage.setItem('refresh_expires_at', data.data.refresh_expires_at);

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

    console.log("hola mundo")