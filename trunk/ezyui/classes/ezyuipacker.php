<?php
//
// Definition of eZYuiPacker class
//
// Created on: <23-Aug-2007 12:42:08 ar>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ Core extension for eZ Publish
// SOFTWARE RELEASE: 1.x
// COPYRIGHT NOTICE: Copyright (C) 2008 eZ Systems AS
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//
// ## END COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
//

/*
 Functions for merging and packing css and javascript files.
 Reduces page load time both in terms of reducing connections from clients
 and bandwidth ( if packing is turned on ).
 
 Packing has 4 levels:
 0 = off
 1 = merge files
 2 = 1 + remove whitespace
 3 = 2 + remove more whitespace  (jsmin is used for scripts)
 
 In case of css files, relative image paths will be replaced
 by absolute paths.

 You can also use css / js generators to generate content dynamically.
 This is better explained in ezyui.ini[Packer_<function>]

 buildStylesheetFiles and buildJavascriptFiles functions does not return html, just 
 an array of file urls / content (from generators).
 
*/

class eZYuiPacker
{
    /**
     * Constructor
     *
     * @access protected
     */
    protected function __construct()
    {
    }
    
    static protected $wwwDir = null;
    static protected $cacheDir = null;
    
    // static :: Builds the xhtml tag(s) for scripts
    static function buildJavascriptTag( $scriptFiles, $type, $lang, $packLevel = 2, $wwwInCacheHash = false, $charset = "utf-8" )
    {
        $ret = '';
        $lang = $lang ? ' language="' . $lang . '"' : '';
        $http = eZHTTPTool::instance();
        $useFullUrl = ( isset( $http->UseFullUrl ) && $http->UseFullUrl );
        $packedFiles = eZYuiPacker::packFiles( $scriptFiles, 'javascript/', '.js', $packLevel, $wwwInCacheHash );
        foreach ( $packedFiles as $packedFile )
        {
            // Is this a js file or js content?
            if ( isset( $packedFile{4} ) && strripos( $packedFile, '.js' ) === ( strlen( $packedFile ) -3 ) )
            {
                if ( $useFullUrl )
                {
                    $packedFile = $http->createRedirectUrl( $packedFile, array( 'pre_url' => false ) );
                }
                $ret .= "<script$lang type=\"$type\" src=\"$packedFile\" charset=\"$charset\"></script>\r\n";
            }
            else
            {
                $ret .=  $packedFile ? "<script$lang type=\"$type\">\r\n$packedFile\r\n</script>\r\n" : '';
            }
        }
        return $ret;
    }
    
    // static :: Builds the xhtml tag(s) for stylesheets
    static function buildStylesheetTag( $cssFiles, $media, $type, $rel, $packLevel = 3, $wwwInCacheHash = true, $charset = "utf-8" )
    {
        $ret = '';
        $packedFiles = eZYuiPacker::packFiles( $cssFiles, 'stylesheets/', '_' . $media . '.css', $packLevel, $wwwInCacheHash );
        $http = eZHTTPTool::instance();
        $useFullUrl = ( isset( $http->UseFullUrl ) && $http->UseFullUrl );
        foreach ( $packedFiles as $packedFile )
        {
            // Is this a css file or css content?
            if ( isset( $packedFile{5} ) && strripos( $packedFile, '.css' ) === ( strlen( $packedFile ) -4 ) )
            {
                if ( $useFullUrl )
                {
                    $packedFile = $http->createRedirectUrl( $packedFile, array( 'pre_url' => false ) );
                }
                $ret .= "<link rel=\"$rel\" type=\"$type\" href=\"$packedFile\" media=\"$media\" />\r\n";
            }
            else
            {
                $ret .= $packedFile ? "<style rel=\"$rel\" type=\"$type\" media=\"$media\">\r\n$packedFile\r\n</style>\r\n" : '';
            }
        }
        return $ret;
    }
    
    
    // static :: Builds a array of script files
    static function buildJavascriptFiles( $scriptFiles, $packLevel = 2, $wwwInCacheHash = false )
    {
        return eZYuiPacker::packFiles( $scriptFiles, 'javascript/', '.js', $packLevel, $wwwInCacheHash );
    }
    
    // static :: Builds a array of stylesheet files
    static function buildStylesheetFiles( $cssFiles, $packLevel = 3, $wwwInCacheHash = true )
    {
        return eZYuiPacker::packFiles( $cssFiles, 'stylesheets/', '_all.css', $packLevel, $wwwInCacheHash );
    }

    // static :: gets the cache dir
    static function getCacheDir()
    {
        if ( self::$cacheDir === null )
        {
            $sys = eZSys::instance();
            self::$cacheDir = $sys->cacheDirectory() . '/public/';
        }
        return self::$cacheDir;
    }

    // static :: gets the www dir
    static function getWwwDir()
    {
        if ( self::$wwwDir === null )
        {
            $sys = eZSys::instance();
            self::$wwwDir = $sys->wwwDir() . '/';
        }
        return self::$wwwDir;
    }
    
    /* static ::
     Merges a collection of files togheter and returns array of paths to the files.
     js /css content is returned as string if packlevel is 0 and you use a js/ css generator.
     $fileArray can also be array of array of files, like array(  'file.js', 'file2.js', array( 'file5.js' ) )
     The name of the cached file is a md5 hash consistant of the file paths
     of the valid files in $file_array and the packlevel. 
     The whole argument is used instead of file path on js/ css generators in the cache hash.
     */
    static function packFiles( $fileArray, $subPath = '', $fileExtension = '.js', $packLevel = 2, $wwwInCacheHash = false )
    {
        if ( !$fileArray )
        {
            return array();
        }
        else if ( !is_array( $fileArray ) )
        {
            $fileArray = array( $fileArray );
        }

        $cacheName = '';
        $lastmodified = 0;
        $validFiles = array();
        $validWWWFiles = array();
        $bases   = eZTemplateDesignResource::allDesignBases();
        
        $packerInfo = array(
            'file_extension' => $fileExtension,
            'pack_level' => $packLevel,
            'sub_path' => $subPath,
            'cache_dir' => self::getCacheDir(),
            'www_dir' => self::getWwwDir(),
        );

        // needed for image includes to work on ezp installs with mixed access methods (virtualhost + url based setup)
        if ( $wwwInCacheHash )
        {
            $cacheName = $packerInfo['www_dir'];
        }

        while( count( $fileArray ) > 0 )
        {
            $file = array_shift( $fileArray );

            // if $file is array, concat it to the file array and continue
            if ( $file && is_array( $file ) )
            {
                $fileArray = array_merge( $file, $fileArray );
                continue;
            }
            else if ( !$file )
            {
                continue;
            }
            // if the file name contains :: it is threated as a custom code genarator
            else if ( strpos( $file, '::' ) !== false )
            {
                $serverCall = eZYuiServerCall::getInstance( explode( '::', $file ) );
                if ( !$serverCall instanceOf eZYuiServerCall )
                {
                    // continue if not valid
                    continue;
                }
                
                $lastmodified = $serverCall->getCacheTime( $lastmodified, $packerInfo );

                // make sure the function is present on the class
                if ( !$serverCall->hasCall() )
                {
                    eZDebug::writeWarning( 'Could not find function: ' . $serverCall->getCallName() . '()', __METHOD__ );
                    continue;
                }

                $validFiles[] = $serverCall;
                $cacheName   .= $file . '_';
                // generate content straight away if packing is disabled
                if ( $packLevel === 0 )
                {
                   $validWWWFiles[] = $serverCall->call( $packerInfo );
                }
                continue;
            }
            // is it a http url  ?
            else if ( strpos( $file, 'http://' ) === 0 || strpos( $file, 'https://' ) === 0 )
            {
                $fileTime = 0;
                $wwwFile  = $file;
            }
            // is it a absolute path ?
            else if ( strpos( $file, 'var/' ) === 0 )
            {
                if ( substr( $file, 0, 2 ) === '//' || preg_match( "#^[a-zA-Z0-9]+:#", $file ) )
                    $file = '/';
                else if ( strlen( $file ) > 0 &&  $file[0] !== '/' )
                    $file = '/' . $file;

                eZURI::transformURI( $file, true, 'relative' );
                // get file time and continue if it return false
                $file     = str_replace( '//' . $packerInfo['www_dir'], '', '//' . $file );
                $fileTime = file_exists( $file ) ? filemtime( $file ): false;
                $wwwFile  = $packerInfo['www_dir'] . $file;
            }
            // or is it a relative path
            else
            {
                $file = $file;
                $triedFiles = array();
                $match = eZTemplateDesignResource::fileMatch( $bases, '', $file, $triedFiles );
                // Work around many extensions use design.ini[JavaScriptSettings].JavaScriptList but the path is not correct 
                if ( $match === false )
                {
                	$file = $subPath . $file;
                	$match = eZTemplateDesignResource::fileMatch( $bases, '', $file, $triedFiles );
                }
                if ( $match === false )
                {
                    eZDebug::writeWarning( "Could not find: $file", __METHOD__ );
                    continue;
                }
                $file = htmlspecialchars( $match['path'] );
                $fileTime = file_exists( $file ) ? filemtime( $file ): false;
                $wwwFile  = $packerInfo['www_dir'] . $file;
            }

            if ( $fileTime === false )
            {
                eZDebug::writeWarning( "Could not get modified time of file: $file", __METHOD__ );
                continue;
            }

            // calculate last modified time and store in arrays
            $lastmodified  = max( $lastmodified, $fileTime );
            $validFiles[] = $file;
            $validWWWFiles[] = $wwwFile;
            $cacheName   .= $file . '_';
        }

        // if packing is disabled, return the valid paths / content we have generated
        if ( $packLevel === 0 ) return $validWWWFiles;

        if ( !$validFiles )
        {
            eZDebug::writeWarning( "Could not find any files: " . var_export( $fileArray, true ), __METHOD__ );
            return array();
        }

        // generate cache file name and path
        $cacheName = md5( $cacheName . $packLevel ) . $fileExtension;
        $cachePath = $packerInfo['cache_dir'] . $subPath;

        if ( file_exists( $cachePath . $cacheName ) )
        {
            // check last modified time and return path to cache file if valid
            if ( $lastmodified <= filemtime( $cachePath . $cacheName ) )
            {
                return array( $packerInfo['www_dir'] . $cachePath . $cacheName );
            }
        }

        // Merge file content and create new cache file
        $content = '';
        foreach ( $validFiles as $file )
        {

           // if this is a js / css generator, call to get content
           if ( $file instanceOf eZYuiServerCall )
           {
               $content .= $file->call( $packerInfo );
               continue;
           }

           // else, get content of normal file
           $fileContent = file_get_contents( $file );

           if ( !trim( $fileContent ) )
           {
               $content .= "/* empty: $file */\r\n";
               continue;
           }

           // we need to fix relative background image paths if this is a css file
           if ( strpos($fileExtension, '.css') !== false )
           {
                $fileContent = eZYuiPacker::fixImgPaths( $fileContent, $file );
           }

           $content .= "/* start: $file */\r\n";
           $content .= $fileContent;
           $content .= "\r\n/* end: $file */\r\n\r\n";
        }

        // Pack the file to save bandwidth
        if ( $packLevel > 1 )
        {
            if ( strpos($fileExtension, '.css') !== false )
                $content = eZYuiPacker::optimizeCSS( $content, $packLevel );
            else
                $content = eZYuiPacker::optimizeScript( $content, $packLevel );
        }

        // save file and return path if sucsessfull
        if( eZFile::create( $cacheName, $cachePath, $content ) )
        {
            return array( $packerInfo['www_dir'] . $cachePath . $cacheName );
        }

        return array();
    }

    static function fixImgPaths( $fileContent, $file )
    {
        if ( preg_match_all("/url\(\s?[\'|\"]?(.+)[\'|\"]?\s?\)/ix", $fileContent, $urlMatches) )
        {
           $urlMatches = array_unique( $urlMatches[1] );
           $cssPathArray   = explode( '/', $file );
           // pop the css file name
           array_pop( $cssPathArray );
           $cssPathCount = count( $cssPathArray );
           foreach( $urlMatches as $match )
           {
               $match = str_replace( array('"', "'"), '', $match );
               $relativeCount = substr_count( $match, '../' );
               // replace path if it is realtive
               if ( $match[0] !== '/' and strpos( $match, 'http:' ) === false )
               {
                   $cssPathSlice = $relativeCount === 0 ? $cssPathArray : array_slice( $cssPathArray  , 0, $cssPathCount - $relativeCount  );
                   $newMatchPath = self::getWwwDir() . implode('/', $cssPathSlice) . '/' . str_replace('../', '', $match);
                   $fileContent = str_replace( $match, $newMatchPath, $fileContent );
               }
           }
        }
        return $fileContent;
    }

    // 'compress' css code by removing whitespace
    static function optimizeCSS( $css, $packLevel )
    {
        // normalize line feeds
        $css = str_replace(array("\r\n", "\r"), "\n", $css);

        // remove multiline comments
        $css = preg_replace('!(?:\n|\s|^)/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);

        // remove whitespace from start and end of line + singelline comment + multiple linefeeds
        $css = preg_replace(array('/\n\s+/', '/\s+\n/', '!\n//.+\n!', '!\n//\n!', '/\n+/'), "\n", $css);

        if ( $packLevel > 2 )
        {
            // remove space around ':' and ','
            $css = preg_replace(array('/:\s+/', '/\s+:/'), ':', $css);
            $css = preg_replace(array('/,\s+/', '/\s+,/'), ',', $css);

            // remove unnecesery line breaks
            $css = str_replace(array(";\n", '; '), ';', $css);
            $css = str_replace(array("}\n","\n}", ';}'), '}', $css);
            $css = str_replace(array("{\n", "\n{", '{;'), '{', $css);

            // optimize css
            $css = str_replace(array(' 0em', ' 0px',' 0pt', ' 0pc'), ' 0', $css);
            $css = str_replace(array(':0em', ':0px',':0pt', ':0pc'), ':0', $css);
            $css = str_replace('0 0 0 0;', '0;', $css);

            // these should use regex to work on all colors
            $css = str_replace(array('#ffffff','#FFFFFF'), '#fff', $css);
            $css = str_replace('#000000', '#000', $css);
        }
        return $css;
    }

    // 'compress' javascript code by removing whitespace
    // uses JSMin if packing level is set to 2 or higher
    static function optimizeScript( $script, $packLevel )
    {
        if ( $packLevel < 3 )
        {
            // normalize line feeds
            $script = str_replace(array("\r\n", "\r"), "\n", $script);
    
            // remove multiline comments
            $script = preg_replace('!(?:\n|\s|^)/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $script);
    
            // remove whitespace from start & end of line + singelline comment + multiple linefeeds
            $script = preg_replace(array('/\n\s+/', '/\s+\n/', '!\n//.+\n!', '!\n//\n!', '/\n+/'), "\n", $script);
        }
        else
        {
            $script = JSMin::minify( $script );
        }
        return $script;
    }
}

?>