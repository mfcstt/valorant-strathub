/* Animação troca de container Login para Cadastro */
function animationLoginRegister() {
  const btnLogin = document.getElementById("btnL");
  const btnRegister = document.getElementById("btnR");
  const containerLogin = document.getElementById("login");
  const containerRegister = document.getElementById("register");

  btnLogin.addEventListener("click", () => {
    btnLogin.checked = true;
    btnRegister.checked = false;

    containerLogin.style.display = "block";

    setTimeout(() => {
      containerLogin.classList.remove("disabled");
    }, 10);

    setTimeout(() => {
      containerRegister.style.display = "none";
    }, 150);
  });

  btnRegister.addEventListener("click", () => {
    btnRegister.checked = true;
    btnLogin.checked = false;

    containerRegister.style.display = "block";
    containerLogin.classList.add("disabled");

    setTimeout(() => {
      containerLogin.style.display = "none";
    }, 150);
  });
}
document.addEventListener("DOMContentLoaded", animationLoginRegister);