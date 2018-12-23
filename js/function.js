var writing_flag = 0;
week = new Array( "日" , "月" , "火" , "水" , "木" , "金" , "土" ) ;

window.onload = function() {
    $('from_preview').checked = false;
    Event.observe('from_text', 'keyup', preview_text);
    Event.observe('from_name', 'keyup', preview_name);
    Event.observe('from_title', 'keyup', preview_title);
    Event.observe('from_url', 'keyup', preview_mail_url);
    Event.observe('from_mail', 'keyup', preview_mail_url);
    Event.observe('from_name', 'blur', errorCheck);
    Event.observe('from_url', 'blur', errorCheck);
    Event.observe('from_mail', 'blur', errorCheck);
    Event.observe('from_title', 'blur', errorCheck);
    Event.observe('from_text', 'blur', errorCheck);
    Event.observe('from_preview', 'change', view);
    Element.setStyle($('from_tag_ber'), "display:block");
    $('from_preview').checked = false;
    Element.setStyle($('pre_check'), "display:inline");
    view();
}

window.onbeforeunload = function() {
    if($("from_text").value != "" && writing_flag != 1) {
        return "コメントに入力情報があります。\nページを移動すると入力情報は失われてしまいますがよろしいでしょうか？";
    }
}

function submitForm() {
    writing_flag = 1;
    return true;
}

function errorCheck(event) {
    var msge;
    msge = "";
    
    if($F("from_title") == "") {
        msge = "タイトルが未記入です";
    } else if($F("from_title").length > 100) {
        msge = "タイトルが最大数を超えています";
    } else if($F("from_name") == "") {
        msge = "名前が未記入です";
    } else if($F("from_name").length > 64) {
        msge = "名前が最大数を超えています";
    } else if($F("from_mail").length > 128) {
        msge = "E-Mailが最大数を超えてます";
    } else if(!$F("from_mail").match(/^([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})$|^$|^$/i)) {
        msge = "E-Mailが正しくありません";
    } else if($F("from_url").length > 128) {
        msge = "URLが最大数を超えています";
    } else if(!$F("from_url").match(/^http:\/\/|^$/i)) {
        msge = "そのURLは登録できません";
    } else if($F("from_text") == "") {
        msge = "コメントが未記入です";
    } else if($F("from_text").length > 1000) {
        msge = "コメントが最大数を超えています";
    } else if(!$F("from_text").match(/.*[ぁ-んァ-ン]/)) {
        msge = "コメントにひらがなを含めてください";
    }
    
    if(msge != "") {
        $('from_write').value = msge;
        $('from_write').disabled = true; 
    } else {
        $('from_write').value = "書き込む";
        $('from_write').disabled = false; 
    }
}

function preview_text() {
    var str  = form_encode( $F("from_text") );
    str = replace_tags( str );
    str = program_highlight( str );
    $("pre_from_text").innerHTML = str;
}

function preview_name() {
    var str  = form_encode( $F("from_name") );
    str = replace_tags( str );
    str = program_highlight( str );
    $("pre_from_name").innerHTML = str;
    $("pre_name").innerHTML = str;
}

function preview_title() {
    var str  = form_encode( $F("from_title") );
    str = replace_tags( str );
    str = program_highlight( str );
    $("pre_from_title").innerHTML = str;
    $("pre_title").innerHTML = str;
}


function preview_mail_url() {
    var flag;
    
    if ($("from_mail").value != "") {
        $("pre_from_mail").innerHTML = "<a href=\"mailto:\"><img src=\"./image/amail.gif\" alt=\"メール\" title=\"メール\" /></a>";
        flag = true;
    } else {
        $("pre_from_mail").innerHTML = "";
    }
    
    if ($F("from_url").match(/^http:\/\/.+/i)) {
        $("pre_from_url").innerHTML = "<a href=\"\"><img src=\"./image/ahome.gif\" alt=\"ホームページ\" title=\"ホームページ\" /></a>";
        flag = true;
    } else {
        $("pre_from_url").innerHTML = "";
    }
    
    if (flag == true) {
        $("pre_br").innerHTML = "<br />";
    } else {
        $("pre_br").innerHTML = "";
    }
}

function view() {
    if( $('from_preview').checked ) {
        Element.setStyle($('preview'), "display:block");
        today = new Date();
        var YYYY = today.getFullYear();
        var MM   = today.getMonth()+1;
        if(MM < 10) MM = "0" + MM;
        var DD   = today.getDate();
        if(DD < 10) DD = "0" + DD;
        var HH   = today.getHours();
        if(HH < 10) HH = "0" + HH;
        var TT   = today.getMinutes();
        if(TT < 10) TT = "0" + TT;
        var DAY = week[today.getDay()];

        var time = YYYY + "/" + MM + "/" + DD + "(" + DAY + ") " + HH + ":" + TT;
        
        preview_title();
        preview_name();
        preview_text();
        $("pre_from_time").innerHTML = time;
    } else {
        Element.setStyle($('preview'), "display:none");
    }
}

function tag_enclosure( tag, option )
{
    var pos = new Object();
     
    if( Prototype.Browser.IE ) {
        Field.focus('from_text');
        var range = document.selection.createRange();
        var clone = range.duplicate();
        
        clone.moveToElementText( $('from_text') );
        clone.setEndPoint( 'EndToEnd', range );
        
        pos.start = clone.text.length - range.text.length;
        pos.end   = clone.text.length;
    } else if( Prototype.Browser.Gecko ) {
        pos.start = $('from_text').selectionStart;
        pos.end   = $('from_text').selectionEnd;
    } else {
        return false;
    }
    
    var value  = $('from_text').value;
    var startStr  = value.substring( 0, pos.start );
    var middleStr = value.substring( pos.start, pos.end );
    var endStr    = value.substring( pos.end, value.length );
    
    $('from_text').value = startStr + "[" + tag;
    if( option ) {
        $('from_text').value += "=" + $( option ).value;
    }
    $('from_text').value += "]" + middleStr + "[/" + tag + "]"
    
    var Strlen = $('from_text').value.length;
    
    $('from_text').value += endStr;
    Field.focus('from_text');
    
    if( Prototype.Browser.Gecko ) {
        $('from_text').selectionStart = Strlen;
        $('from_text').selectionEnd   = Strlen;
    }
}

function form_encode( str )
{
    str = str.escapeHTML();
    str = str.replace(/'/g, '&#39;' );
    str = str.replace(/"/g, '&quot;' );
    str = str.replace(/,/g, '&#44;' );
    str = str.replace(/ /g, '&nbsp;' );
    str = str.replace(/　/g, '&nbsp;&nbsp;' );
    str = str.replace(/\t/g, '&nbsp;&nbsp;&nbsp;&nbsp;' );
    str = str.replace(/\r/g, '' );
    str = str.replace(/\n/g, '<br />' );
    
    return str;
}

function replace_tags( str )
{
    str = str.replace(/\[b\](.*?)\[\/b\]/ig, "<strong>$1</strong>" );
    str = str.replace(/\[i\](.*?)\[\/i\]/ig, "<em>$1</em>" );
    str = str.replace(/\[u\](.*?)\[\/u\]/ig, "<u>$1</u>" );
    str = str.replace(/\[url\=(http:\/\/[a-zA-Z\.\/\-=&%?,;#]*)\](.*?)\[\/url\]/ig, "<a href=\"$1\" target=\"_blank\">$2</a>" );
    str = str.replace(/\[color\=(#[0-9a-f]{6}?)\](.*?)\[\/color\]/ig, "<span color=\"$1\">$2</span>" );
    str = str.replace(/\[code](<br \/>)?(.*?)(<br \/>)*\[\/code\]/ig, "<code>$2</code>" );
    str = str.replace(/\[code\=([0-9]{1,5}?)\](<br \/>)?(.*?)(<br \/>)*\[\/code\]/ig, "<code num=\"$1\">$3</code>" );
    
    return str;
}

function program_highlight( str )
{
    var tmp = str.match( /<code( num="([0-9]{1,5}?)")?>(.*?)<\/code>/g );
    
    if( tmp ) {
        tmp.each( function ( value ) {
            var num_str = ""; num = value.match( /<code( num="([0-9]{1,5}?)")?>/ );
            var len      = value.match( /<br \/>/g );
            var count;
            
            if( num[2] ) {
                count = num[2];
            } else {
                count  = 1;
                num[2] = 1;
            }
            
            if( len ) {
                len = len.length;
            } else {
                len = 0;
            }
            
            for( ; count <= len + Number(num[2]); count++ ) {
                num_str += count + "<br />";
            }
            
            value = value.replace(/(&#39;.*?&#39;)/g, "<span class=\"string\">$1</span>" );
            value = value.replace(/(&quot;.*?&quot;)/g, "<span class=\"string\">$1</span>" );
            value = value.replace(/(&|;|<|,|\()(int|double|float|fool|long|const|char|void|return|if|for|while|switch|case|while|extern|#define|#include)(&|;|<|,|\)|)/g, "$1<span class=\"keyword\">$2</span>$3" );
            value = value.replace(/(\/\*.*?\*\/|\/\/.*?<br \/>)/g, "<span class=\"comment\">$1</span>" );
            
            str = str.replace(/<code( num="([0-9]{1,5}?)")?>(.*?)<\/code>/, "</p><div class=\"code\"><table><tr><th>" + num_str + "</th><td>" + value + "</td></tr></table></div><p>");
        });
    }
    
    return str;
}
