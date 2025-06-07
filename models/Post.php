<?php

namespace app\models;
use yii\db\ActiveRecord;
// Removed: use yii\behaviors\TimestampBehavior; // TimestampBehavior is removed
use yii\behaviors\SluggableBehavior;
// Removed: use yii\helpers\Inflector; // Not strictly needed for default SluggableBehavior

use Yii;

/**
 * This is the model class for table "post".
 *
 * @property int $id
 * @property string|null $vimeo_video_url
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property string|null $carousel_image_url
 * @property string|null $result_image_url
 * @property int $uploaded_at
 */
class Post extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%post}}'; // Use {{%post}} to use table prefix if configured
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'sluggable' => [
                'class' => SluggableBehavior::class,
                'attribute' => 'title', // Source attribute
                'slugAttribute' => 'slug', // Target attribute
                'ensureUnique' => true,    // Ensures that the slug is unique
                'immutable' => true,       // Slug will not be updated when 'title' changes after creation
            ],
            // TimestampBehavior removed as created_at and updated_at are not in the new schema.
            // uploaded_at will be handled in the controller.
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'slug', 'uploaded_at'], 'required'],
            [['description'], 'string'],
            [['uploaded_at'], 'integer'],
            [['title', 'slug', 'vimeo_video_url', 'carousel_image_url', 'result_image_url'], 'string', 'max' => 255],
            [['vimeo_video_url', 'carousel_image_url', 'result_image_url'], 'url', 'defaultScheme' => 'https'],
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
            'vimeo_video_url' => 'Vimeo Video URL (for background/main video)',
            'title' => 'Title',
            'slug' => 'Slug',
            'description' => 'Description (was Content)',
            'carousel_image_url' => 'Carousel Image URL (was Main Image URL)',
            'result_image_url' => 'Result Image URL',
            'uploaded_at' => 'Uploaded At (was Created At)',
        ];
    }
}