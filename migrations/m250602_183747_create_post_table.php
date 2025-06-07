<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%post}}`.
 */
class m250602_183747_create_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%post}}', [
            'id' => $this->primaryKey(),
            'vimeo_video_url' => $this->string(255),
            'title' => $this->string(255)->notNull(),
            'slug' => $this->string(255)->notNull()->unique(),
            'description' => $this->text(),
            'carousel_image_url' => $this->string(255),       // For carousel image
            'result_image_url' => $this->string(255), 
            'uploaded_at' => $this->integer()->notNull(),
        ]);

        // Add index for slug for faster lookups
        $this->createIndex(
            'idx-post-slug',
            '{{%post}}',
            'slug'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-post-slug', '{{%post}}');
        $this->dropTable('{{%post}}');
    }
}
