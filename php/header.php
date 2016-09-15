<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=0.8">
		<title>WebServant</title>
		<!-- jQuery include -->
		<script src="js/jquery-1.11.3.min.js"></script>
		<!-- Custom menu toggling scripts -->
		<script src="js/toggle.js"></script>
		<!-- Automatic resizing of textareas -->
		<script src="js/autosize/dist/autosize.min.js"></script>
		<script>
			$(function () {
				autosize($('textarea'));
			});
		</script>
		<!-- Date/time picker -->
		<link rel="stylesheet" href="js/datetimepicker/jquery.datetimepicker.css">
		<script src="js/datetimepicker/jquery.datetimepicker.js"></script>
		<!-- Font Awesome icons -->
		<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
		<!-- Main stylesheet -->
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		
		<header role="banner" class="no-select">
			<div class="header-interior">
				<h1><a href="./">WebServant</a></h1>

				<nav role="menu" class="main-menu">
					<?php if (Auth::isLoggedIn()) { ?>
						<a href="./" title="Dashboard"><i class="fa fa-dashboard"></i><span class="sr-only">Dashboard</span></a>
						<?php if (User::current()->isEmployee() || User::current()->isAdmin()) { ?>
						<a href="./?projects" title="Projects"><i class="fa fa-cloud"></i><span class="sr-only">Projects</span></a>
						<?php } ?>
						<?php if (User::current()->isEmployee() || User::current()->isAdmin()) { ?>
						<a href="./?finances" title="Finances"><i class="fa fa-usd"></i><span class="sr-only">Finances</span></a>
						<?php } ?>
						<?php if (User::current()->isEmployee()) { ?>
						<a href="./?docs" title="Docs"><i class="fa fa-book"></i><span class="sr-only">Docs</span></a>
						<?php } ?>
						<a href="./?notes" title="Notes"><i class="fa fa-pencil-square-o"></i><span class="sr-only">Notes</span></a>
						<div class="dropdown">
							<?php if (User::current()->isEmployee() && Employee::current()->getProfilePic()) {
								$iconClass = "profile-pic profile-pic-small";
								$style = " style=\"background-image: url('" . Employee::current()->getProfilePic() . "');\"";
							} else {
								$iconClass = "profile-pic profile-pic-empty profile-pic-small";
								$style = "";
							} ?>
							<button type="button">
								<div class="<?php echo $iconClass; ?>"<?php echo $style; ?>></div>
								<span class="text-hide-small"><?php echo User::current()->getUsername(); ?></span>
								<div class="caret"></div>
							</button>
							<div class="dropdown-menu">
								<?php if (User::current()->isAdmin()) { ?>
								<a href="./?clients"><i class="fa fa-briefcase"></i>Clients</a>
								<a href="./?employees"><i class="fa fa-male"></i>Employees</a>
								<a href="./?applications"><i class="fa fa-user-plus"></i>Job Apps</a>
								<?php } ?>
								<?php if (User::current()->isSuperAdmin()) { ?>
								<a href="./?users"><i class="fa fa-user"></i>Users</a>
								<a href="./?sitecontent"><i class="fa fa-code"></i>Site Content</a>
								<?php } ?>
								<a href="./?settings"><i class="fa fa-gear"></i>Settings</a>
								<a href="./?logout"><i class="fa fa-sign-out"></i>Log Out</a>
							</div>
							<div class="dropdown-menu-hitbox"></div>
						</div>
					<?php } else { ?>
						<a href="./" class="nav-textonly">Log In</a>
					<?php } ?>

				</nav>

			</div>
		</header>


		<main>

			<?php if (BETA_MESSAGE) echo '<p class="beta-notice">WebServant is still in beta. Please report any issues you encounter to <a href="mailto:' . ADMIN_EMAIL . '">' . ADMIN_EMAIL . '</a>.</p>'; ?>
