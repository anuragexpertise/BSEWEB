document.addEventListener('DOMContentLoaded', function () {
    if (document.querySelector('.hero-carousel')) {
        const carouselVideoPlayer = document.getElementById('carouselVideoPlayer');
        const carouselTitleEl = document.getElementById('carouselTitle');
        const carouselSubtitleEl = document.getElementById('carouselSubtitle');
        const carouselReadMoreEl = document.getElementById('carouselReadMore');

        const heroSwiper = new Swiper('.hero-carousel', {
            loop: true,
            autoplay: {
                delay: 7000, // Matches 7-second video, or a bit longer
                disableOnInteraction: true,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            on: {
                slideChange: function () {
                    updateCarouselContent(this.slides[this.activeIndex]);
                },
                init: function () {
                    // Initial content update
                    if (this.slides.length > 0) {
                        updateCarouselContent(this.slides[this.activeIndex]);
                    }
                }
            },
        });

        function updateCarouselContent(activeSlide) {
            if (!activeSlide) return;

            const videoUrl = activeSlide.getAttribute('data-video-url');
            const title = activeSlide.getAttribute('data-title');
            const subtitle = activeSlide.getAttribute('data-subtitle');
            const postUrl = activeSlide.getAttribute('data-post-url');

            if (carouselVideoPlayer && videoUrl) {
                carouselVideoPlayer.src = videoUrl;
                carouselVideoPlayer.load(); // Important for changing source
                carouselVideoPlayer.play().catch(error => console.log("Autoplay prevented:", error));
            }

            if (carouselTitleEl) carouselTitleEl.textContent = title || '';
            if (carouselSubtitleEl) carouselSubtitleEl.textContent = subtitle || '';
            if (carouselReadMoreEl && postUrl) {
                carouselReadMoreEl.href = postUrl;
            } else if (carouselReadMoreEl) {
                carouselReadMoreEl.href = '#'; // Fallback
            }
        }
    }
});
