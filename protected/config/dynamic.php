<?php return array (
  'components' => 
  array (
    'db' => 
    array (
      'class' => 'yii\\db\\Connection',
      'dsn' => 'mysql:host=www.skillslink.sg;dbname=skillslink',
      'username' => 'root',
      'password' => 'password',
      'charset' => 'utf8',
    ),
    'user' => 
    array (
    ),
    'mailer' => 
    array (
      'transport' => 
      array (
      ),
      'useFileTransport' => true,
      'view' => 
      array (
        'theme' => 
        array (
          'name' => 'HumHub',
        ),
      ),
    ),
    'view' => 
    array (
      'theme' => 
      array (
        'name' => 'HumHub',
      ),
    ),
    'formatter' => 
    array (
      'defaultTimeZone' => 'Asia/Singapore',
    ),
    'formatterApp' => 
    array (
      'defaultTimeZone' => 'Asia/Singapore',
      'timeZone' => 'Asia/Singapore',
    ),
  ),
  'params' => 
  array (
    'installer' => 
    array (
      'db' => 
      array (
        'installer_hostname' => 'www.skillslink.sg',
        'installer_database' => 'skillslink',
      ),
    ),
    'config_created_at' => 1448278648,
    'installed' => true,
  ),
  'name' => 'SkillsLink',
  'language' => 'en',
  'timeZone' => 'Asia/Singapore',
); ?>