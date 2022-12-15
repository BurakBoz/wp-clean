# wp-clean.php
#### WordPress Cleaner | WordPress Refresher | WordPress Downloader | WordPress Fixer

#### Dependencies: cURL extension and some passion.

#### Multi Purpose WordPress Cleaner & Refresher
1. WordPress downloader
2. WordPress plugin downloader
3. WordPress theme downloader
4. WordPress cleaner
5. WordPress refresher
6. WordPress fixer
7. Clean install archive generator

#### Usage:

### Shell commands:
````
# Download script with wget
wget --no-check-certificate https://raw.githubusercontent.com/BurakBoz/wp-clean/main/wp-clean.php -O wp-clean.php

# Download script with curl
curl -k https://raw.githubusercontent.com/BurakBoz/wp-clean/main/wp-clean.php -o wp-clean.php

# Download with wget and run with self destruct mode
wget --no-check-certificate https://raw.githubusercontent.com/BurakBoz/wp-clean/main/wp-clean.php -O wp-clean.php && php wp-clean.php delete

# Download with wget and run with self destruct and bypass warning mode
wget --no-check-certificate https://raw.githubusercontent.com/BurakBoz/wp-clean/main/wp-clean.php -O wp-clean.php && php wp-clean.php delete force

# Download with curl and run with self destruct mode
curl -k https://raw.githubusercontent.com/BurakBoz/wp-clean/main/wp-clean.php -o wp-clean.php && php wp-clean.php delete

# Download with curl and run with self destruct and bypass warning mode
curl -k https://raw.githubusercontent.com/BurakBoz/wp-clean/main/wp-clean.php -o wp-clean.php && php wp-clean.php delete force

# bypass warning message
php wp-clean.php force

# delete it self after finish
php wp-clean.php delete

# bypass warning and delete itself after finish
php wp-clean.php force delete

# install plugins (do not use any space here.)
php wp-clean.php plugins="akismet,litespeed-cache"

# install themes (do not use any space here.)
php wp-clean.php themes="astra,twentytwentytwo"

# Complex example
php wp-clean.php force delete plugins="akismet,litespeed-cache" themes="astra,twentytwentytwo"
````

### Browser execution:
````
# bypass warning message
https://example.com/wp-clean.php?force=

# delete it self after finish
https://example.com/wp-clean.php?delete=

# bypass warning and delete itself after finish
https://example.com/wp-clean.php?force=&delete=

# install plugins (do not use any space here.)
https://example.com/wp-clean.php?plugins=akismet,litespeed-cache

# install themes (do not use any space here.)
https://example.com/wp-clean.php?themes=astra,twentytwentytwo
````

### WARNING! READ THIS! | UYARI! BENI OKU! | WARNUNG! LESEN SIE DIES!
#### If you don't have any installation it will download, extract and clean latest WordPress for you.

It deletes all default themes, plugins, translations files in latest.zip file by Default.

This means you have to upload (or choose with param) your own themes, plugins if you are using for clean install.

Of course you can configure this by changing $deleteFolders list.


#### If you have an installation on current directory:
Please update latest version to your WordPress if you can or you can broke your system.

If you can't able to update your wordpress you can change your $downloadUrl value to your version.

#### It won't touch your themes / plugins / translations or anything in wp-content folder.
It will clean / refresh your wordpress core files.

## This script can fix hacked, broken, untrusted WordPress installations.

Usually hackers change original files or put web shells in wp-admin or wp-includes and also wp-content dirs.
### This script only cleans & refreshes WordPress core installation.

It can clean or automaticly download plugins and themes when you select plugins="plugin-name" and themes="theme-name" parameters. 
But becareful it will delete the original directories on your wp-content/plugins/plugin-name and wp-content/themes/theme-name

You have to clean your wp-content folder and themes / plugins / uploads folders by your self!
## It won't touch these files and folders by default:
wp-content folder .htaccess and wp-config.php file on current dir OR document root

## It will replace these files and folders with clean ones by default:
wp-admin and wp-includes folder
index.php, xmlrpc.php and wp-*.php files EXCEPT wp-config.php and .htaccess

### It will create wp-config.php and .htaccess backups and restore backups after cleaning your installation.
So you have to check those files if you hacked or have any issue with your installation.

#### You can run it either by shell (command line) or web interface both of them are works seamlessly.

It won't create clean archive by default you can enable by setting $repack = true;
You can use / distribute the clean archive.

It can delete itself after finished the task you can set it by changing $deleteAfterFinish = true;
