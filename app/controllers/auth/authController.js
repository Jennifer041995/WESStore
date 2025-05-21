const API = '../../models/auth/AuthModel.php';

async function postData(action, form) {
  const formData = new FormData(form);
  const response = await fetch(`${API}?action=${action}`, {
    method: 'POST',
    body: formData
  });
  const text = await response.text();
  try {
    return JSON.parse(text);
  } catch (err) {
    console.error('Respuesta no JSON:', text);
    Swal.fire('Error del servidor. Revisa la consola.');
    throw new Error('Invalid JSON');
  }
}

// Registro
const registerForm = document.getElementById('registerForm');
if (registerForm) {
  registerForm.addEventListener('submit', async e => {
    e.preventDefault();
    try {
      const result = await postData('register', registerForm);
      Swal.fire(result.msg);
      if (result.status) location.href = 'login.html';
    } catch {} // el error ya se mostró
  });
}

// Login
const loginForm = document.getElementById('loginForm');
if (loginForm) {
  loginForm.addEventListener('submit', async e => {
    e.preventDefault();
    try {
      const result = await postData('login', loginForm);
      if (result.status) location.href = '../../../index.html';
      else Swal.fire(result.msg);
    } catch {} // ya manejado
  });
}

// Logout
function logout() {
  fetch(`${API}?action=logout`)
    .then(() => location.href = 'login.html');
}