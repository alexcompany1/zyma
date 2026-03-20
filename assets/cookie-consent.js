(function () {
  var modal = document.getElementById("cookieModal");
  if (!modal) {
    return;
  }
  var forceShow = modal.dataset.forceShow === "1";
  var endpoint = modal.dataset.endpoint || "cookie_consent_action.php";

  var acceptBtn = document.getElementById("cookieAcceptAll");
  var rejectBtn = document.getElementById("cookieRejectAll");
  var customizeBtn = document.getElementById("cookieCustomize");
  var saveCustomBtn = document.getElementById("cookieSaveCustom");
  var customizePanel = document.getElementById("cookieCustomizePanel");
  var analyticsInput = document.getElementById("cookieAnalytics");
  var marketingInput = document.getElementById("cookieMarketing");
  var openPreferenceLinks = document.querySelectorAll(".open-cookie-preferences");
  var messageEl = null;

  function closeModal() {
    modal.hidden = true;
    modal.style.display = "none";
    modal.setAttribute("aria-hidden", "true");
    document.body.classList.remove("cookie-modal-open");
  }

  function openModal() {
    modal.hidden = false;
    modal.style.display = "flex";
    modal.setAttribute("aria-hidden", "false");
    document.body.classList.add("cookie-modal-open");
  }

  function openCustomizationModal() {
    openModal();
    if (customizePanel) {
      customizePanel.hidden = false;
    }
  }

  function setBusy(isBusy) {
    var controls = [acceptBtn, rejectBtn, customizeBtn, saveCustomBtn];
    controls.forEach(function (btn) {
      if (!btn) {
        return;
      }
      btn.disabled = isBusy;
    });
  }

  function ensureMessageEl() {
    if (messageEl) {
      return messageEl;
    }
    messageEl = document.createElement("div");
    messageEl.className = "cookie-feedback";
    messageEl.setAttribute("aria-live", "polite");
    var card = modal.querySelector(".cookie-modal-card");
    if (card) {
      card.insertBefore(messageEl, card.firstChild.nextSibling);
    }
    return messageEl;
  }

  function showMessage(text, isError) {
    var el = ensureMessageEl();
    el.textContent = text;
    el.classList.toggle("is-error", !!isError);
    el.classList.toggle("is-success", !isError);
  }

  function showToast(text) {
    var toast = document.createElement("div");
    toast.className = "toast-notification cookie-toast show";
    toast.innerHTML =
      '<div class="toast-icon">OK</div><span>' +
      String(text || "Preferencias guardadas") +
      "</span>";

    document.body.appendChild(toast);
    setTimeout(function () {
      toast.classList.remove("show");
      setTimeout(function () {
        if (toast && toast.parentNode) {
          toast.parentNode.removeChild(toast);
        }
      }, 320);
    }, 2400);
  }

  function postAction(payload) {
    setBusy(true);

    var body = new URLSearchParams(payload);
    return fetch(endpoint, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
      },
      body: body.toString(),
      credentials: "same-origin",
    })
      .then(function (response) {
        return response.text().then(function (text) {
          var data = null;
          try {
            data = JSON.parse(text);
          } catch (err) {
            throw new Error("Respuesta inválida del servidor");
          }

          if (!response.ok) {
            throw new Error((data && data.message) || "No se pudo completar la solicitud");
          }

          return data;
        });
      })
      .finally(function () {
        setBusy(false);
      });
  }

  if (forceShow) {
    openModal();
  } else {
    closeModal();
  }

  if (openPreferenceLinks && openPreferenceLinks.length > 0) {
    openPreferenceLinks.forEach(function (link) {
      link.addEventListener("click", function (e) {
        e.preventDefault();
        openCustomizationModal();
      });
    });
  }

  if (acceptBtn) {
    acceptBtn.addEventListener("click", function () {
      postAction({ action: "accept_all" })
        .then(function (result) {
          if (!result || !result.ok) {
            throw new Error((result && result.message) || "No se pudo guardar");
          }
          closeModal();
          showToast(result.message || "Preferencias guardadas correctamente");
        })
        .catch(function (err) {
          showMessage(err.message || "No se pudo guardar tu preferencia de cookies.", true);
        });
    });
  }

  if (rejectBtn) {
    rejectBtn.addEventListener("click", function () {
      postAction({ action: "reject_all" })
        .then(function (result) {
          if (!result || !result.ok) {
            throw new Error((result && result.message) || "No se pudo guardar");
          }
          var redirect = result.redirect || "login.php?cookie_rejected=1";
          window.location.href = redirect;
        })
        .catch(function (err) {
          showMessage(err.message || "No se pudo procesar el rechazo de cookies.", true);
        });
    });
  }

  if (customizeBtn && customizePanel) {
    customizeBtn.addEventListener("click", function () {
      customizePanel.hidden = !customizePanel.hidden;
    });
  }

  if (saveCustomBtn) {
    saveCustomBtn.addEventListener("click", function () {
      postAction({
        action: "save_custom",
        analytics: analyticsInput && analyticsInput.checked ? "1" : "0",
        marketing: marketingInput && marketingInput.checked ? "1" : "0",
      })
        .then(function (result) {
          if (!result || !result.ok) {
            throw new Error((result && result.message) || "No se pudo guardar");
          }
          closeModal();
          showToast(result.message || "Personalización guardada correctamente");
        })
        .catch(function (err) {
          showMessage(err.message || "No se pudo guardar tu configuración de cookies.", true);
        });
    });
  }
})();
