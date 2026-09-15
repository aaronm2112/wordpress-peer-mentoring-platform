(function () {
  "use strict";

  document.addEventListener("submit", function (event) {
    const form = event.target.closest(".peer-reflection-form");

    if (!form) {
      return;
    }

    const field = form.querySelector("#peer-reflection-response");
    const summary = form.querySelector(".peer-form-summary");
    const error = form.querySelector("#peer-reflection-error");
    const value = field.value.trim();

    summary.textContent = "";
    error.textContent = "";
    error.hidden = true;
    field.removeAttribute("aria-invalid");

    if (value) {
      return;
    }

    event.preventDefault();
    const message = "Enter a reflection before saving.";
    field.setAttribute("aria-invalid", "true");
    error.textContent = message;
    error.hidden = false;
    summary.textContent = message;
    summary.focus();
  });
}());