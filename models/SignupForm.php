<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\helpers\Url;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => '\app\models\User', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\app\models\User', 'message' => 'This email address has already been taken.'],

            ['password', 'required'],
            ['password', 'string', 'min' => Yii::$app->params['user.passwordMinLength'] ?? 8],
        ];
    }

    /**
     * Signs user up.
     *
     * @return bool whether the creating new account was successful and email was sent
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->generateEmailVerificationToken();
        $user->status = User::STATUS_INACTIVE; // Set status to inactive, requiring email verification
        
        // Save user first, then send email. If email fails, user is still saved but inactive.
        return $user->save() && $this->sendEmail($user) ? $user : null;
    }

    /**
     * Sends confirmation email to user
     * @param User $user user model to whom email should be sent
     * @return bool whether the email was sent
     */
    protected function sendEmail($user)
    {
        $verifyLink = Yii::$app->urlManager->createAbsoluteUrl(['site/verify-email', 'token' => $user->verification_token]);
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'emailVerify-html', 'text' => 'emailVerify-text'], // View files for the verification email
                ['user' => $user, 'verifyLink' => $verifyLink]
            )
            ->setFrom([Yii::$app->params['supportEmail'] ?? 'support@example.com' => Yii::$app->name . ' robot'])
            ->setTo($this->email)
            ->setSubject('Account verification for ' . Yii::$app->name)
            ->send();
    }
}