<?php
namespace Vendor\Hiland\Utils\Data;

class DateHelper
{

    /**
     * 获取从1970年1月1日以来总共的毫秒数
     *
     * @return float
     */
    public static function getTotalMilliSeconds()
    {
        list ($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    /**
     * 获取当前时间的毫秒数信息
     *
     * @return float
     */
    public static function getCurrentMilliSecond()
    {
        list ($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1)) * 1000);
    }

    /**
     * 对日期进行时间间隔处理
     *
     * @param int $orignalDate
     *            int类型的时间戳
     * @param string $intervalType
     *            时间间隔类型，具体如下：
     *            y:年
     *            M:月
     *            d:日
     *
     *            q:季度
     *            w:星期
     *
     *            h:小时
     *            m:分钟
     *            s:秒钟
     *
     * @param int $intervalValue
     *            时间间隔值
     * @return int int类型的时间戳
     */
    public static function addInterval($orignalDate, $intervalType, $intervalValue)
    {
        $datetimearray = getdate($orignalDate);
        $hours = $datetimearray["hours"];
        $minutes = $datetimearray["minutes"];
        $seconds = $datetimearray["seconds"];
        $month = $datetimearray["mon"];
        $day = $datetimearray["mday"];
        $year = $datetimearray["year"];
        switch ($intervalType) {
            case "y":
                $year += $intervalValue;
                break;
            case "q":
                $month += ($intervalValue * 3);
                break;
            case "M":
                $month += $intervalValue;
                break;
            case "d":
                $day += $intervalValue;
                break;
            case "w":
                $day += ($intervalValue * 7);
                break;
            case "h":
                $hours += $intervalValue;
                break;
            case "m":
                $minutes += $intervalValue;
                break;
            case "s":
                $seconds += $intervalValue;
                break;
        }
        $timestamp = mktime($hours, $minutes, $seconds, $month, $day, $year);
        return $timestamp;
    }

    /**
     * 对数字表示的timestamp进行格式化友好显示
     * @param int $time timestamp格式的时间
     * @param string $formatString 格式化字符串
     * @return string
     */
    public static function format($time = NULL, $formatString = 'Y-m-d H:i')
    {
        $time = $time === NULL ? time() : intval($time);

        return date($formatString, $time);
    }

    /**
     * 获取一个指定时间点的timestamp
     * @param string $date 指定的时间点 ，可以是“201603161312”格式，也可以是“2016-03-16 13:12:25”
     * @return int
     */
    public static function getTimestamp($date)
    {
        $datePart = '';
        $timePart = '';
        if (StringHelper::isContains($date, ' ')) {
            $datePart = StringHelper::getSeperatorBeforeString($date, ' ');
            $timePart = StringHelper::getSeperatorAfterString($date, ' ');
        } else {
            $datePart = $date;
            $timePart = '';
        }

        if (!StringHelper::isContains($datePart, '-')) {
            $temp = $datePart;
            $datePart = substr($temp, 0, 8);
            $timePart = substr($temp, 8);
            if (strlen($timePart) < 6) {
                $timePart = str_pad($timePart, 6, '0', STR_PAD_RIGHT);
            }
            $datePart = StringHelper::format($datePart, '{4}-{2}-{2}');
            $timePart = StringHelper::format($timePart, '{2}:{2}:{2}');
        }

        $date = "$datePart $timePart";
        return strtotime($date);
    }


}

?>