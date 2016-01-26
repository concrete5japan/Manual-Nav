<?php
defined('C5_EXECUTE') or die("Access Denied.");
$c = Page::getCurrentPage();
?>

<?php
if (count($rows) > 0) {
	$rows[0]['class'] = "nav-first";
	foreach ($rows as &$rowp) {
		if ($rowp['internalLinkCID'] === $c->getCollectionID()) {
			$rowp['class'] .= " nav-selected";
		}
	}
	?>
	<ul class="nav">
		<?php foreach ($rows as $row) { ?>
			<?php
			// view title
			$title = null;
			if ($row['title'] != null) {
				$title = $row['title'];
			} elseif ($row['collectionName'] != null) {
				$title = $row['collectionName'];
			} else {
				$title = t('(Untitled)');
			}
			?>

			<li class="<?php echo $row['class'] ?>">

				<a href="<?php echo $row['linkURL'] ?>">
					<?php if (is_object($row['image'])) { ?>
						<img src="<?php echo $row['image']->getRelativePath() ?>" />
					<?php } ?>
		<?php echo h($title); ?>
				</a>
			</li>
	<?php } ?>
	</ul>
<?php } else { ?>
	<div class="ccm-manual-nav-placeholder">
		<p><?php echo t('No nav Entered.'); ?></p>
	</div>
<?php } ?>
