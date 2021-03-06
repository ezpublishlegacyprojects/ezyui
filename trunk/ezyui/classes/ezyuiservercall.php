<?php
//
// Definition of eZYuiServerCall class
//
// Created on: <1-Jul-2008 12:42:08 ar>
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
  Perfoms calls to custom functions or templates depending on arguments and ini settings 
*/


class eZYuiServerCall
{
    
    protected $className = null;
    protected $functionName = null;
    protected $functionArguments = array();
    protected $isTemplateFunction = false;

    protected function eZYuiServerCall( $className, $functionName = 'call', $functionArguments = array(), $isTemplateFunction = false )
    {
        $this->className = $className;
        $this->functionName = $functionName;
        $this->functionArguments = $functionArguments;
        $this->isTemplateFunction = $isTemplateFunction;
    }

    public static function getInstance( $arguments, $requireIniGroupe = true, $checkFunctionExistence = false )
    {
        if ( !is_array( $arguments ) || count( $arguments ) < 2 )
        {
            // returns null if argumenst are invalid
            return null;   
        }

        $className = $callClassName = array_shift( $arguments );
        $functionName = array_shift( $arguments );
        $isTemplateFunction = false;
        $permissionFunctions = false;
        $permissionPrFunction = false;
        $ezyuiIni = eZINI::instance( 'ezyui.ini' );
        $ezyuiFunctionList = $ezyuiIni->variable( 'eZYuiServerCall', 'FunctionList' );

        if ( $ezyuiIni->hasGroup( 'eZYuiServerCall_' . $callClassName ) )
        {
           // load file if defined, else use autoload 
           if ( $ezyuiIni->hasVariable( 'eZYuiServerCall_' . $callClassName, 'File' ) )
                include_once( $ezyuiIni->variable( 'eZYuiServerCall_' . $callClassName, 'File' ) );

           if ( $ezyuiIni->hasVariable( 'eZYuiServerCall_' . $callClassName, 'TemplateFunction' ) )
                $isTemplateFunction = $ezyuiIni->variable( 'eZYuiServerCall_' . $callClassName, 'TemplateFunction' ) === 'true';

           // check permissions
           if ( $ezyuiIni->hasVariable( 'eZYuiServerCall_' . $callClassName, 'Functions' ) )
                $permissionFunctions = $ezyuiIni->variable( 'eZYuiServerCall_' . $callClassName, 'Functions' );

           // check permissions
           if ( $ezyuiIni->hasVariable( 'eZYuiServerCall_' . $callClassName, 'PermissionPrFunction' ) )
                $permissionPrFunction = $ezyuiIni->variable( 'eZYuiServerCall_' . $callClassName, 'PermissionPrFunction' ) === 'enabled';

           // get class name if defined, else use first argument as class name
           if ( $ezyuiIni->hasVariable( 'eZYuiServerCall_' . $callClassName, 'Class' ) )
                $className = $ezyuiIni->variable( 'eZYuiServerCall_' . $callClassName, 'Class' );
        }
        else if ( $requireIniGroupe )
        {
            // return null if ini is not defined as a safty messure
            // to avoid letting user call all eZ Publish classes
            return null;
        }

        if ( $checkFunctionExistence && !self::staticHasCall( $className, $functionName, $isTemplateFunction  ) )
        {
            return null;
        }

        if ( $permissionFunctions !== false )
        {
        	if ( !self::hasAccess( $ezyuiFunctionList, $permissionFunctions, ( $permissionPrFunction ? $functionName : null ) ) )
        	{
        	    return null;
        	}
        }

        return new eZYuiServerCall( $className, $functionName, $arguments, $isTemplateFunction );
    }
    
    public function getCallName()
    {
        return $this->className . '::' . $this->functionName;
    }

    public function getCacheTime( $lastmodified = 0, $environmentArguments = array()  )
    {
        if ( $this->isTemplateFunction )
        {
            return $lastmodified;
        }
        else if ( method_exists( $this->className, 'getCacheTime' ) )
        {
            return max( $lastmodified, call_user_func( array( $this->className, 'getCacheTime' ), $this->functionArguments, $environmentArguments ));
        }
        else
        {
            return $lastmodified;
        }
    }

    public static function hasAccess( $iniFunctionList, $requiredFunctions, $functionName = null )
    {
    	$currentUser = eZUser::currentUser();
        foreach( $requiredFunctions as $requiredFunction )
        {
        	$permissionName = $requiredFunction . ( $functionName !== null ? '_' . $functionName : ''  );
        	if ( !in_array( $permissionName, $iniFunctionList ) )
        	{
        		eZDebug::writeWarning( "'$permissionName' is not defined in ezyui.ini[eZYuiServerCall]FunctionList", __METHOD__ );
        		return false;
        	}

        	$accessResult = $currentUser->hasAccessTo( 'ezyui', 'call_' . $permissionName );
        	if ( $accessResult[ 'accessWord' ] !== 'yes'  )
        	{
        		return false;
        	}
        }
        return true;
    }
    
    public function hasCall()
    {
        return self::staticHasCall( $this->className, $this->functionName, $this->isTemplateFunction  );
    }

    public static function staticHasCall( $className, $functionName, $isTemplateFunction = false )
    {
        if ( $isTemplateFunction )
        {
            return true;//todo: find a way to look for templates
        }
        else
        {
            return method_exists( $className, $functionName );
        }
    }

    public function call( $environmentArguments = array()  )
    {
        if ( $this->isTemplateFunction )
        {
            include_once( 'kernel/common/template.php' );
            $tpl = templateInit();
            $tpl->setVariable( 'arguments', $this->functionArguments );
            $tpl->setVariable( 'environment', $environmentArguments );
            return $tpl->fetch( 'design:' . $this->className . '/' . $this->functionName . '.tpl' );
        }
        else
        {
            return call_user_func( array( $this->className, $this->functionName ), $this->functionArguments, $environmentArguments );
        }
    }
    
}

?>