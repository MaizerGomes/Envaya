
function Class(){}
function makeClass($base)
{$base=$base||Class;var $class=function(){this.init.apply(this,arguments);};var $proto=function(){};$proto.prototype=$base.prototype;$class.prototype=new $proto;return $class;}
function addEvent(elem,type,fn)
{if(elem.addEventListener)
{elem.addEventListener(type,fn,false);}
else
{elem.attachEvent('on'+type,fn);}}
function removeEvent(elem,type,fn)
{if(elem.removeEventListener)
{elem.removeEventListener(type,fn,false);}
else
{elem.detachEvent('on'+type,fn);}}
var _jsonCache={};function fetchJson(url,fn)
{if(_jsonCache[url])
{setTimeout(function(){fn(_jsonCache[url]);},1);return null;}
else
{var xhr=(window.ActiveXObject&&!window.XMLHttpRequest)?new ActiveXObject("Msxml2.XMLHTTP"):new XMLHttpRequest();xhr.onreadystatechange=function()
{if(xhr.readyState==4&&xhr.status==200)
{var $data;eval("$data = "+xhr.responseText);_jsonCache[url]=$data;fn($data);}};xhr.open("GET",url,true);xhr.send(null);return xhr;}}
function bind(obj,fn)
{return function(){return fn(obj);};}
function removeChildren(elem)
{while(elem.firstChild)
{elem.removeChild(elem.firstChild);}}
function removeElem(elem)
{if(elem.parentNode)
{elem.parentNode.removeChild(elem);}}
function createElem()
{var elemType=arguments[0];var el=document.createElement(elemType);for(var i=1;i<arguments.length;i++)
{var arg=arguments[i];switch(typeof(arg))
{case'string':el.appendChild(document.createTextNode(arg));break;case'object':if(arg!=null)
{if(arg.nodeName)
{el.appendChild(arg);}
else
{for(var key in arg)
{if(arg.hasOwnProperty(key))
{var val=arg[key];if(typeof(val)=='function')
{addEvent(el,key,val);}
else
{el[key]=arg[key];}}}}}
break;}}
return el;}
window.dirty=false;function setDirty($dirty)
{if($dirty&&!window.submitted)
{if(!window.onbeforeunload)
{window.onbeforeunload=function(){return<?php echo json_encode(__("page:dirty"))?>;};}}
else
{window.onbeforeunload=null;}
window.dirty=$dirty;return true;}
function setSubmitted()
{setDirty(false);window.submitted=true;return true;}