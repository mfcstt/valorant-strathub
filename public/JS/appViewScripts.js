/* Submit quando avatar de perfil for selecionado*/
function submitFormOnFileSelect() {
  var fileInput = document.getElementById("avatarProfile");
  var form = document.getElementById("formAvatarProfile");
  if (fileInput && form) {
    fileInput.addEventListener("change", function () {
      form.submit();
    });
  }
}
window.onload = submitFormOnFileSelect;

/* Abrir e Fechar o modal para avaliar um filme */
function Modal() {
  const modal = document.querySelector("dialog");
  if (!modal) return;
  const showBtns = document.querySelectorAll(".showModal");
  const closeBtn = document.querySelector(".closeModal");
  const isOpen = modal.classList.contains("open");

  const overlay = document.querySelector(".overlay");
  const divBlur = document.querySelectorAll(".modalBlur");
  const divOverFlow = document.querySelector(".modalOverFlow");

  if (isOpen) {
    modal.show();

    divBlur.forEach((div) => {
      div.classList.add("blur-sm");
    });

    divOverFlow.classList.toggle("overflow-hidden");
    overlay.classList.toggle("hidden");
  }

  const focusableElements = "button, [href], textarea";
  let firstFocusableElement;
  let lastFocusableElement;

  showBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      modal.show();

      divBlur.forEach((div) => {
        div.classList.toggle("blur-sm");
      });

      divOverFlow.classList.toggle("overflow-hidden");
      overlay.classList.toggle("hidden");

      // Começar evento de focar apenas elementos do modal
      const focusableContent = modal.querySelectorAll(focusableElements);
      firstFocusableElement = focusableContent[0];
      lastFocusableElement = focusableContent[focusableContent.length - 1];

      document.addEventListener("keydown", trapFocus);

      // Remover foco automatico do primeiro elemento do modal
      document.activeElement.blur();
    });
  });

  closeBtn.addEventListener("click", () => {
    modal.close();

    divBlur.forEach((div) => {
      div.classList.toggle("blur-sm");
    });

    divOverFlow.classList.toggle("overflow-hidden");
    overlay.classList.toggle("hidden");

    // Remover evento para focar apenas elementos do modal
    document.removeEventListener("keydown", trapFocus);
  });

  // Função para focar apenas elementos do modal
  function trapFocus(event) {
    if (event.key === "Tab") {
      if (event.shiftKey) {
        if (document.activeElement === firstFocusableElement) {
          lastFocusableElement.focus();
          event.preventDefault();
        }
      } else {
        if (document.activeElement === lastFocusableElement) {
          firstFocusableElement.focus();
          event.preventDefault();
        }
      }
    }
  }
}
document.addEventListener("DOMContentLoaded", Modal);

/* Selecionar avaliação */
function starRating() {
  const stars = document.querySelectorAll(".star-icon");

  stars.forEach((star) => {
    star.addEventListener("click", () => {
      stars.forEach((s) => {
        s.classList.remove("starActive", "firstStar");
      });

      star.classList.add("starActive");
    });
  });
}
document.addEventListener("DOMContentLoaded", starRating);

/* Agent Selection Enhancement */
function initAgentSelection() {
  const agentOptions = document.querySelectorAll('.agent-option');
  
  agentOptions.forEach(option => {
    const radio = option.querySelector('.agent-radio');
    const card = option.querySelector('.agent-card');
    
    // Add click handler to the entire card
    card.addEventListener('click', () => {
      radio.checked = true;
      
      // Remove selected state from all other options
      agentOptions.forEach(otherOption => {
        if (otherOption !== option) {
          otherOption.querySelector('.agent-card').classList.remove('selected');
        }
      });
      
      // Add selected state to current option
      card.classList.add('selected');
    });
    
    // Handle radio change
    radio.addEventListener('change', () => {
      if (radio.checked) {
        agentOptions.forEach(otherOption => {
          if (otherOption !== option) {
            otherOption.querySelector('.agent-card').classList.remove('selected');
          }
        });
        card.classList.add('selected');
      }
    });
  });
}

document.addEventListener("DOMContentLoaded", initAgentSelection);

/* Map Selection Enhancement */
function initMapSelection() {
  const mapOptions = document.querySelectorAll('.map-option');
  
  mapOptions.forEach(option => {
    const radio = option.querySelector('.map-radio');
    const card = option.querySelector('.map-card');
    
    // Add click handler to the entire card
    card.addEventListener('click', () => {
      radio.checked = true;
      
      // Remove selected state from all other options
      mapOptions.forEach(otherOption => {
        if (otherOption !== option) {
          otherOption.querySelector('.map-card').classList.remove('selected');
        }
      });
      
      // Add selected state to current option
      card.classList.add('selected');
    });
    
    // Handle radio change
    radio.addEventListener('change', () => {
      if (radio.checked) {
        mapOptions.forEach(otherOption => {
          if (otherOption !== option) {
            otherOption.querySelector('.map-card').classList.remove('selected');
          }
        });
        card.classList.add('selected');
      }
    });
  });
}

document.addEventListener("DOMContentLoaded", initMapSelection);

function initVideoCovers() {
  const videos = document.querySelectorAll('.video-cover');

  videos.forEach((video) => {
    try {
      video.muted = true;
      video.playsInline = true;

      const seekToStart = () => {
        try { video.currentTime = 0.01; } catch (e) {}
      };

      video.addEventListener('loadedmetadata', seekToStart);
      video.addEventListener('loadeddata', () => { try { video.pause(); } catch (e) {} });
      if (video.readyState >= 1) { seekToStart(); }
    } catch (e) {}
  });
}

// Lazy load para vídeos nas listas (adiar definir o src até entrar na viewport)
function lazyLoadVideos() {
  const lazyVideos = document.querySelectorAll('video.lazy-video[data-src]');
  if (!('IntersectionObserver' in window)) {
    // Fallback: carrega imediatamente
    lazyVideos.forEach(v => { v.src = v.dataset.src; v.removeAttribute('data-src'); v.preload = 'metadata'; });
    initVideoCovers();
    return;
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const v = entry.target;
        try {
          v.src = v.dataset.src;
          v.removeAttribute('data-src');
          v.preload = 'metadata';
        } catch (e) {}
        observer.unobserve(v);
        // Reaplicar comportamento de capa quando carregar
        v.addEventListener('loadedmetadata', () => { try { v.currentTime = 0.01; } catch (e) {} });
        v.addEventListener('loadeddata', () => { try { v.pause(); } catch (e) {} });
      }
    });
  }, { rootMargin: '200px 0px' });

  lazyVideos.forEach(v => observer.observe(v));
}

document.addEventListener('DOMContentLoaded', lazyLoadVideos);