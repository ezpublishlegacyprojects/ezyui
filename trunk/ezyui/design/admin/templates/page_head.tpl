{default enable_help=true() enable_link=true()}

{let name=Path
     path=$module_result.path
     reverse_path=array()}
  {section show=is_set($module_result.title_path)}
    {set path=$module_result.title_path}
  {/section}
  {section loop=$:path}
    {set reverse_path=$:reverse_path|array_prepend($:item)}
  {/section}

{set-block scope=root variable=site_title}
{section loop=$Path:reverse_path}{$:item.text|wash}{delimiter} / {/delimiter}{/section} - {$site.title|wash}
{/set-block}

{/let}

    <title>{$site_title}</title>

    {section show=and(is_set($#Header:extra_data),is_array($#Header:extra_data))}
      {section name=ExtraData loop=$#Header:extra_data}
      {$:item}
      {/section}
    {/section}

    {* check if we need a http-equiv refresh *}
    {section show=$site.redirect}
    <meta http-equiv="Refresh" content="{$site.redirect.timer}; URL={$site.redirect.location}" />

    {/section}

    {section name=HTTP loop=$site.http_equiv}
    <meta http-equiv="{$HTTP:key|wash}" content="{$HTTP:item|wash}" />

    {/section}

    {section name=meta loop=$site.meta}
    <meta name="{$meta:key|wash}" content="{$meta:item|wash}" />

    {/section}

    <meta name="MSSmartTagsPreventParsing" content="TRUE" />
    <meta name="generator" content="eZ Publish" />

{section show=$enable_link}
    {include uri="design:link.tpl" enable_help=$enable_help enable_link=$enable_link}
{/section}

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
    var YUI3_config = {ldelim}{rdelim};
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
{/default}
