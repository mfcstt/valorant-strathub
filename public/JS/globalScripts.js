/* Mostrar botão e limpar um campo input */
function clearField() {
  const buttons = document.querySelectorAll('.cleanBtn');
  buttons.forEach((button) => {
    const container = button.parentElement;
    const input = container ? container.querySelector('input.inpForm') : null;
    if (!input) return;

    // Inicializa visibilidade com base no valor atual
    button.style.display = input.value.trim() ? 'block' : 'none';

    input.addEventListener('input', () => {
      button.style.display = input.value.trim() ? 'block' : 'none';
    });

    button.addEventListener('click', () => {
      input.value = '';
      button.style.display = 'none';
      const form = input.closest('form');
      if (form && input.name === 'pesquisar') {
        form.submit();
      }
    });
  });
}
document.addEventListener('DOMContentLoaded', clearField);
