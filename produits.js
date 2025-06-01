document.getElementById('promoOnly').addEventListener('change', function () {
  const promoOnly = this.checked;
  const products = document.querySelectorAll('.produit-card');

  products.forEach(card => {
    const hasPromo = card.querySelector('.promo-label') !== null;
    if (promoOnly && !hasPromo) {
      card.style.display = 'none';
    } else {
      card.style.display = '';
    }
  });
});
document.addEventListener('DOMContentLoaded', function () {
  const filterButtons = document.querySelectorAll('.filter-btn');
  const toggleMoreBtn = document.getElementById('toggleMoreBtn');
  const moreCategories = document.getElementById('moreCategories');
  const productsContainer = document.getElementById('produitsList'); // Assuming this is where products are listed

  // زر "Voir plus / Voir moins"
  toggleMoreBtn.addEventListener('click', function () {

    // ✱ انقل القائمة لتصبح قبل الزر
    this.parentNode.insertBefore(moreCategories, this);

    // ✱ أظهر أو أخفِ القائمة
    const isHidden = moreCategories.style.display === 'none' || moreCategories.style.display === '';
    moreCategories.style.display = isHidden ? 'flex' : 'none';
    this.textContent = isHidden ? 'Voir moins' : 'Voir plus';
  });
  // Filter products based on category
  filterButtons.forEach(button => {
    button.addEventListener('click', function () {
      // Remove the active class from all buttons
      filterButtons.forEach(btn => btn.classList.remove('active'));

      // Add active class to the clicked button
      this.classList.add('active');

      const category = this.getAttribute('data-category');

      // Get all product cards
      const productCards = Array.from(productsContainer.getElementsByClassName('produit-card'));

      productCards.forEach(card => {
        const productCategory = card.getAttribute('data-category'); // Assuming each card has a data-category attribute

        // Show or hide based on category
        if (category === 'all' || productCategory === category) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });
});
