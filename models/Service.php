<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "service".
 *
 * @property int $id
 * @property string|null $vimeo_video_url
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property string|null $button_image_url
 * @property string|null $result_image_url
 * @property int|null $price
 * @property int|null $rating
 * @property int $uploaded_at
 */
class Service extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'service';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vimeo_video_url', 'description', 'button_image_url', 'result_image_url', 'price', 'rating'], 'default', 'value' => null],
            [['title', 'slug', 'uploaded_at'], 'required'],
            [['description'], 'string'],
            [['price', 'rating', 'uploaded_at'], 'integer'],
            [['vimeo_video_url', 'title', 'slug', 'button_image_url', 'result_image_url'], 'string', 'max' => 255],
            [['slug'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'vimeo_video_url' => 'Vimeo Video Url',
            'title' => 'Title',
            'slug' => 'Slug',
            'description' => 'Description',
            'button_image_url' => 'Button Image Url',
            'result_image_url' => 'Result Image Url',
            'price' => 'Price',
            'rating' => 'Rating',
            'uploaded_at' => 'Uploaded At',
        ];
    }

}
