// Smooth scrolling
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

        // Header background on scroll
        const header = document.getElementById('header');
        if (header) {
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });
        }

        // Mobile menu toggle
        const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
        const navMenu = document.querySelector('.nav-menu');

        function closeMobileMenu() {
            if (!navMenu || !mobileMenuToggle) {
                return;
            }

            navMenu.classList.remove('is-open');
            mobileMenuToggle.setAttribute('aria-expanded', 'false');
        }

        if (mobileMenuToggle && navMenu) {
            mobileMenuToggle.addEventListener('click', function() {
                const isOpen = navMenu.classList.toggle('is-open');
                mobileMenuToggle.setAttribute('aria-expanded', String(isOpen));
            });

            navMenu.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 1024) {
                        closeMobileMenu();
                    }
                });
            });

            window.addEventListener('resize', function() {
                if (window.innerWidth > 1024) {
                    closeMobileMenu();
                }
            });
        }

        // Intersection Observer for animations on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all section headers and cards
        document.querySelectorAll('.section-header, .model-card, .service-card, .financing-card').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'all 0.6s ease';
            observer.observe(el);
        });

        // Model gallery modal
        const galleryModal = document.getElementById('gallery-modal');
        const galleryTitle = document.getElementById('gallery-title');
        const galleryGrid = document.getElementById('gallery-grid');
        const galleryClose = document.getElementById('gallery-close');
        const imageViewer = document.getElementById('image-viewer');
        const imageViewerImg = document.getElementById('image-viewer-img');
        const imageViewerClose = document.getElementById('image-viewer-close');
        const imageViewerPrev = document.getElementById('image-viewer-prev');
        const imageViewerNext = document.getElementById('image-viewer-next');
        let currentGalleryImages = [];
        let currentImageIndex = 0;

        function showCurrentImage() {
            if (!imageViewerImg || currentGalleryImages.length === 0) {
                return;
            }

            imageViewerImg.src = currentGalleryImages[currentImageIndex];
            imageViewerImg.alt = `Imagen ampliada (${currentImageIndex + 1}/${currentGalleryImages.length})`;
        }

        function navigateImage(step) {
            if (currentGalleryImages.length <= 1) {
                return;
            }

            currentImageIndex = (currentImageIndex + step + currentGalleryImages.length) % currentGalleryImages.length;
            showCurrentImage();
        }

        function closeImageViewer() {
            if (!imageViewer || !imageViewerImg) {
                return;
            }

            imageViewer.classList.remove('is-open');
            imageViewer.setAttribute('aria-hidden', 'true');
            imageViewerImg.src = '';
            imageViewerImg.alt = 'Imagen ampliada';
            currentGalleryImages = [];
            currentImageIndex = 0;
        }

        function openImageViewer(images, index) {
            if (!imageViewer || !imageViewerImg) {
                return;
            }

            currentGalleryImages = images.slice(0, 3);
            currentImageIndex = Math.max(0, Math.min(index, currentGalleryImages.length - 1));
            showCurrentImage();
            imageViewer.classList.add('is-open');
            imageViewer.setAttribute('aria-hidden', 'false');
        }

        function closeGalleryModal() {
            if (!galleryModal) {
                return;
            }

            closeImageViewer();
            galleryModal.classList.remove('is-open');
            galleryModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            if (galleryGrid) {
                galleryGrid.innerHTML = '';
            }
        }

        function openGalleryModal(modelName, images) {
            if (!galleryModal || !galleryTitle || !galleryGrid) {
                return;
            }

            galleryTitle.textContent = `${modelName} · GALERÍA`;
            galleryGrid.innerHTML = '';

            const modelImages = images.slice(0, 3);

            modelImages.forEach((imageSrc, index) => {
                const image = document.createElement('img');
                image.src = imageSrc;
                image.alt = `${modelName} - Foto`;
                image.loading = 'lazy';
                image.addEventListener('click', function() {
                    openImageViewer(modelImages, index);
                });
                galleryGrid.appendChild(image);
            });

            galleryModal.classList.add('is-open');
            galleryModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        document.querySelectorAll('.btn-gallery').forEach(button => {
            button.addEventListener('click', function() {
                const modelName = this.dataset.model || 'MODELO';
                const rawImages = this.dataset.images || '';
                const images = rawImages
                    .split(',')
                    .map(item => item.trim())
                    .filter(Boolean);

                if (images.length === 0) {
                    return;
                }

                openGalleryModal(modelName, images);
            });
        });

        if (galleryClose) {
            galleryClose.addEventListener('click', closeGalleryModal);
        }

        if (galleryModal) {
            galleryModal.addEventListener('click', function(event) {
                if (event.target === galleryModal) {
                    closeGalleryModal();
                }
            });
        }

        if (imageViewerClose) {
            imageViewerClose.addEventListener('click', closeImageViewer);
        }

        if (imageViewerPrev) {
            imageViewerPrev.addEventListener('click', function(event) {
                event.stopPropagation();
                navigateImage(-1);
            });
        }

        if (imageViewerNext) {
            imageViewerNext.addEventListener('click', function(event) {
                event.stopPropagation();
                navigateImage(1);
            });
        }

        if (imageViewer) {
            imageViewer.addEventListener('click', function(event) {
                if (event.target === imageViewer) {
                    closeImageViewer();
                }
            });
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                if (imageViewer && imageViewer.classList.contains('is-open')) {
                    closeImageViewer();
                    return;
                }

                if (galleryModal && galleryModal.classList.contains('is-open')) {
                    closeGalleryModal();
                }
            }

            if (imageViewer && imageViewer.classList.contains('is-open')) {
                if (event.key === 'ArrowLeft') {
                    navigateImage(-1);
                } else if (event.key === 'ArrowRight') {
                    navigateImage(1);
                }
            }
        });
