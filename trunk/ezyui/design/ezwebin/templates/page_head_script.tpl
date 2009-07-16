{if ezini( 'YUI', 'LoadFromYahooCDN', 'ezyui.ini',,true() )|eq('enabled')}
    <script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/yuiloader/yuiloader-min.js"></script>
    <script type="text/javascript" src="http://yui.yahooapis.com/3.0.0b1/build/yui/yui-min.js"></script>
    <script type="text/javascript">
    <!--
    var YUILoader =  new YAHOO.util.YUILoader({ldelim}
        loadOptional: true,
        combine: true
    {rdelim});
    // use this as parameter for YUI( YUI3_config ) for now, until yui 3.0 has a better way of setting global base path
    var YUI3_config = false;
    -->
    </script>
{else}
    <script type="text/javascript" src={'lib/yui/2.7.0/build/yuiloader/yuiloader-min.js'|ezdesign}></script>
    <script type="text/javascript" src={'lib/yui/3.0/build/yui/yui-min.js'|ezdesign}></script>
    <script type="text/javascript">
    <!--
    var YUILoader =  new YAHOO.util.YUILoader({ldelim}
        base: '{"lib/yui/2.7.0/build/"|ezdesign( 'no' )}',
        loadOptional: true
    {rdelim});
    // use this as parameter for YUI( YUI3_config ) for now, until yui 3.0 has a better way of setting global base path
    var YUI3_config = {ldelim} 'base' : '{"lib/yui/3.0/build/"|ezdesign( 'no' )}' {rdelim};
    -->
    </script>
{/if}

{foreach ezini( 'JavaScriptSettings', 'JavaScriptList', 'design.ini' ) as $script}
    <script language="javascript" type="text/javascript" src={concat( 'javascript/', $script )|ezdesign}></script>
{/foreach}