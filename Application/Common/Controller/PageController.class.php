<?php
/**
 * Created by PhpStorm.
 * User: xiedali
 * Date: 2017/12/14
 * Time: 9:48
 */

namespace Common\Controller;


use Think\Controller;

class PageController extends Controller
{
    /**
     * 向页面传递title等基础信息
     */
    public function passInfrastructure2Page()
    {
        $pageTitle = C('PAGE_TITLE');
        $pageKeywords = C('PAGE_KEYWORDS');
        $pageDescription = C('PAGE_DESCRIPTION');

        $this->assign("pageTitle", $pageTitle);
        $this->assign("pageKeywords", $pageKeywords);
        $this->assign("pageDescription", $pageDescription);
    }
}