<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%service}}`.
 */
class m250611_135403_create_service_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%service}}', [
            'id' => $this->primaryKey(),
            'vimeo_video_url' => $this->string(255),
            'title' => $this->string(255)->notNull(),
            'slug' => $this->string(255)->notNull()->unique(),
            'description' => $this->text(),
            'button_image_url' => $this->string(255),       // For button image
            'result_image_url' => $this->string(255),
            'price' => $this->integer(),
            'rating' => $this->integer(),
            'uploaded_at' => $this->integer()->notNull(),
        ]);

        // Add index for slug for faster lookups
        $this->createIndex(
            'idx-service-slug',
            '{{%service}}',
            'slug'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-service-slug', '{{%service}}');
        $this->dropTable('{{%service}}');
    }
}
