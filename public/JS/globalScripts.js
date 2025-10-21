/* Mostrar botão e limpar um campo input */
function clearField() {
  const btns = document.querySelectorAll('.cleanBtn');
  const inpts = document.querySelectorAll('.inpForm');

  inpts.forEach((input, i) => {
    const button = btns[i];

    input.addEventListener('input', () => {
      button.style.display = input.value.trim() ? 'block' : 'none';
    });

    button.addEventListener('click', () => {
      input.value = '';
      button.style.display = 'none';
    });
  });
}
document.addEventListener('DOMContentLoaded', clearField);
