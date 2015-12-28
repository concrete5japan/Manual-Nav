<?php
namespace Concrete\Package\ManualNav\Block\ManualNav;

use Concrete\Core\Block\BlockController;
use Database;
use Page;

class Controller extends BlockController
{
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

    public function getBlockTypeDescription()
    {
        return t("Manual Nav.");
    }

    public function getBlockTypeName()
    {
        return t("Manual Nav");
    }

    public function getSearchableContent()
    {
        $content = '';
        $db = Database::getActiveConnection();
        $v = array($this->bID);
        $q = 'select * from btManualNavEntries where bID = ?';
        $r = $db->query($q, $v);
        foreach($r as $row) {
           $content.= $row['title'].' ';
        }
        return $content;
    }

    public function add()
    {
        $this->requireAsset('core/sitemap');
    }

    public function edit()
    {
        $this->requireAsset('core/sitemap');
        $db = Database::getActiveConnection();
        $query = $db->GetAll('SELECT * from btManualNavEntries WHERE bID = ? ORDER BY sortOrder', array($this->bID));
        $this->set('rows', $query);
    }

    public function view()
    {
        $db = Database::getActiveConnection();
        $r = $db->GetAll('SELECT * from btManualNavEntries WHERE bID = ? ORDER BY sortOrder', array($this->bID));
        // in view mode, linkURL takes us to where we need to go whether it's on our site or elsewhere
        $rows = array();
        foreach($r as $q) {
            if (!$q['linkURL'] && $q['internalLinkCID']) {
                $c = Page::getByID($q['internalLinkCID'], 'ACTIVE');
                $q['linkURL'] = $c->getCollectionLink();
		$q['collectionName'] = $c->getCollectionName();
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
            $db->execute('INSERT INTO btManualNavEntries (bID, linkURL, title, sortOrder, internalLinkCID) values(?,?,?,?,?)',
                array(
                    $newBID,
                    $row['linkURL'],
                    $row['title'],
                    $row['sortOrder'],
                    $row['internalLinkCID']
                )
            );
        }
    }

    public function delete()
    {
        $db = Database::getActiveConnection();
        $db->delete('btManualNavEntries', array('bID' => $this->bID));
        parent::delete();
    }

    public function save($args)
    {
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

            $db->execute('INSERT INTO btManualNavEntries (bID, title, sortOrder, linkURL, internalLinkCID) values(?, ?,?,?,?)',
                array(
                    $this->bID,
                    $args['title'][$i],
                    $args['sortOrder'][$i],
                    $linkURL,
                    $internalLinkCID
                )
            );
            $i++;
        }
    }

}
