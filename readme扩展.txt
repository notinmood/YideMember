1、扩展尽量控制在以下几个目录中
	变更原有的目录：
	\Application\Admin
	
	新建的目录：
	\Application\Tencent
	\ThinkPHP\Library\Vendor
	
2、在原来文件的中的修改
	1、 根目录文件\index.php中添加全局常量
		define('PHYSICAL_ROOT_PATH', dirname(__FILE__));

		define('HILAND_COMPANY_NAME', '青岛紫光海澜网络技术有限公司');
		define('HILAND_COMPANY_ADDRESS', '青岛市市南区鹊山路海信清泉别墅');
		define('HILAND_COMPANY_URL', 'www.hilandsoft.com');
		define('HILAND_COMPANY_MAIL', 'develope@foxmail.com');
		define('HILAND_COMPANY_QQ', '9727005');
		
		define('HILAND_CMS_NAME', 'HilandCMS');
		define('HILAND_CMS_NAME_CN', '海澜内容管理系统');
		define('HILAND_CMS_NAME_DISPLAY', '海澜内容管理系统(HilandCMS)');
		define('HILAND_CMS_VERSION', '6.0124');
		
	2、\Application\Common\Conf\config.php第52行以下