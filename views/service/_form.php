<?php

use yii\helpers\Html;
use yii\helpers\Url; // Added for Url::to()
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Service $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="service-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'vimeo_video_url')->textInput(['maxlength' => true, 'id' => 'service-vimeo_video_url']) ?>
    <button type="button" id="fetch-vimeo-data-btn" class="btn btn-info btn-sm mb-3">Fill from Vimeo</button>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'id' => 'service-title']) ?>

    <?= $form->field($model, 'slug')->textInput(['maxlength' => true, 'id' => 'service-slug']) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6, 'id' => 'service-description']) ?>

    <?= $form->field($model, 'button_image_url')->textInput(['maxlength' => true, 'id' => 'service-button_image_url']) ?>

    <?= $form->field($model, 'result_image_url')->textInput(['maxlength' => true, 'id' => 'service-result_image_url']) ?>

    <?= $form->field($model, 'price')->textInput() ?>

    <?= $form->field($model, 'rating')->textInput() ?>

    <?php // Hidden field for uploaded_at, will be populated by JS or controller default ?>
    <?= $form->field($model, 'uploaded_at')->hiddenInput(['id' => 'service-uploaded_at'])->label(false) ?>
    <?php
    // Optional: A display field for the date, if you want to show it to the user after fetching
    // echo '<div class="form-group field-service-uploaded_at_display">';
    // echo Html::label('Upload Date (from Vimeo)', 'service-uploaded_at_display', ['class' => 'control-label']);
    // echo Html::textInput('uploaded_at_display', $model->uploaded_at ? Yii::$app->formatter->asDate($model->uploaded_at) : '', ['id' => 'service-uploaded_at_display', 'class' => 'form-control', 'readonly' => true]);
    // echo '</div>';
    ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$fetchUrl = Url::to(['service/fetch-vimeo-data']);
$csrfTokenName = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;

$js = <<<JS
$('#fetch-vimeo-data-btn').on('click', function() {
    var vimeoUrl = $('#service-vimeo_video_url').val();
    if (!vimeoUrl) {
        alert('Please enter a Vimeo URL first.');
        return;
    }

    $(this).prop('disabled', true).text('Fetching...');

    $.ajax({
        url: '{$fetchUrl}',
        type: 'GET',
        data: { vimeoUrl: vimeoUrl }, // No CSRF needed for GET usually, but Yii might require it for all AJAX.
                                     // If POST, add: '{$csrfTokenName}': '{$csrfToken}'
        dataType: 'json',
        success: function(data) {
            $('#fetch-vimeo-data-btn').prop('disabled', false).text('Fill from Vimeo');
            if (data && !data.error) {
                if (data.title) $('#service-title').val(data.title);
                if (data.description) $('#service-description').val(data.description);
                if (data.button_image_url) $('#service-button_image_url').val(data.button_image_url);
                if (data.result_image_url) $('#service-result_image_url').val(data.result_image_url);
                if (data.uploaded_at_timestamp) {
                    $('#service-uploaded_at').val(data.uploaded_at_timestamp);
                    // If you have a display field:
                    // var date = new Date(data.uploaded_at_timestamp * 1000);
                    // $('#service-uploaded_at_display').val(date.toLocaleDateString()); // Or any other format
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
