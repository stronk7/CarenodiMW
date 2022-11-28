<?php

namespace MediaWiki\Extension;

use ConfigFactory;
use ContentHandler;
use MediaWiki\Revision\RevisionRecord;
use OutputPage;
use ParserOutput;
use Skin;
use WikiPage;

/**
 * Main CarenodiMW class implementation.
 *
 * @copyright 2022 onwards Eloy Lafuente (stronk7)
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 */
class CarenodiMW {

    /**
     * Add the rel=canonical and the meta=refresh tags to pages matching a regexp-based configuration.
     *
     * @param OutputPage &$out
     * @param ParserOutput &$parser
     */
    public static function onOutputPageParserOutput(OutputPage &$out, ParserOutput &$parser) {

        // We are going to apply the changes to existing article pages.
        if (!$out->getTitle()->exists()) { // Non-existing page, nothing to do.
            return true;
        }
        if ($out->getTitle()->isTalkPage()) { // Talk pages, nothing to do.
            return true;
        }
        if (!$out->isArticle()) { // Non-article page, nothing to do.
            return true;
        }
        if ($out->getPageTitle() !== $out->getTitle()->getText()) { // Page and title don't match, nothing to do.
            return true;
        }

        wfDebugLog('CarenodiMW', __METHOD__ . ' called for "' . $out->getPageTitle() . '"');

        // Arrived here, let's verify the configuration.
        $config = ConfigFactory::getDefaultInstance()->makeConfig('CarenodiMW');
        $base = $config->get('CarenodiMW_base');
        $canonicalRegexp = $config->get('CarenodiMW_canonical_regexp');
        $refreshRegexp = $config->get('CarenodiMW_refresh_regexp');
        $refreshWait = $config->get('CarenodiMW_refresh_wait');

        if (empty($base) || (empty($canonicalRegexp) && empty($refreshRegexp))) {
            wfDebugLog('CarenodiMW', __METHOD__ . ' - Missing configuration. Nothing to do');
            return true;
        }

        if (@preg_match($canonicalRegexp, '') === false) {
            wfDebugLog('CarenodiMW', __METHOD__ . ' - Invalid $wgCarenodiMW_canonical_regexp regular expression found');
            return true;
        }

        if (@preg_match($refreshRegexp, '') === false) {
            wfDebugLog('CarenodiMW', __METHOD__ . ' - Invalid $wgCarenodiMW_refresh_regexp regular expression found');
            return true;
        }

        if (!is_int($refreshWait) || $refreshWait < 0) {
            wfDebugLog('CarenodiMW', __METHOD__ . ' - Invalid $wgCarenodiMW_refresh_wait positive integer found');
            return true;
        }

        // This is not ideal, but after playing with different hooks, individually or combining them, it's
        // the easiest way to access to the original wikitext of the page that we want to examine against
        // our regular expressions. 4-5 queries are added to the page.
        if (!$wikiPage = WikiPage::factory($out->getTitle())) {
            wfDebugLog('CarenodiMW', __METHOD__ . ' - Something went wrong, cannot get the original wikitext');
        }
        $wikitext = ContentHandler::getContentText($wikiPage->getContent(RevisionRecord::RAW));

        // Do we need to add the rel=canonical.
        $path = '';
        if ($canonicalRegexp) {
            if (preg_match($canonicalRegexp, $wikitext, $matches) && isset($matches[1])) {
                $path = $matches[1];
            }
            // Let's add the link=canonical tag if a path for it has been found.
            if ($path) {
                $canonical = trim($base, ' /\\') . '/' . trim($path, ' /\\');
                $out->setCanonicalUrl($canonical);
                wfDebugLog('CarenodiMW', __METHOD__ . ' - Adding rel=canonical: ' . $canonical);
            } else {
                wfDebugLog('CarenodiMW', __METHOD__ . ' - $wgCarenodiMW_canonical_regexp not matched');
            }
        }

        // Let's grep to add the meta=refresh.
        if ($refreshRegexp) {
            // Shortcut if both regular expressions are the same to save one execution.
            if ($refreshRegexp !== $canonicalRegexp) {
                $path = '';
                if (preg_match($refreshRegexp, $wikitext, $matches) && isset($matches[1])) {
                    $path = $matches[1];
                }
            }
            // Let's add the meta=refresh tag if a path for it has been found.
            if ($path) {
                $refresh = trim($refreshWait ) . '; ' . trim($base, ' /\\') . '/' . trim($path, ' /\\');
                $out->addMeta('http:refresh', $refresh);
                wfDebugLog('CarenodiMW', __METHOD__ . ' - Adding meta=refresh: ' . $refresh);
            } else {
                wfDebugLog('CarenodiMW', __METHOD__ . ' - $wgCarenodiMW_refresh_regexp not matched');
            }
        }
    }
}
