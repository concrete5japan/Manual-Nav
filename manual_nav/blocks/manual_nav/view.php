<?php defined('C5_EXECUTE') or die("Access Denied.");
$c = Page::getCurrentPage();
?>

<div class="ccm-manual-nav-container ccm-block-manual-nav" >
    <div class="ccm-manual-nav">
        <div class="ccm-manual-nav-inner">

        <?php if(count($rows) > 0) { ?>
        <ul id="ccm-manual-nav-<?php echo $bID ?>">
            <?php foreach($rows as $row) { ?>
                <li>
                    <a href="<?php echo $row['linkURL'] ?>" class="mega-link-overlay"><?php echo $row['title'] != null ? h($row['title']) : h($row['collectionName']);  ?></a>
                </li>
            <?php } ?>
        </ul>
        <?php } else { ?>
        <div class="ccm-manual-nav-placeholder">
            <p><?php echo t('No nav Entered.'); ?></p>
        </div>
        <?php } ?>
        </div>

    </div>
</div>
