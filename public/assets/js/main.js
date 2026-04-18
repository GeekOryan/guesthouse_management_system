
//  public/assets/js/main.js
//  Custom JavaScript for the guesthouse website


// Navbar scroll effect — adds shadow when scrolling
window.addEventListener('scroll', function () {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});