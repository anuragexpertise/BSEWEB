<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url; // Make sure Url helper is used

/** @var yii\web\View $this */
/** @var app\models\Post $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="post-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'vimeo_video_url')->textInput(['maxlength' => true, 'id' => 'post-vimeo_video_url']) ?>
    <button type="button" id="fetch-vimeo-data-btn" class="btn btn-info btn-sm mb-3">Fill from Vimeo</button>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'id' => 'post-title']) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6, 'id' => 'post-description']) ?>

    <?= $form->field($model, 'carousel_image_url')->textInput(['maxlength' => true, 'id' => 'post-carousel_image_url']) ?>
    
    <?= $form->field($model, 'result_image_url')->textInput(['maxlength' => true, 'id' => 'post-result_image_url']) ?>

    <?php // Hidden field for uploaded_at, will be populated by JS or controller default ?>
    <?= $form->field($model, 'uploaded_at')->hiddenInput(['id' => 'post-uploaded_at'])->label(false) ?>
    <?php // Optional: A display field for the date, if you want to show it to the user after fetching
    // echo Html::textInput('uploaded_at_display', $model->uploaded_at ? Yii::$app->formatter->asDate($model->uploaded_at) : '', ['id' => 'post-uploaded_at_display', 'class' => 'form-control', 'readonly' => true, 'placeholder' => 'Upload Date (from Vimeo)']);
    ?>


    <div class="form-group mt-3">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$fetchUrl = Url::to(['post/fetch-vimeo-data']);
$csrfTokenName = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;

$js = <<<JS
$('#fetch-vimeo-data-btn').on('click', function() {
    var vimeoUrl = $('#post-vimeo_video_url').val();
    if (!vimeoUrl) {
        alert('Please enter a Vimeo URL first.');
        return;
    }

    // Add a loading indicator if you like
    $(this).prop('disabled', true).text('Fetching...');

    $.ajax({
        url: '{$fetchUrl}',
        type: 'GET',
        data: { vimeoUrl: vimeoUrl },
        dataType: 'json',
        success: function(data) {
            $('#fetch-vimeo-data-btn').prop('disabled', false).text('Fill from Vimeo');
            if (data && !data.error) {
                if (data.title) $('#post-title').val(data.title);
                if (data.description) $('#post-description').val(data.description);
                if (data.carousel_image_url) $('#post-carousel_image_url').val(data.carousel_image_url);
                if (data.result_image_url) $('#post-result_image_url').val(data.result_image_url);
                if (data.uploaded_at_timestamp) {
                    $('#post-uploaded_at').val(data.uploaded_at_timestamp);
                    // If you have a display field:
                    // var date = new Date(data.uploaded_at_timestamp * 1000);
                    // $('#post-uploaded_at_display').val(date.toLocaleDateString()); // Or any other format
                }
                alert('Fields populated from Vimeo data.');
            } else {
                alert('Failed to fetch Vimeo data: ' + (data.error || 'Unknown error'));
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $('#fetch-vimeo-data-btn').prop('disabled', false).text('Fill from Vimeo');
            alert('Error fetching Vimeo data: ' + textStatus + ' - ' + errorThrown);
            console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
        }
    });
});
JS;
$this->registerJs($js);
?>
