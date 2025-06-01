// Get DOM elements
const modal = document.getElementById("loginModal");
const openModalBtn = document.getElementById("open-modal");
const closeModalBtn = document.querySelector(".close");
const loginTab = document.getElementById("tab-login");
const registerTab = document.getElementById("tab-register");
const loginForm = document.getElementById("loginForm");
const registerForm = document.getElementById("registerForm");

// Open modal
openModalBtn?.addEventListener("click", function (e) {
  e.preventDefault();
  modal.style.display = "block";
});

// Close modal
closeModalBtn?.addEventListener("click", function () {
  modal.style.display = "none";
});

// Close when clicking outside the modal
window.addEventListener("click", function (e) {
  if (e.target == modal) {
    modal.style.display = "none";
  }
});

// Tab switching logic
loginTab?.addEventListener("click", function () {
  loginTab.classList.add("active");
  registerTab.classList.remove("active");
  loginForm.classList.add("active");
  registerForm.classList.remove("active");
});

registerTab?.addEventListener("click", function () {
  registerTab.classList.add("active");
  loginTab.classList.remove("active");
  registerForm.classList.add("active");
  loginForm.classList.remove("active");
});
// MENU BURGER RESPONSIVE
const menuToggle = document.getElementById("menu-toggle");
const navLinks = document.getElementById("nav-links");

menuToggle.addEventListener("click", () => {
  navLinks.classList.toggle("active");
});

