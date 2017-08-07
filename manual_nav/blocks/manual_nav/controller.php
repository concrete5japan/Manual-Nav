<?php

namespace Concrete\Package\ManualNav\Block\ManualNav;

use Concrete\Core\Block\BlockController;
use Database;
use Page;
use File;
use Core;
use Less_Parser;
use Less_Tree_Rule;

class Controller extends BlockController {

    protected $btTable = 'btManualNav';
    protected $btExportTables = array('btManualNav', 'btManualNavEntries');
    protected $btInterfaceWidth = "600";
    protected $btWrapperClass = 'ccm-ui';
    protected $btInterfaceHeight = "465";
    protected $btCacheBlockRecord = true;
//    protected $btExportFileColumns = array('fID');
    protected $btCacheBlockOutput = true;
    protected $btCacheBlockOutputOnPost = true;
    protected $btCacheBlockOutputForRegisteredUsers = false;

    public function getBlockTypeDescription() {
        return t("Manual Nav.");
    }

    public function getBlockTypeName() {
        return t("Manual Nav");
    }

    public function getSearchableContent() {
        $content = '';
        $db = Database::getActiveConnection();
        $v = array($this->bID);
        $q = 'select * from btManualNavEntries where bID = ?';
        $r = $db->query($q, $v);
        foreach ($r as $row) {
            $content.= $row['title'] . ' ';
        }
        return $content;
    }

    public function add() {
        $this->requireAsset('core/file-manager');
        $this->requireAsset('core/sitemap');
        $this->requireAsset('redactor');

        $this->requireAsset('css', 'font-awesome');
        $classes = $this->getIconClasses();
        $icons = array('' => t('Choose Icon'));
        $txt = Core::make('helper/text');
        foreach ($classes as $class) {
            $icons[$class] = $txt->unhandle($class);
        }
        $this->set('icons', $icons);

        $c = \Page::getCurrentPage();
        $areaBlocks = $c->getBlocks();
        $blockAnchorIDs = [];
        foreach($areaBlocks as $index => $ab){
            if($ab->getCustomStyle(true)->getStyleSet() instanceof \Concrete\Core\Entity\StyleCustomizer\Inline\StyleSet){
                if($ab->getCustomStyle(true)->getStyleSet()->getCustomID()){
                    $blockAnchorIDs[] = $ab->getCustomStyle(true)->getStyleSet()->getCustomID();
                }
            }
        }

        $as = new \Area('main');
        $areas = $as->getListOnPage($c);
        $areaAnchorIDs = [];
        foreach($areas as $area){
            if(is_object($c->getAreaCustomStyle($area))){
                $set = $c->getAreaCustomStyle($area)->getStyleSet();
                if($set->getCustomID()){
                    $areaAnchorIDs[] = $set->getCustomID();
                }
            }
        }

        $this->set('blockAnchorIDs',$blockAnchorIDs);
        $this->set('areaAnchorIDs',$areaAnchorIDs);
    }

    public function edit() {
        $this->requireAsset('core/sitemap');
        $this->requireAsset('core/file-manager');
        $this->requireAsset('redactor');

        $this->requireAsset('css', 'font-awesome');
        $db = Database::getActiveConnection();
        $query = $db->GetAll('SELECT * from btManualNavEntries WHERE bID = ? ORDER BY sortOrder', array($this->bID));
        $this->set('rows', $query);

        $this->requireAsset('css', 'font-awesome');
        $classes = $this->getIconClasses();
        $icons = array('' => t('Choose Icon'));
        $txt = Core::make('helper/text');
        foreach ($classes as $class) {
            $icons[$class] = $txt->unhandle($class);
        }
        $this->set('icons', $icons);

        $c = \Page::getCurrentPage();
        $areaBlocks = $c->getBlocks();
        $blockAnchorIDs = [];
        foreach($areaBlocks as $index => $ab){
            if($ab->getCustomStyle(true)->getStyleSet() instanceof \Concrete\Core\Entity\StyleCustomizer\Inline\StyleSet){
                if($ab->getCustomStyle(true)->getStyleSet()->getCustomID()){
                    $blockAnchorIDs[] = $ab->getCustomStyle(true)->getStyleSet()->getCustomID();
                }
            }
        }

        $as = new \Area('main');
        $areas = $as->getListOnPage($c);
        $areaAnchorIDs = [];
        foreach($areas as $area){
            if(is_object($c->getAreaCustomStyle($area))){
                $set = $c->getAreaCustomStyle($area)->getStyleSet();
                if($set->getCustomID()){
                    $areaAnchorIDs[] = $set->getCustomID();
                }
            }
        }

        $this->set('blockAnchorIDs',$blockAnchorIDs);
        $this->set('areaAnchorIDs',$areaAnchorIDs);
    }

    protected function getIconClasses()
    {
        $iconLessFile = DIR_BASE_CORE . '/css/build/vendor/font-awesome/variables.less';
        $icons = array();

        $l = new Less_Parser();
        $parser = $l->parseFile($iconLessFile, false, true);
        $rules = $parser->rules;

        foreach ($rules as $rule) {
            if ($rule instanceof Less_Tree_Rule) {
                if (strpos($rule->name, '@fa-var') === 0) {
                    $name = str_replace('@fa-var-', '', $rule->name);
                    $icons[] = $name;
                }
            }
        }
        asort($icons);

        return $icons;
    }

    private function action_getCollectionBlockCustomID(\Page $page){
        $areaBlocks = $page->getBlocks();
        $anchorIDs = [];
        foreach($areaBlocks as $index => $ab){
            if($ab->getCustomStyle(true)->getStyleSet() instanceof \Concrete\Core\Entity\StyleCustomizer\Inline\StyleSet){
                if($ab->getCustomStyle(true)->getStyleSet()->getCustomID()){
                    $anchorIDs[] = $ab->getCustomStyle(true)->getStyleSet()->getCustomID();
                }
            }
        }
        return $anchorIDs;

    }

    public function view() {
        $db = Database::getActiveConnection();
        $r = $db->GetAll('SELECT * from btManualNavEntries WHERE bID = ? ORDER BY sortOrder', array($this->bID));
        // in view mode, linkURL takes us to where we need to go whether it's on our site or elsewhere
        $rows = array();
        foreach ($r as $q) {
            if(!$q['linkURL'] && $q['anchorLinkID']){
                $q['linkURL'] = '#' . h($q['anchorLinkID']);
            }else if (!$q['linkURL'] && $q['internalLinkCID']) {
                $lc = Page::getByID($q['internalLinkCID'], 'ACTIVE');
                $q['linkURL'] =  ($lc->getCollectionPointerExternalLink() != '') ? $lc->getCollectionPointerExternalLink() : $lc->getCollectionLink();
                $q['collectionName'] = $lc->getCollectionName();
            }
            //image type
            if ($this->displayImage == 1) {
                $lc = Page::getByID($q['internalLinkCID'], 'ACTIVE');
                if (is_object($lc)) {
                    $q['image'] = $lc->getAttribute('thumbnail');
                }
            } else if ($this->displayImage == 2) {
                $q['image'] = File::getByID($q['fID']);
            }
            
            $q['isVectorImage'] = false;
            if($this->displayImage){
                $f = Core::make('helper/file');
                $ex = array('svg');
                if(is_object($q['image'])){
                    $q['isVectorImage'] = in_array(strtolower(Core::make('helper/file')->getExtension($q['image']->getFilename())),$ex,true);
                }
            }
            
            $rows[] = $q;
        }
        $this->set('rows', $rows);
    }

    public function duplicate($newBID) {
        parent::duplicate($newBID);
        $db = Database::getActiveConnection();
        $v = array($this->bID);
        $q = 'select * from btManualNavEntries where bID = ?';
        $r = $db->query($q, $v);
        while ($row = $r->FetchRow()) {
            $db->execute('INSERT INTO btManualNavEntries (bID, fID, icon, linkURL, title, sortOrder, internalLinkCID, anchorLinkID,openInNewWindow) values(?,?,?,?,?,?,?,?,?)', array(
                $newBID,
                $row['fID'],
                $row['icon'],
                $row['linkURL'],
                $row['title'],
                $row['sortOrder'],
                $row['internalLinkCID'],
                $row['anchorLinkID'],
                $row['openInNewWindow']
                )
            );
        }
    }

    public function delete() {
        $db = Database::getActiveConnection();
        $db->delete('btManualNavEntries', array('bID' => $this->bID));
        parent::delete();
    }

    public function save($args) {
        $db = Database::getActiveConnection();
        $db->execute('DELETE from btManualNavEntries WHERE bID = ?', array($this->bID));
        $count = count($args['sortOrder']);
        $i = 0;
        parent::save($args);
        while ($i < $count) {
            $linkURL = $args['linkURL'][$i];
            $internalLinkCID = $args['internalLinkCID'][$i];
            $anchorLinkID = $args['anchorLinkID'][$i];
            switch (intval($args['linkType'][$i])) {
                case 1:
                    $linkURL = '';
                    $anchorLinkID ='';
                    break;
                case 2:
                    $internalLinkCID = 0;
                    $anchorLinkID ='';
                    break;
                case 3:
                    $linkURL = '';
                    $internalLinkCID = 0;
                    break;
                default:
                    $linkURL = '';
                    $internalLinkCID = 0;
                    $anchorLinkID ='';
                    break;
            }
            if ($args['fID'][$i] == null) {
                $args['fID'][$i] = 0;
            }
            
            $openInNewWindow =  $args['openInNewWindow'][$i] == null ? 0 : 1;
            $db->execute('INSERT INTO btManualNavEntries (bID, fID, icon, title, sortOrder, linkURL, internalLinkCID, anchorLinkID, openInNewWindow) values(?,?,?,?,?,?,?,?,?)', array(
                $this->bID,
                $args['fID'][$i],
                $args['icon'][$i],
                $args['title'][$i],
                $args['sortOrder'][$i],
                $linkURL,
                $internalLinkCID,
                $anchorLinkID,
                $openInNewWindow
                    )
            );
            $i++;
        }
    }

}
