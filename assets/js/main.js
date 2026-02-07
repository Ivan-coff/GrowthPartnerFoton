const navToggle = document.querySelector('.nav__toggle');
const navLinks = document.querySelector('.nav__links');

if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
        const isOpen = navLinks.classList.toggle('is-open');
        navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    navLinks.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            navLinks.classList.remove('is-open');
            navToggle.setAttribute('aria-expanded', 'false');
        });
    });
}

const contactForm = document.getElementById('contactForm');
if (contactForm) {
    contactForm.addEventListener('submit', (event) => {
        const name = contactForm.querySelector('#name');
        const email = contactForm.querySelector('#email');
        const message = contactForm.querySelector('#message');
        let valid = true;

        [name, email, message].forEach((field) => {
            field.classList.remove('is-error');
            if (!field.value.trim()) {
                valid = false;
                field.classList.add('is-error');
            }
        });

        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
            valid = false;
            email.classList.add('is-error');
        }

        if (!valid) {
            event.preventDefault();
            alert('Revisa los campos del formulario.');
        }
    });
}

const revealItems = document.querySelectorAll('.reveal');
if (revealItems.length > 0) {
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

        revealItems.forEach((item) => observer.observe(item));
    } else {
        revealItems.forEach((item) => item.classList.add('is-visible'));
    }
}

const catalogBlocks = document.querySelectorAll('.catalog__block');
catalogBlocks.forEach((block) => {
    const track = block.querySelector('.carousel__track');
    const prev = block.querySelector('[data-carousel-prev]');
    const next = block.querySelector('[data-carousel-next]');

    if (!track || !prev || !next) {
        return;
    }

    const updateControls = () => {
        const maxScroll = track.scrollWidth - track.clientWidth;
        const hasOverflow = track.scrollWidth > track.clientWidth + 2;

        prev.disabled = track.scrollLeft <= 4;
        next.disabled = track.scrollLeft >= maxScroll - 4;

        block.classList.toggle('carousel--active', hasOverflow);
    };

    const scrollByAmount = (direction) => {
        const amount = Math.round(track.clientWidth * 0.85);
        track.scrollBy({ left: direction * amount, behavior: 'smooth' });
    };

    prev.addEventListener('click', () => scrollByAmount(-1));
    next.addEventListener('click', () => scrollByAmount(1));
    track.addEventListener('scroll', () => window.requestAnimationFrame(updateControls));
    window.addEventListener('resize', updateControls);

    updateControls();
});
