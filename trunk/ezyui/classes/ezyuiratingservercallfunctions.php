<?php
//
// Definition of eZYuiRatingServerCallFunctions
//
// Created on: <27-Jul-2009 12:42:08 ar>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ Yui extension for eZ Publish
// SOFTWARE RELEASE: 1.x
// COPYRIGHT NOTICE: Copyright (C) 2009 eZ Systems AS
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
  eZ Yui Server Call class for rating  
*/


class eZYuiRatingServerCallFunctions
{
    /**
     * Rate content objects
     * Arguments:
     * - object id to rate
     * - rating, floating number from 0 to 5 (configurable) 
     * 
     * @param array $args [ 'contentobject_id', 'rating' ] all optional
     */
    public static function rate( $args )
    {
        $objId    = (int) $args[0];
        $rate     = isset( $args[1] ) && is_numeric( $args[1] ) ? (float) str_replace(',', '.', $args[1] ) : null;
        $obj      = $objId !== 0 ? eZContentObject::fetch( $objId ) : false;
        $user     = eZUser::currentUser();
        $maxRate  = 5;
        
        
        if ( !$obj )
        {
            return 'Could not find page!';
        }
        else if ( !$user instanceof eZUser )
        {
            return 'Could not fetch current user!';
        }
        else if ( !$obj->checkAccess('read') )
        {
            return 'You don\'t have read access to the given page!';
        }
        else if ( $rate === null || $rate > $maxRate )
        {
            return "Rating must be a number between 0 and $maxRate!";
        }

        $userId    = $user->attribute('contentobject_id');
        $now       = time();
        $sessionId = session_id();

        if ( $user->attribute('is_logged_in') )
        {
            $selectSQL = "SELECT
                              COUNT(*) as count
                          FROM
                              ezcontentobject_rating 
                          WHERE
                              contentobject_id = $objId AND
                              user_id = $userId";
        }
        else
        {
            // trick to use index withouth matching on user id
            $selectSQL = "SELECT
                              COUNT(*) as count
                          FROM
                              ezcontentobject_rating 
                          WHERE
                              contentobject_id = $objId AND
                              user_id <> 0 AND
                              session_key = '$sessionId'";
        }

        $db = eZDB::instance();
        $rs = $db->arrayQuery( $selectSQL );
        if ( $rs === false )
        {
            return 'Rating table is missing, contact administrator!';
        }
        else if ( $rs[0]['count'] !== '0' )
        {
            return 'You are only allowed to vote 1 time per unique page!';
        }
        else
        {
            $rs = $db->query( "INSERT INTO ezcontentobject_rating 
                               VALUES ( $objId, $userId, '$sessionId', $rate, $now )" );
            if ( $rs !== true )
            {
                return 'Something went wrong on rating insert, contact administrator!';
            }
        }
        
        eZContentCacheManager::clearContentCache( $objId, true, false );

        return 'ok';
    }
}

?>
