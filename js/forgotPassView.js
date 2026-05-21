document.addEventListener("DOMContentLoaded", () => {

  const setupToggle = (toggleId, inputId) => {
    const toggle = document.getElementById(toggleId);
    const input = document.getElementById(inputId);

    if (!toggle || !input) return;

    toggle.addEventListener("click", () => {
      const isHidden = input.type === "password";
      input.type = isHidden ? "text" : "password";

      toggle.classList.toggle("fa-eye");
      toggle.classList.toggle("fa-eye-slash");
    });
  };

  setupToggle("toggleNewPassword", "new_password");
  setupToggle("toggleConfirmPassword", "confirm_password");

});