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
})();
