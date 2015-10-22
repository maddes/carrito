# Carrito 5.1.0
* Refactored autoloader.
* Added mapping facilities to the `Registry`.
* Added https://github.com/paragonie/random_compat polyfills to use random_str() instead of token().
* Refactored startup.php completely, moved tons of code to respective classes.
* Removed .jsbeautifyrc and updated .editorconfig for PSR-2

# Carrito 5.0.0
* Bumped PHP version required to 5.4.

# Carrito 4.0.0
* Mayor startup overhaul, index.php files left to a simple two-liner.
* `admin/config.php` file no longer needed.
* Lots of logic condensed into `startup.php`, will review later.
* Side-effect: mayor abuse of $registry on `startup.php`.
* Introduced APP constant, values can be 'install', 'admin' or 'catalog'.
* Fixed bug of mis-named theme folder on admin and install.

# Carrito 3.0.1
* Version bump.

# Carrito 3.0.0
* Lazy loading of models added to the Registry Class
* 2000+ (all) calls to $this->load->model() removed.
* Side-effect: Models must have one and only one folder level deep due to name collisions. (lame)
* Version bump because Loader::model() signature changed. (we now receive the IOC hash)

# Carrito 2.0.1
* Removed Vagrant files

# Carrito 2.0.0
* Renamed all .tpl to .php
* Delegated template lookup to the loader class
* Side-effect: admin and install may be themed 0.0

# Carrito 1.0.3
* Version bump

# Carrito 1.0.2
* Versioning fixes

# Carrito 1.0.1
* Removed testing artefacts

# Carrito 1.0.0.0 changelog

* Just changed branding for now… OC v2.1.0.0 = Carrito 1.0.0.0
