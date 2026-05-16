<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\Agent;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model {

    public $email;

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'exist',
                'targetClass' => '\common\models\Agent',
                'targetAttribute' => 'agent_email',
                'filter' => ['agent_status' => Agent::STATUS_ACTIVE],
                'message' => 'There is no agent with this email address.'
            ],
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was send
     */
    public function sendEmail() {
        /* @var $agent Agent */
        $agent = Agent::findOne([
                    'agent_status' => Agent::STATUS_ACTIVE,
                    'agent_email' => $this->email,
        ]);

        if (!$agent) {
            return false;
        }

        if (!Agent::isPasswordResetTokenValid($agent->agent_password_reset_token)) {
            
            $agent->generatePasswordResetToken();

            if (!$agent->save()) {
                Yii::error([
                    'message' => 'Failed to save frontend agent password reset token.',
                    'agent_id' => $agent->agent_id,
                    'errors' => $agent->errors,
                ], __METHOD__);

                return false;
            }
        }

        $resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $agent->agent_password_reset_token]);

        $mailer = Yii::$app->mailer
            ->compose(
                [
                    'html' => 'passwordResetToken-html', 
                    'text' => 'passwordResetToken-text'
                ], [
                    'resetLink' => $resetLink,
                    'agent' => $agent
                ]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name])
            ->setTo($this->email)
            ->setSubject('Password reset for ' . Yii::$app->name . ' Dashboard');

        if(\Yii::$app->params['elasticMailIpPool'])
            $mailer->setHeader ("poolName", \Yii::$app->params['elasticMailIpPool']);

        return $mailer->send();
    }

}
