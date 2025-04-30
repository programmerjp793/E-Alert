function toggleMenu() {
    document.querySelector(".navbar").classList.toggle("active");
}

document.addEventListener("DOMContentLoaded", function() {
    document.body.classList.add("loaded");
});


// ---------------------- 1 Section at a time Scroll and UI ----------------------


// Scroll-To-Top Button
function toggleScrollToTopButton() {
    const button = document.getElementById('scrollToTopBtn');
    const scrollPosition = window.scrollY + window.innerHeight;
    const documentHeight = document.documentElement.scrollHeight;

    button.style.display = (scrollPosition >= documentHeight - 10) ? 'block' : 'none';
}

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

window.addEventListener('scroll', toggleScrollToTopButton);
toggleScrollToTopButton();
