/*
Copyright (c) 2009, Yahoo! Inc. All rights reserved.
Code licensed under the BSD License:
http://developer.yahoo.net/yui/license.txt
version: 3.0.0b1
build: 1163
*/
YUI.add("io-form",function(A){A.mix(A.io,{_serialize:function(D){var I=encodeURIComponent,H=[],M=D.useDisabled||false,P=0,K,J,E,N,L,G,C,F,O,D,B=(typeof D.id==="string")?D.id:D.id.getAttribute("id");if(!B){B=A.guid("io:");D.id.setAttribute("id",B);}J=A.config.doc.getElementById(B);for(G=0,C=J.elements.length;G<C;++G){K=J.elements[G];L=K.disabled;E=K.name;if((M)?E:(E&&!L)){E=encodeURIComponent(E)+"=";N=encodeURIComponent(K.value);switch(K.type){case"select-one":if(K.selectedIndex>-1){D=K.options[K.selectedIndex];H[P++]=E+I((D.attributes.value&&D.attributes.value.specified)?D.value:D.text);}break;case"select-multiple":if(K.selectedIndex>-1){for(F=K.selectedIndex,O=K.options.length;F<O;++F){D=K.options[F];if(D.selected){H[P++]=E+I((D.attributes.value&&D.attributes.value.specified)?D.value:D.text);}}}break;case"radio":case"checkbox":if(K.checked){H[P++]=E+N;}break;case"file":case undefined:case"reset":case"button":break;case"submit":default:H[P++]=E+N;}}}return H.join("&");}},true);},"3.0.0b1",{requires:["io-base"]});