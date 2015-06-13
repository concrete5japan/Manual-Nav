<?php       

namespace Concrete\Package\ManualNav;
use Package;
use BlockType;
use BlockTypeSet;
use Config;

defined('C5_EXECUTE') or die(_("Access Denied."));

class Controller extends Package
{

	protected $pkgHandle = 'manual_nav';
	protected $appVersionRequired = '5.7.1';
	protected $pkgVersion = '1.0.0';
	
	
	
	public function getPackageDescription()
	{
		return t("Manual Nav");
	}

	public function getPackageName()
	{
		return t("Manual Nav");
	}
	
	public function install()
	{
		$pkg = parent::install();
	        BlockType::installBlockTypeFromPackage('manual_nav', $pkg);
	}
}
?>
