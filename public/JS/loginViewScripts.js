/**
 * Alternância entre os painéis de login e cadastro.
 *
 * A versão anterior chamava addEventListener sem checar a existência dos
 * elementos, então qualquer página que carregasse este script sem os dois
 * painéis quebrava com TypeError e interrompia o restante do arquivo.
 */

function initAuthPanelSwitch() {
  const loginTab = document.getElementById('btnL')
  const registerTab = document.getElementById('btnR')
  const loginPanel = document.getElementById('login')
  const registerPanel = document.getElementById('register')

  if (!loginTab || !registerTab || !loginPanel || !registerPanel) return

  const FADE_MS = 400

  const show = (incoming, outgoing) => {
    outgoing.classList.add('disabled')

    setTimeout(() => {
      outgoing.style.display = 'none'
      outgoing.classList.add('hidden')

      incoming.classList.remove('hidden')
      incoming.style.display = 'block'
      incoming.classList.add('disabled')

      requestAnimationFrame(() => incoming.classList.remove('disabled'))
    }, FADE_MS)
  }

  loginTab.addEventListener('click', () => {
    loginTab.checked = true
    registerTab.checked = false
    show(loginPanel, registerPanel)
  })

  registerTab.addEventListener('click', () => {
    registerTab.checked = true
    loginTab.checked = false
    show(registerPanel, loginPanel)
  })
}

document.addEventListener('DOMContentLoaded', initAuthPanelSwitch)
