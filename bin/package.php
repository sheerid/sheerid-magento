#!/usr/bin/php -q
<?php

$MD5 = getenv('HASH_EXEC');
if (!$MD5) {
	$MD5 = 'md5sum';
}

$argv = $_SERVER['argv'];

if (count($argv) < 2) {
   echo "Usage: ${argv[0]} COMPOSER_MANIFEST\n";
   exit(1);
}

function get_src_base($target_name) {
	if ('magecommunity' == $target_name) {
		return 'app/code';
	} else if ('mageetc' == $target_name) {
		return 'etc';
	} else if ('magelocale' == $target_name) {
		return 'locale';
	} else if ('magedesign' == $target_name) {
		return 'app/design';
	} else if ('mageskin' == $target_name) {
		return 'skin';
	}
	return '.';
}

function get_target_base($target_name) {
	if ('magecommunity' == $target_name) {
		return 'app/code/community';
	} else if ('mageetc' == $target_name) {
		return 'app/etc';
	} else if ('magelocale' == $target_name) {
		return 'app/locale';
	} else {
		return get_src_base($target_name);
	}
}

function add_tree($node, $tree) {
	foreach ($tree as $k => $t) {
		if (is_string($k)) {
			$d = $node->addChild('dir');
			$d->addAttribute('name', $k);
			add_tree($d, $t);
		} else {
			$f = $node->addChild('file');
			$f->addAttribute('name', $t['name']);
			$f->addAttribute('hash', $t['hash']);
		}
	}
}

function ensure_dir($path) {
	if (!is_dir($path)) {
		mkdir($path, 0777, true);
	}
}

function copy_file($from, $to) {
	$target_dir = preg_replace(",/[^/]+$,", "", $to);
	ensure_dir($target_dir);
	exec("cp $from $to");
}

# ensure working dirs exist
exec('rm -rf build target');
ensure_dir('target');
ensure_dir('build');

$manifest_file = $argv[1];
$manifest = json_decode(file_get_contents($manifest_file));
$version = exec('/bin/sh -c "git tag | sort -n | tail -n1"');

$mconnect = $manifest->extras->magento_connect;

$package = new SimpleXMLElement('<package/>');
$package->addChild('name', str_replace("/", "_", $manifest->name));
$package->addChild('version', $version);
$package->addChild('stability', $manifest->{'minimum-stability'});
$lic = $package->addChild('license', $manifest->license);
$lic->addAttribute('uri', $manifest->license_uri);
$package->addChild('channel', $mconnect->channel);
$package->addChild('extends');
$package->addChild('summary');
$package->addChild('description', $manifest->description);
$package->addChild('notes');

$authors = $package->addChild('authors');
foreach ($manifest->authors as $a) {
	$email_parts = preg_split('/@/', $a->email);
	$author = $authors->addChild('author');
	$author->addChild('name', $a->name);
	$author->addChild('user', $email_parts[0]);
	$author->addChild('email', $a->email);
}

$package->addChild('date', date('Y-m-d'));
$package->addChild('time', date('H:i:s'));

$targets = array();
foreach ($mconnect->content as $content) {
	if (!array_key_exists($content->type, $targets)) {
		$targets[$content->type] = array();
	}
	$targets[$content->type][] = $content;
}

$contents = $package->addChild('contents');

foreach ($targets as $target_name => $refs) {
	$tgt = $contents->addChild('target');
	$tgt->addAttribute('name', $target_name);
	foreach ($refs as $ref) {
		$files = array();
		$src_base = get_src_base($target_name);
		$target_base = get_target_base($target_name);
		ensure_dir("build/$target_base");
		$path = $ref->path;
		if ('dir' == $ref->structure) {
			$cmd = "cd $src_base; /usr/bin/find $path -type f ! -path '*/.git/*'";
			$output = array();
			exec("/bin/sh -c \"$cmd\"", $output);
			$files = array_merge($files, $output);
		} else if ('file' == $ref->structure) {
			$files[] = $ref->path;
		}
		sort($files);
		$tree = array();
		foreach ($files as $file) {
			copy_file("$src_base/$file", "build/$target_base/$file");
			$cur = &$tree;
			$parts = explode('/', $file);
			while ($part = array_shift($parts)) {
				if (count($parts)) {
					if (!array_key_exists($part, $cur)) {
						$cur[$part] = array();
					}	
					$cur = &$cur[$part];
				} else {
					$output = explode(' ', exec("$MD5 $src_base/$file"));
					$cur[] = array("name" => $part, "hash" => array_pop($output));
				}
			}
		}
		add_tree($tgt, $tree);
	}
}

$package->addChild('compatible');
$deps = $package->addChild('dependencies');
$reqd = $deps->addChild('required');
$php = $reqd->addChild('php');
$php->addChild('min', $mconnect->php_min);
$php->addChild('max', $mconnect->php_max);

$dom = new DOMDocument("1.0");
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($package->asXML());

file_put_contents('build/package.xml', $dom->saveXML());

exec("sh -c 'cd build; COPYFILE_DISABLE=1 tar czf ../target/SheerID_Verify-$version.tgz *'");