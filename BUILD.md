# Building/Installing From Source

## Build extension package

Within the root of the project, you can run:

    bin/package.php composer.json

This results in a tarball being created within `target/` named `SheerID_Verify-${version}.tgz`

 > NOTE: Mac OS users may need to override the name of script used for hashing (part of composer build), by default the script will use `md5sum`. This can be done by setting `HASH_EXEC` environment variable, for example:

    HASH_EXEC=/sbin/md5 bin/package.php composer.json

## Install from command line

Within `$MAGENTO_HOME` (eg: `/var/www/magento`) on your magento instance, copy the built tarball and run the following to install from your build:

    ./mage install SheerID_Verify-${version}.tgz

If the extension has already been installed (for example from community Magento Connect channel), you can uninstall with the following command:

    ./mage uninstall community SheerID_Verify
