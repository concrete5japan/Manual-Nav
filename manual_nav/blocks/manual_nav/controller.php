<?php

namespace Concrete\Package\ManualNav\Block\ManualNav;

use Concrete\Core\Block\BlockController;
use Database;
use Page;
use File;
use Core;

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
    }

    public function edit() {
        $this->requireAsset('core/sitemap');
        $this->requireAsset('core/file-manager');
        $this->requireAsset('redactor');
        $db = Database::getActiveConnection();
        $query = $db->GetAll('SELECT * from btManualNavEntries WHERE bID = ? ORDER BY sortOrder', array($this->bID));
        $this->set('rows', $query);
    }

    public function view() {
        $db = Database::getActiveConnection();
        $r = $db->GetAll('SELECT * from btManualNavEntries WHERE bID = ? ORDER BY sortOrder', array($this->bID));
        // in view mode, linkURL takes us to where we need to go whether it's on our site or elsewhere
        $rows = array();
        foreach ($r as $q) {
            if (!$q['linkURL'] && $q['internalLinkCID']) {
                $c = Page::getByID($q['internalLinkCID'], 'ACTIVE');
                $q['linkURL'] = $c->getCollectionLink();
                $q['collectionName'] = $c->getCollectionName();
            }
            //image type
            if ($this->displayImage == 1) {
                $c = Page::getByID($q['internalLinkCID'], 'ACTIVE');
                if (is_object($c)) {
                    $q['image'] = $c->getAttribute('thumbnail');
                }
            } else if ($this->displayImage == 2) {
                $q['image'] = File::getByID($q['fID']);
            }
            
            $q['isVerctorImage'] = false;
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
            $db->execute('INSERT INTO btManualNavEntries (bID, fID, linkURL, title, sortOrder, internalLinkCID, openInNewWindow) values(?,?,?,?,?,?,?)', array(
                $newBID,
                $row['fID'],
                $row['linkURL'],
                $row['title'],
                $row['sortOrder'],
                $row['internalLinkCID'],
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
            switch (intval($args['linkType'][$i])) {
                case 1:
                    $linkURL = '';
                    break;
                case 2:
                    $internalLinkCID = 0;
                    break;
                default:
                    $linkURL = '';
                    $internalLinkCID = 0;
                    break;
            }
            if ($args['fID'][$i] == null) {
                $args['fID'][$i] = 0;
            }
            
            $openInNewWindow =  $args['openInNewWindow'][$i] == null ? 0 : 1;

            $db->execute('INSERT INTO btManualNavEntries (bID, fID, title, sortOrder, linkURL, internalLinkCID, openInNewWindow) values(?,?,?,?,?,?,?)', array(
                $this->bID,
                $args['fID'][$i],
                $args['title'][$i],
                $args['sortOrder'][$i],
                $linkURL,
                $internalLinkCID,
                $openInNewWindow
                    )
            );
            $i++;
        }
    }

}
