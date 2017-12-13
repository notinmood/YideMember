<?php
/**
 * Created by PhpStorm.
 * User: xiedali
 * Date: 2017/11/30
 * Time: 14:49
 */

namespace Tencent\Model;


use Vendor\Hiland\Utils\DataModel\ModelMate;

class CharGame
{
    public static function generateChar($userid, $randChar)
    {
        if (empty($randChar)) {
            $randChar = self::getRandGameChar();
        }

        $mate = new ModelMate("chargame");
        $condition = array(
            'userid' => $userid,
            'charname' => $randChar,
        );
        $existRow = $mate->find($condition);

        $result = null;
        if ($existRow) {
            $existRow['charcount'] += 1;
            $result = $mate->interact($existRow);
        } else {
            $data = array(
                'userid' => $userid,
                'charname' => $randChar,
                'charcount' => 1,
            );
            $result = $mate->interact($data);
        }

        return $result;
    }

    /** 随机生成一个字符
     * @return 随机字符
     */
    public static function getRandGameChar()
    {
        $systemChars = self::getAllGameChars();
        $systemCharsCount = count($systemChars);
        $randNumber = mt_rand(0, $systemCharsCount - 1);
        $randomChar = $systemChars[$randNumber];

        return $randomChar;
    }

    public static function getAllGameChars()
    {
        return array("福", "禄", "寿", "喜");
    }
}