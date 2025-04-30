function toggleMenu() {
    document.querySelector(".navbar").classList.toggle("active");
}

let index = 0;  // Starting index of images

// Function to change slide
function moveSlide(direction) {
    const slides = document.querySelectorAll('.carousel-image');
    const totalSlides = slides.length;

    // Calculate new index and ensure looping behavior
    index += direction;

    if (index < 0) {
        index = totalSlides - 1;
    } else if (index >= totalSlides) {
        index = 0;
    }

    // Update the slide position
    document.querySelector('.carousel-slide').style.transition = "transform 0.5s ease-in-out";
    document.querySelector('.carousel-slide').style.transform = `translateX(-${index * 100}%)`;
}

// Automatic slideshow (every 2 seconds)
let autoSlide = setInterval(() => moveSlide(1), 8000);

// Reset auto-slide when user interacts
function resetAutoSlide() {
    clearInterval(autoSlide);
    autoSlide = setInterval(() => moveSlide(1), 8000);
}



// ---------------------- Emergency Reports Carousel (With Reverse Loop) ----------------------

// Reports Carousel (Shows 3 at a time & Reverses)
let indexReports = 0;
let forwardReports = true;

//For each screen
function getVisibleSlides() {
    if (window.innerWidth <= 768) {
        return 1; // Small screens
    } else if (window.innerWidth <= 1024) {
        return 2; // Medium screens
    } else {
        return 3; // Large screens:
    }
}

function moveSlideReports(directionManual = null) {
    const carousel = document.querySelector('#reports-carousel .carousel-slide2');
    const slidesReports = document.querySelectorAll('#reports-carousel .report-slide');
    const totalSlidesReports = slidesReports.length;
    const visibleSlides = getVisibleSlides();
    const maxIndex = totalSlidesReports - visibleSlides; // Last full set

    // If manual button click, set forward direction explicitly
    if (directionManual !== null) {
        forwardReports = directionManual === 1;
    }

    if (forwardReports) {
        if (indexReports < maxIndex) {
            indexReports++;
        } else {
            forwardReports = false;
            indexReports--;
        }
    } else {
        if (indexReports > 0) {
            indexReports--;
        } else {
            forwardReports = true;
            indexReports++;
        }
    }

    carousel.style.transition = "transform 0.5s ease-in-out";
    carousel.style.transform = `translateX(-${indexReports * (100 / visibleSlides)}%)`;
}

// Auto-slide every 5 seconds
let reportSlideInterval = setInterval(() => moveSlideReports(), 10000);

// Attach event listeners to buttons
document.querySelector("#reports-carousel .prev-btn").addEventListener("click", () => {
    moveSlideReports(-1);
    resetAutoSlideReports();
});
document.querySelector("#reports-carousel .next-btn").addEventListener("click", () => {
    moveSlideReports(1);
    resetAutoSlideReports();
});

// Reset auto-slide when user interacts
function resetAutoSlideReports() {
    clearInterval(reportSlideInterval);
    reportSlideInterval = setInterval(() => moveSlideReports(), 5000);
}

// Adjust slides dynamically on window resize
window.addEventListener("resize", () => {
    moveSlideReports(0);
});


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