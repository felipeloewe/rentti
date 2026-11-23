(() => {
  document.documentElement.classList.add("js-ready");

  const modes = document.querySelectorAll("[data-search-mode]");
  const modeInput = document.querySelector("[data-mode-input]");
  const form = document.querySelector("[data-search-form]");

  modes.forEach((btn) => {
    btn.addEventListener("click", () => {
      modes.forEach((b) => {
        b.classList.toggle("is-active", b === btn);
        b.setAttribute("aria-pressed", b === btn ? "true" : "false");
      });
      if (modeInput) modeInput.value = btn.dataset.searchMode || "arriendo";
    });
  });

  if (form) {
    form.addEventListener("submit", (event) => {
      event.preventDefault();
      const data = new FormData(form);
      const mode = data.get("mode") || "arriendo";
      const comuna = String(data.get("comuna") || "").trim();
      const tipo = String(data.get("tipo") || "").trim();
      const results = document.querySelector("#resultados");
      if (results) {
        const label = mode === "compra" ? "Compra" : "Arriendo";
        const bits = [label, tipo, comuna].filter(Boolean).join(" · ");
        results.hidden = false;
        results.textContent = bits
          ? `Mostrando muestra para ${bits}. (Catálogo real próximamente.)`
          : "Mostrando muestra de propiedades. (Catálogo real próximamente.)";
        results.scrollIntoView({ behavior: "smooth", block: "start" });
      }
    });
  }

  const hero = document.querySelector("[data-hero-banner]");
  const slides = hero ? [...hero.querySelectorAll("[data-hero-slide]")] : [];
  const dotsRoot = hero?.querySelector("[data-hero-dots]");
  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  const INTERVAL_MS = 6000;

  if (hero && slides.length > 1 && dotsRoot) {
    let index = Math.max(
      0,
      slides.findIndex((slide) => slide.classList.contains("is-active"))
    );
    let timer = 0;
    let paused = false;

    const dots = slides.map((_, i) => {
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className = "hero__dot";
      btn.setAttribute("role", "tab");
      btn.setAttribute("aria-label", `Foto ${i + 1} de ${slides.length}`);
      btn.setAttribute("aria-selected", i === index ? "true" : "false");
      btn.addEventListener("click", () => goTo(i, true));
      dotsRoot.appendChild(btn);
      return btn;
    });

    const sync = () => {
      slides.forEach((slide, i) => {
        const active = i === index;
        slide.classList.toggle("is-active", active);
        if (active) {
          // Restart Ken Burns on the newly active slide when motion is allowed.
          slide.style.animation = "none";
          // Force reflow so the animation can restart.
          void slide.offsetWidth;
          slide.style.animation = "";
        }
      });
      dots.forEach((dot, i) => {
        dot.setAttribute("aria-selected", i === index ? "true" : "false");
      });
    };

    const goTo = (next, userDriven = false) => {
      index = ((next % slides.length) + slides.length) % slides.length;
      sync();
      if (userDriven) restart();
    };

    const tick = () => {
      if (paused || document.hidden) return;
      goTo(index + 1);
    };

    const stop = () => {
      if (timer) {
        window.clearInterval(timer);
        timer = 0;
      }
    };

    const start = () => {
      if (reduceMotion || paused) return;
      stop();
      timer = window.setInterval(tick, INTERVAL_MS);
    };

    const restart = () => {
      stop();
      start();
    };

    const setPaused = (value) => {
      paused = value;
      if (paused) stop();
      else start();
    };

    hero.addEventListener("mouseenter", () => setPaused(true));
    hero.addEventListener("mouseleave", () => setPaused(false));
    hero.addEventListener("focusin", () => setPaused(true));
    hero.addEventListener("focusout", (event) => {
      if (!hero.contains(event.relatedTarget)) setPaused(false);
    });
    document.addEventListener("visibilitychange", () => {
      if (document.hidden) stop();
      else start();
    });

    sync();
    start();
  }
})();
