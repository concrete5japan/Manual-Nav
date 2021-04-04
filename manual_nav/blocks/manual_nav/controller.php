<?php
namespace Concrete\Package\ManualNav\Block\ManualNav;

use Concrete\Core\Block\BlockController;
use Page;
use File;
use Less_Parser;
use Less_Tree_Rule;

class Controller extends BlockController
{
    protected $btTable = 'btManualNav';
    protected $btExportTables = ['btManualNav', 'btManualNavEntries'];
    protected $btInterfaceWidth = '600';
    protected $btWrapperClass = 'ccm-ui';
    protected $btInterfaceHeight = '465';
    protected $btCacheBlockRecord = true;
//    protected $btExportFileColumns = array('fID');
    protected $btCacheBlockOutput = true;
    protected $btCacheBlockOutputOnPost = true;
    protected $btCacheBlockOutputForRegisteredUsers = false;

    public function getBlockTypeDescription()
    {
        return t('Manual Nav.');
    }

    public function getBlockTypeName()
    {
        return t('Manual Nav');
    }

    public function on_start()
    {
        // Set font awesome icons
        $classes = $this->getIconClasses();
        $icons = ['' => t('Choose Icon')];
        $txt = $this->app->make('helper/text');
        foreach ($classes as $class) {
            $icons[$class] = $txt->unhandle($class);
        }
        $this->set('icons', $icons);
    }

    public function registerViewAssets($outputContent = '')
    {
        $this->requireAsset('core/file-manager');
        $this->requireAsset('core/sitemap');
        $this->requireAsset('redactor');
        $this->requireAsset('css', 'font-awesome');
    }

    public function getSearchableContent()
    {
        $content = '';
        $db = $this->app->make('database')->connection();
        $v = [$this->bID];
        $q = 'select * from btManualNavEntries where bID = ?';
        $r = $db->query($q, $v);
        foreach ($r as $row) {
            $content .= $row['title'] . ' ';
        }

        return $content;
    }

    public function edit()
    {
        $db = $this->app->make('database')->connection();
        $query = $db->fetchAll('SELECT * from btManualNavEntries WHERE bID = ? ORDER BY sortOrder', [$this->bID]);
        $this->set('rows', $query);
    }

    protected function getIconClasses()
    {
        $iconLessFile = DIR_BASE_CORE . '/css/build/vendor/font-awesome/variables.less';
        $icons = [];

        $l = new Less_Parser();
        $parser = $l->parseFile($iconLessFile, false, true);
        $rules = $parser->rules;

        foreach ($rules as $rule) {
            if (($rule instanceof Less_Tree_Rule) && strpos($rule->name, '@fa-var') === 0) {
                $name = str_replace('@fa-var-', '', $rule->name);
                $icons[] = $name;
            }
        }
        asort($icons);

        return $icons;
    }

    public function view()
    {
        $db = $this->app->make('database')->connection();
        $r = $db->fetchAll('SELECT * from btManualNavEntries WHERE bID = ? ORDER BY sortOrder', [$this->bID]);
        // in view mode, linkURL takes us to where we need to go whether it's on our site or elsewhere
        $rows = [];
        foreach ($r as $q) {
            // Update the variable types
            $q['fID'] = (int) $q['fID'];
            $q['cID'] = (int) $q['cID'];
            $q['internalLinkCID'] = (int) $q['internalLinkCID'];
            $q['internalLinkFID'] = (int) $q['internalLinkFID'];

            if (!$q['linkURL'] && $q['internalLinkCID']) {
                $lc = Page::getByID($q['internalLinkCID'], 'ACTIVE');
                if (is_object($lc)) {
                    $q['linkURL'] = ($lc->getCollectionPointerExternalLink() != '') ? $lc->getCollectionPointerExternalLink() : $lc->getCollectionLink();
                    $q['collectionName'] = $lc->getCollectionName();
                }
            } elseif (!$q['linkURL'] && $q['internalLinkFID']) {
                $file = File::getByID($q['internalLinkFID']);
                if (is_object($file)) {
                    $q['linkURL'] = $file->getDownloadURL();
                    $q['collectionName'] = $file->getFileName();
                }
            }

            //image type
            if ($this->displayImage == 1) {
                $lc = Page::getByID($q['internalLinkCID'], 'ACTIVE');
                if (is_object($lc)) {
                    $q['image'] = $lc->getAttribute('thumbnail');
                }
            } elseif ($this->displayImage == 2) {
                $file = File::getByID($q['fID']);
                 if (is_object($file)) {
                    $q['image'] = $file;
                }
            }

            $q['isVectorImage'] = false;
            if ($this->displayImage) {
                $fh = $this->app->make('helper/file');
                $ex = ['svg'];
                if (is_object($q['image'])) {
                    $q['isVectorImage'] = in_array(strtolower($fh->getExtension($q['image']->getFilename())), $ex, true);
                }
            }

            $rows[] = $q;
        }
        $this->set('rows', $rows);
    }

    public function duplicate($newBID)
    {
        parent::duplicate($newBID);
        $db = $this->app->make('database')->connection();
        $v = [$this->bID];
        $q = 'select * from btManualNavEntries where bID = ?';
        $r = $db->query($q, $v);
        while ($row = $r->fetch()) {
            $db->executeQuery('INSERT INTO btManualNavEntries (bID, fID, icon, linkURL, title, sortOrder, internalLinkCID, internalLinkFID, openInNewWindow) values(?,?,?,?,?,?,?,?,?)', [
                $newBID,
                $row['fID'],
                $row['icon'],
                $row['linkURL'],
                $row['title'],
                $row['sortOrder'],
                $row['internalLinkCID'],
                $row['internalLinkFID'],
                $row['openInNewWindow'],
                ]
            );
        }
    }

    public function delete()
    {
        $db = $this->app->make('database')->connection();
        $db->delete('btManualNavEntries', ['bID' => $this->bID]);
        parent::delete();
    }

    public function save($args)
    {
        $db = $this->app->make('database')->connection();
        $db->executeQuery('DELETE from btManualNavEntries WHERE bID = ?', [$this->bID]);
        $count = count($args['sortOrder']);
        $i = 0;
        parent::save($args);
        while ($i < $count) {
            $linkURL = h($args['linkURL'][$i]);
            $internalLinkCID = h($args['internalLinkCID'][$i]);
            $internalLinkFID = h($args['internalLinkFID'][$i]);
            switch ((int)$args['linkType'][$i]) {
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

            $openInNewWindow = $args['openInNewWindow'][$i] == 1 ? 1 : 0;

            $db->executeQuery('INSERT INTO btManualNavEntries (bID, fID, icon, title, sortOrder, linkURL, internalLinkCID, internalLinkFID, openInNewWindow) values(?,?,?,?,?,?,?,?,?)', [
                $this->bID,
                h($args['fID'][$i]),
                h($args['icon'][$i]),
                h($args['title'][$i]),
                h($args['sortOrder'][$i]),
                $linkURL,
                $internalLinkCID,
                $internalLinkFID,
                $openInNewWindow,
                    ]
            );
            ++$i;
        }
    }
}
