// Demonstrates: AJAX navigation that defers to a server-chosen full-render fallback instead of always patching the DOM.
(function () {
  "use strict";

  document.addEventListener("click", async function (event) {
    const trigger = event.target.closest("[data-peer-activity-id]");

    if (!trigger) {
      return;
    }

    event.preventDefault();

    const status = document.querySelector("#peer-activity-status");
    const content = document.querySelector("#peer-activity-content");
    status.textContent = "Loading activity...";

    try {
      const response = await fetch(window.peerMentoringShowcase.ajaxUrl, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
        body: new URLSearchParams({
          action: "peer_load_activity",
          activity_id: trigger.dataset.peerActivityId,
          nonce: window.peerMentoringShowcase.nonce,
        }),
      });

      const result = await response.json();

      if (!result.success) {
        throw new Error(result.data && result.data.message);
      }

      // The server, not the client, decides when a full page load is required.
      if (result.data.requires_full_render) {
        window.location.assign(result.data.full_render_url);
        return;
      }

      content.innerHTML = result.data.content;
      status.textContent = "";
    } catch (error) {
      status.textContent = error.message || "Unable to load the activity.";
    }
  });
}());
