// ../js/Apropos.js

document.addEventListener('DOMContentLoaded', () => {
  const goCompteBtn = document.getElementById('goCompteBtn');
  const goHomeBtn = document.getElementById('goHomeBtn');

  if (goCompteBtn) {
    goCompteBtn.addEventListener('click', () => {
      window.location.href = 'compte.php';
    });
  }

  if (goHomeBtn) {
    goHomeBtn.addEventListener('click', () => {
      window.location.href = 'home0.php';
    });
  }
});
