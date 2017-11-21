<?php
namespace Game\Model;

use Vendor\Hiland\Utils\DataModel\ModelMate;

/**
 * Created by HilandSoft.
 * User: devel
 * Date: 2016/4/2 0002
 * Time: 10:40
 */
class GameBiz
{
    /**
     * 为微信用户创建辨字游戏的一个棋局
     * @param $openID
     * @return bool|number
     */
    public static function generateCharactorGameSet($openID)
    {
        $subjectMate = new ModelMate('game_character_subject');

        $subjectMaxID = (int)$subjectMate->queryValue('MAX(id)');

        $subjectCountPerSet = C('GAME_CHARACTER_SUBJECTCOUNT_PERSET');
        $subjectAllCount = (int)$subjectMate->queryValue('COUNT(id)');

        if ($subjectAllCount < $subjectCountPerSet) {
            $subjectCountPerSet = $subjectAllCount;
        }

        $countGotten = 0;
        $idsGotten = array();
        while ($countGotten < $subjectCountPerSet) {
            $randValue = mt_rand(1, $subjectMaxID);

            $where = "id=$randValue";
            $idGotten = (int)$subjectMate->queryValue('id', $where);

            if ($idGotten && !in_array($idGotten, $idsGotten)) {
                $countGotten++;
                $idsGotten[] = $idGotten;
            }
        }

        $gameSetMate = new ModelMate('game_set');
        $gameSetData = null;
        $gameSetData['playeropenid'] = $openID;
        $gameSetData['gamename'] = '汉字测试';
        $gameSetData['gametime'] = time();

        $gameSetID = $gameSetMate->interact($gameSetData);

        if ($gameSetID) {
            $gameSetDetailsMate = new ModelMate('game_set_details');
            foreach ($idsGotten as $subjectID) {
                $gameSetDetailsData = null;
                $gameSetDetailsData['setid'] = $gameSetID;
                $gameSetDetailsData['subjectid'] = $subjectID;

                $gameSetDetailsMate->interact($gameSetDetailsData);
            }

            return $gameSetID;
        } else {
            return false;
        }
    }
}