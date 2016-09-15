<?php

$siteSettings = SiteSetting::getAll();
$siteContents = SiteContent::getAll();

$errors = new ErrorCollector();

if (isset($_POST["siteContentSubmit"])) {

	foreach ($siteSettings as $setting) {
		$pid = $setting->getPID();
		if ($setting->getType() == SiteSetting::CHECKBOX) {
			if (array_key_exists("setting-" . $pid, $_POST)) {
				$value = 1;
			} else {
				$value = 0;
			}
			try {
				$setting->setValue($value);
				$setting->save();
			} catch (InvalidArgumentException $e) {
				$errors->addError("Could not save changes to \"" . $setting->getLabel() . "\".", ErrorCollector::WARNING);
			}
		}
	}

	foreach ($siteContents as $content) {
		$pid = $content->getPID();
		if (array_key_exists("content-" . $pid, $_POST)) {
			try {
				$content->setContents($_POST["content-" . $pid]);
				$content->save();
			} catch (InvalidArgumentException $e) {
				$errors->addError("Could not save changes to " . $content->getDescription() . ".", ErrorCollector::WARNING);
			}
		}
	}

	if (!$errors->hasErrors()) {
		$errors->addError("Changes saved successfully.", ErrorCollector::SUCCESS);
	}

}

include_once "php/header.php" ?>

<h2 class="breadcrumbs"><i class="fa fa-code"></i> Edit Site Content</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Site Settings</h2>
		<?php foreach ($siteSettings as $setting) {
			if ($setting->getType() == SiteSetting::CHECKBOX) { ?>
				<input type="checkbox" name="setting-<?php echo $setting->getPID(); ?>" id="setting-<?php echo $setting->getPID(); ?>" <?php echo ($setting->getValue()) ? "checked" : "" ; ?>>
				<label for="setting-<?php echo $setting->getPID(); ?>"><?php echo $setting->getLabel(); ?></label><br>
			<?php } ?>
		<?php } ?>
	</section>

	<?php foreach ($siteContents as $row) {
		$pid         = $row->getPID();
		$description = $row->getDescription();
		$contents    = $row->getContents();
		?>
		<section>
			<h2 class="section-heading"><label for="content-<?php echo $pid; ?>"><?php echo $description; ?></label></h2>
			<textarea name="content-<?php echo $pid; ?>" id="content-<?php echo $pid; ?>" style="min-height: 4.5em;" <?php echo ($row->getCategory() == "applicationquestion") ? 'placeholder="Leaving this blank will remove the question from the application."' : ""; ?>><?php echo $contents; ?></textarea>
		</section>
	<?php } ?>

	<input type="hidden" name="siteContentSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Save Changes</button>

</form>

<?php include_once "php/footer.php";
die();