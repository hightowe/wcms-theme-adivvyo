<!DOCTYPE html>
<?php
global $Wcms;
if(defined('VERSION') && !defined('version'))
	define('version', VERSION);
if(version<'2.0.0')
	defined('INC_ROOT') OR die('Direct access is not allowed.');

$theme_css = 'css/style-adivvyo.css';
$Wcms->addListener('menu', 'getMenuAdivvyo'); // in functions.php
$Wcms->addListener('settings', 'alterAdmin'); // in functions.php
?>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="<?=wCMS::page('description')?>">
	<meta name="keywords" content="<?=wCMS::page('keywords')?>">
	<meta property="og:type" content="website">
	<meta property="og:image" content="<?=acGet('og:image')?>">
	<meta property="og:url" content="<?=acGet('og:url')?>">
	<meta property="og:description" content="<?=acGet('og:description')?>">
	<meta property="og:title" content="<?=wCMS::get('config','siteTitle')?>">
	<meta property="og:site_name" content="<?=wCMS::get('config','siteTitle')?>">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<link id="stylesheet" rel="stylesheet" href="<?=wCMS::asset($theme_css)?>">
	<link href='https://fonts.googleapis.com/css?family=Baumans' rel='stylesheet'>
	<?=wCMS::css()?>
	<?php
	# If /favicon.xxx exists, insert a <link> for it
        foreach (array('png','jpg','gif','ico') as $ext) {
	  $ico_file = '/favicon.' . $ext;
	  $ico_path = $Wcms->rootDir . $ico_file;
	  if (file_exists($ico_path)) {
	    echo "\n";
	    echo "\t<link rel=\"icon\" type=\"image/$ext\" href=\"$ico_file\">";
	    echo "\n";
	    break; # Break out of the foreach loop
	  }
        }
	?>
	<title><?=wCMS::get('config','siteTitle')?> - <?=wCMS::page('title')?></title>
</head>
<body>
	<?=wCMS::alerts()?>
	<?=wCMS::settings()?>

	<nav class="navbar navbar-default">
		<div class="container css3-shadow colorBackground">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#menu-collapse">
					<span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="<?=wCMS::url()?>">
				<?php
    # If there is a title_logo.png in place, use it, else the siteTitle
    $logo_file = 'title_logo.png';
    $logo_url = $Wcms->asset("img/" . $logo_file);
    $logo_on_disk = $Wcms->rootDir .
        '/themes/' . $Wcms->get('config', 'theme') . '/img/' . $logo_file;
    if (file_exists($logo_on_disk)) {
      echo "<img class=\"navbar-brand title-logo\" src=\"$logo_url\">";
    } else {
      echo wCMS::get('config','siteTitle');
    }
				?>
				</a>
			</div>
			<div class="collapse navbar-collapse" id="menu-collapse">
				<ul class="nav navbar-nav navbar-right">
					<?=wCMS::menu()?>
				</ul>
			</div>
		</div>
	</nav>

	<div class="container-fluid">
		<div class="row-fluid">
			<div class="box css3-shadow whiteBackground col-lg-12 text-left padding40 d-flex" style="min-height:85vh">
				<?=wCMS::page('content')?>

			</div>
		</div>
	</div>

	<div class="container footer colorBackground">
		<div class="whiteFont col-lg-12 text-center">
			<!-- ?=wCMS::block('subside')? -->
			<?=wCMS::footer()?>
			<?=getSiteLinkedInLink()?>
			</div>
	</div>
	<script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/jquery.autosize/3.0.17/autosize.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<?=wCMS::js()?>
</body>
</html>
