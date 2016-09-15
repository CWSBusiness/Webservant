<?php
$announcements = Announcement::getCurrent();
$parsedown     = new Parsedown();

if (count($announcements) == 0) { ?>
	<p>No announcements to display.</p>
<?php } else { ?>
	<ul>
		<?php foreach ($announcements as $announcement) { ?>
			<li>
				<h5><?php echo Format::date($announcement->getUpTime(), Format::MYSQL_TIMESTAMP_FORMAT) ?></h5>
				<h3>
					<?php echo $announcement->getTitle();
					if (User::current()->isAdmin()) { ?>
						<a href="./?announcements&id=<?php echo $announcement->getPID(); ?>&edit" title="Edit"><i class="fa fa-pencil"></i></a>
					<?php } ?>
				</h3>
				<?php echo $parsedown->text($announcement->getContents()); ?>
				<br><br>
			</li>
		<?php } ?>
	</ul>
<?php } ?>