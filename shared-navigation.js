// Shared Navigation JavaScript
function toggleMenu() {
  const navbar = document.querySelector('.navbar');
  const authButtons = document.querySelector('.auth-buttons');
  
  navbar.classList.toggle('active');
  authButtons.classList.toggle('active');
}

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
  const header = document.querySelector('header');
  const navbar = document.querySelector('.navbar');
  const authButtons = document.querySelector('.auth-buttons');
  const menuToggle = document.querySelector('.menu-toggle');
  
  if (!header.contains(event.target) || (!menuToggle.contains(event.target) && event.target !== menuToggle)) {
    navbar.classList.remove('active');
    authButtons.classList.remove('active');
  }
});

// Add scroll effect to header
window.addEventListener('scroll', function() {
  const header = document.querySelector('header');
  if (window.scrollY > 50) {
    header.style.background = '#4c2e82';
    header.style.boxShadow = '0 2px 15px rgba(0, 0, 0, 0.2)';
  } else {
    header.style.background = 'linear-gradient(135deg, #5c3c92, #4c2e82)';
    header.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
  }
});

// Handle dropdown menus on mobile
document.addEventListener('DOMContentLoaded', function() {
  const dropdowns = document.querySelectorAll('.dropdown');
  
  dropdowns.forEach(dropdown => {
    const link = dropdown.querySelector('a');
    const menu = dropdown.querySelector('.dropdown-menu');
    
    if (link && menu) {
      link.addEventListener('click', function(event) {
        if (window.innerWidth <= 768) {
          event.preventDefault();
          menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }
      });
    }
  });
});

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      target.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }
  });
});
