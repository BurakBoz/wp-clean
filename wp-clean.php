<?php
/**
 * @Author          : Burak Boz
 * @WebSite         : https://github.com/BurakBoz
 * @Script          : wp-clean.php
 * @License         : MIT
 * @Dependencies    : cURL extension and some passion.
 * @Usage           :
 *
 * Shell commands:
 * wp-clean.php force # bypass warning message
 * wp-clean.php delete # delete it self after finish
 * wp-clean.php force delete # bypass warning and delete itself after finish
 *
 * Browser:
 * wp-clean.php?force=  # bypass warning message
 * wp-clean.php?delete= # delete it self after finish
 * wp-clean.php?force=&delete= # bypass warning and delete itself after finish
 *
 * @Description     : Multi Purpose WordPress Cleaner & Refresher
 * 1. WordPress downloader
 * 2. WordPress cleaner
 * 3. Clean install archive generator
 *
 *
 * WARNING! READ THIS! | UYARI! BENI OKU! | WARNUNG! LESEN SIE DIES!
 *
 *
 * If you don't have any installation it will download, extract and clean latest WordPress for you.
 * It deletes all default themes, plugins, translations files in latest.zip file by Default.
 * This means you have to upload your own themes, plugins if you are using for clean install.
 * Of course you can configure this by changing $deleteFolders list.
 *
 * If you have an installation on current directory:
 * Please update latest version to your WordPress if you can or you can broke your system.
 * If you can't able to update your wordpress you can change your $downloadUrl value to your version.
 *
 * It won't touch your themes / plugins / translations or anything in wp-content folder.
 * It will clean / refresh your wordpress core files.
 *
 * This script can fix hacked, broken, untrusted WordPress installations.
 *
 * Usually hackers change original files or put web shells in wp-admin or wp-includes and also wp-content dirs.
 *
 * This script only cleans & refreshes WordPress core installation.
 *
 * You have to clean your wp-content folder and themes / plugins / uploads folder by your self!
 *
 * It won't touch these files and folders by default:
 * 1. wp-content folder
 * 2. .htaccess and wp-config.php file on current dir OR document root
 *
 * It will replace these files and folders with clean ones by default:
 * wp-admin and wp-includes folder
 * index.php, xmlrpc.php and wp-*.php files EXCEPT wp-config.php and .htaccess
 *
 * It will create wp-config.php and .htaccess backups and restore backups after cleaning your installation.
 * So you have to check those files if you hacked or have any issue with your installation.
 *
 * You can run it either by shell (command line) or web interface both of them are works seamlessly.
 *
 * It won't create clean archive by default you can enable by setting $repack = true;
 * You can use / distribute the clean archive.
 *
 * It can delete itself after finished the task you can set it by changing $deleteAfterFinish = true;
 *
 * If you are able to read code, please read before using it. It only ~430 lines.
 *
 */

@ignore_user_abort(false);
@ob_start();
@ob_implicit_flush(true);
@session_write_close();
@set_time_limit(3600);
@ini_set('zlib.output_compression',0);
@ini_set('implicit_flush',1);
@ini_set('output_buffering', 0);
@header("Content-Type: text/plain;charset=utf8");
//@header("Transfer-Encoding: chunked");
@header("Content-Encoding: none");
!isset($argv) && $argv = [];

/** BEGIN CONFIGURATION */
// You can change this with your fixed version or your own language download link.
$downloadUrl      = "https://wordpress.org/latest.zip";
/* Examples:
https://wordpress.org/latest.zip
https://wordpress.org/wordpress-5.9.3.zip
https://tr.wordpress.org/latest-tr_TR.zip
https://tr.wordpress.org/wordpress-5.9.3-tr_TR.zip
*/

// do not change if you don't know what you are doing.
$path             = __DIR__ . DIRECTORY_SEPARATOR . "wordpress" . DIRECTORY_SEPARATOR;

// It will delete it self after finished if you set this true. true|false
$deleteAfterFinish = false;

// It deletes this files on wordpress.org/latest.zip downloaded archive. Not the ones on production site.
$deleteFiles      = [
    "gpl.txt",
    "GPL.txt",
    "GPL.TXT",
    "changelog.txt",
    "changelog.md",
    "changelog",
    "CHANGELOG",
    "CHANGELOG.md",
    "CHANGELOG.MD",
    "license.txt",
    "license.commercial.txt",
    "LICENSE",
    "LICENSE.txt",
    "LICENSE.md",
    "LICENSE.MD",
    "readme.html",
    "readme.txt",
    "readme.md",
    "readme",
    "README.md",
    "README.MD",
    "README",
    ".DS_Store",
    "Thumbs.db",
    "thumbs.db",
];

// It deletes this folders on wordpress.org/latest.zip downloaded archive. Not the ones on production site.
$deleteFolders    = [
    "wp-content/mu-plugins",
    "wp-content/uploads",
    "wp-content/uploads/" . date("Y"),
    "wp-content/uploads/" . date("Y/m"),
    "wp-content/languages",
    "wp-content/upgrade",
];

// Backup and Restore these files if exists in current dir.
$backupFiles      = [
    "wp-config.php",
    ".htaccess",
];

// Prevent auto indexing, create empty index.php files.
$indexSource      = "<?php\n// Silence is gold";

// Original WordPress archive name.
$downloadFileName = "latest.zip";

// Create clean .tar.gz file for distribution. true|false
$repack = false;

// Clean archive filename.
$repackFilename = "latest.tar.gz";

/** END CONFIGURATION */

// delete configuration by param
if(isset($_GET["delete"]) xor in_array("delete", $argv, true))
{
    $deleteAfterFinish = true;
}
if(@file_exists("wordpress") && (!isset($_GET["force"]) xor in_array("force", $argv, true)))
{
    exit("Oh! No! wordpress/ directory exists.
If this is a working WordPress installation you must backup or rename the folder otherwise you will loss data.
You can bypass this warning message with opening /{$_SERVER["PHP_SELF"]}?force on browser.
Or on shell by typing this command:
php {$_SERVER["PHP_SELF"]} force
");
}
sendBuffer("Multi Purpose WordPress Cleaner & Refresher" . PHP_EOL);
sendBuffer("For more visit https://github.com/BurakBoz and https://gist.github.com/BurakBoz" . PHP_EOL);
if($deleteAfterFinish)
{
    sendBuffer("Self destruct mode is active! When this job is finished this file will delete it self." . PHP_EOL);
}
register_shutdown_function(function () use ($downloadFileName, $deleteAfterFinish){
    @unlink($downloadFileName);
    rrmdir(__DIR__ . "/wordpress/");
    if($deleteAfterFinish === true)
    {
        @unlink(__FILE__);
    }
});
clearstatcache(false, $downloadFileName);

// Download latest zip if not exists
if(!file_exists($downloadFileName))
{
    if(curlSave($downloadUrl, $downloadFileName))
    {
        sendBuffer("Latest WordPress '$downloadFileName' downloaded from $downloadUrl" . PHP_EOL);
        // Extract from archive
        if(extractArchive($downloadFileName, __DIR__))
        {
            sendBuffer("Zip archive extracted successfully." . PHP_EOL);
            if(@unlink($downloadFileName))
            {
                sendBuffer("Archive file deleted." . PHP_EOL);
            }
        }
        else
        {
            sendBuffer("Zip archive extraction error!" . PHP_EOL);
            exit();
        }
    }
    else
    {
        sendBuffer("Cannot download latest WordPress from $downloadUrl. Please download manually to '$downloadFileName'" . PHP_EOL);
        exit();
    }
}

// Delete defaults
@unlink($path . "wp-content/plugins/hello.php");
rrmdir($path . "wp-content/plugins/akismet/");
rrmdir($path . "wp-content/themes/twentytwentythree/");
rrmdir($path . "wp-content/themes/twentytwentytwo/");
rrmdir($path . "wp-content/themes/twentytwentyone/");
rrmdir($path . "wp-content/themes/twentytwenty/");

// Download plugins
downloadPluginOrTheme($path . "wp-content" . DIRECTORY_SEPARATOR . "plugins" . DIRECTORY_SEPARATOR, arg("plugins"));

// Download themes
downloadPluginOrTheme($path . "wp-content" . DIRECTORY_SEPARATOR . "themes" . DIRECTORY_SEPARATOR, arg("themes"), "theme");

$deleteFolders = array_unique(array_filter(array_map(function ($item) use ($path) {
    $path = rtrim($path,"/") . DIRECTORY_SEPARATOR . str_replace("/", DIRECTORY_SEPARATOR, $item);
    !is_dir($path) && @mkdir($path, 0755, true);
    $path = realpath($path);
    return $path ? $path : null;
    }, $deleteFolders)));

$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);

foreach ($objects as $name => $file)
{
    @clearstatcache($path, $name);
    if (in_array($file->getBasename(), [
        ".",
        "..",
    ], true))
    {
        continue;
    }

    // Delete files
    if ($file->isFile() && in_array($file->getBasename(), $deleteFiles, true))
    {
        sendBuffer("Deleted: $name" . PHP_EOL);
        @unlink($name);
    }

    // Create index.php if not exists.
    $index = $name . DIRECTORY_SEPARATOR . "index.php";
    if($file->isDir() && !file_exists($index))
    {
        file_put_contents($index, $indexSource,FILE_BINARY);
        sendBuffer("Created: ". $index . PHP_EOL);
    }

    if ($file->isDir() && in_array($name, $deleteFolders))
    {
        sendBuffer("Cleaned: $name" . PHP_EOL);
        rrmdir($name);
        @mkdir($name, 0755, true);
        // Create index.php if not exists.
        file_put_contents($name . DIRECTORY_SEPARATOR . "index.php", $indexSource,FILE_BINARY);
    }
}

// File patches
patchFile($path . "wp-settings.php", function ($src){
    return str_replace('<?php', '<?php if(!defined("ABSPATH")) exit();', $src); // prevent display errors
});

// Create index.php for empty directories.
file_put_contents($path . "wp-content/index.php", $indexSource,FILE_BINARY);
file_put_contents($path . "wp-content/themes/index.php", $indexSource,FILE_BINARY);
file_put_contents($path . "wp-content/plugins/index.php", $indexSource,FILE_BINARY);
// Create .htaccess file for routing.
if(file_put_contents($path . ".htaccess", <<<HTACCESS
# BEGIN WordPress
# The directives (lines) between "BEGIN WordPress" and "END WordPress" are
# dynamically generated, and should only be modified via WordPress filters.
# Any changes to the directives between these markers will be overwritten.
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>

# END WordPress

HTACCESS, FILE_BINARY))
{
    sendBuffer("Created .htaccess file" . PHP_EOL);
}

if($repack)
{
    sendBuffer("Creating $repackFilename archive." . PHP_EOL);
    try
    {
        $tar = str_replace(".gz","",$repackFilename);
        $a = new PharData($tar);
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($objects as $name => $file)
        {
            if (in_array($file->getBasename(), [".",".."], true))
            {
                continue;
            }
            $archiveName = @explode(DIRECTORY_SEPARATOR."wordpress".DIRECTORY_SEPARATOR,str_replace(["/","\\"],DIRECTORY_SEPARATOR, $name), 2)[1] ?? "";
            sendBuffer("Adding: ".$archiveName . PHP_EOL);
            if($file->isDir())
            {
                $a->addEmptyDir($archiveName);
            }
            else
            {
                $a->addFile($name,$archiveName);
            }
        }
        $a->compress(Phar::GZ);
        @unlink($tar);
        sendBuffer("$repackFilename archive is ready for distribution." . PHP_EOL);
    }
    catch (Exception $e)
    {
        sendBuffer("RepackException : " . $e . PHP_EOL);
    }
}

foreach ($backupFiles as $original)
{
    $backup = $original.".backup".date("dmY");
    if(file_exists($original))
    {
        if(rename($original, $backup))
        {
            sendBuffer("Created backup of $original file." . PHP_EOL);
        }
        else
        {
            sendBuffer("Error! Cannot rename $original file to $backup" . PHP_EOL);
        }
    }
}

if(file_exists("wp-admin"))
{
    sendBuffer("Deleting current wp-admin folder." . PHP_EOL);
    rrmdir("wp-admin");
}

if(file_exists("wp-includes"))
{
    sendBuffer("Deleting current wp-includes folder." . PHP_EOL);
    rrmdir("wp-includes");
}

sendBuffer("Moving clean files to current directory." . PHP_EOL);
move($path, __DIR__);

foreach ($backupFiles as $original)
{
    $backup = $original.".backup".date("dmY");
    if(file_exists($backup))
    {
        @unlink($original);
        sendBuffer("Restoring $backup to $original" . PHP_EOL);
        if(rename($backup,$original))
        {
            sendBuffer("$backup restored." . PHP_EOL);
        }
        else
        {
            sendBuffer("Error! Cannot rename $backup file to $original" . PHP_EOL);
        }
    }
}

sendBuffer("Ready to go.");

@flush();
@ob_end_flush();

function rrmdir($dir)
{
    if (!is_dir($dir)) return;
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files as $fileinfo)
    {
        $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
        @$todo($fileinfo->getRealPath());
    }
    @rmdir($dir);
}

function move($src, $dst)
{
    $dir = @opendir($src);
    @mkdir($dst);
    while(false !== ( $file = @readdir($dir)) )
    {
        if (( $file != '.' ) && ( $file != '..' ))
        {
            if ( is_dir($src . '/' . $file) )
            {
                move($src . '/' . $file,$dst . '/' . $file);
            }
            else
            {
                @unlink($dst . '/' . $file);
                @rename($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    @closedir($dir);
}

function curlSave($url, $file, array $options = [])
{
    $fp = fopen($file, 'wb');
    if(!is_resource($fp)) return false;
    $ch = curl_init();
    $defaultOptions = [
        CURLOPT_URL => $url,
        CURLOPT_FILE => $fp,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.2661.102 Safari/537.36",
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ];
    curl_setopt_array($ch,array_replace($defaultOptions,$options));
    $status = curl_exec($ch);
    $hs = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($fp);
    clearstatcache(true, $file);
    clearstatcache(false, $file);
    if(filesize($file)<1)
    {
        @unlink($file);
    }
    return $status == true && in_array($hs,[200,301,302]);
}

function extractArchive($file, $path = __DIR__, $files = null)
{
    try
    {
        $phar = new PharData($file);
        $phar->extractTo($path, $files, true); // extract all files and overwrite
        return true;
    }
    catch (Exception $e)
    {
        echo $e->getMessage() . PHP_EOL;
        return false;
    }
}

function sendBuffer($chunk)
{
    if(PHP_SAPI === "cli")
    {
        echo trim($chunk) . PHP_EOL;
    }
    else
    {
        echo str_pad($chunk, 4097);
    }
    @flush();
    @ob_flush();
}

function patchFile($file, callable $callback = null)
{
    if(file_exists($file) and is_callable($callback))
    {
        $src = file_get_contents($file);
        if($src !== false) return file_put_contents($file, $callback($src), FILE_BINARY);
    }
    return false;
}

function downloadPluginOrTheme($path, $name, $type = "plugin", $storeUrl = "https://downloads.wordpress.org/%s/%s.zip")
{
    if (empty($name))
    {
        return;
    }
    if(!in_array($type,["plugin", "theme"]))
    {
        return;
    }
    sendBuffer("Downloading {$type}s.." . PHP_EOL);
    $path = rtrim($path,"/\\") . DIRECTORY_SEPARATOR;
    if(!is_dir($path)) @mkdir($path, 0755, true);
    $names = explode(",",$name);
    $names = array_filter(array_map('trim',!is_array($names) ? [$name] : $names));
    foreach ($names as $name)
    {
        $downloadUrl = sprintf($storeUrl, $type, $name);
        $filename = basename($downloadUrl);
        $archiveFile = $path . $filename;
        sendBuffer("--------------" . PHP_EOL);
        sendBuffer("$type: $name downloading from $downloadUrl to $archiveFile" . PHP_EOL);
        if(curlSave($downloadUrl, $archiveFile))
        {
            $archiveFile = realpath($archiveFile);
            if($archiveFile === false || filesize($archiveFile)<1)
            {
                sendBuffer("$type: $name ($downloadUrl) download error! Skipping..");
                continue;
            }
            sendBuffer("Download complete. Extracting..." . PHP_EOL);
            if(extractArchive($archiveFile, $path))
            {
                sendBuffer("Extraction complete. Deleting file.." . PHP_EOL);
                rrmdir(__DIR__ . "/wp-content/{$type}s/$name/");
            }
        }
        else
        {
            sendBuffer("Download error! Skipping $name $type.." . PHP_EOL);
        }
        if(@unlink($archiveFile))
        {
            sendBuffer("$filename is deleted." . PHP_EOL);
        }
    }
    sendBuffer("--------------" . PHP_EOL);
}

function arg($name)
{
    global $argv;
    $argv = array_map('trim',!is_array($argv) ? [] : $argv);
    foreach ($argv as $arg)
    {
        if(preg_match('/^'.preg_quote($name, "/").'[ =:](.*?)$/is', $arg, $matches))
        {
            return trim($matches[1],"\"'");
        }
    }
    return isset($_GET[$name]) ? $_GET[$name] : null;
}
