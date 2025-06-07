<?php

use app\models\Post;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\PostSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Posts';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="post-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Post', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'title',
            'slug',
            'description:ntext',
            //'background_video_url:url',
            //'main_image_url:url',
            //'carousel_video_url:url',
            [
                'attribute' => 'uploaded_at',
                'format' => 'datetime',
            ],
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Post $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
