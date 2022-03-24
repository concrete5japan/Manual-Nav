<?php

use Concrete\Core\Html\Service\FontAwesomeIcon;
use Concrete\Core\Page\Page;
use Concrete\Core\Support\Facade\Application;
use HtmlObject\Image;

defined('C5_EXECUTE') or die('Access Denied.');

$app = Application::getFacadeApplication();
$im = $app->make('helper/image');
$rows = isset($rows) ? $rows : [];
$displayImage = isset($displayImage) ? $displayImage : 0;
$c = isset($c) ? $c : Page::getCurrentPage();
?>

<?php
if (count($rows) > 0) {
    $rows[0]['class'] .= 'nav-first';
    foreach ($rows as &$rowp) {
        if ($rowp['internalLinkCID'] === $c->getCollectionID()) {
            $rowp['class'] .= 'active nav-selected';
        }
    } ?>
<div class="ccm-block-autonav">
    <ul class="nav flex-column">
        <?php foreach ($rows as $row) {
        ?>
            <?php
            // create title
            $title = null;
        if ($row['title'] != null) {
            $title = $row['title'];
        } elseif ($row['collectionName'] != null) {
            $title = $row['collectionName'];
        } else {
            $title = t('(Untitled)');
        }

        $tag = '';
        $icon = '';
        if ($displayImage >= 1 && $displayImage <= 2) {
            if (is_object($row['image'])) {
                if ($row['isVectorImage']) {
//                        $image = Core::make('html/image', array($row['image']));
//                        $tag = $image->getTag();
                    $tag = '<img src="' . $row['image']->getURL() . '" width="100px" height="100px">';
                } else {
                    $thumb = $im->getThumbnail($row['image'], 100, 100);
                    $tag = new Image();
                    $tag->src($thumb->src)->width($thumb->width)->height($thumb->height);
                    $tag->alt(h($title));
                }
            }
        } elseif ($displayImage == 3) {
            if (class_exists(FontAwesomeIcon::class)) {
                // V9
                $icon = FontAwesomeIcon::getFromClassNames($row['icon'])->getTag();
            } else {
                // V8
                $icon = '<i class="fa fa-' . $row['icon'] . '" area-hidden="true"></i>';
            }
        } ?>

            <li class="nav-item">

                <a href="<?php echo $row['linkURL']; ?>" class="nav-link <?php echo $row['class']; ?>" <?php echo $row['openInNewWindow'] ? 'target="_blank"' : ''; ?>>
                    <?php echo $tag; ?>
                    <?php echo $icon; ?>
                    <?php echo h($title); ?>
                </a>
            </li>
        <?php
    } ?>
    </ul>
</div>
<?php
} else {
        ?>
    <div class="ccm-manual-nav-placeholder">
        <p><?php echo t('No nav Entered.'); ?></p>
    </div>
<?php
    } ?>
