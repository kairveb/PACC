(() => {
  const form = document.getElementById("login-form");
  const error = document.getElementById("login-error");
  const password = document.getElementById("login-password");
  const passwordToggle = document.querySelector("[data-password-toggle]");

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
    if (!form.checkValidity()) {
      event.preventDefault();
      form.reportValidity();
      return;
    }

    const values = new FormData(form);
    const passwordValue = String(values.get("password") || "");
    if (!passwordValue.trim()) {
      event.preventDefault();
      if (error) {
        error.textContent = "Enter a password to continue.";
        error.hidden = false;
      }
      return;
    }

    if (error) {
      error.hidden = true;
      error.textContent = "";
    }
  });
})();
