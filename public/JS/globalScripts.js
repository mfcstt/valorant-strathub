/**
 * Comportamentos presentes em toda página.
 *
 * Antes, boa parte disso vivia em blocos <script> inline dentro das views — o
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
 * Toasts de mensagem e erro: entram pela direita e saem sozinhos.
 */
function initToasts() {
  const PADDING = 32
  const VISIBLE_MS = 4500

  document.querySelectorAll('[data-toast]').forEach((toast) => {
    const progress = toast.querySelector('.progress')
    const offscreen = () => `-${toast.offsetWidth + PADDING}px`

    toast.style.right = offscreen()
    requestAnimationFrame(() => {
      toast.style.right = `${PADDING}px`
    })

    if (progress) {
      progress.style.transition = `width ${VISIBLE_MS}ms linear`
      requestAnimationFrame(() => {
        progress.style.width = '100%'
      })
    }

    setTimeout(() => {
      toast.style.right = offscreen()
    }, VISIBLE_MS)
  })
}

/**
 * Confirmação antes de enviar formulários destrutivos.
 *
 * Declarada por `data-confirm` no <form>, em vez de `onclick` inline em cada
 * botão — o que também mantinha a mensagem duplicada em quatro lugares.
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
