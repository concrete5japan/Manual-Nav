<?php defined('C5_EXECUTE') or die("Access Denied.");
$c = Page::getCurrentPage();
?>

    <?php if (count($rows) > 0) { ?>
    <ul class="nav">
    <?php foreach ($rows as $row) { ?>
	    <li>
		<a href="<?php echo $row['linkURL'] ?>"><?php echo $row['title'] != null ? h($row['title']) : h($row['collectionName']); ?></a>
	    </li>
    <?php } ?>
    </ul>
<?php } else { ?>
    <div class="ccm-manual-nav-placeholder">
        <p><?php echo t('No nav Entered.'); ?></p>
    </div>
<?php } ?>