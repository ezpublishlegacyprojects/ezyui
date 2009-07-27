<?php
//
// Definition of eZYuiKeywordServerCallFunctions
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
eZ Yui Server Call class for keywords
*/


class eZYuiKeywordServerCallFunctions
{
    /**
     * Get keywords by input parameters
     * Arguments:
     * - keyword string to search for
     * - limit, how many suggestions to return, default ini value is used if not set
     * - class id, serach is restricted to class id if set
     * 
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
}

?>
