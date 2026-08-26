/**
 * Comportamentos presentes em toda página.
 *
 * Antes, boa parte disso vivia em blocos <script> inline dentro das views - o
 * que impede cache pelo navegador e, mais adiante, impede adotar uma Content
 * Security Policy sem `unsafe-inline`.
 */

/**
 * Botão de limpar campo de texto.
 */
function initClearFieldButtons() {
  document.querySelectorAll('.cleanBtn').forEach((button) => {
    const container = button.parentElement
    const input = container?.querySelector('input.inpForm, textarea')
    if (!input) return

    const sync = () => button.classList.toggle('hidden', input.value.trim() === '')

    sync()
    input.addEventListener('input', sync)

    button.addEventListener('click', () => {
      input.value = ''
      sync()
      input.focus()

      // Limpar a busca deve reexibir a lista completa na hora.
      const form = input.closest('form')
      if (form && input.name === 'pesquisar') form.submit()
    })
  })
}

/**
 * Toasts de mensagem e erro: entram pela direita.
 *
 * Um aviso curto ("Estratégia excluída com sucesso.") some sozinho - a
 * barrinha de progresso mostra quanto tempo falta. Um aviso longo (por
 * exemplo, o texto explicando que a estratégia entrou em moderação) fica na
 * tela até a pessoa fechar: sumia rápido demais pra dar tempo de ler o
 * parágrafo inteiro. O botão de fechar funciona nos dois casos.
 */
function initToasts() {
  const PADDING = 32
  const VISIBLE_MS = 4500
  const LONG_MESSAGE_CHARS = 70

  document.querySelectorAll('[data-toast]').forEach((toast) => {
    const progress = toast.querySelector('.progress')
    const closeButton = toast.querySelector('[data-toast-close]')
    const text = toast.querySelector('span')?.textContent ?? ''
    const isLong = text.trim().length > LONG_MESSAGE_CHARS
    const offscreen = () => `-${toast.offsetWidth + PADDING}px`

    let hideTimeout = null

    const hide = () => {
      if (hideTimeout) clearTimeout(hideTimeout)
      toast.style.right = offscreen()
    }

    toast.style.right = offscreen()
    requestAnimationFrame(() => {
      toast.style.right = `${PADDING}px`
    })

    closeButton?.addEventListener('click', hide)

    if (isLong) {
      // Sem barra de progresso nem timer: a pessoa fecha quando quiser.
      progress?.parentElement?.classList.add('hidden')
      return
    }

    if (progress) {
      progress.style.transition = `width ${VISIBLE_MS}ms linear`
      requestAnimationFrame(() => {
        progress.style.width = '100%'
      })
    }

    hideTimeout = setTimeout(hide, VISIBLE_MS)
  })
}

/**
 * Confirmação antes de enviar formulários destrutivos.
 *
 * Declarada por `data-confirm` no <form>, em vez de `onclick` inline em cada
 * botão - o que também mantinha a mensagem duplicada em quatro lugares.
 */
function initConfirmForms() {
  document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (!window.confirm(form.dataset.confirm)) event.preventDefault()
    })
  })
}

/**
 * Selects que submetem o formulário ao mudar (ordenação das listagens).
 */
function initAutoSubmitSelects() {
  document.querySelectorAll('[data-submit-on-change]').forEach((select) => {
    select.addEventListener('change', () => select.form?.submit())
  })
}

/**
 * Botão "Voltar" da página de erro.
 */
function initHistoryBack() {
  document.querySelectorAll('[data-history-back]').forEach((button) => {
    button.addEventListener('click', () => {
      if (window.history.length > 1) window.history.back()
      else window.location.assign('/explore')
    })
  })
}

document.addEventListener('DOMContentLoaded', () => {
  initClearFieldButtons()
  initToasts()
  initConfirmForms()
  initAutoSubmitSelects()
  initHistoryBack()
})
