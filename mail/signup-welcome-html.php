<?php
/** @var yii\web\View $this */
/** @var app\models\User $user */

use yii\helpers\Html;
?>
<div class="signup-welcome">
    <p>Hello <?= Html::encode($user->username) ?>,</p>

    <p>Welcome to <?= Html::encode(Yii::$app->name) ?>! We are excited to have you.</p>

    <p>You can now login using your credentials.</p>
</div>