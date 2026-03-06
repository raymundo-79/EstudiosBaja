/*
// Funciones para el funcionamiento del menu en todas las paginas.
const menuLinks = document.querySelectorAll('.menu-link');	// Obtener todos los enlaces de menú
// Agregar el evento de clic a cada enlace
menuLinks.forEach(link => {
  link.addEventListener('click', (e) => {
    e.preventDefault(); // Prevenir el comportamiento por defecto (no recargar la página)

    // Obtener el nombre de la página a mostrar
    const pageId = e.target.getAttribute('data-page');

    // Ocultar todas las secciones
    const pages = document.querySelectorAll('.page');
    pages.forEach(page => {
      page.classList.remove('active');
    });

    // Mostrar la sección correspondiente
    const activePage = document.getElementById(pageId);
    if (activePage) {
      activePage.classList.add('active');
    }
  });
});
*/
// Funciones para el funcionamiento de los paneles en la pagina facilities.html.
function showTab(index) {
  const buttons = document.querySelectorAll('.tab-button');
  const panels = document.querySelectorAll('.tab-panel');

  // Remueve la clase 'active' de todos los botones y paneles
  buttons.forEach(btn => btn.classList.remove('active'));
  panels.forEach(panel => panel.classList.remove('active'));

  // Agrega 'active' al botón y panel seleccionados
  buttons[index].classList.add('active');
  panels[index].classList.add('active');
}

// Funciones para el funcionamiento de los tooltip en la pagina facilities.html y production.html.
function toggleTooltip(imgElement) {
  const container = imgElement.parentElement;
  const tooltip = container.querySelector('.tooltip-box');

  // Cierra cualquier otro tooltip abierto
  document.querySelectorAll('.tooltip-box').forEach(t => {
    if (t !== tooltip) t.style.display = 'none';
  });

  // Alternar visibilidad del tooltip clicado
  tooltip.style.display = (tooltip.style.display === 'block') ? 'none' : 'block';
}

// Opcional: cerrar tooltip si se hace clic fuera
document.addEventListener('click', function (e) {
  if (!e.target.classList.contains('tooltip-image')) {
    document.querySelectorAll('.tooltip-box').forEach(t => {
      t.style.display = 'none';
    });
  }
});

// Funciones para el mensaje de enviado
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("formulario-contacto");

  if (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault(); // Evita que se recargue la página

      const formData = new FormData(form);

      fetch("guardar.php", {
        method: "POST",
        body: formData
      })
      .then(response => response.text()) // tu PHP devuelve texto
      .then(data => {
        alert(data);           // Mostrar el resultado en alerta
        form.reset();          // Limpiar el formulario
        document.getElementById("respuesta").textContent = data; // Opcional: mostrar también en pantalla
      })
      .catch(error => {
        console.error("Error en el envío:", error);
        alert("Hubo un problema al enviar el formulario.");
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

  let scale = 1;
  let lastDistance = 0;

  // === Mostrar imagen ===
  imagenes.forEach(img => {
    img.addEventListener('dblclick', () => {
      imagenAmpliada.src = img.src;
      overlay.style.display = 'flex';
      document.body.style.overflow = 'hidden';
      resetZoom();
    });
  });

  // === Cerrar ===
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

  // === Zoom con rueda del mouse ===
  overlay.addEventListener('wheel', e => {
    e.preventDefault();
    if (e.deltaY < 0) scale *= 1.1;
    else scale /= 1.1;
    applyZoom();
  });

  // === Zoom con dos dedos (pinch) ===
  overlay.addEventListener('touchmove', e => {
    if (e.touches.length === 2) {
      e.preventDefault();
      const dx = e.touches[0].clientX - e.touches[1].clientX;
      const dy = e.touches[0].clientY - e.touches[1].clientY;
      const distance = Math.sqrt(dx * dx + dy * dy);
      if (lastDistance) {
        const delta = distance / lastDistance;
        scale *= delta;
        scale = Math.min(Math.max(0.5, scale), 5); // límites
        applyZoom();
      }
      lastDistance = distance;
    }
  });

  overlay.addEventListener('touchend', e => {
    if (e.touches.length < 2) lastDistance = 0;
  });

  // === Funciones auxiliares ===
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
    // Cambia el icono ☰ ↔ ✖
    toggle.textContent = nav.classList.contains('show') ? '✖' : '☰';
  };

  // Para clicks y toques táctiles (Safari, Android, etc.)
  toggle.addEventListener('click', toggleMenu);
  toggle.addEventListener('touchstart', e => {
    e.preventDefault();
    toggleMenu();
  });
});

*/