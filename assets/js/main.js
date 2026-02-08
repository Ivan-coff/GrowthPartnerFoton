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

        const fields = [name, email, message];

        fields.forEach((field) => {
            field.classList.remove('is-invalid');
        });

        fields.forEach((field) => {
            if (!field.value.trim()) {
                valid = false;
                field.classList.add('is-invalid');
            }
        });

        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
            valid = false;
            email.classList.add('is-invalid');
        }

        if (!valid) {
            event.preventDefault();
        }
    });

    contactForm.querySelectorAll('input, textarea').forEach((field) => {
        field.addEventListener('input', () => {
            field.classList.remove('is-invalid');
        });
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

const toast = document.querySelector('[data-toast]');
if (toast) {
    window.setTimeout(() => {
        toast.classList.add('is-hiding');
    }, 1800);

    window.setTimeout(() => {
        toast.remove();
    }, 2400);
}

const lightbox = document.getElementById('lightbox');
const galleryItems = document.querySelectorAll('[data-gallery]');

if (lightbox && galleryItems.length > 0) {
    const imageEl = lightbox.querySelector('.lightbox__image');
    const prevBtn = lightbox.querySelector('[data-lightbox-prev]');
    const nextBtn = lightbox.querySelector('[data-lightbox-next]');
    const closeBtns = lightbox.querySelectorAll('[data-lightbox-close]');
    const countEl = lightbox.querySelector('[data-lightbox-count]');

    let images = [];
    let index = 0;
    let altText = '';

    const update = () => {
        if (!images.length) {
            return;
        }

        imageEl.src = images[index];
        imageEl.alt = altText;

        if (countEl) {
            countEl.textContent = `${index + 1} / ${images.length}`;
        }

        const disabled = images.length <= 1;
        prevBtn.disabled = disabled;
        nextBtn.disabled = disabled;
    };

    const openLightbox = (list, startIndex, alt) => {
        images = list;
        index = startIndex;
        altText = alt;
        update();
        lightbox.classList.add('is-open');
        lightbox.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    const closeLightbox = () => {
        lightbox.classList.remove('is-open');
        lightbox.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    const move = (dir) => {
        if (!images.length) {
            return;
        }
        index = (index + dir + images.length) % images.length;
        update();
    };

    galleryItems.forEach((item) => {
        const imagesAttr = item.getAttribute('data-gallery') || '';
        const list = imagesAttr.split('|').filter(Boolean);
        if (list.length === 0) {
            return;
        }

        const img = item.querySelector('img');
        const currentSrc = img ? img.getAttribute('src') : '';
        const startIndex = Math.max(list.indexOf(currentSrc), 0);
        const alt = img ? img.getAttribute('alt') || '' : '';

        const open = () => openLightbox(list, startIndex, alt);

        item.addEventListener('click', open);
        item.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                open();
            }
        });
    });

    prevBtn.addEventListener('click', () => move(-1));
    nextBtn.addEventListener('click', () => move(1));

    closeBtns.forEach((btn) => btn.addEventListener('click', closeLightbox));

    document.addEventListener('keydown', (event) => {
        if (!lightbox.classList.contains('is-open')) {
            return;
        }

        if (event.key === 'Escape') {
            closeLightbox();
        }

        if (event.key === 'ArrowLeft') {
            move(-1);
        }

        if (event.key === 'ArrowRight') {
            move(1);
        }
    });
}
