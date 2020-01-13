<?php

# Simply returns as associative array of Adivvyo theme options
function getAdivvyoOpts() {
  $adivvyoOpts = array(
		'theme:og:url' => "Site's og:url (needs full URL)",
		'theme:og:image' => "Site's og:image (needs full URL)",
		'theme:og:description' => "Site's og:description",
		'theme:LinkedInURL' => "Site's LinkedIn URL",
		);
  return $adivvyoOpts;
}

# Our settings hook: $Wcms->addListener('settings', 'alterAdmin');
# In here we give users an optional interface (versus config.txt)
# where they can set the Adivvyo theme options.
# Hat tip to Stephan Stanisic's theme-parallax for an example of
# how to do this.
function alterAdmin($args) {
  global $Wcms;
  if(!$Wcms->get('config', 'loggedIn')) return $args;
  $doc = new DOMDocument();
  # Note that mb_convert_encoding() is used here because without it we
  # suffer the problem described by "hanhvansu at yahoo dot com", here:
  # https://www.php.net/manual/en/domdocument.loadhtml.php
  # I suspect that theme-parallax suffers this problem, as well.
  @$doc->loadHTML(mb_convert_encoding($args[0], 'HTML-ENTITIES', "UTF-8"));

  # Place a label identifying these are Adivvyo theme options
  $themeLabel = $doc->createElement("p");
  $themeLabel->setAttribute("class", "subTitle");
  $themeLabel->setAttribute("style", "font-weight:bold; font-variant: normal;");
  $themeLabel->nodeValue = "Adivvyo Theme Options";
  $doc->getElementById("general")->appendChild($themeLabel);

  # Setup style for id=themeInfoDiv
  $infoDivStyle = $doc->createElement("style");
  $infoDivStyle->nodeValue =
	"\n" .
	"#themeInfoDiv a { color: #5bc0de; }\n" .
	"#themeInfoDiv p, ul, li { font-variant: normal; }\n" .
	"";
  $doc->getElementById("general")->appendChild($infoDivStyle);
  # Create a div to provide some information to the user in.
  $infoDiv = $doc->createElement("div");
  $infoDiv->setAttribute("id", "themeInfoDiv");
  $doc->getElementById("general")->appendChild($infoDiv);

  # If config.txt is in place, just tell the user and bail.
  if (acGet('source') === 'config.txt') {
    appendHTML($infoDiv, 
	"<p>There is a config.txt file in place and so it " .
	"is preferentally used versus this settings panel. " .
	"Values set by it are shown below. Please make " .
	"your changes there, or remove that file.</p>");
    $vals_html = "<p><ul>\n";
    $adivvyoOpts = getAdivvyoOpts();
    foreach ($adivvyoOpts as $id => $label) {
      $real_id = preg_replace('/^theme:/', '', $id);
      $vals_html .= "<li><b>$real_id</b> = " . acGet($real_id) . "\n";
    }
    $vals_html .= "</ul></p>\n";
    appendHTML($infoDiv, $vals_html);
    $args[0] = preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $doc->saveHTML());
    return $args;
  }

  # Let the user know that they can use a config.txt file instead.
  appendHTML($infoDiv, 
	"<p>If you would prefer, these values can instead be " .
	"set in a config.txt file placed in this theme's top folder, " .
	"and those values would be used instead of these." .
	"\n" .
	'The format is simple "key = value" style, one per line, ' .
	'and lines can be commented with a leading pound/hash (#).</p>');
  # Give the user some info about the Open Graph protocol.
  appendHTML($infoDiv, 
	"<p>For Open Graph protocol info (og::xxx values), see: " .
	'<a href="https://ogp.me">https://ogp.me</a> and ' .
	'<a href="https://css-tricks.com/essential-meta-tags-social-media">' .
	'https://css-tricks.com/essential-meta-tags-social-media</a></p>');

  # Loop over the getAdivvyoOpts() and made input fields for each one.
  $adivvyoOpts = getAdivvyoOpts();
  foreach ($adivvyoOpts as $id => $label) {
    # Field label
    $real_id = preg_replace('/^theme:/', '', $id);
    $label_html =
	'<p class="subTitle" style="font-size:1.3em; font-variant:normal;">' .
	$label .
	'<span style="font-size:0.8em; font-family: monospace;"> - <b>' .
						$real_id. '</b></span>' .
	'</p>';
    appendHTML($doc->getElementById("general"), $label_html);
    # Field input-box
    $wrapper = $doc->createElement("div");
    $wrapper->setAttribute("class", "change");
    $input = $doc->createElement("div");
    $input->setAttribute("class", "editText");
    $input->setAttribute("data-target", "blocks");
    $input->setAttribute("id", $id);
    $value = '';
    if (isset($Wcms->get('blocks', $id)->content)) {
      $value = $Wcms->get('blocks', $id)->content;
    } else if ($real_id === 'og:url') { // og:url not already set
      // Default to site's root URL when og:url is not already set
      $value = $Wcms->url();
    }
    $input->nodeValue = $value;
    $wrapper->appendChild($input);
    $doc->getElementById("general")->appendChild($wrapper);
    #$doc->getElementById("general")->insertBefore($wrapper, $doc->getElementById("general")->lastChild->nextSibling);
  }

  $args[0] = preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $doc->saveHTML());
  return $args;
}

# Hold the theme conf in a global so that we only load it once.
global $AdivvyoConf;
$AdivvyoConf = loadAdivvyoConf();

# This function loads the global $AdivvyoConf, preferentially from
# from config.txt if it exits, and if not, from wCMS settings that
# the theme adds.
function loadAdivvyoConf() {
  global $Wcms;
  $conf = array( 'loaded' => true );
  $conf_file = $Wcms->rootDir .
      '/themes/' . $Wcms->get('config', 'theme') . '/config.txt';

  # If config.txt does not exist, load from wCMS settings
  if (! file_exists($conf_file)) {
    $conf['source'] = 'wCMS_settings';
    $adivvyoOpts = getAdivvyoOpts();
    foreach ($adivvyoOpts as $id => $label) {
      # We prepend 
      $real_id = preg_replace('/^theme:/', '', $id);
      $conf[$real_id] = $Wcms->get('blocks', $id)->content;
    }
    return $conf;
  }

  # Load from config.txt
  $conf['source'] = 'config.txt';
  $file_lines = file($conf_file);
  foreach ($file_lines as $line) {
    if (strlen($line) < 3) { continue; } # empty or impossible lines
    if (preg_match("/^\s*#/", $line)) { continue; } # comments
    list($key, $val) = preg_split("/\s*=\s*/", $line, 2);
    $val = rtrim($val);
    $conf[$key] = $val;
    #error_log("*$key* = *$val*");
  }
  return $conf;
}

# Gets an $AdivvyoConf value or empty string if it does not exist
function acGet($key) {
  global $AdivvyoConf;
  if (! (array_key_exists('loaded', $AdivvyoConf) and $AdivvyoConf['loaded'])) {
    $AdivvyoConf = loadAdivvyoConf();
  }
  if (array_key_exists($key, $AdivvyoConf)) {
    return $AdivvyoConf[$key];
  }
  return '';
}

# Returns the HTML for the footer's LinkedIn link.
# Uses $AdivvyoConf settings from theme's config.txt
function getSiteLinkedInLink() {
  global $Wcms;
  $LI_URL = acGet('LinkedInURL');
  $icon = 'LinkedInLogo.png';
  $path = 'img/';
  $icon_url = $Wcms->asset($path . $icon);
  $icon_on_disk = $Wcms->rootDir .
      '/themes/' . $Wcms->get('config', 'theme') . '/' . $path . $icon;
  if (strlen($LI_URL) and file_exists($icon_on_disk)) {
    return '&bull;&nbsp;' .
	'<a href="'.$LI_URL.'"><img src="'.$icon_url.'" height="20px"></a>';
  }
  return '';
}

# The $Wcms->menu() hook for the Adivvyo theme.
# $Wcms->addListener('menu', 'getMenuAdivvyo');
function getMenuAdivvyo($args) {
  global $Wcms;
  $output = '';
  foreach ($Wcms->get('config', 'menuItems') as $item) {
    if ($item->visibility === 'hide') {
      continue;
    }
    # Adds class "nav-default-page" which can be used in the CSS
    # to hide the "homepage" menu option.
    $extra_class = '';
    if ($Wcms->get('config','defaultPage') === $item->slug) {
      $extra_class .= 'nav-default-page ';
    }
    # To support .../theme/img/menu/<pagename>.png icons for menu items
    $icon = $item->slug . ".png";
    $icon_url = $Wcms->asset("img/menu/" . $icon);
    $icon_on_disk = $Wcms->rootDir .
	'/themes/' . $Wcms->get('config', 'theme') . '/img/menu/' . $icon;
    $menu_content = $item->name;
    if (file_exists($icon_on_disk)) {
      $extra_class .= 'nav-icon ';
      $menu_content = '<img class="' .$extra_class. '" src="' .$icon_url. '">';
    }
    $output .= '<li class="' .
			$extra_class .
			($Wcms->currentPage === $item->slug ? 'active ' : '') .
			 'nav-item">' .
		'<a class="nav-link" href="' .
			wCMS::url($item->slug) . '">' . $menu_content . '</a>' .
		'</li>';
  }
  $args[0] = $output;
  return $args;
}

# Convenience and code-reduction function.
function appendHTML(DOMNode $parent, $source) {
    $tmpDoc = new DOMDocument();
    $tmpDoc->loadHTML($source);
    foreach ($tmpDoc->getElementsByTagName('body')->item(0)->childNodes as $node) {
        $node = $parent->ownerDocument->importNode($node, true);
        $parent->appendChild($node);
    }
}

?>

