<?php
/**
 *                    Jojo CMS
 *                ================
 *
 * Copyright 2008 Michael Cochrane <mikec@jojocms.org>
 *
 * See the enclosed file license.txt for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Michael Cochrane <mikec@jojocms.org>
 * @license http://www.fsf.org/copyleft/lgpl.html GNU Lesser General Public License
 * @link    http://www.jojocms.org JojoCMS
 * @package jojo_core
 */

class Jojo_Plugin_Core_External extends Jojo_Plugin_Core {

    /**
     * Serve an external file.
     */
    function __construct()
    {
        /* Get requested filename */
        $file = Jojo::getFormData('file', false);
        $f = $file;

        /* Check file name is set */
        if (!$file) {
            /* Not valid, 404 */
            header("HTTP/1.0 404 Not Found", true, 404);
            exit;
        }

        /* Check for existence of cached copy if user has not pressed CTRL-F5 */
        $cachefile = _CACHEDIR . '/external/' . $file;
        $cachetime = Jojo::getOption('contentcachetime_resources', 604800);
        $fromcache = false;
        if (Jojo::fileExists($cachefile) && !Jojo::ctrlF5()) {
            Jojo::runHook('jojo_core:externalCachedFile', array('filename' => $cachefile));

            parent::sendCacheHeaders(filemtime($cachefile), $cachetime);
            $file = $cachefile;
            $fromcache = true;
        } else {
            /* Check for external in a Theme */
            $files = Jojo::listThemes('external/' . $file);

            if (isset($files[0])) {
                $file = $files[0];
            } else {
                /* Check for external in a Plugin */
                $files = Jojo::listPlugins('external/' . $file);
                if (isset($files[0])) {
                    $file = $files[0];
                } else {
                    /* Not found, 404 time */
                    header("HTTP/1.0 404 Not Found", true, 404);
                    exit;
                }
            }
        }

        if (Jojo::getFileExtension($file) == 'php') {
            /* Create PHP_SELF for external code that expects it to be accurate */
            $_SERVER['PHP_SELF'] = str_replace('index.php', substr($file, strpos($file, 'external/')), $_SERVER['PHP_SELF']);

            /* Change to directory */
            chdir(dirname($file));

            /* Include the php file */
            throw new Jojo_Exception_IncludeFile('Include file', $file);
            exit();
        }

        /* Read only session */
        define('_READONLYSESSION', true);

        Jojo::runHook('jojo_core:externalFile', array('filename' => $file));

        /* Get Content */
        $content = file_get_contents($file);

        /* Send header */
        switch (Jojo::getFileExtension($file)) {
            case 'css':
                header('Content-Type: text/css');
                if (!$fromcache) {
                    if (!defined('_CONTENTCACHE'))     define('_CONTENTCACHE',     Jojo::getOption('contentcache') == 'no' ? false : true);
                    if (!defined('_CONTENTCACHETIME')) define('_CONTENTCACHETIME', Jojo::either(Jojo::getOption('contentcachetime'), 3600));
                    $css = new Jojo_Stitcher();
                    $css->type = 'css';
                    $css->getServerCache();
                    $css->addText($content);
                    $css->setServerCache();
                    $content = $css->fetch();
                }
                break;

            case 'js':
                header('Content-Type: application/x-javascript');
                if (!$fromcache) {
                    /* JSmin sometimes corrupts files. This is a list of exclusions */
                    $nojsmin = array();
                    $nojsmin[] = 'jquery.jqUploader.js';
                    $nojsmin[] = 'jquery.flash.js';
                    $nojsmin[] = 'ext-all.js';

                    /* also anything with .pack.js in the filename can't be jsminned */


                    if (!in_array(basename($file), $nojsmin) && strpos($file, 'pack')==false && strpos($file, 'min')==false) {
                        set_time_limit(180);
                        require_once(_BASEPLUGINDIR . '/jojo_core/external/jshrink/src/JShrink/Minifier.php');
                        try {
                            $newContent = JShrink\Minifier::minify($content);
                        } catch (Exception $e) { 
                            $newContent = $content;
                        }
                        if (strlen($newContent) <= strlen($content)) {
                            $content = $newContent;
                        } else {
                            $content = sprintf('/* JSMIN enlarged file by %s bytes */', strlen($newContent) - strlen($content)) . $content;
                        }
                    }
                }
                break;

            case 'gif':
                header('Content-Type: image/gif');
                break;

            case 'jpg':
            case 'jpeg':
                header('Content-Type: image/jpeg');
                break;

            case 'png':
                header('Content-Type: image/png');
                break;
            case 'htc':
                header('Content-Type: text/x-component');
                break;
            default:
                $mime = Jojo::getMimeType($file);
                if ($mime) {
                    header('Content-Type: '.$mime);
                }
                break;
        }

        /* cache a copy for next time */
        if (!$fromcache) {
            Jojo::RecursiveMkdir(dirname($cachefile));
            file_put_contents($cachefile, $content);
        }

        /* Send Content */
        if (Jojo::getOption('enablegzip') == 1) Jojo::gzip();

        parent::sendCacheHeaders(time(), $cachetime);
        header('Content-Length: ' . strlen($content));
        echo $content;
        Jojo::publicCache($file, $content);
        exit;
    }
}

class Jojo_Exception_IncludeFile extends Exception
{
    protected $fileToInclude;

    public function __construct($message, $file, $code = 0) {
        $this->fileToInclude = $file;
        parent::__construct($message, $code);
    }

    public function getFileToInclude() {
        return $this->fileToInclude;
    }
}
