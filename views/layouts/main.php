<?php

/** @var \yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Url; // Added for Url::to()

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-png', 'href' => Yii::getAlias('@web/bsllogo.png')]);

$isHomePage = (Yii::$app->controller->id === 'site' && Yii::$app->controller->action->id === 'index');
$backgroundVideoUrl = '';

if ($isHomePage && isset($this->params['featuredPosts'][0]->vimeo_video_url)) {
    // Extract Vimeo ID and construct embed URL for background
    if (preg_match('/vimeo\.com\/(\d+)/', $this->params['featuredPosts'][0]->vimeo_video_url, $matches)) {
        $vimeoId = $matches[1];
        // Ensure background=1, muted=1, autoplay=1, loop=1, and controls=0 for a clean background
        $backgroundVideoUrl = "https://player.vimeo.com/video/{$vimeoId}?autoplay=1&loop=1&muted=1&background=1&controls=0&transparent=0";
    }
}
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">

<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body class="d-flex flex-column h-100">
    <?php $this->beginBody() ?>

    <?php if ($isHomePage && !empty($backgroundVideoUrl)): ?>
        <iframe src="<?= Html::encode($backgroundVideoUrl) ?>" class="hero-video-iframe" frameborder="0"
            allow="autoplay; fullscreen; picture-in-picture" allowfullscreen title="Background Video"></iframe>
    <?php endif; ?>

    <header id="header" class="glassy-header">
        <?php
        NavBar::begin([
            'brandLabel' => Html::img(Yii::getAlias('@web/bsl.png'), ['alt' => Yii::$app->name, 'class' => 'navbar-brand-logo', 'style' => 'height: 60px;']), // Adjust height as needed
            'brandUrl' => Yii::$app->homeUrl,
            'options' => ['class' => 'navbar navbar-expand-md fixed-top']
        ]);
        $menuItems = [
            ['label' => 'Home', 'url' => ['/site/index']],
            ['label' => 'About', 'url' => ['/site/about']],
            ['label' => 'Contact', 'url' => ['/site/contact']],
        ];
        if (Yii::$app->user->isGuest) {
            $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
        } else {
            if (Yii::$app->user->identity->username === 'admin') {
                $menuItems[] = ['label' => 'Posts', 'url' => ['/post/index']];
            }
            $menuItems[] = '<li class="nav-item">'
                . Html::beginForm(['/site/logout'])
                . Html::submitButton(
                    'Logout (' . Yii::$app->user->identity->username . ')',
                    ['class' => 'nav-link btn btn-link logout']
                )
                . Html::endForm()
                . '</li>';
        }
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav ms-auto mb-2 mb-md-0 header-menu'],
            'items' => $menuItems,
        ]);
        NavBar::end();
        ?>
    </header>

    <main id="main" class="flex-shrink-0 main-content-wrapper" role="main">
        <div class="container<?= $isHomePage ? ' container-homepage' : ' container-overlay' ?>">
            <?php if (!empty($this->params['breadcrumbs'])): ?>
                <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
            <?php endif ?>
            <?= Alert::widget() ?>
            <?= $content ?>
        </div>
    </main>

    <footer id="footer" class="mt-auto py-3 glassy-footer">
        <div class="container">
            <div class="row ">
                <div class="col-md-6 text-center text-md-start">&copy; BodySculptExpert <?= date('Y') ?></div>
                <div class="col-md-6 text-center text-md-end"><?= Yii::powered() ?></div>
            </div>
        </div>
    </footer>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>