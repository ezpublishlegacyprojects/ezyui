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
     * Generates the javascript needed to do server calls directly from javascript
     */
    public static function ez()
    {
        $url = self::getIndexDir() . 'ezyui/';
        return "
YUI( YUI3_config ).add('io-ez', function( Y )
{
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

    /**
     * Internal function to get current index dir
     */
    protected static function getIndexDir()
    {
        static $cachedIndexDir = null;
    	if ( $cachedIndexDir === null )
        {
            $sys = eZSys::instance();
            $cachedIndexDir = $sys->indexDir() . '/';
        }
        return $cachedIndexDir;
    }
}

?>