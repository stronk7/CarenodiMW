# CarenodiMW

CarenodiMW is a mediawiki extension that adds the [rel=canonical](https://en.wikipedia.org/wiki/Canonical_link_element) and the [meta=refresh](https://en.wikipedia.org/wiki/Meta_refresh) tags to pages which wikitext matches a regexp-based configuration.

Note that this extension manages both features as HTML tags, not as HTTP headers.

## Installation

- Current version requires `MediaWiki >=1.39`. Use release [v0.99.2](https://github.com/stronk7/CarenodiMW/releases/tag/v0.99.2) for `MediaWiki >=1.31 <1.36`.
- [Download and unzip it](https://github.com/stronk7/CarenodiMW/releases) (or clone this git repo).
- Copy the folder as `CarenodiMW` within the `extensions`directory of your mediawiki site.

## Usage

### Enable the extension

Edit `LocalSettings.php` and add the following, configuring the settings to suit your needs.

```php
wfLoadExtension('CarenodiMW'); // Load me!

// Base URL to be used for all the canonical and refresh tags.
$wgCarenodiMW_base = 'https://example.com';

// Regular expression to extract the path for the canonical tags.
// The first captured group in the regular expression will be used.
// Any empty value disables the feature globally.
$wgCarenodiMW_canonical_regexp = '/new location is (.*) this/';

// Regular expression to extract the path for the refresh tags.
// The first captured group in the regular expression will be used)
// Any empty value disables the feature globally.
// (note that, normally, it will be the same than the canonical one
// but it's possible to specify different one).
$wgCarenodiMW_refresh_regexp = '/new location is (.*) this/';

// Number of seconds to perform the refresh to new URL (0 = immediate).
$wgCarenodiMW_refresh_wait = 5;

// Enable separate debug for the extension
// $wgDebugLogGroups['CarenodiMW'] = '/tmp/CarenodiMW.log';

```

## Contributing

[Pull requests](https://github.com/stronk7/CarenodiMW/pulls) are welcome. For major changes, please [open an issue](https://github.com/stronk7/CarenodiMW/issues) first to discuss what you would like to change.

## License and copyright

[BSD 3-Clause](https://choosealicense.com/licenses/bsd-3-clause/) - Copyright (c) 2022 onwards, Eloy Lafuente (stronk7).
