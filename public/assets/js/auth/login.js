(() => {
  const form = document.getElementById("login-form");
  const error = document.getElementById("login-error");
  const email = document.getElementById("login-email");
  const password = document.getElementById("login-password");
  const rememberEmail = document.getElementById("remember-email");
  const passwordToggle = document.querySelector("[data-password-toggle]");
  const rememberedEmailKey = "himsRememberedEmail";

  try {
    const rememberedEmail = localStorage.getItem(rememberedEmailKey);
    if (rememberedEmail && email && rememberEmail) {
      email.value = rememberedEmail;
      rememberEmail.checked = true;
    }
  } catch { /* Storage may be unavailable in privacy-restricted contexts. */ }

  passwordToggle?.addEventListener("click", () => {
    if (!password) return;
    const show = password.type === "password";
    password.type = show ? "text" : "password";
    passwordToggle.setAttribute("aria-pressed", String(show));
    passwordToggle.setAttribute("aria-label", show ? "Hide password" : "Show password");
    const icon = passwordToggle.querySelector("i");
    if (icon) icon.className = `ph ${show ? "ph-eye-slash" : "ph-eye"}`;
  });

  form?.addEventListener("submit", (event) => {
    if (!form.checkValidity()) { form.reportValidity(); event.preventDefault(); return; }
    const values = new FormData(form);
    const passwordValue = String(values.get("password") || "");
    if (!passwordValue.trim()) {
      event.preventDefault();
      error.textContent = "Enter a password to continue.";
      error.hidden = false;
      return;
    }

    try {
      if (rememberEmail?.checked) {
        localStorage.setItem(rememberedEmailKey, String(values.get("email") || "").trim());
      } else {
        localStorage.removeItem(rememberedEmailKey);
      }
    } catch {
      /* Remember-email is optional and does not affect sign-in. */
    }

    error.hidden = true;
    error.textContent = "";
  });
})();
