<?php
/** @var yii\web\View $this */
/** @var \app\models\Post[] $featuredPosts */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Body Sculpt  Expert Site';

// Register SwiperJS assets (example, you'd create an AssetBundle)
$this->registerCssFile('https://unpkg.com/swiper/swiper-bundle.min.css', ['position' => \yii\web\View::POS_HEAD]);
$this->registerJsFile('https://unpkg.com/swiper/swiper-bundle.min.js', ['position' => \yii\web\View::POS_END]);

// Dummy data if not fetching from DB yet
if (empty($featuredPosts)) {
    $featuredPosts = [
        // Updated vimeo_video_url to be Vimeo page URLs
        (object) ['id' => 1, 'title' => 'Exploring the Mountains', 'description' => 'A journey through peaks and valleys.', 'carousel_image_url' => 'https://i.vimeocdn.com/video/1090090773_1920x1080.jpg', 'vimeo_video_url' => 'https://vimeo.com/1090090773', 'slug' => 'exploring-mountains'],
        (object) ['id' => 2, 'title' => 'The Serene Beach', 'description' => 'Relaxation by the ocean waves.', 'carousel_image_url' => 'https://i.vimeocdn.com/video/859981000_1920x1080.jpg', 'vimeo_video_url' => 'https://vimeo.com/859981000', 'slug' => 'serene-beach'], // Example different video
        (object) ['id' => 3, 'title' => 'Urban Adventures', 'description' => 'Discovering the city life.', 'carousel_image_url' => 'https://i.vimeocdn.com/video/59777392_1920x1080.jpg', 'vimeo_video_url' => 'https://vimeo.com/59777392', 'slug' => 'urban-adventures'], // Example different video
    ];
}
?>

<div class="site-index">
    <iframe id="heroBackgroundVideoIframe" src="" /* Source will be set by JavaScript */ class="hero-video-iframe"
        frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen
        title="Hero Video Background"></iframe>
    <div class="content-overlay">
        <div class="container text-center" id="defaultHeroTextContainer">
            <h1 class="display-4"
                style="color: var(--theme-text-light); margin-bottom: 0.5em; font-weight: bold; text-shadow: 2px 2px 4px rgba(0,0,0,0.7);">
                Welcome to Glassy MedSpa</h1>
            <p class="lead"
                style="color: var(--theme-text-medium); font-size: 1.5em; text-shadow: 1px 1px 3px rgba(0,0,0,0.7);">
                Your journey to rejuvenation starts here.</p>
            <?= Html::a('Book Now', ['/site/contact'], ['class' => 'btn btn-lg mt-3', 'style' => 'background-color: var(--theme-green-highlight); color: var(--theme-brown-dark); border:none; font-weight:bold;']) ?>
        </div>
    </div>

    <div class="carousel-text-overlay" id="carouselTextOverlayContainer" style="display: none;">

        <h1 id="carouselTitle" class="color: green; font-weight: bold;"></h1>
        <h4 id="carouselDescription"></h4>
        <a href="#" id="carouselContactUs" class="btn btn-primary text-end">Contact Us</a>
    </div>

    <!-- Swiper container now directly positioned -->
    <div class="swiper-container hero-carousel">
        <div class="swiper-wrapper">
            <?php foreach ($featuredPosts as $post): ?>
                <div class="swiper-slide" data-title="<?= Html::encode($post->title) ?>"
                    data-description="<?= Html::encode($post->description) ?>"
                    data-video-url="<?= Html::encode($post->vimeo_video_url) ?>"
                    data-post-url="<?= Url::to(['/post/display', 'slug' => $post->slug]) ?>">
                    <img src="<?= Html::encode($post->carousel_image_url) ?>" alt="<?= Html::encode($post->title) ?>"
                        class="carousel-bg-image" />
                </div>
            <?php endforeach; ?>
        </div>
        <!-- Add Pagination -->
        <div class="swiper-pagination"></div>
        <!-- Add Navigation -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
</div>
<div class="body-content">

</div>

<?php
$this->registerJs(<<<JS
document.addEventListener('DOMContentLoaded', function () {
    const heroBgIframe = document.getElementById('heroBackgroundVideoIframe');
    const carouselTitleEl = document.getElementById('carouselTitle');
    const carouselDescriptionEl = document.getElementById('carouselDescription');
    const carouselContactUsEl = document.getElementById('carouselContactUs');
    const defaultHeroTextContainerEl = document.getElementById('defaultHeroTextContainer');
    const carouselTextOverlayContainerEl = document.getElementById('carouselTextOverlayContainer');

    function getVimeoIdFromUrl(url) {
        if (!url || !url.includes('vimeo.com')) return null;
        // Matches vimeo.com/12345678 or vimeo.com/channels/mychannel/12345678 etc.
        const match = url.match(/vimeo\.com\/(?:channels\/[^\/]+\/)?(\d+)/);
        return match ? match[1] : null;
    }

    function updateHeroBackgroundAndText(activeSlide) {
        if (!activeSlide) { // Should not happen with swiper init/slideChange, but good guard
            if (defaultHeroTextContainerEl) defaultHeroTextContainerEl.style.display = 'block';
            if (carouselTextOverlayContainerEl) carouselTextOverlayContainerEl.style.display = 'none';
            return;
        }

        const videoPageUrl = activeSlide.getAttribute('data-video-url');
        const title = activeSlide.getAttribute('data-title');
        const postUrl = activeSlide.getAttribute('data-post-url');

        const description = activeSlide.getAttribute('data-description'); // Get description
        // Update background video
        const vimeoId = getVimeoIdFromUrl(videoPageUrl);

        if (heroBgIframe) {
            if (vimeoId) {
                const playerUrl = `https://player.vimeo.com/video/\${vimeoId}?autoplay=1&loop=1&muted=1&background=1&autopause=0&quality=auto&transparent=0`;
                if (heroBgIframe.src !== playerUrl) {
                    heroBgIframe.src = playerUrl;
                }
                // Video is playing/will play: show carousel text, hide default
                if (defaultHeroTextContainerEl) defaultHeroTextContainerEl.style.display = 'none';
                if (carouselTextOverlayContainerEl) carouselTextOverlayContainerEl.style.display = 'block';

                // Update carousel text overlay
                if (carouselTitleEl) carouselTitleEl.textContent = title;
                if (carouselDescriptionEl) carouselDescriptionEl.textContent = description; // Update description
                if (carouselContactUsEl) carouselContactUsEl.href = postUrl;

            } else {
                // No valid video for this slide: show default text, hide carousel text
                // heroBgIframe.src = ''; // Optional: Clear iframe if no video, or let previous video continue
                console.warn('Invalid or non-Vimeo URL for hero background:', videoPageUrl);
                if (defaultHeroTextContainerEl) defaultHeroTextContainerEl.style.display = 'block';
                if (carouselTextOverlayContainerEl) carouselTextOverlayContainerEl.style.display = 'none';
            }
        }
    }

    var swiper = new Swiper('.hero-carousel', {
        loop: true,
        slidesPerView: 3,
        spaceBetween: 15,
        centeredSlides: true,
        grabCursor: true,
        autoplay: {
            delay: 15000, 
            disableOnInteraction: true,
        },
        pagination: { el: '.swiper-pagination', clickable: true },
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
        on: {
            init: function () {
                updateHeroBackgroundAndText(this.slides[this.activeIndex]);
            },
            slideChangeTransitionEnd: function () {
                updateHeroBackgroundAndText(this.slides[this.activeIndex]);
            },
        },
    });
});
JS, \yii\web\View::POS_END);
?>