{if ezini( 'YUI', 'LoadFromYahooCDN', 'ezyui.ini' )|eq('enabled')}
    <script type="text/javascript" src="{ezini( 'YUI', 'YahooCDNUrl', 'ezyui.ini' )}"></script>
{else}
    <script type="text/javascript" src={'lib/yui/3.0/build/yui/yui-min.js'|ezdesign}></script>
    <script type="text/javascript">
    <!--
    yui_config.base = '{"lib/yui/3.0/build/"|ezdesign( 'no' )}';
    -->
    </script>
{/if}

{foreach ezini( 'JavaScriptSettings', 'JavaScriptList', 'design.ini' ) as $script}
    <script language="javascript" type="text/javascript" src={concat( 'javascript/', $script )|ezdesign}></script>
{/foreach}