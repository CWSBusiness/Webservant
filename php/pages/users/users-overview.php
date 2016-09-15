<?php include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-user"></i> Users</h2>

<section id="users">
	<a href="./?users&add" class="top-right-button"><i class="fa fa-plus"></i>Add New</a>
	<h2 class="section-heading">All Users</h2>

	<?php $users = User::getAll();
	
	if (count($users) > 0) { ?>
		<table class="data-table">
			<thead>
				<tr>
					<th></th>
					<th>Username</th>
					<th>Last Login</th>
					<th>Email</th>
					<th>Admin Status</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($users as $user) { ?>
				<tr>
					<td>
						<?php
						$title = "";
						$styles = "";
						if ($user->isEmployee()) {
							$employee = Employee::withID($user->getEmployeeID());
							$title = "Employee Account: " . $employee;
							if ($employee->getProfilePic()) { ?>
								<a href="./?employees&id=<?php echo $employee->getPID(); ?>" class="profile-pic profile-pic-small" style="background-image: url('<?php echo $employee->getProfilePic(); ?>');" title="<?php echo $title; ?>"></a>
							<?php } else { ?>
								<a href="./?employees&id=<?php echo $employee->getPID(); ?>" class="profile-pic profile-pic-small" title="<?php echo $title; ?>"><i class="fa fa-male"></i></a>
							<?php }
						} else { ?>
							<div class="profile-pic profile-pic-small profile-pic-empty"></div>
						<?php } ?>
					</td>
					<td><a href="./?users&edit=<?php echo $user->getPID(); ?>"><?php echo $user->getUsername(); ?></a></td>
					<td title="<?php echo ($user->getLastLogin()) ? Format::date($user->getLastLogin(), Format::DATETIME_FORMAT) : ""; ?>"><?php echo ($user->getLastLogin()) ? Format::relativeTime($user->getLastLogin()) : "Never"; ?></td>
					<td><a href="mailto:<?php echo $user->getEmail(); ?>"><?php echo $user->getEmail(); ?></a></td>
					<td><?php echo $user->getAdminStatusText(); ?></td>
					<td class="action-column">
					<?php if (User::current()->getAdminStatus() < $user->getAdminStatus()) { ?>
						<span title="You cannot edit accounts with higher access than you." class="disabled"><i class="fa fa-pencil"></i></span>
					<?php } else { ?>
						<a href="./?users&edit=<?php echo $user->getPID(); ?>" title="Edit"><i class="fa fa-pencil"></i></a>
					<?php }
					if ($user->getPID() == User::current()->getPID()) { ?>
						<span title="You cannot delete your own account." class="disabled"><i class="fa fa-trash-o"></i></span>
					<?php } else if ($user->getAdminStatus() > User::current()->getAdminStatus()) { ?>
						<span title="You cannot delete accounts with higher access than you." class="disabled"><i class="fa fa-trash-o"></i></span>
					<?php } else { ?>
						<a href="./?users&delete=<?php echo $user->getPID(); ?>" title="Delete"><i class="fa fa-trash-o"></i></a>
					<?php } ?>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	<?php } else {
		echo '<p>No entries to display.</p>';
	}
	
	?>
	
</section>

<?php include_once "php/footer.php";