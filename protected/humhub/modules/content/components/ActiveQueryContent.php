<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use Yii;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;

/**
 * ActiveQueryContent is an enhanced ActiveQuery with additional selectors for especially content.
 * 
 * @inheritdoc
 *
 * @author luke
 */
class ActiveQueryContent extends \yii\db\ActiveQuery
{

    /**
     * Own content scope for userRelated 
     * @see ActiveQueryContent::userRelated
     */
    const USER_RELATED_SCOPE_OWN = 1;
    const USER_RELATED_SCOPE_SPACES = 2;
    const USER_RELATED_SCOPE_FOLLOWED_SPACES = 3;
    const USER_RELATED_SCOPE_FOLLOWED_USERS = 4;
    const USER_RELATED_SCOPE_OWN_PROFILE = 5;

    /**
     * Only returns user readable records
     * 
     * @param \humhub\modules\user\models\User $user
     * @return \humhub\modules\content\components\ActiveQueryContent
     */
    public function readable($user = null)
    {
        if ($user === null) {
            $user = Yii::$app->user->getIdentity();
        }

        $this->joinWith(['content', 'content.space']);
        $this->leftJoin('space_membership', 'content.space_id=space_membership.space_id AND space_membership.user_id=:userId', [':userId' => $user->id]);

        // Build Access Check based on Content Container
        $conditionSpace = 'space.id IS NOT NULL AND (';                                         // space content
        $conditionSpace .= ' (space_membership.status=3)';                                      // user is space member
        $conditionSpace .= ' OR (content.visibility=1 AND space.visibility != 0)';               // visibile space and public content
        $conditionSpace .= ')';
        $conditionUser = 'space.id IS NULL AND (';                                              // No Space Content -> User
        $conditionUser .= '   (content.visibility = 1) OR';                                     // public visible content
        $conditionUser .= '   (content.visibility = 0 AND content.user_id=' . $user->id . ')';  // private content of user
        $conditionUser .= ')';

        $this->andWhere("{$conditionSpace} OR {$conditionUser}");

        return $this;
    }

    /**
     * Limits the returned records to the given ContentContainer.
     * 
     * @param ContentContainerActiveRecord $container
     * @return \humhub\modules\content\components\ActiveQueryContent
     * @throws \yii\base\Exception
     */
    public function contentContainer($container)
    {
        $this->joinWith(['content', 'content.user', 'content.space']);

        if ($container->className() == Space::className()) {
            $this->andWhere(['content.space_id' => $container->id]);
        } elseif ($container->className() == User::className()) {
            $this->andWhere(['content.user_id' => $container->id]);
            $this->andWhere('content.space_id IS NULL OR content.space_id = ""');
        } else {
            throw new \yii\base\Exception("Invalid container given!");
        }

        return $this;
    }

    /**
     * Adds an additional WHERE condition to the existing one.
     * 
     * @inheritdoc
     * 
     * @param type $condition
     * @param type $params
     * @return type
     */
    public function where($condition, $params = array())
    {
        return parent::andWhere($condition, $params);
    }

    /**
     * Finds user related content.
     * All available scopes: ActiveQueryContent::USER_RELATED_SCOPE_*
     * 
     * @param array $scopes 
     * @param User $user
     * @return \humhub\modules\content\components\ActiveQueryContent
     */
    public function userRelated($scopes = array(), $user = null)
    {
        if ($user === null) {
            $user = Yii::$app->user->getIdentity();
        }

        $this->joinWith(['content']);

        $conditions = [];
        $params = [];

        if (in_array(self::USER_RELATED_SCOPE_OWN_PROFILE, $scopes)) {
            $conditions[] = 'content.space_id IS NULL and content.user_id=:userId';
            $params[':userId'] = $user->id;
        }

        if (in_array(self::USER_RELATED_SCOPE_SPACES, $scopes)) {
            $spaceMemberships = (new \yii\db\Query())
                    ->select("sm.id")
                    ->from('space_membership')
                    ->leftJoin('space sm', 'sm.id=space_membership.space_id')
                    ->where('space_membership.user_id=:userId AND space_membership.status=' . \humhub\modules\space\models\Membership::STATUS_MEMBER);
            $conditions[] = 'content.space_id IN (' . Yii::$app->db->getQueryBuilder()->build($spaceMemberships)[0] . ')';
            $params[':userId'] = $user->id;
        }

        if (in_array(self::USER_RELATED_SCOPE_SPACES, $scopes)) {
            $spaceMemberships = (new \yii\db\Query())
                    ->select("sm.id")
                    ->from('space_membership')
                    ->leftJoin('space sm', 'sm.id=space_membership.space_id')
                    ->where('space_membership.user_id=:userId AND space_membership.status=' . \humhub\modules\space\models\Membership::STATUS_MEMBER);
            $conditions[] = 'content.space_id IN (' . Yii::$app->db->getQueryBuilder()->build($spaceMemberships)[0] . ')';
            $params[':userId'] = $user->id;
        }

        if (in_array(self::USER_RELATED_SCOPE_OWN, $scopes)) {
            $conditions[] = 'content.user_id = :userId';
            $params[':userId'] = $user->id;
        }

        if (in_array(self::USER_RELATED_SCOPE_FOLLOWED_SPACES, $scopes)) {
            $spaceFollow = (new \yii\db\Query())
                    ->select("sf.id")
                    ->from('user_follow')
                    ->leftJoin('space sf', 'sf.id=user_follow.object_id AND user_follow.object_model=:spaceClass')
                    ->where('user_follow.user_id=:userId AND sf.wall_id IS NOT NULL');
            $conditions[] = 'content.space_id IN (' . Yii::$app->db->getQueryBuilder()->build($spaceFollow)[0] . ')';
            $params[':spaceClass'] = Space::className();
            $params[':userId'] = $user->id;
        }

        if (in_array(self::USER_RELATED_SCOPE_FOLLOWED_USERS, $scopes)) {
            $userFollow = (new \yii\db\Query())
                    ->select(["uf.id"])
                    ->from('user_follow')
                    ->leftJoin('user uf', 'uf.id=user_follow.object_id AND user_follow.object_model=:userClass')
                    ->where('user_follow.user_id=:userId AND uf.wall_id IS NOT NULL');
            $conditions[] = 'content.user_id IN (' . Yii::$app->db->getQueryBuilder()->build($userFollow)[0] . ' AND content.space_id IS NULL)';
            $params[':userClass'] = User::className();
            $params[':userId'] = $user->id;
        }

        if (count($conditions) != 0) {
            $this->andWhere("(" . join(') OR (', $conditions) . ")", $params);
        } else {
            // No results, when no selector given
            $this->andWhere('1=2');
        }

        return $this;
    }

}
