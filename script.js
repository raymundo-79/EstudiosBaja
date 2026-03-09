/*
// Funciones para el funcionamiento del menu en todas las paginas.
const menuLinks = document.querySelectorAll('.menu-link');
menuLinks.forEach(link => {
  link.addEventListener('click', (e) => {
    e.preventDefault();
    const pageId = e.target.getAttribute('data-page');
    const pages = document.querySelectorAll('.page');
    pages.forEach(page => page.classList.remove('active'));
    const activePage = document.getElementById(pageId);
    if (activePage) activePage.classList.add('active');
  });
});
*/

function showTab(index) {
  const buttons = document.querySelectorAll('.tab-button');
  const panels = document.querySelectorAll('.tab-panel');

  buttons.forEach((btn, btnIndex) => {
    const isActive = btnIndex === index;
    btn.classList.toggle('active', isActive);
    btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
    btn.setAttribute('tabindex', isActive ? '0' : '-1');
  });

  panels.forEach((panel, panelIndex) => {
    const isActive = panelIndex === index;
    panel.classList.toggle('active', isActive);
    panel.hidden = !isActive;
  });
}

function setTooltipVisibility(image, tooltip, isVisible) {
  if (!tooltip || !image) return;
  tooltip.style.display = isVisible ? 'block' : 'none';
  tooltip.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
  image.setAttribute('aria-expanded', isVisible ? 'true' : 'false');
}

function closeAllTooltips(exceptTooltip = null) {
  document.querySelectorAll('.tooltip-box').forEach(tooltip => {
    if (tooltip !== exceptTooltip) {
      const container = tooltip.parentElement;
      const image = container ? container.querySelector('.tooltip-image, .tooltip-image-d') : null;
      setTooltipVisibility(image, tooltip, false);
    }
  });
}

function toggleTooltip(imgElement) {
  const container = imgElement.parentElement;
  if (!container) return;

  const tooltip = container.querySelector('.tooltip-box');
  if (!tooltip) return;

  const isCurrentlyVisible = tooltip.style.display === 'block';
  closeAllTooltips(tooltip);
  setTooltipVisibility(imgElement, tooltip, !isCurrentlyVisible);
}

function initializeTabs() {
  const buttons = document.querySelectorAll('.tab-button');
  const panels = document.querySelectorAll('.tab-panel');
  if (!buttons.length || !panels.length) return;

  buttons.forEach((button, index) => {
    button.setAttribute('role', 'tab');
    button.id = button.id || `tab-${index}`;
    button.setAttribute('aria-controls', `tab-panel-${index}`);

    if (panels[index]) {
      panels[index].id = `tab-panel-${index}`;
      panels[index].setAttribute('role', 'tabpanel');
      panels[index].setAttribute('aria-labelledby', button.id);
    }

    button.addEventListener('click', () => showTab(index));
    button.addEventListener('keydown', event => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        showTab(index);
      }
    });
  });

  const activeIndex = Array.from(buttons).findIndex(btn => btn.classList.contains('active'));
  showTab(activeIndex >= 0 ? activeIndex : 0);
}

function initializeTooltips() {
  const images = document.querySelectorAll('.tooltip-image, .tooltip-image-d');

  images.forEach((image, index) => {
    const container = image.parentElement;
    const tooltip = container ? container.querySelector('.tooltip-box') : null;
    if (!tooltip) return;

    image.setAttribute('tabindex', '0');
    image.setAttribute('role', 'button');
    image.setAttribute('aria-haspopup', 'true');
    image.setAttribute('aria-expanded', 'false');

    const tooltipId = tooltip.id || `tooltip-${index}`;
    tooltip.id = tooltipId;
    tooltip.setAttribute('role', 'tooltip');
    tooltip.setAttribute('aria-hidden', 'true');
    image.setAttribute('aria-describedby', tooltipId);

    if (/^(Image|Diagram)\s+\d+\s+tooltip$/i.test(tooltip.textContent.trim())) {
      const label = image.getAttribute('alt') || `Imagen ${index + 1}`;
      tooltip.textContent = `${label}: fotografía de referencia de Baja Studios.`;
    }

    image.addEventListener('click', event => {
      event.stopPropagation();
      toggleTooltip(image);
    });

    image.addEventListener('keydown', event => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        toggleTooltip(image);
      }
    });
  });
}

// Cerrar tooltip si se hace clic fuera
document.addEventListener('click', function (e) {
  if (!e.target.classList.contains('tooltip-image') && !e.target.classList.contains('tooltip-image-d')) {
    closeAllTooltips();
  }
});

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    closeAllTooltips();
  }
});

// Funciones para el mensaje de enviado
document.addEventListener('DOMContentLoaded', function () {
  initializeTabs();
  initializeTooltips();

  const form = document.getElementById('formulario-contacto');

  if (form) {
    const formTimestamp = document.getElementById('form_ts');
    if (formTimestamp) {
      formTimestamp.value = String(Math.floor(Date.now() / 1000));
    }
    form.addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(form);

      fetch('guardar.php', {
        method: 'POST',
        body: formData
      })
        .then(response => response.text())
        .then(data => {
          alert(data);
          form.reset();
          const responseContainer = document.getElementById('respuesta');
          if (responseContainer) {
            responseContainer.textContent = data;
          }
        })
        .catch(error => {
          console.error('Error en el envío:', error);
          alert('Hubo un problema al enviar el formulario.');
        });
    });
  }
});

// === Doble clic para ampliar imagen ===
document.addEventListener('DOMContentLoaded', () => {
  const imagenes = document.querySelectorAll('.tooltip-image-d');
  const overlay = document.getElementById('visor-imagen');
  const imagenAmpliada = document.getElementById('imagen-ampliada');
  const btnCerrar = document.querySelector('.cerrar');

  if (!overlay || !imagenAmpliada || !btnCerrar) {
    return;
  }

  let scale = 1;
  let lastDistance = 0;

  imagenes.forEach(img => {
    img.addEventListener('dblclick', () => {
      imagenAmpliada.src = img.src;
      overlay.style.display = 'flex';
      document.body.style.overflow = 'hidden';
      resetZoom();
    });
  });

  const cerrarVisor = () => {
    overlay.style.display = 'none';
    document.body.style.overflow = 'auto';
    resetZoom();
  };

  btnCerrar.addEventListener('click', cerrarVisor);
  overlay.addEventListener('click', e => {
    if (e.target === overlay) cerrarVisor();
  });
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') cerrarVisor();
  });

  overlay.addEventListener('wheel', e => {
    e.preventDefault();
    if (e.deltaY < 0) scale *= 1.1;
    else scale /= 1.1;
    applyZoom();
  });

  overlay.addEventListener('touchmove', e => {
    if (e.touches.length === 2) {
      e.preventDefault();
      const dx = e.touches[0].clientX - e.touches[1].clientX;
      const dy = e.touches[0].clientY - e.touches[1].clientY;
      const distance = Math.sqrt(dx * dx + dy * dy);
      if (lastDistance) {
        const delta = distance / lastDistance;
        scale *= delta;
        scale = Math.min(Math.max(0.5, scale), 5);
        applyZoom();
      }
      lastDistance = distance;
    }
  });

  overlay.addEventListener('touchend', e => {
    if (e.touches.length < 2) lastDistance = 0;
  });

  function applyZoom() {
    imagenAmpliada.style.transform = `scale(${scale})`;
  }

  function resetZoom() {
    scale = 1;
    lastDistance = 0;
    imagenAmpliada.style.transform = 'scale(1)';
  }
});

/*
// === Menú hamburguesa compatible con iPhone ===
document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.getElementById('menu-toggle');
  const nav = document.querySelector('nav ul');

  const toggleMenu = () => {
    nav.classList.toggle('show');
    toggle.textContent = nav.classList.contains('show') ? '✖' : '☰';
  };

  toggle.addEventListener('click', toggleMenu);
  toggle.addEventListener('touchstart', e => {
    e.preventDefault();
    toggleMenu();
  });
});
*/
