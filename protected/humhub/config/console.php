<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

return [
    'id' => 'humhub-console',
    'controllerNamespace' => 'humhub\commands',
    'bootstrap' => ['gii'],
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
    'components' => [
        'user' => [
            'class' => 'humhub\modules\user\components\User',
            'identityClass' => 'humhub\modules\user\models\User',
            'enableAutoLogin' => false,
            'enableSession' => false,
            'loginUrl' => ['/user/auth/login']
        ],
    ],
];
