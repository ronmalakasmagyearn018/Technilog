console.log("viewPass.js loaded");
document.addEventListener("DOMContentLoaded", function () {
  const togglePassword = document.getElementById("togglePassword");
  const password = document.getElementById("password");

  if (togglePassword && password) {
    togglePassword.addEventListener("click", function () {
      const isHidden = password.type === "password";
      password.type = isHidden ? "text" : "password";

      this.classList.toggle("fa-eye");
      this.classList.toggle("fa-eye-slash");
    });
  }

  const toggleConfirm = document.getElementById("toggleConfirmPassword");
  const confirmPassword = document.getElementById("confirm_password");

  if (toggleConfirm && confirmPassword) {
    toggleConfirm.addEventListener("click", function () {
      const isHidden = confirmPassword.type === "password";
      confirmPassword.type = isHidden ? "text" : "password";

      this.classList.toggle("fa-eye");
      this.classList.toggle("fa-eye-slash");
    });
  }
});

