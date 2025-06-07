<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset'
    ];
    public $css = [
        'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap', // Montserrat font
        'css/site.css',
        'css/glassy.css', // Theme-specific CSS
    ];
    public $js = [
        'js/blog.js',
        'js/theme.js', // Theme-specific JS
    ];

}
