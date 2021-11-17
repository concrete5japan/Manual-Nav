<?php

namespace Concrete\Package\ManualNav;

use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\Block\BlockType\Set as BlockTypeSet;
use Concrete\Core\Package\Package;

defined('C5_EXECUTE') or die('Access Denied.');

class Controller extends Package
{
    protected $pkgHandle = 'manual_nav';
    protected $appVersionRequired = '8.0';
    protected $pkgVersion = '2.4.0';
    protected static $blockTypes = [
        [
            'handle' => 'manual_nav', 'set' => 'navigation',
        ],
    ];

    public function getPackageDescription()
    {
        return t("Manual Nav let you create navigation whatever you would like manually. It's concrete5.7 version of Jordan Lev's famous Manual Nav but developed independently by acliss19xx from concrete5 Japan community.");
    }

    public function getPackageName()
    {
        return t('Manual Nav');
    }

    public function install()
    {
        $pkg = parent::install();
        foreach (self::$blockTypes as $blockType) {
            $existingBlockType = BlockType::getByHandle($blockType['handle']);
            if (!$existingBlockType) {
                BlockType::installBlockType($blockType['handle'], $pkg);
            }
            if (isset($blockType['set']) && $blockType['set']) {
                $navigationBlockTypeSet = BlockTypeSet::getByHandle($blockType['set']);
                if ($navigationBlockTypeSet) {
                    $navigationBlockTypeSet->addBlockType(BlockType::getByHandle($blockType['handle']));
                }
            }
        }
    }
}
