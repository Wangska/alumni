<?php include 'admin/db_connect.php' ?>
```php
<?php
session_start();
include 'admin/db_connect.php';

if(isset($_GET['id'])){
	$qry = $conn->query("SELECT * FROM careers where id=".$_GET['id'])->fetch_array();
	foreach($qry as $k =>$v){
		$$k = $v;
	}
}
?>
<!-- Tailwind + FontAwesome (needed for landing-style header utilities) -->
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
?>
<!-- Landing-style Navigation (copied from index.php) -->
<style>
	.navbar-scrolled { backdrop-filter: blur(10px); background: rgba(127, 29, 29, 0.95) !important; }
	.glass-effect { backdrop-filter: blur(16px) saturate(180%); background-color: rgba(255, 255, 255, 0.95); border: 1px solid rgba(209, 213, 219, 0.3);}
	.glass-dark { backdrop-filter: blur(16px) saturate(180%); background-color: rgba(127, 29, 29, 0.95); border: 1px solid rgba(185, 28, 28, 0.3);}
	/* Normalize nav item sizing to match landing; ensure active state keeps same size */
	.nav-link, .nav-link.active { position: relative; overflow: hidden; display: inline-flex; align-items: center; padding: 0.5rem 1rem; gap: 0.5rem; line-height: 1; }
	.nav-link i, .nav-link.active i { margin-right: 0.5rem; }
	.nav-link::before { content: ''; position: absolute; bottom: 0; left: 50%; width: 0; height: 2px; background: linear-gradient(90deg,transparent,#fca5a5,transparent); transition: all 0.3s ease; transform: translateX(-50%);}        
	.nav-link:hover::before { width: 100%; }
</style>

<header id="mainNav" class="fixed top-0 w-full z-40 py-4 transition-all duration-300 glass-dark shadow-lg backdrop-blur-lg">
	});
</script>
	<p>Job Title: <b><large><?php echo ucwords($job_title) ?></large></b></p>
	<?php
	session_start();
	include 'admin/db_connect.php';

	$company = $job_title = $description = '';
	if(isset($_GET['id'])){
		$id = intval($_GET['id']);
		$qry = $conn->query("SELECT * FROM careers where id=$id");
		if($qry && $qry->num_rows > 0){
			$row = $qry->fetch_assoc();
			$company = $row['company'] ?? '';
			$job_title = $row['job_title'] ?? '';
			$description = $row['description'] ?? '';
		}
	}
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Job Details</title>
		<!-- Tailwind + FontAwesome -->
		<script src="https://cdn.tailwindcss.com"></script>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
		<style>
			.navbar-scrolled { backdrop-filter: blur(10px); background: rgba(127, 29, 29, 0.95) !important; }
			.glass-effect { backdrop-filter: blur(16px) saturate(180%); background-color: rgba(255, 255, 255, 0.95); border: 1px solid rgba(209, 213, 219, 0.3);}        
			.glass-dark { backdrop-filter: blur(16px) saturate(180%); background-color: rgba(127, 29, 29, 0.95); border: 1px solid rgba(185, 28, 28, 0.3);}
			.nav-link { position: relative; overflow: hidden;}
			.nav-link::before { content: ''; position: absolute; bottom: 0; left: 50%; width: 0; height: 2px; background: linear-gradient(90deg,transparent,#fca5a5,transparent); transition: all 0.3s ease; transform: translateX(-50%);}        
			.nav-link:hover::before { width: 100%; }
		</style>
	</head>
	<body class="bg-red-50 min-h-screen">

	<header id="mainNav" class="fixed top-0 w-full z-40 py-4 transition-all duration-300 glass-dark shadow-lg backdrop-blur-lg">
		<div class="container mx-auto px-6">
			<div class="flex justify-between items-center">
				<a href="./" class="text-2xl font-bold text-white hover:text-red-300 transition-colors duration-300">Alumni Nexus</a>
				<button id="mobile-menu-btn" class="md:hidden text-white p-2 rounded-lg hover:bg-white/10 transition-colors"><i class="fas fa-bars text-xl"></i></button>
				<nav id="desktop-nav" class="hidden md:flex items-center space-x-8">
					<a href="index.php?page=home" class="nav-link text-white hover:text-red-300 px-4 py-2 rounded-lg transition-all duration-300 flex items-center"><i class="fas fa-home mr-2"></i>Home</a>
					<a href="alumni_list.php" class="nav-link text-white hover:text-red-300 px-4 py-2 rounded-lg transition-all duration-300 flex items-center"><i class="fas fa-users mr-2"></i>Alumni</a>
					<a href="gallery.php" class="nav-link text-white hover:text-red-300 px-4 py-2 rounded-lg transition-all duration-300 flex items-center"><i class="fas fa-images mr-2"></i>Gallery</a>
					<div id="auth-links" class="flex items-center space-x-6">
						<?php if(isset($_SESSION['login_username'])): ?>
						<a href="careers.php" class="nav-link text-white hover:text-red-300 px-4 py-2 rounded-lg transition-all duration-300 flex items-center active"><i class="fas fa-briefcase mr-2"></i>Jobs</a>
						<a href="forum.php" class="nav-link text-white hover:text-red-300 px-4 py-2 rounded-lg transition-all duration-300 flex items-center"><i class="fas fa-comments mr-2"></i>Forums</a>
						<div id="user-dropdown" class="relative">
							<button id="account_settings" type="button" class="flex items-center space-x-2 text-white hover:text-red-300 px-4 py-2 rounded-lg transition-all duration-300"><i class="fas fa-user-circle text-xl"></i><span><?= htmlspecialchars($_SESSION['login_username']) ?></span><i class="fas fa-angle-down"></i></button>
							<div class="absolute right-0 mt-2 w-48 glass-effect rounded-lg shadow-xl py-2 hidden" id="dropdown-menu"><a href="admin/ajax.php?action=logout2" class="flex items-center px-4 py-2 text-gray-700 hover:bg-red-50 transition-colors duration-200"><i class="fas fa-sign-out-alt mr-3"></i>Logout</a></div>
						</div>
						<?php else: ?>
						<a href="login.php" id="login" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-full transition-all duration-300 transform hover:scale-105 shadow-lg flex items-center"><i class="fas fa-sign-in-alt mr-2"></i>Login</a>
						<?php endif; ?>
					</div>
				</nav>
				<nav id="mobile-nav" class="md:hidden absolute top-full left-0 right-0 glass-dark rounded-b-2xl shadow-xl hidden">
					<div class="px-6 py-4 space-y-4">
						<a href="index.php" class="block text-white hover:text-red-300 py-2 transition-colors duration-300 flex items-center"><i class="fas fa-home mr-3"></i>Home</a>
						<a href="alumni_list.php" class="block text-white hover:text-red-300 py-2 transition-colors duration-300 flex items-center"><i class="fas fa-users mr-3"></i>Alumni</a>
						<a href="gallery.php" class="block text-white hover:text-red-300 py-2 transition-colors duration-300 flex items-center"><i class="fas fa-images mr-3"></i>Gallery</a>
						<div class="pt-4 border-t border-gray-600">
							<?php if(isset($_SESSION['login_username'])): ?>
								<a href="careers.php" class="block text-white hover:text-red-300 py-2 transition-colors duration-300 flex items-center"><i class="fas fa-briefcase mr-3"></i>Jobs</a>
								<a href="forum.php" class="block text-white hover:text-red-300 py-2 transition-colors duration-300 flex items-center"><i class="fas fa-comments mr-3"></i>Forums</a>
								<button id="mobile-account_settings" type="button" class="w-full bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg transition-all duration-300 flex items-center justify-center"><i class="fas fa-user-circle mr-2"></i><?= htmlspecialchars($_SESSION['login_username']) ?><i class="fas fa-angle-down ml-2"></i></button>
								<a href="admin/ajax.php?action=logout2" class="block text-center mt-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 rounded-lg border border-gray-200 transition">Logout</a>
							<?php else: ?>
								<button id="mobile-login" class="w-full bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg transition-all duration-300 flex items-center justify-center"><i class="fas fa-sign-in-alt mr-2"></i>Login</button>
							<?php endif; ?>
					</div>
				</nav>
			</div>
		</div>
	</header>

	<main class="container mx-auto px-6 mt-28 max-w-4xl">
		<div class="bg-white p-8 rounded-2xl shadow-lg">
			<h2 class="text-2xl font-bold mb-2 text-gray-800"><?php echo htmlspecialchars(ucwords($job_title)) ?></h2>
			<p class="text-gray-600 mb-4"><i class="fas fa-building mr-2"></i><?php echo htmlspecialchars(ucwords($company)) ?></p>
			<hr class="my-4">
			<div class="prose max-w-none text-gray-700">
				<?php echo html_entity_decode($description) ?>
			</div>
		</div>
	</main>

	<!-- Modal footer for uni_modal compatibility -->
	<div class="modal-footer display" style="display:none;">
		<div class="row"><div class="col-md-12"><button class="btn float-right btn-secondary" type="button" data-dismiss="modal">Close</button></div></div>
	</div>

	<script>
	// mobile menu and dropdown toggles
	document.getElementById('mobile-menu-btn')?.addEventListener('click', function(){ const nav = document.getElementById('mobile-nav'); if(nav) nav.classList.toggle('hidden'); });
	document.getElementById('account_settings')?.addEventListener('click', function(e){ e.preventDefault(); const menu = document.getElementById('dropdown-menu'); if(menu) menu.classList.toggle('hidden'); });
	document.getElementById('mobile-account_settings')?.addEventListener('click', function(){ const menu = document.getElementById('dropdown-menu'); if(menu) menu.classList.toggle('hidden'); });
	</script>

	</body>
	</html>
<div class="modal-footer display">
	<div class="row">
		<div class="col-md-12">
			<button class="btn float-right btn-secondary" type="button" data-dismiss="modal">Close</button>
		</div>
	</div>
</div>
<style>
	<?php
	session_start();
	include 'admin/db_connect.php';

	if(isset($_GET['id'])){
		$qry = $conn->query("SELECT * FROM careers where id=".$_GET['id'])->fetch_array();
		foreach($qry as $k =>$v){
			$$k = $v;
		}
	}
	?>
	<!-- Tailwind + FontAwesome (needed for landing-style header utilities) -->
	<script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<!-- Landing-style Navigation (copied from index.php) -->
			success:function(resp){
				if(resp == 1){
					alert_toast("Data successfully saved.",'success')
					setTimeout(function(){
						location.reload()
					},1000)
				}
			}
		})
	})
</script>