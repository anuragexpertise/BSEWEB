<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\PostSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="post-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'title') ?>

    <?= $form->field($model, 'subtitle') ?>

    <?= $form->field($model, 'slug') ?>

    <?= $form->field($model, 'content') ?>

    <?php // echo $form->field($model, 'background_video_url') ?>

    <?php // echo $form->field($model, 'main_image_url') ?>

    <?php // echo $form->field($model, 'carousel_video_url') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
