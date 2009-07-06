<?php
//
// Created on: <16-Jun-2008 00:00:00 ar>
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
 * Some eZYuiServerCall Functions
 */

class eZYuiServerCallFunctions
{
    /*
     * Example function for returning time stamp
     * + first function argument if present
     */
    public static function time( $args )
    {
        if ( $args && isset( $args[0] ) )
            return $args[0]. '_' . time();
        return time();
    }

    /**
     * Get keywords by input parameters
     * Arguments:
     * - keyword string to search for, but postvariable Keyword
     *   is prefered because of encoding issues in urls
     * - limit, how many suggestions to return, default ini value is used if not set
     * - class id, serach is restricted to class id if set
     * @param array $args [ 'keyword', 'limit', 'class_id' ] all optional
     */
    public static function keyword( $args )
    {
        $ezyuiINI = eZINI::instance( 'ezyui.ini' );
        
        $keywordLimit            = 30;
        $keywordSuggestionsArray = $ezyuiINI->variable( 'Keyword', 'SuggestionsArray' );
        $classID                 = false;
        $keywordStr              = '';

        if ( isset( $args[0] ) )
        {
            $keywordStr = $args[0];
        }

        if ( isset( $args[1] ) )
        {
            $keywordLimit = (int) $args[1];
        }
        else if( $ezyuiINI->hasVariable( 'Keyword', 'Limit' ) )
        {
            $keywordLimit = (int) $ezyuiINI->variable( 'Keyword', 'Limit' );
        }

        if ( isset( $args[2] ) )
        {
            $classID = (int) $args[2];
        }

        if ( !is_array( $keywordSuggestionsArray ) )
        {
            $keywordSuggestionsArray = array();
        }

        $keywords = array();
        $searchList = array( 'result' => array() );

        // first return keyword matches from ini
        foreach ( $keywordSuggestionsArray as $string )
        {
            if( $keywordStr === '' || strpos( strtolower( $string ), strtolower( $keywordStr ) ) === 0 )
            {
                $keywords[] = $string;
                --$keywordLimit;
                if ( $keywordLimit === 0 ) break;
            }
        }

        if ( $keywordLimit > 0 )
        {
            $searchList = eZContentFunctionCollection::fetchKeyword( $keywordStr, $classID, 0, $keywordLimit );
        }

        //then return matches from database
        foreach ( $searchList['result'] as $node )
        {
            if ( $node['keyword'] )
            {
                $keywords[] = $node['keyword'];
            }
        }
        $keywords = array_unique( $keywords );
        //echo var_dump( $keywordStr );

        return $keywords;
    }

    /*
     * Generates the javascript needed to do server calls directly from javascript
    */
    public static function ez()
    {
        $url = self::getIndexDir() . 'ezyui/';
        return "
YUI( YUI3_config ).add('io-ez', function( Y ){
    var _serverUrl = '$url', _seperator = '@SEPERATOR$';

    function _ez( callArgs, c )
    {
        callArgs = callArgs.join !== undefined ? callArgs.join( _seperator ) : callArgs;
        var url = _serverUrl + 'call/' + encodeURIComponent( callArgs );
        return Y.io( url, c );
    }

    _ez.url = _serverUrl;
    _ez.seperator = _seperator;
    Y.io.ez = _ez;

}, '3.0.0b1' ,{requires:['io-base']});
        ";
    }

    public static function getCacheTime( $functionName )
    {
        // this data only expires when this timestamp is increased
        return 1218018250;
    }

    protected static function getIndexDir()
    {
        if ( self::$cachedIndexDir === null )
        {
            $sys = eZSys::instance();
            self::$cachedIndexDir = $sys->indexDir() . '/';
        }
        return self::$cachedIndexDir;
    }
    
    protected static $cachedIndexDir = null;
}

?>