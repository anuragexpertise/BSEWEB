<?php

/** @var yii\web\View $this */
/** @var app\models\User $user */

$verifyLink = Yii::$app->urlManager->createAbsoluteUrl(['site/verify-email', 'token' => $user->verification_token]);
?>
Hello <?= $user->username ?>,

Follow the link below to verify your email:
<?= $verifyLink ?>

If you did not request this email, please ignore it.

Thanks,
The <?= Yii::$app->name ?> Team