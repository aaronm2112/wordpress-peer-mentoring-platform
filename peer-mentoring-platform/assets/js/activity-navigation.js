(function () {
  "use strict";

  document.addEventListener("click", async function (event) {
    const control = event.target.closest("[data-peer-activity-id]");

    if (!control || !window.peerMentoringShowcase) {
      return;
    }

    event.preventDefault();

    const status = document.querySelector("#peer-activity-status");
    const content = document.querySelector("#peer-activity-content");
    status.textContent = window.peerMentoringShowcase.messages.loading;
    control.setAttribute("aria-busy", "true");

    try {
      const response = await fetch(window.peerMentoringShowcase.ajaxUrl, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
        body: new URLSearchParams({
          action: "peer_mentoring_load_activity",
          activity_id: control.dataset.peerActivityId,
          nonce: window.peerMentoringShowcase.nonce,
        }),
      });
      const result = await response.json();

      if (!result.success) {
        throw new Error(result.data && result.data.message);
      }

      if (result.data.requires_full_render) {
        window.location.assign(result.data.full_render_url);
        return;
      }

      content.innerHTML = result.data.content;
      status.textContent = result.data.title;
    } catch (error) {
      status.textContent = error.message || window.peerMentoringShowcase.messages.error;
    } finally {
      control.removeAttribute("aria-busy");
    }
  });
}());