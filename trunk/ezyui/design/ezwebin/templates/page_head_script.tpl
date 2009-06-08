<script type="text/javascript" src="http://yui.yahooapis.com/combo?3.0.0pr2/build/yui/yui-min.js&3.0.0pr2/build/oop/oop-min.js&3.0.0pr2/build/event/event-min.js&3.0.0pr2/build/dom/dom-min.js&3.0.0pr2/build/node/node-min.js"></script>
{foreach ezini( 'JavaScriptSettings', 'JavaScriptList', 'design.ini' ) as $script}
    <script language="javascript" type="text/javascript" src={concat( 'javascript/', $script )|ezdesign}></script>
{/foreach}