/**
 * Comportamentos das páginas internas.
 *
 * Cada função verifica se os seus elementos existem, então o mesmo arquivo
 * serve todas as páginas do app sem precisar de um bundle por rota.
 */

/* -------------------------------------------------------------------------- */
/* Menu mobile                                                                */
/* -------------------------------------------------------------------------- */

function initMobileMenu() {
  const toggle = document.getElementById('mobileMenuToggle')
  const panel = document.getElementById('mobileMenuPanel')
  const backdrop = document.getElementById('mobileMenuBackdrop')
  if (!toggle || !panel || !backdrop) return

  const setOpen = (open) => {
    panel.classList.toggle('hidden', !open)
    backdrop.classList.toggle('hidden', !open)
    toggle.setAttribute('aria-expanded', String(open))
  }

  toggle.addEventListener('click', () => setOpen(panel.classList.contains('hidden')))
  backdrop.addEventListener('click', () => setOpen(false))
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') setOpen(false)
  })
}

/* -------------------------------------------------------------------------- */
/* Painel de filtros das listagens                                            */
/* -------------------------------------------------------------------------- */

function initFiltersPanel() {
  const toggle = document.querySelector('[data-filters-toggle]')
  const panel = document.querySelector('[data-filters-panel]')
  const backdrop = document.querySelector('[data-filters-backdrop]')
  if (!toggle || !panel || !backdrop) return

  const setOpen = (open) => {
    panel.classList.toggle('hidden', !open)
    backdrop.classList.toggle('hidden', !open)
    toggle.setAttribute('aria-expanded', String(open))
  }

  toggle.addEventListener('click', () => setOpen(panel.classList.contains('hidden')))
  backdrop.addEventListener('click', () => setOpen(false))
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') setOpen(false)
  })
}

/* -------------------------------------------------------------------------- */
/* Modal de avaliação                                                         */
/* -------------------------------------------------------------------------- */

function initRatingModal() {
  const modal = document.querySelector('dialog.modal')
  if (!modal) return

  const openButtons = document.querySelectorAll('.showModal')
  const closeButton = modal.querySelector('.closeModal')
  const overlay = document.querySelector('.overlay')
  const blurTargets = document.querySelectorAll('.modalBlur')
  const scrollContainer = document.querySelector('.modalOverFlow')

  const FOCUSABLE = 'button, [href], textarea, input:not([type="hidden"]), select, label.star-icon'
  let firstFocusable = null
  let lastFocusable = null

  // Mantém o foco dentro do modal enquanto ele está aberto: sem isso, o Tab
  // leva a pessoa para trás do overlay, onde não há como enxergar o foco.
  const trapFocus = (event) => {
    if (event.key !== 'Tab' || !firstFocusable) return

    if (event.shiftKey && document.activeElement === firstFocusable) {
      lastFocusable.focus()
      event.preventDefault()
    } else if (!event.shiftKey && document.activeElement === lastFocusable) {
      firstFocusable.focus()
      event.preventDefault()
    }
  }

  const open = () => {
    modal.show()
    blurTargets.forEach((element) => element.classList.add('blur-sm'))
    scrollContainer?.classList.add('overflow-hidden')
    overlay?.classList.remove('hidden')
    document.body.style.overflow = 'hidden'

    const focusable = modal.querySelectorAll(FOCUSABLE)
    firstFocusable = focusable[0] ?? null
    lastFocusable = focusable[focusable.length - 1] ?? null
    document.addEventListener('keydown', trapFocus)
  }

  const close = () => {
    modal.close()
    blurTargets.forEach((element) => element.classList.remove('blur-sm'))
    scrollContainer?.classList.remove('overflow-hidden')
    overlay?.classList.add('hidden')
    document.body.style.overflow = ''
    document.removeEventListener('keydown', trapFocus)
  }

  openButtons.forEach((button) => button.addEventListener('click', open))
  closeButton?.addEventListener('click', close)
  overlay?.addEventListener('click', close)
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal.open) close()
  })

  // Reaberto automaticamente quando o envio anterior teve erro de validação.
  if (modal.classList.contains('open')) open()
}

/* -------------------------------------------------------------------------- */
/* Estrelas da avaliação                                                      */
/* -------------------------------------------------------------------------- */

function initStarRating() {
  const stars = Array.from(document.querySelectorAll('.star-icon'))
  if (stars.length === 0) return

  const paint = (upTo) => {
    stars.forEach((star, index) => {
      star.classList.remove('firstStar')
      star.classList.toggle('starActive', index <= upTo)
    })
  }

  stars.forEach((star, index) => {
    const radio = star.querySelector('input[type="radio"]')

    star.addEventListener('click', () => {
      if (radio) radio.checked = true
      paint(index)
    })

    // Suporte a teclado: o label recebe foco pelo radio interno.
    radio?.addEventListener('change', () => paint(index))
    if (radio?.checked) paint(index)
  })
}

/* -------------------------------------------------------------------------- */
/* Seleção de agente e mapa                                                   */
/* -------------------------------------------------------------------------- */

/**
 * Marca visualmente o card escolhido.
 *
 * Havia duas implementações concorrentes disto: uma aqui, aplicando a classe
 * `selected`, e outra inline no componente, aplicando classes de borda do
 * Tailwind. As duas rodavam juntas e disputavam o mesmo elemento.
 */
function initCardPicker(optionSelector, cardSelector) {
  const options = Array.from(document.querySelectorAll(optionSelector))
  if (options.length === 0) return

  const select = (chosen) => {
    options.forEach((option) => {
      const isChosen = option === chosen
      option.querySelector(cardSelector)?.classList.toggle('selected', isChosen)

      const radio = option.querySelector('input[type="radio"]')
      if (radio && isChosen) radio.checked = true
    })
  }

  options.forEach((option) => {
    option.addEventListener('click', () => select(option))

    const radio = option.querySelector('input[type="radio"]')
    radio?.addEventListener('change', () => {
      if (radio.checked) select(option)
    })

    if (radio?.checked) select(option)
  })
}

/* -------------------------------------------------------------------------- */
/* Pré-visualização dos uploads                                               */
/* -------------------------------------------------------------------------- */

function initImagePreview() {
  const input = document.getElementById('image-input')
  const box = document.getElementById('image-upload-box')
  const previewBox = document.getElementById('image-preview-box')
  const preview = document.getElementById('image-preview')
  const warning = document.getElementById('image-upload-warning')
  if (!input || !preview || !previewBox) return

  let objectUrl = null

  input.addEventListener('change', () => {
    if (objectUrl) URL.revokeObjectURL(objectUrl)

    const file = input.files?.[0]
    warning?.classList.toggle('hidden', !file)

    if (!file) {
      previewBox.classList.add('hidden')
      box?.querySelector('.upload-placeholder')?.classList.remove('hidden')
      preview.removeAttribute('src')
      objectUrl = null
      return
    }

    // URL.createObjectURL em vez de FileReader + data URL: não carrega o arquivo
    // inteiro na memória como string base64, o que importa em vídeos de 100 MB.
    objectUrl = URL.createObjectURL(file)
    preview.src = objectUrl
    previewBox.classList.remove('hidden')
    box?.querySelector('.upload-placeholder')?.classList.add('hidden')
  })
}

function initVideoPreview() {
  const input = document.getElementById('video-input')
  const box = document.getElementById('video-upload-box')
  const previewBox = document.getElementById('video-preview-box')
  const player = document.getElementById('video-player')
  const fallback = document.getElementById('video-fallback')
  const warning = document.getElementById('video-upload-warning')
  if (!input || !previewBox || !player) return

  let objectUrl = null

  const reset = () => {
    player.pause()
    player.removeAttribute('src')
    player.load()
    player.classList.add('hidden')
    fallback?.classList.add('hidden')
    fallback?.classList.remove('flex')
    previewBox.classList.add('hidden')
    box?.querySelector('.upload-placeholder')?.classList.remove('hidden')

    if (objectUrl) {
      URL.revokeObjectURL(objectUrl)
      objectUrl = null
    }
  }

  input.addEventListener('change', () => {
    reset()

    const file = input.files?.[0]
    warning?.classList.toggle('hidden', !file)
    if (!file) return

    objectUrl = URL.createObjectURL(file)

    // Placeholder textual enquanto os metadados carregam; o player aparece
    // quando há de fato um frame para mostrar.
    fallback?.classList.remove('hidden')
    fallback?.classList.add('flex')
    previewBox.classList.remove('hidden')
    box?.querySelector('.upload-placeholder')?.classList.add('hidden')

    player.addEventListener(
      'loadeddata',
      () => {
        fallback?.classList.add('hidden')
        fallback?.classList.remove('flex')
        player.classList.remove('hidden')
      },
      { once: true },
    )

    player.addEventListener(
      'error',
      () => {
        player.classList.add('hidden')
        fallback?.classList.remove('hidden')
        fallback?.classList.add('flex')
      },
      { once: true },
    )

    player.src = objectUrl
    player.load()
  })
}

/* -------------------------------------------------------------------------- */
/* Upload direto para o Storage                                               */
/* -------------------------------------------------------------------------- */

/**
 * Some com o corpo de um vídeo (ou imagem) antes mesmo de ele sair do
 * navegador: toda função serverless da Vercel corta a requisição em ~4,5 MB,
 * bem abaixo dos 100 MB de vídeo e 5 MB de imagem que o formulário anuncia.
 * Em vez de o arquivo viajar dentro do POST de /strategy-create, ele vai
 * direto para o Storage assim que é escolhido - o formulário principal só
 * carrega o caminho resultante, um texto curto que nunca esbarra em limite
 * nenhum.
 *
 * Só roda quando o servidor marcou data-direct-upload="1" no <form> (ver
 * strategy-create.component.php) - no ambiente local, sem esse limite, o
 * upload tradicional dentro do próprio POST continua funcionando normalmente.
 */
function initDirectUpload() {
  const form = document.querySelector('form[data-direct-upload="1"]')
  if (!form) return

  const token = form.querySelector('input[name="_token"]')?.value
  const submitButton = document.getElementById('publish-button')
  let pendingUploads = 0

  const setUploading = (isUploading) => {
    pendingUploads += isUploading ? 1 : -1
    if (submitButton) submitButton.disabled = pendingUploads > 0
  }

  const setIcon = (icon, name) => {
    if (!icon) return
    icon.classList.remove('ph-clock', 'ph-spinner', 'animate-spin', 'ph-check-circle', 'ph-warning')
    icon.classList.add(name)
    if (name === 'ph-spinner') icon.classList.add('animate-spin')
  }

  const fields = [
    {
      input: document.getElementById('image-input'),
      kind: 'capa',
      pathField: document.getElementById('capa-path'),
      text: document.getElementById('image-upload-warning-text'),
      icon: document.getElementById('image-upload-warning-icon'),
    },
    {
      input: document.getElementById('video-input'),
      kind: 'video',
      pathField: document.getElementById('video-path'),
      text: document.getElementById('video-upload-warning-text'),
      icon: document.getElementById('video-upload-warning-icon'),
    },
  ]

  fields.forEach(({ input, kind, pathField, text, icon }) => {
    if (!input || !pathField) return

    input.addEventListener('change', async () => {
      const file = input.files?.[0]
      pathField.value = ''
      if (!file) return

      const extension = (file.name.split('.').pop() || '').toLowerCase()

      if (text) text.textContent = 'Enviando...'
      setIcon(icon, 'ph-spinner')
      setUploading(true)

      try {
        const signResponse = await fetch('/upload-sign', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': token || '' },
          body: JSON.stringify({ kind, extension }),
        })

        const signData = await signResponse.json().catch(() => null)

        if (!signResponse.ok || !signData?.upload_url) {
          throw new Error(signData?.error || 'Não foi possível preparar o envio.')
        }

        // Mesmo formato que o SDK oficial do Supabase usa para este endpoint:
        // FormData com o arquivo num campo sem nome, não o arquivo cru no
        // corpo - o endpoint de upload assinado espera multipart.
        const uploadBody = new FormData()
        uploadBody.append('cacheControl', '3600')
        uploadBody.append('', file)

        // PUT, não POST: o SDK oficial (storage-js) usa PUT para efetivar o
        // envio nesta URL - um POST aqui cai no mesmo endpoint que *cria* a
        // URL assinada e devolve outro token, sem gravar nada no Storage
        // (foi exatamente isso que causou o vídeo "sumido" em produção).
        const uploadResponse = await fetch(signData.upload_url, {
          method: 'PUT',
          headers: {
            'x-upsert': 'true',
            // A API do Supabase Storage exige os dois - sem apikey, "headers
            // must have required property 'authorization'"; só com
            // Authorization e sem apikey, ela tenta decodificar o valor como
            // JWT e recusa a chave publicável nova com "Invalid Compact JWS".
            apikey: signData.authorization,
            Authorization: `Bearer ${signData.authorization}`,
          },
          body: uploadBody,
        })

        if (!uploadResponse.ok) {
          throw new Error('O envio para o servidor de arquivos falhou. Tente de novo.')
        }

        pathField.value = signData.path

        // O <input> original não precisa mais viajar no envio principal do
        // formulário - o arquivo já está no Storage, só o caminho importa.
        // Sem isso o binário seria enviado de novo, dessa vez sim estourando
        // o limite da função serverless.
        input.removeAttribute('name')

        if (text) text.textContent = 'Enviado. Clique em “Publicar” para concluir.'
        setIcon(icon, 'ph-check-circle')
      } catch (error) {
        pathField.value = ''
        if (text) text.textContent = error instanceof Error ? error.message : 'Falha ao enviar o arquivo.'
        setIcon(icon, 'ph-warning')
      } finally {
        setUploading(false)
      }
    })
  })
}

/* -------------------------------------------------------------------------- */
/* Vídeos usados como capa nas listagens                                      */
/* -------------------------------------------------------------------------- */

function initLazyVideoCovers() {
  const videos = Array.from(document.querySelectorAll('video.lazy-video[data-src]'))
  if (videos.length === 0) return

  const activate = (video) => {
    video.muted = true
    video.playsInline = true
    video.preload = 'metadata'
    video.src = video.dataset.src
    video.removeAttribute('data-src')

    // Avança um frame para o vídeo mostrar imagem em vez de um retângulo preto.
    video.addEventListener('loadedmetadata', () => {
      try {
        video.currentTime = 0.01
      } catch {
        /* alguns navegadores recusam o seek antes do buffer; ignorar */
      }
    })
    video.addEventListener('loadeddata', () => video.pause())
  }

  if (!('IntersectionObserver' in window)) {
    videos.forEach(activate)
    return
  }

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return
        activate(entry.target)
        observer.unobserve(entry.target)
      })
    },
    { rootMargin: '200px 0px' },
  )

  videos.forEach((video) => observer.observe(video))
}

/* -------------------------------------------------------------------------- */
/* Lightbox da capa                                                           */
/* -------------------------------------------------------------------------- */

function initImageLightbox() {
  const trigger = document.getElementById('strategyCoverImage')
  const lightbox = document.getElementById('imageLightbox')
  const image = document.getElementById('imageLightboxImg')
  const closeButton = document.getElementById('closeImageLightbox')
  const backdrop = document.getElementById('imageLightboxBackdrop')
  if (!trigger || !lightbox || !image) return

  const open = () => {
    image.src = trigger.src
    lightbox.classList.remove('hidden')
    document.body.style.overflow = 'hidden'
    closeButton?.focus()
  }

  const close = () => {
    lightbox.classList.add('hidden')
    document.body.style.overflow = ''
    trigger.focus()
  }

  trigger.addEventListener('click', open)
  closeButton?.addEventListener('click', close)
  backdrop?.addEventListener('click', close)
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !lightbox.classList.contains('hidden')) close()
  })
}

/* -------------------------------------------------------------------------- */
/* Compartilhar                                                               */
/* -------------------------------------------------------------------------- */

function showTransientToast(text, icon = 'ph-share-network') {
  const PADDING = 32
  const VISIBLE_MS = 4000

  const toast = document.createElement('div')
  toast.className =
    'fixed bottom-8 right-[-400px] z-20 w-auto max-w-[90vw] md:max-w-[480px] break-words flex flex-col pb-1 px-1 text-white border border-red-base rounded-md bg-gray-1 shadow-buttonHover'
  toast.setAttribute('role', 'status')

  const body = document.createElement('div')
  body.className = 'flex items-center gap-2 px-8 pt-4 pb-3'

  const iconElement = document.createElement('i')
  iconElement.className = `ph ${icon} text-red-base text-2xl`

  const label = document.createElement('span')
  label.className = 'text-lg'
  // textContent, não innerHTML: o texto pode conter o título da estratégia.
  label.textContent = text

  body.append(iconElement, label)

  const track = document.createElement('div')
  track.className = 'w-full h-0.5 bg-gray-3 rounded-xl'
  const progress = document.createElement('div')
  progress.className = 'h-full bg-red-light'
  progress.style.width = '0'
  progress.style.transition = `width ${VISIBLE_MS}ms linear`
  track.append(progress)

  toast.append(body, track)
  document.body.append(toast)

  toast.style.right = `-${toast.offsetWidth + PADDING}px`
  requestAnimationFrame(() => {
    toast.style.right = `${PADDING}px`
    progress.style.width = '100%'
  })

  setTimeout(() => {
    toast.style.right = `-${toast.offsetWidth + PADDING}px`
    setTimeout(() => toast.remove(), 600)
  }, VISIBLE_MS)
}

async function copyToClipboard(text) {
  if (navigator.clipboard?.writeText) {
    await navigator.clipboard.writeText(text)
    return
  }

  // Fallback para navegadores sem a Clipboard API (ou fora de HTTPS).
  const field = document.createElement('textarea')
  field.value = text
  field.setAttribute('readonly', '')
  field.style.position = 'fixed'
  field.style.top = '-9999px'
  document.body.append(field)
  field.select()

  try {
    document.execCommand('copy')
  } finally {
    field.remove()
  }
}

function initShareButtons() {
  document.querySelectorAll('[data-share]').forEach((button) => {
    button.addEventListener('click', async () => {
      const url = new URL(button.dataset.shareUrl, window.location.origin).toString()
      const title = button.dataset.shareTitle || 'Estratégia de Valorant'

      if (navigator.share) {
        try {
          await navigator.share({ title, url })
          return
        } catch {
          // Cancelar o compartilhamento nativo não é erro: segue para o copiar.
        }
      }

      try {
        await copyToClipboard(url)
        showTransientToast('Link copiado para a área de transferência.')
      } catch {
        showTransientToast('Não foi possível copiar o link.', 'ph-warning')
      }
    })
  })
}

/* -------------------------------------------------------------------------- */
/* Pré-visualização do elo no perfil                                          */
/* -------------------------------------------------------------------------- */

function initEloPreview() {
  const select = document.querySelector('[data-elo-select]')
  const preview = document.querySelector('[data-elo-preview]')
  if (!select || !preview) return

  const update = () => {
    const elo = select.value.toLowerCase()
    preview.src = `/assets/images/elos/${elo}.png`
    preview.alt = `Elo ${elo}`
  }

  select.addEventListener('change', update)
  update()
}

/* -------------------------------------------------------------------------- */
/* Rótulo de input de arquivo customizado                                     */
/* -------------------------------------------------------------------------- */

function initFileLabels() {
  document.querySelectorAll('[data-file-input]').forEach((input) => {
    const label = input.closest('label')?.querySelector('[data-file-label]')
      ?? document.querySelector(`label[for="${input.id}"] [data-file-label]`)
    if (!label) return

    const defaultText = label.textContent

    input.addEventListener('change', () => {
      label.textContent = input.files?.[0]?.name ?? defaultText
    })
  })
}

/* -------------------------------------------------------------------------- */

document.addEventListener('DOMContentLoaded', () => {
  initMobileMenu()
  initFiltersPanel()
  initRatingModal()
  initStarRating()
  initCardPicker('.agent-option', '.agent-card')
  initCardPicker('.map-option', '.map-card')
  initImagePreview()
  initVideoPreview()
  initDirectUpload()
  initLazyVideoCovers()
  initImageLightbox()
  initShareButtons()
  initEloPreview()
  initFileLabels()
})
