<?php
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\User $user */

$verifyLink = Yii::$app->urlManager->createAbsoluteUrl(['site/verify-email', 'token' => $user->verification_token]);
?>
<div class="verify-email">
    <p>Hello <?= Html::encode($user->username) ?>,</p>

    <p>Follow the link below to verify your email:</p>

    <p><?= Html::a(Html::encode($verifyLink), $verifyLink) ?></p>

    <p>If you did not request this email, please ignore it.</p>

    <p>Thanks,</p>
    <p>The <?= Html::encode(Yii::$app->name) ?> Team</p>
</div>