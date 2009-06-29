{default attribute_base=ContentObjectAttribute}
<input id="ezcoa-{if ne( $attribute_base, 'ContentObjectAttribute' )}{$attribute_base}-{/if}{$attribute.contentclassattribute_id}_{$attribute.contentclass_attribute_identifier}" class="box ezcc-{$attribute.object.content_class.identifier} ezcca-{$attribute.object.content_class.identifier}_{$attribute.contentclass_attribute_identifier} ezcca-edit-keyword" type="text" size="70" name="{$attribute_base}_ezkeyword_data_text_{$attribute.id}" value="{$attribute.content.keyword_string|wash(xhtml)}" autocomplete="off" />
{/default}


{run-once}
{ezscript( array('ezyui::ez') )}

<script type="text/javascript">
<!--
{literal}
YUI( YUI3_config ).use('node', 'event', 'io-ez', function( Y )
{
    var _inputs = false,
        _dropdownLI = 0,
        _div = null,
        _timeout = null,
        _inpIndex = -1,
        _liIndex = 0;

    Y.on( "domready", function( e )
    {

        _inputs = Y.all('input.ezcca-edit-keyword');
        // if there are input keyword boxes on this page, we'll have to attache a couple of events
        if ( _inputs.size()  > 0 )
        {
            //Create div for suggestions
            _div = document.createElement('div');
            _div.id = 'ezyui_keyword_suggestion_dropdown';
            _div = Y.get('body').appendChild( _div );

            // Avoid that enter submits the form while selecting keyword (does not work at the moment..)
            Y.get('#editform').on( 'submit', Y.bind( function( e ){ e.stopPropagation(); if ( _dropdownLI != 0 ) return false; else return true; }, this ) );

            //Cleanup and hide the keywords when the input box loses focus
            _inputs.on( 'blur', Y.bind( function(){ _dropdownLI = 0; _div.setStyle('display', 'none'); }, this ) );

            //event function on keydown on the input element
            _inputs.on( 'keydown', _press );
            _div.setStyle('display', 'none');
        }
    });

    // var currentClassID = {$#object.content_class.id},

    function _callBack( id, o )
    {
        if ( o.responseText && o.responseText.length > 0 )
        {
            var content = o.responseText.split(',')
            _div.set('innerHTML', '<ul><li>' + content.join('</li><li>') + '</li></ul>' );

            var inp = _inputs.item( _inpIndex );
            _div.setStyles({
                top: inp.getY() + 22 + 'px',/*inp.getStyle('height')*/
                left: inp.getX() + 'px',
                width: inp.getStyle('width'),
                position: 'absolute',
                'z-index': 95,
                overflow: 'hidden',
                background: '#fff',
                border: '1px solid #bfbfb7',
                'border-top': '0px none',
                'text-align': 'left',
                height: '200px',
            });

            _dropdownLI = Y.all('#ezyui_keyword_suggestion_dropdown li');
            _dropdownLI.on('mouseover', _mouse );
            _dropdownLI.on('mousedown', Y.bind( _enter, this, inp ) );

            _div.setStyle('display', '');
        }
        // TODO: show error text somehow
    }

    function _press( e )
    {
        //cancle that the event bubbles up to the form element
        e.stopPropagation();
        clearTimeout( _timeout );

        var c = e.keyCode || e.which, node = e.currentTarget, keyword = node.get('value').split(',').pop().replace(/^\s+|\s+$/g, '');

        // Break any futher action on specific keys like backspace
        if ( c === 44 || c === 8 || c === 188 || c === 32 || c === 16 || c === 17 || c === 18 || c === 37 || c === 39 || keyword.length < 1) return true;
        // Let up and down buttons change selection
        else if ( c === 38 || c === 40 ) return _dropdownLI != 0 ? _select( c ) : false;
        // Select element on enter
        else if ( c === 13 ) return _enter( node );

        _div.setStyle('display', 'none');
        _inpIndex = _indexOfInput( node, _inputs );
        _timeout = setTimeout( Y.bind( _call, this, (keyword +''+ String.fromCharCode(c)) ), (c == 46 ? 200 : 100) );
        return true;
    }

    function _select( c )
    {
        var i = _liIndex;
        _liIndex = i = ( _dropdownLI == 0 || i < 0) ? 0 : i + c - 39;
        _liIndex = i = ( _dropdownLI != 0 && _dropdownLI.size() <= i ) ? _dropdownLI.size() : i;
        return _setClass( i );
    }

     function _mouse( e )
    {
         var node = e.currentTarget, i = _liIndex = _indexOfListItem( node ) + 1;
        return _setClass( i );
    }

    function _setClass( i )
    {
        if ( _dropdownLI != 0 )
        {
            if( i > 0 ) _dropdownLI.item( i - 1 ).addClass( 'selected' );
            else _dropdownLI.removeClass( 'selected' );
        }
        return false;
    }

    function _enter( node )
    {
        if ( _dropdownLI != 0 && _liIndex > 0 )
        {
           var arr = node.get('value').split(',');
           arr[arr.length -1] = ' ' + _dropdownLI.item( _liIndex - 1 ).get('innerHTML');
           node.set('value', arr.join(',').replace(/^\s+|\s+$/g, '') );
           node.focus();
        }
       _liIndex = 0;
       _div.setStyle('display', 'none');
       return false;
    }

    function _indexOfInput( node, arr )
    {
        var index = -1, i = 0;
        if ( arr ) arr.each( function( o )
        {
            if ( node.get('id') === o.get('id') || node === o  ) index = i;
            ++i;
        });
        return index;
    }

    function _indexOfListItem( node )
    {
        var i = 0;
        while ( node = node.previous('li') )
            ++i;
        return i;
    }

    function _call( key )
    {
        Y.io.ez( 'ezyui::keyword::' + key, { on : { success: _callBack} } )
    }
});
{/literal}
-->
</script>
{/run-once}