<?php
/** @var yii\web\View $this */
/** @var \app\models\Post[] $featuredPosts */
/** @var \app\models\Service[] $services */

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

        <h1 id="carouselTitle" style="color: var(--theme-green-highlight); font-weight: bold;"></h1>
        <h4 id="carouselDescription"></h4>
        <p id="carouselPrice" style="font-size: 1.2em; color: var(--theme-green-highlight); font-weight: bold; margin-top: 0.5rem; margin-bottom: 1rem; display: none;"></p>
        <a href="#" id="carouselActionLink" class="btn btn-primary text-end">View Details</a>
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

<!-- Service Grid Container -->
<?php if (!empty($services)): ?>
<div class="service-grid-container">
    <div class="service-grid">
        <?php foreach ($services as $service): ?>
            <div class="service-grid-item"
                 data-title="<?= Html::encode($service->title) ?>"
                 data-description="<?= Html::encode($service->description) ?>"
                 data-video-url="<?= Html::encode($service->vimeo_video_url) ?>"
                 data-price="<?= Html::encode(Yii::$app->formatter->asCurrency($service->price)) ?>"
                 data-results-image-url="<?= Html::encode($service->result_image_url) ?>"
                 data-slug="<?= Html::encode($service->slug) ?>"
                 style="background-image: url('<?= Html::encode($service->button_image_url ?? '') ?>');">
                <div class="service-grid-item-title"><?= Html::encode($service->title) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Results Overlay -->
<div class="results-overlay" id="resultsOverlayContainer" style="display: none;">
    <img id="resultsImage" src="" alt="Service Results" />
    <h3 id="resultsTitle"></h3>
</div>

<div class="body-content">

</div>

<?php
$this->registerJs(<<<JS
document.addEventListener('DOMContentLoaded', function () {
    const heroBgIframe = document.getElementById('heroBackgroundVideoIframe');
    const carouselTitleEl = document.getElementById('carouselTitle');
    const carouselDescriptionEl = document.getElementById('carouselDescription');
    const carouselPriceEl = document.getElementById('carouselPrice');
    const carouselActionLinkEl = document.getElementById('carouselActionLink');
    const defaultHeroTextContainerEl = document.getElementById('defaultHeroTextContainer');
    const carouselTextOverlayContainerEl = document.getElementById('carouselTextOverlayContainer');
    const resultsOverlayContainerEl = document.getElementById('resultsOverlayContainer');
    const resultsImageEl = document.getElementById('resultsImage');
    const resultsTitleEl = document.getElementById('resultsTitle');

    let serviceVideoTimeout = null;
    let lastActiveSwiperIndex = 0;

    function getVimeoIdFromUrl(url) {
        if (!url || !url.includes('vimeo.com')) return null;
        // Matches vimeo.com/12345678 or vimeo.com/channels/mychannel/12345678 etc.
        const match = url.match(/vimeo\.com\/(?:channels\/[^\/]+\/)?(\d+)/);
        return match ? match[1] : null;
    }

    function updateHeroBackgroundAndText(activeSlide, isService = false, serviceData = null) {
        clearTimeout(serviceVideoTimeout);
        if (resultsOverlayContainerEl) resultsOverlayContainerEl.style.display = 'none';

        if (!activeSlide && !isService) {
            if (defaultHeroTextContainerEl) defaultHeroTextContainerEl.style.display = 'block';
            if (carouselTextOverlayContainerEl) carouselTextOverlayContainerEl.style.display = 'none';
            return;
        }

        let videoPageUrl, title, description, actionUrl, price, entitySlug;

        if (isService && serviceData) {
            videoPageUrl = serviceData.videoUrl;
            title = serviceData.title;
            description = serviceData.description;
            price = serviceData.price;
            entitySlug = serviceData.slug;
            // Assuming a service display page like /services/slug
            actionUrl = `<?= Url::to(['/service/display', 'slug' => '']) ?>` + entitySlug;

            if (serviceData.resultImageUrl && resultsImageEl && resultsTitleEl && resultsOverlayContainerEl) {
                resultsImageEl.src = serviceData.resultImageUrl;
                resultsTitleEl.textContent = `Results:`;
                resultsOverlayContainerEl.style.display = 'block';
            }
        } else if (activeSlide) {
            videoPageUrl = activeSlide.getAttribute('data-video-url');
            title = activeSlide.getAttribute('data-title');
            description = activeSlide.getAttribute('data-description');
            actionUrl = activeSlide.getAttribute('data-post-url');
            price = null; // Posts don't have a price in this context
        } else {
            if (defaultHeroTextContainerEl) defaultHeroTextContainerEl.style.display = 'block';
            if (carouselTextOverlayContainerEl) carouselTextOverlayContainerEl.style.display = 'none';
            return;
        }

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

                if (carouselTitleEl) carouselTitleEl.textContent = title;
                if (carouselDescriptionEl) carouselDescriptionEl.textContent = description;
                if (carouselPriceEl) {
                    carouselPriceEl.textContent = price ? price : '';
                    carouselPriceEl.style.display = price ? 'block' : 'none';
                }
                if (carouselActionLinkEl) {
                    carouselActionLinkEl.href = actionUrl;
                    carouselActionLinkEl.textContent = isService ? 'Service Details' : 'View Post';
                    carouselActionLinkEl.style.display = actionUrl ? 'inline-block' : 'none';
                }
            } else {
                // No valid video for this slide: show default text, hide carousel text
                // heroBgIframe.src = ''; // Optional: Clear iframe if no video, or let previous video continue
                console.warn('Invalid or non-Vimeo URL for hero background:', videoPageUrl);
                if (defaultHeroTextContainerEl) defaultHeroTextContainerEl.style.display = 'block';
                if (carouselTextOverlayContainerEl) carouselTextOverlayContainerEl.style.display = 'none';
                if (resultsOverlayContainerEl) resultsOverlayContainerEl.style.display = 'none';
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
                lastActiveSwiperIndex = this.activeIndex;
                updateHeroBackgroundAndText(this.slides[this.activeIndex]);
            },
            slideChangeTransitionEnd: function () {
                clearTimeout(serviceVideoTimeout);
                if (resultsOverlayContainerEl) resultsOverlayContainerEl.style.display = 'none';
                lastActiveSwiperIndex = this.activeIndex;
                updateHeroBackgroundAndText(this.slides[this.activeIndex]);
            },
        },
    });

    const serviceGridItems = document.querySelectorAll('.service-grid-item');
    serviceGridItems.forEach(item => {
        item.addEventListener('click', function() {
            clearTimeout(serviceVideoTimeout);

            const serviceData = {
                title: this.dataset.title,
                description: this.dataset.description,
                videoUrl: this.dataset.videoUrl,
                price: this.dataset.price,
                resultImageUrl: this.dataset.resultImageUrl,
                slug: this.dataset.slug
            };

            // lastActiveSwiperIndex is already set by swiper events
            updateHeroBackgroundAndText(null, true, serviceData);

            serviceVideoTimeout = setTimeout(() => {
                if (swiper && swiper.slides[lastActiveSwiperIndex]) {
                    updateHeroBackgroundAndText(swiper.slides[lastActiveSwiperIndex]);
                } else if (swiper && swiper.slides.length > 0) { // Fallback
                    updateHeroBackgroundAndText(swiper.slides[swiper.activeIndex]);
                }
                // updateHeroBackgroundAndText called with a slide will hide resultsOverlay
            }, 15000); // 15 seconds
        });
    });

});
JS, \yii\web\View::POS_END);
?>