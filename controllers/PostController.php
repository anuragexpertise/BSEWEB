<?php

namespace app\controllers;

use app\models\Post;
use app\models\PostSearch;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Yii;
/**
 * PostController implements the CRUD actions for Post model.
 */
class PostController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [
                    'class' => \yii\filters\AccessControl::class, // Use fully qualified class name
                    'only' => ['index', 'view', 'create', 'update', 'delete', 'fetch-vimeo-data'], // Actions to protect
                    'rules' => [
                        [
                            'actions' => ['index', 'view', 'create', 'update', 'delete', 'fetch-vimeo-data'],
                            'allow' => true,
                            'matchCallback' => function ($rule, $action) {
                                return !Yii::$app->user->isGuest && Yii::$app->user->identity->username === 'admin';
                            }
                        ],
                    ],
                    'denyCallback' => function ($rule, $action) {
                        throw new ForbiddenHttpException('You are not authorized to perform this action.');
                    }
                ],
                'verbs' => [
                    'class' => VerbFilter::class, // Use fully qualified class name
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],

            ]
        );
    }

    /**
     * Lists all Post models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new PostSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Post model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Displays a single Post model to the public using its slug.
     * @param string $slug
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDisplay($slug)
    {
        $model = $this->findModelBySlug($slug);
        // It's good practice to render a different view file for public display
        // if its layout or content differs significantly from the admin 'view'.
        // For now, we can reuse the 'view' template, but consider creating 'display.php'.
        return $this->render('view', [ // Or 'display' if you create a new view file
            'model' => $model,
        ]);
    }
    /**
     * Creates a new Post model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Post();
        if ($this->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                // Ensure uploaded_at is set if not provided by form (e.g., if Vimeo fetch didn't happen or didn't provide it)
                if (empty($model->uploaded_at)) {
                    $model->uploaded_at = time(); // Default to current time if not set
                }

                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Post created successfully.');
                    return $this->redirect(['view', 'id' => $model->id]); // Or your display action
                } else {
                    $errorMessages = [];
                    foreach ($model->getErrors() as $attribute => $errors) {
                        $errorMessages[] = $attribute . ': ' . implode(', ', $errors);
                    }
                    $detailedError = 'Failed to save the post. Errors: ' . implode('; ', $errorMessages);
                    Yii::error($detailedError . ' Submitted data: ' . print_r($model->attributes, true), 'app\controllers\PostController');
                    Yii::$app->session->setFlash('error', $detailedError);
                }
            }
        } else {
            $model->loadDefaultValues();
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Fetches Vimeo data for a given URL and returns it as JSON.
     * @param string $vimeoUrl The Vimeo video URL.
     * @return array
     */
    public function actionFetchVimeoData($vimeoUrl)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (empty($vimeoUrl) || strpos($vimeoUrl, 'vimeo.com') === false) {
            return ['error' => 'Invalid Vimeo URL provided.'];
        }

        $vimeoData = $this->fetchVimeoOembedData($vimeoUrl);

        if ($vimeoData) {
            $description = $vimeoData['description'] ?? '';
            $responseData = [
                'title' => $vimeoData['title'] ?? null,
                'description' => mb_substr(strip_tags($description), 0, 250) . (mb_strlen(strip_tags($description)) > 250 ? '...' : ''),
                'carousel_image_url' => $vimeoData['thumbnail_url'] ?? null,
                'result_image_url' => $vimeoData['thumbnail_url'] ?? null, // Same as carousel_image_url
                'uploaded_at_timestamp' => isset($vimeoData['upload_date']) ? strtotime($vimeoData['upload_date']) : time(), // Default to current time if not available
            ];
            return $responseData;
        }
        return ['error' => 'Failed to fetch data from Vimeo.'];
    }

    /**
     * Fetches oEmbed data from Vimeo.
     * @param string $videoUrl The Vimeo video page URL.
     * @return array|null The oEmbed data as an array, or null on failure.
     */
    protected function fetchVimeoOembedData($videoUrl)
    {
        $oembedUrl = 'https://vimeo.com/api/oembed.json?url=' . urlencode($videoUrl);

        // Use Yii's HTTP client or file_get_contents (ensure allow_url_fopen is on)
        // For robustness, consider Yii2 HTTP client or Guzzle for production
        try {
            $context = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 5]]); // Add timeout
            $response = @file_get_contents($oembedUrl, false, $context);

            if ($response === false) {
                Yii::error("Vimeo oEmbed request failed for URL: $videoUrl", 'app\controllers\PostController');
                return null;
            }

            // Check HTTP status code if possible (more advanced with cURL or HTTP client)
            // For file_get_contents, $http_response_header is populated
            if (isset($http_response_header[0]) && strpos($http_response_header[0], '200 OK') === false) {
                Yii::error("Vimeo oEmbed API returned non-200 status for $videoUrl. Response header: " . $http_response_header[0], 'app\controllers\PostController');
                return null;
            }

            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Yii::error("Failed to decode Vimeo oEmbed JSON response for $videoUrl. Error: " . json_last_error_msg(), 'app\controllers\PostController');
                return null;
            }
            return $data;

        } catch (\Exception $e) {
            Yii::error("Exception during Vimeo oEmbed fetch for $videoUrl: " . $e->getMessage(), 'app\controllers\PostController');
            return null;
        }
    }

    /**
     * Updates an existing Post model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {


            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Post updated successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                // Collect error messages for detailed feedback
                $errorMessages = [];
                foreach ($model->getErrors() as $attribute => $errors) {
                    $errorMessages[] = $attribute . ': ' . implode(', ', $errors);
                }
                $detailedError = 'Failed to update the post. Errors: ' . implode('; ', $errorMessages);
                Yii::error($detailedError . ' Submitted data: ' . print_r($model->attributes, true), 'app\controllers\PostController');
                Yii::$app->session->setFlash('error', $detailedError);
            }
        }
        // For GET request or if model save failed (after setting flash messages)
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Post model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Post model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Post the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModelBySlug($slug)
    {
        if (($model = Post::findOne(['slug' => $slug])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Finds the Post model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Post the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Post::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Fetches the primary thumbnail URL for a given Vimeo video URL using oEmbed.
     *
     * @param string $vimeoVideoUrl The full URL of the Vimeo video (e.g., https://vimeo.com/12345678).
     * @return string|null The thumbnail URL if found, or null on error.
     */
    public static function getVimeoThumbnailUrl($vimeoVideoUrl)
    {
        if (empty($vimeoVideoUrl) || strpos($vimeoVideoUrl, 'vimeo.com') === false) {
            Yii::warning("Invalid or non-Vimeo URL provided: " . $vimeoVideoUrl, 'app\helpers');
            return null;
        }

        $oembedUrl = 'https://vimeo.com/api/oembed.json?url=' . rawurlencode($vimeoVideoUrl);

        // Use Yii's HTTP client if available and preferred, or fallback to file_get_contents
        // For simplicity, using file_get_contents here. Consider error handling for production.
        $responseJson = @file_get_contents($oembedUrl);

        if ($responseJson === false) {
            Yii::error("Failed to fetch Vimeo oEmbed data for URL: " . $vimeoVideoUrl, 'app\helpers');
            // You might want to log the error or handle it more gracefully
            return null;
        }

        $data = json_decode($responseJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Yii::error("Invalid JSON received from Vimeo oEmbed for URL: " . $vimeoVideoUrl . ". Response: " . $responseJson, 'app\helpers');
            return null;
        }

        if (isset($data['thumbnail_url'])) {
            return $data['thumbnail_url'];
        } elseif (isset($data['picture_id'])) {
            // Fallback for some private videos, construct URL if picture_id is available
            // This is a common pattern but check Vimeo docs for latest on private video thumbnails via oEmbed
            // return "https://i.vimeocdn.com/video/" . $data['picture_id'] . "_640x360.jpg"; // Example size
        }


        Yii::warning("Thumbnail URL not found in Vimeo oEmbed response for URL: " . $vimeoVideoUrl, 'app\helpers');
        return null;
    }

    // Example usage (e.g., in your controller when preparing $featuredPosts):
// $vimeoVideoUrl = 'https://vimeo.com/1090090773';
// $thumbnail = getVimeoThumbnailUrl($vimeoVideoUrl);
// if ($thumbnail) {
//     echo "Thumbnail URL: " . $thumbnail;
// } else {
//     echo "Could not retrieve thumbnail.";
// }

}
