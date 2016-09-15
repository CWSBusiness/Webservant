<?php

if (!User::current()->isAdmin()) {
	Auth::redirect("./?announcements");
}

$now = new DateTime();
$now->setTime($now->format("G"), 0, 0);     // round to the nearest hour

$upTimeDefault = clone $now;
$downTimeDefault = clone $now->add(DateInterval::createFromDateString("2 weeks"));

$announcement = array(
	"upTime" => $upTimeDefault,
	"downTime" => $downTimeDefault,
	"appliesTo" => 0,
	"title" => "",
	"contents" => ""
);

$errors = new ErrorCollector();

if (isset($_POST["announcementsAddSubmit"])) {

	$continue = true;

	try {
		$temp = DateTime::createFromFormat(Format::DATEPICKER_FORMAT, $_POST["upTime"]);
		if ($temp === false) {
			$continue = false;
			$errors->addError("Please enter a valid up time.", ErrorCollector::WARNING);
		} else {
			$announcement["upTime"] = clone $temp;
		}
		unset($temp);
	} catch (Exception $e) {
		$continue = false;
		$errors->addError("Please enter a valid up time.", ErrorCollector::WARNING);
	}

	try {
		$temp = DateTime::createFromFormat(Format::DATEPICKER_FORMAT, $_POST["downTime"]);
		if ($temp === false) {
			$continue = false;
			$errors->addError("Please enter a valid down time.", ErrorCollector::WARNING);
		} else {
			$announcement["downTime"] = clone $temp;
		}
		unset($temp);
	} catch (Exception $e) {
		$continue = false;
		$errors->addError("Please enter a valid down time.", ErrorCollector::WARNING);
	}

	$announcement["appliesTo"] = $_POST["appliesTo"];

	$announcement["title"] = $_POST["title"];
	if (!Validate::plainText($_POST["title"])) {
		$continue = false;
		$errors->addError("Please enter a valid title.", ErrorCollector::WARNING);
	}

	$announcement["contents"] = $_POST["contents"];
	if (!Validate::HTML($_POST["contents"])) {
		$continue = false;
		$errors->addError("Please enter valid content.", ErrorCollector::WARNING);
	}

	if ($continue) {
		try {
			$newAnnouncement = new Announcement($announcement["title"], $announcement["contents"], 0, $announcement["upTime"], $announcement["downTime"]);
			$newAnnouncement->save();
			Auth::redirect("./?announcements");
		} catch (InvalidArgumentException $e) {
			$errors->addError("The announcement could not be created.", ErrorCollector::DANGER);
		}
	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-bullhorn"></i> <a href="./?announcements">Announcements</a> &gt; Add</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">New Announcement</h2>

		<table>
			<tr>
				<th><label for="upTime">Up Time</label></th>
				<td><input name="upTime" id="upTime" type="text" class="datetimepicker" value="<?php echo Format::date($announcement["upTime"], Format::DATEPICKER_FORMAT); ?>" placeholder="<?php echo Format::date($upTimeDefault, Format::DATEPICKER_FORMAT); ?>"></td>
			</tr>
			<tr>
				<th><label for="downTime">Down Time</label></th>
				<td><input name="downTime" id="downTime" type="text" class="datetimepicker" value="<?php echo Format::date($announcement["downTime"], Format::DATEPICKER_FORMAT); ?>" placeholder="<?php echo Format::date($downTimeDefault, Format::DATEPICKER_FORMAT); ?>"></td>
			</tr>
			<tr>
				<th><label for="appliesTo">Broadcast Group</label></th>
				<td>
					<select name="appliesTo" id="appliesTo">
						<?php foreach (Announcement::getGroups() as $code => $groupText) { ?>
							<option value="<?php echo $code; ?>" <?php echo ($code == $announcement["appliesTo"]) ? "selected" : ""; ?>><?php echo $groupText; ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="title">Title</label></th>
				<td><input name="title" id="title" type="text" value="<?php echo $announcement["title"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="contents">Contents</label></th>
				<td><textarea name="contents" id="contents"><?php echo $announcement["contents"]; ?></textarea></td>
			</tr>
		</table>

	</section>

	<input type="hidden" name="announcementsAddSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Create</button>

</form>

<?php
include_once "php/footer.php";