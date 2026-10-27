var _csc_c0nnect_url_ = "http://www.csclive.in";
var _csc_vend_1d_ = "75185a02-7612-4077-be5f-cdcc03d23626";
var _csc_d0ma1n_1d_ = "f61a0286-e14d-4487-9021-a75de8bc2c73";
var _csc_c0nnected_5uccess_l0c_ = "";
var _csc_plug1n_vers10n_ = "v1.0";
var winParms = "menubar=0,resizable=0,status=0,scrollbars=0,top=0,left=0,width=800,height=700";
var frameParams = " height=\"700\" width=\"800\" scrolling=no scrollbar=no frameBorder=\"0\" ";
document.write("<div id=\"cscconnectcontainer15072011\"></div>");
document.write("<IMG SRC=\""+_csc_c0nnect_url_+"/cscconnect/images/connectbtn.jpg\" id=\"cscconnectionimg\" name=\"cscconnectionimg\" BORDER=\"0\" onclick=\"javascript:gocsc()\">");

var isOpera, isIE, isChrome, isFF = false;
var url = _csc_c0nnect_url_+"/cscconnect";
var params = "vendid="+_csc_vend_1d_+"&domid="+_csc_d0ma1n_1d_+"&ver="+_csc_plug1n_vers10n_+"&r="+document.location.href;
url = url+"?"+params+"&uts="+new Date().valueOf();
if(navigator.userAgent.indexOf('MSIE') > 0){isIE = true;}
if(navigator.userAgent.indexOf('MSIE') > 0 && navigator.userAgent.indexOf('8.0;') > 0){isIE = true;}
if(navigator.userAgent.indexOf('Chrome') > 0){isChrome = true;}
if(navigator.userAgent.indexOf('Firefox') > 0){isFF = true;}
if(navigator.userAgent.indexOf('Opera') > 0){isOpera = true;}

var cscConnectWin = null;
// FRAME CONTENTS
var _csc_connector__ChildContent = "<!DOCTYPE HTML> <HTML> <HEAD> <TITLE> CSCConnect </TITLE> </HEAD><BODY topmargin=\"0\" leftmargin=\"0\"><iframe id=\"_csc_connector_frame_\" src=\""+url+"\" "+frameParams+"></iframe> <script LANGUAGE=\"JavaScript\"> var _origURL = document.location.href; var csc_connectInfo_setter = function(e) { createCookie('__csc_connect__inf0_',e.data.replace('^^||^^','='),0); var _c_b_i_t_s_ = e.data.split('&'); if(_c_b_i_t_s_.length>=7) {_c_b_i_t_s_ = _c_b_i_t_s_[6].split('=')[1]; _c_b_i_t_s_ = _c_b_i_t_s_.replace('^^||^^','=');} else {_c_b_i_t_s_ = this.top.opener.location;} if(_c_b_i_t_s_ == '') _c_b_i_t_s_ = _origURL; this.top.opener.location=_c_b_i_t_s_+''; window.close();}; if(window.postMessage) { if(typeof window.addEventListener != 'undefined') { window.addEventListener('message', csc_connectInfo_setter, false); } else if(typeof window.attachEvent != 'undefined') { window.attachEvent('onmessage', csc_connectInfo_setter); } else if(typeof document.attachEvent != 'undefined') { window.attachEvent('onmessage', csc_connectInfo_setter); } } else { var _poller = function() { if(document.location.href != _origURL) { var retVal = document.location.href; retVal = retVal.split(\"#\")[1]; createCookie(\"__csc_connect__inf0_\",retVal.replace('^^||^^','='),0); var _c_b_i_t_s_ = retVal.split('&'); if(_c_b_i_t_s_.length>=7 && _c_b_i_t_s_[6].split('=')[1] != '') {_c_b_i_t_s_ = _c_b_i_t_s_[6].split('=')[1]; _c_b_i_t_s_ = _c_b_i_t_s_.replace('^^||^^','=');} else {_c_b_i_t_s_ = _origURL} this.top.opener.location=_c_b_i_t_s_+''; clearInterval(_pollerInterval); window.close();} }; var _pollerInterval = setInterval(_poller, 1000); } function createCookie(name,value,days) { if (days) { var date = new Date(); date.setTime(date.getTime()+(days*24*60*60*1000)); var expires = \"; expires=\"+date.toGMTString(); } else var expires = \"\"; document.cookie = name+\"=\"+value+expires+\"; path=/\"; } function readCookie(name) { var nameEQ = name + \"=\"; var ca = document.cookie.split(';'); for(var i=0;i < ca.length;i++) { var c = ca[i]; while (c.charAt(0)==' ') c = c.substring(1,c.length); if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length); } return null; } function eraseCookie(name) { createCookie(name,\"\",-1); } </script></BODY> </HTML>";

var _cscConnect_inf0__CookieVal = readCookie('__csc_connect__inf0_');
if (_cscConnect_inf0__CookieVal) {
	document.cscconnectionimg.src = _csc_c0nnect_url_+"/cscconnect/images/connectedbtn.jpg";
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

var clickedOnce = false;
function gocsc(){
	if (!clickedOnce){
		clickedOnce = true;
		if (_cscConnect_inf0__CookieVal) {
			alert("You are already authenticated as a valid CSC. \n\nYour CSC ID is " + _cscConnect_inf0__CookieVal.split("&")[0].split("=")[1]);
			return;
		}
		if(cscConnectWin != null && !cscConnectWin.closed){
			// do nothing as a child window is still open
		} else {
			if (isOpera){
				cscConnectWin = window.open("","cscConnectWinParm",winParms);
				cscConnectWin.document.write(_csc_connector__ChildContent);
			} else if (isFF){
				cscConnectWin = window.open("","cscConnectWinParm",winParms);
				cscConnectWin.document.write(_csc_connector__ChildContent);
			} else if (isChrome){
				cscConnectWin = window.open("","cscConnectWinParm",winParms);
				cscConnectWin.document.write(_csc_connector__ChildContent);
			} else {
				cscConnectWin = window.open("","cscConnectWinParm",winParms);
				cscConnectWin.document.write(_csc_connector__ChildContent);
			}
		}
		clickedOnce = false;
	}
}
