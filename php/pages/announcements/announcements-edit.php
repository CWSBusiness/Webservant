<?php

if (!User::current()->isAdmin()) {
	Auth::redirect("./?announcements");
}

try {
	$announcement = Announcement::withID($_GET["id"]);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./");
}

$data = array(
	"upTime" => $announcement->getUpTime(),
	"downTime" => $announcement->getDownTime(),
	"group" => $announcement->getGroup(),
	"title" => $announcement->getTitle(),
	"contents" => $announcement->getContents()
);

$errors = new ErrorCollector();

if (isset($_POST["announcementsEditSubmit"])) {

	$continue = true;

	$data["upTime"] = $_POST["upTime"];
	if (trim($data["upTime"]) != "") {
		try {
			$temp = DateTime::createFromFormat(Format::DATEPICKER_FORMAT, $data["upTime"]);
			if ($temp === false) {
				$continue = false;
				$errors->addError("Please enter a valid up time.", ErrorCollector::WARNING);
			} else {
				$data["upTime"] = clone $temp;
			}
			unset($temp);
		} catch (Exception $e) {
			$continue = false;
			$errors->addError("Please enter a valid up time.", ErrorCollector::WARNING);
		}
	} else {
		$data["upTime"] = new DateTime();
	}

	$data["downTime"] = $_POST["downTime"];
	if (trim($data["downTime"]) != "") {
		try {
			$temp = DateTime::createFromFormat(Format::DATEPICKER_FORMAT, $data["downTime"]);
			if ($temp === false) {
				$continue = false;
				$errors->addError("Please enter a valid down time.", ErrorCollector::WARNING);
			} else {
				$data["downTime"] = clone $temp;
			}
			unset($temp);
		} catch (Exception $e) {
			$continue = false;
			$errors->addError("Please enter a valid down time.", ErrorCollector::WARNING);
		}
	} else {
		$data["downTime"] = $data["upTime"]->add(DateInterval::createFromDateString("2 weeks"));
	}

	try {
		$announcement->setUpTime($data["upTime"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid up time.", ErrorCollector::WARNING);
	}

	try {
		$announcement->setDownTime($data["downTime"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid up time.", ErrorCollector::WARNING);
	}

	$data["group"] = $_POST["appliesTo"];
	try {
		$announcement->setGroup($data["group"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid broadcast group.", ErrorCollector::WARNING);
	}

	$data["title"] = $_POST["title"];
	try {
		$announcement->setTitle($data["title"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid title.", ErrorCollector::WARNING);
	}

	$data["contents"] = $_POST["contents"];
	try {
		$announcement->setContents($data["contents"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter valid text.", ErrorCollector::WARNING);
	}

	if ($continue) {
		$success = $announcement->save();
		if($success) {
			Auth::redirect("./?announcements");
		} else {
			$errors->addError("An error occurred saving your changes.", ErrorCollector::DANGER);
		}
	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-bullhorn"></i> <a href="./?announcements">Announcements</a> &gt; Edit</h2>


<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Edit Announcement</h2>

		<table>
			<tr>
				<th><label for="upTime">Up Time</label></th>
				<td><input name="upTime" id="upTime" type="text" class="datetimepicker" value="<?php echo Format::date($data["upTime"], Format::DATEPICKER_FORMAT); ?>" placeholder="<?php echo Format::date($data["upTime"]); ?>"></td>
			</tr>
			<tr>
				<th><label for="downTime">Down Time</label></th>
				<td><input name="downTime" id="downTime" type="text" class="datetimepicker" value="<?php echo Format::date($data["downTime"], Format::DATEPICKER_FORMAT); ?>" placeholder="<?php echo Format::date($data["downTime"], Format::DATEPICKER_FORMAT); ?>"></td>
			</tr>
			<tr>
				<th><label for="appliesTo">Broadcast Group</label></th>
				<td>
					<select name="appliesTo" id="appliesTo">
						<?php foreach (Announcement::getGroups() as $code => $groupText) { ?>
							<option value="<?php echo $code; ?>" <?php echo ($code == $data["group"]) ? "selected" : ""; ?>><?php echo $groupText; ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="title">Title</label></th>
				<td><input name="title" id="title" type="text" value="<?php echo $data["title"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="contents">Contents</label></th>
				<td><textarea name="contents" id="contents"><?php echo $data["contents"]; ?></textarea></td>
			</tr>
		</table>

	</section>

	<a href="./?announcements&id=<?php echo $announcement->getPID(); ?>&delete" class="top-right-button delete-link" tabindex="-1"><i class="fa fa-trash-o"></i>Delete Announcement</a>
	<input type="hidden" name="announcementsEditSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Save Changes</button>

</form>

<?php
include_once "php/footer.php";