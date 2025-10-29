/* Animação troca de container Login para Cadastro */
function animationLoginRegister() {
  const btnLogin = document.getElementById('btnL');
  const btnRegister = document.getElementById('btnR');
  const containerLogin = document.getElementById('login');
  const containerRegister = document.getElementById('register');

  btnLogin.addEventListener('click', () => {
    btnLogin.checked = true;
    btnRegister.checked = false;

    // Primeiro faz fade out do cadastro, depois mostra login com fade in
    containerRegister.classList.add('disabled');
    setTimeout(() => {
      containerRegister.style.display = 'none';

      containerLogin.style.display = 'block';
      containerLogin.classList.add('disabled');
      setTimeout(() => {
        containerLogin.classList.remove('disabled');
      }, 10);
    }, 400);
  });

  btnRegister.addEventListener('click', () => {
    btnRegister.checked = true;
    btnLogin.checked = false;

    // Primeiro faz fade out do login, depois mostra cadastro com fade in
    containerLogin.classList.add('disabled');
    setTimeout(() => {
      containerLogin.style.display = 'none';

      containerRegister.style.display = 'block';
      containerRegister.classList.add('disabled');
      setTimeout(() => {
        containerRegister.classList.remove('disabled');
      }, 10);
    }, 400);
  });
}

document.addEventListener('DOMContentLoaded', animationLoginRegister);
