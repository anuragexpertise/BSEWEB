<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\SignupForm; 
use app\controllers\PostController; 
use app\models\Post;
use app\models\User;
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'], // Allow guest users to signup
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'], // Allow authenticated users to logout
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $featuredPosts = Post::find()
            ->where(['not', ['carousel_image_url' => null]]) // Ensure they have carousel images
            ->andWhere(['not', ['vimeo_video_url' => null]]) // And Vimeo videos for potential thumbnail updates or direct play
            ->orderBy(['uploaded_at' => SORT_DESC])
            ->all();

        // This loop might be redundant if carousel_image_url is reliably set at post creation/update.
        // If kept, ensure consistency:
        foreach ($featuredPosts as $post) {
            // Check if vimeo_video_url is a Vimeo link and carousel_image_url might need updating or is missing
            if (empty($post->carousel_image_url) && isset($post->vimeo_video_url) && strpos($post->vimeo_video_url, 'vimeo.com') !== false) {
                $thumbnailUrl = PostController::getVimeoThumbnailUrl($post->vimeo_video_url); 
                if ($thumbnailUrl) {
                    $post->carousel_image_url = $thumbnailUrl; 
                } else {
                    // Optionally set a default placeholder if thumbnail fetch fails
                    // To use Url::to here, ensure 'use yii\helpers\Url;' is at the top of this file.
                    // $post->carousel_image_url = \yii\helpers\Url::to('@web/images/default-thumbnail.jpg');
                }
            }
        }

        return $this->render('index', [
            'featuredPosts' => $featuredPosts,
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            if (Yii::$app->user->identity && Yii::$app->user->identity->username === 'admin') {
                return $this->redirect(['/post/index']);
            }
            return $this->goBack(); // Default redirect for other users

        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');
            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {




        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
            return $this->goHome();
        }

        return $this->render('signup', [ // Assumes you have a views/site/signup.php
            'model' => $model,
        ]);
    }

    // You might have other actions like actionAbout, etc.

    /**
     * Verifies email address.
     *
     * @param string $token
     * @throws BadRequestHttpException
     * @return yii\web\Response
     */
    public function actionVerifyEmail($token)
    {
        if (empty($token) || !is_string($token)) {
            throw new BadRequestHttpException('Email verification token cannot be blank.');
        }

        $user = User::findByVerificationToken($token);

        if ($user) {
            $user->status = User::STATUS_ACTIVE;
            $user->removeEmailVerificationToken(); // Or $user->verification_token = null;
            if ($user->save(false)) { // Skip validation as we are only changing status and token
                Yii::$app->session->setFlash('success', 'Your email has been confirmed! You can now login.');
                return $this->redirect(['site/login']);
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to verify your account.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Invalid or expired verification link.');
        }

        return $this->goHome();
    }
}