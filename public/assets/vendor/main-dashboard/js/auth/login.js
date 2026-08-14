(() => {
  const form = document.getElementById("login-form");
  const error = document.getElementById("login-error");
  const email = document.getElementById("login-email");
  const password = document.getElementById("login-password");
  const rememberEmail = document.getElementById("remember-email");
  const passwordToggle = document.querySelector("[data-password-toggle]");
  const rememberedEmailKey = "himsRememberedEmail";
  const apiBaseUrl = window.__APP_CONFIG__?.apiBaseUrl || "";

  try {
    const rememberedEmailValue = localStorage.getItem(rememberedEmailKey);
    if (rememberedEmailValue && email) {
      email.value = rememberedEmailValue;
      if (rememberEmail) rememberEmail.checked = true;
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

  form?.addEventListener("submit", async (event) => {
    event.preventDefault();
    if (!form.checkValidity()) { form.reportValidity(); return; }
    const values = new FormData(form);
    const passwordValue = String(values.get("password") || "");
    if (!passwordValue.trim()) { error.textContent = "Enter a password to continue."; error.hidden = false; return; }
    error.hidden = true;
    error.textContent = "";
    try {
      if (rememberEmail?.checked) localStorage.setItem(rememberedEmailKey, String(values.get("email") || "").trim());
      else localStorage.removeItem(rememberedEmailKey);
    } catch { /* Remember-email is optional and does not affect sign-in. */ }

    try {
      const loginUrl = `${apiBaseUrl}/api/v1/auth/login`;
      const response = await fetch(loginUrl || "/api/v1/auth/login", {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify({
          email: String(values.get("email") || "").trim(),
          password: passwordValue,
        }),
      });

      const payload = await response.json().catch(() => ({}));
      if (!response.ok || !payload?.success) {
        throw new Error(payload?.message || "Unable to sign in to the HIMS backend.");
      }

      const user = payload?.data?.user;
      const role = String(values.get("role") || "").trim();
      const session = {
        authenticated: true,
        token: payload?.data?.token,
        user: {
          id: user?.id,
          name: String(values.get("name") || user?.name || "").trim(),
          email: String(values.get("email") || user?.email || "").trim(),
          role: role || (user?.roles?.[0] || "Patient"),
          roles: user?.roles || [],
        },
      };
      window.HimsSession.create(session.user);
      sessionStorage.setItem("himsMainSessionToken", payload?.data?.token || "");
      form.reset();
      window.location.replace("/dashboard");
    } catch (loginError) {
      error.textContent = loginError.message || "Unable to sign in to the HIMS backend.";
      error.hidden = false;
    }
  });
})();
