<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'About';
// $this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        BodySculptExpert - An Aesthetic Beauty Medspa. Located in the heart of 'City of Taj', Agra. We specialize in -
        Aesthetic Medicine, Cosmetic Dentistry and Plastic Surgery
    </p>
    <h4>Know Our Doctors:</h4>
    <div class="row">
        <div class="col-lg-4">
            <img src="<?= Yii::getAlias('@web/anuragcard.webp') ?>" style="width: 300px; height: auto; margin: 20px"
                alt="Anurag card">
            <p> Since Jan 2003</p>
        </div>
        <div class="col-lg-4">
            <img src="<?= Yii::getAlias('@web/rashicard.webp') ?>" style="width: 300px; height: auto; margin: 20px"
                alt="Rashi card">
            <p> Since Nov 2003</p>
        </div>
        <?= Html::a('Contact us', ['contact'], ['class' => 'btn btn-success']) ?>
        <h4 class="mt-5 mb-3"> Opening Hours:</h4>
        <p>Mon - Sat : 8:00 to 14:00 and 17:00 to 20:00</p>
        <p>Sun : 8:00 to 14:00</p>
    </div>
</div>