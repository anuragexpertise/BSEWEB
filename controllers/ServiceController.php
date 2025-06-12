<?php

namespace app\controllers;

use app\models\Service;
use app\models\ServiceSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use Yii; // Added for Yii::$app
use yii\filters\VerbFilter;

/**
 * ServiceController implements the CRUD actions for Service model.
 */
class ServiceController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
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
     * Lists all Service models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ServiceSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Service model.
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
     * Creates a new Service model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Service();

        if ($this->request->isPost && $model->load(Yii::$app->request->post())) {
            // Ensure uploaded_at is set if not provided by form
            if (empty($model->uploaded_at)) {
                $model->uploaded_at = time(); // Default to current time
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Service created successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                $errorMessages = [];
                foreach ($model->getErrors() as $attribute => $errors) {
                    $errorMessages[] = $attribute . ': ' . implode(', ', $errors);
                }
                $detailedError = 'Failed to save the service. Errors: ' . implode('; ', $errorMessages);
                Yii::error($detailedError . ' Submitted data: ' . print_r($model->attributes, true), __METHOD__);
                Yii::$app->session->setFlash('error', $detailedError);
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
                'button_image_url' => $vimeoData['thumbnail_url'] ?? null, // Mapped to primary thumbnail
                'result_image_url' => $vimeoData['thumbnail_url'] ?? null, // Also mapped to primary thumbnail as "second" isn't standard
                'uploaded_at_timestamp' => isset($vimeoData['upload_date']) ? strtotime($vimeoData['upload_date']) : time(),
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
        try {
            $context = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 5]]);
            $response = @file_get_contents($oembedUrl, false, $context);

            if ($response === false) {
                Yii::error("Vimeo oEmbed request failed for URL: $videoUrl", __METHOD__);
                return null;
            }

            if (isset($http_response_header[0]) && strpos($http_response_header[0], '200 OK') === false) {
                Yii::error("Vimeo oEmbed API returned non-200 status for $videoUrl. Response header: " . $http_response_header[0], __METHOD__);
                return null;
            }

            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Yii::error("Failed to decode Vimeo oEmbed JSON response for $videoUrl. Error: " . json_last_error_msg(), __METHOD__);
                return null;
            }
            return $data;
        } catch (\Exception $e) {
            Yii::error("Exception during Vimeo oEmbed fetch for $videoUrl: " . $e->getMessage(), __METHOD__);
            return null;
        }
    }

    /**
     * Updates an existing Service model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Service updated successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                $errorMessages = [];
                foreach ($model->getErrors() as $attribute => $errors) {
                    $errorMessages[] = $attribute . ': ' . implode(', ', $errors);
                }
                $detailedError = 'Failed to update the service. Errors: ' . implode('; ', $errorMessages);
                Yii::error($detailedError . ' Submitted data: ' . print_r($model->attributes, true), __METHOD__);
                Yii::$app->session->setFlash('error', $detailedError);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Service model.
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
     * Finds the Service model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Service the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Service::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
