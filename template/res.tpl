<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta http-equiv="Content-Script-Type" content="text/javascript" />
  <link rel="shortcut icon" href="../icon.ico" />
  <link rel="stylesheet" type="text/css" href="./style.css" />
  <title>${bbs_title}</title>
  <script type="text/javascript" src="../count.php" charset="UTF-8"></script>
  <script type="text/javascript" src="../js/prototype.js" charset="UTF-8"></script>
  <script type="text/javascript" src="./js/function.js" charset="UTF-8"></script>
</head>
<body>
  {include file="menu_head.tpl"}
  <div id="body">
    <div id="title">
      <a href="../"><img src="./image/title.gif" alt="${bbs_title}" title="${bbs_title}" width="616" height="57" /></a>
    </div>
    
    
    <div id="main">
    
      {include file="menu.tpl"}
      
      <h1>${topic["title"]}</h1>
      <div id="list">
        [${topic["id"]}] <a href="#topics">${topic["name"]}</a><br />
        %{FOREACH($res as $value) : $i++;}
          ・ <a href="#${i}">${value["name"]}</a><br />
        %{ENDFOREACH}
      </div>
      <div id="topics">
      
      
        <div class="topic">
          <h2>No:${topic["id"]}  Title: ${topic["title"]}</h2>
          <div class="board">
            <h3 class="block_left">
              Name: ${topic["name"]}
            </h3>
            <p class="block_right">
              [<a href="#form" title="返信フォーム">返信</a>]
              [<a href="./edit.php?type=login&kind=topic&no=${topic["id"]}" title="編集・削除">編集</a>]
            </p>
            
            
            %{IF( $topic["url"] ) : }
            <a href="${topic["url"]}">
            <img src="./image/ahome.gif" alt="ホームページ" title="ホームページ" />
            </a>
            %{ENDIF}
            %{IF( $topic["mail"] ) : }
            <a href="mailto:${topic["mail"]}">
            <img src="./image/amail.gif" alt="メール" title="メール" />
            </a>
            %{ENDIF}
            %{IF( $topic["mail"] || $topic["url"] ) : }
            <br />
            %{ENDIF}
            
            
            ${topic["text"]}
            <p class="right">${topic["time"]}</p>
          </div>
        </div>
        
        
        
        %{$i = 0; FOREACH($res as $value) : $i++;}
        <div class="res" id="${i}">
          <h2>Title: ${value["title"]}</h2>
          <div class="board">
            <h3 class="block_left">
              Name: ${value["name"]}
            </h3>
            <p class="block_right">
              [<a href="#form" title="返信フォーム">返信</a>]
              [<a href="./edit.php?type=login&kind=res&no=${value["id"]}" title="編集・削除">編集</a>]
            </p>
            
            
            %{IF( $value["url"] ) : }
            <a href="${value["url"]}">
            <img src="./image/ahome.gif" alt="ホームページ" title="ホームページ" />
            </a>
            %{ENDIF}
            %{IF( $value["mail"] ) : }
            <a href="mailto:${value["mail"]}">
            <img src="./image/amail.gif" alt="メール" title="メール" />
            </a>
            %{ENDIF}
            %{IF( $value["mail"] || $value["url"] ) : }
            <br />
            %{ENDIF}
            
            
            ${value["text"]}
            <p class="right">${value["time"]}</p>
          </div>
        </div>
        %{ENDFOREACH}
        
        
        <span id="preview" class="none">
        <div class="res" id="pre">
          <h2>Title: <span id="pre_from_title"></span></h2>
          <div class="board">
            <h3 class="block_left">
              Name: <span id="pre_from_name"></span>
            </h3>
            <p class="block_right">
              [返信]
              [編集]
            </p>
            <span id="pre_from_url"></span>
            <span id="pre_from_mail"></span>
            <span id="pre_br"></span>
            
            <span id="pre_from_text"></span>
            <p class="right"><span id="pre_from_time"></span></p>
          </div>
        </div>
        <span id="pre_title" class="none"></span>
        <span id="pre_name" class="none"></span>
        </span>
        
        
      </div>
    </div>
    <div id="form">
      <form method="POST" action="./tp${topic["id"]}" onsubmit="return submitForm()">
        <table>
          <input type="hidden" name="${post["MODE"]}" value="${mode}">
          <input type="hidden" name="no" value="${topic["id"]}">
          <tr>
            <th>名前</th>
            <td><input type="text" id="from_name" name="${post["NAME"]}" size="50" maxlength="40" value="${user["name"]}" /></td>
          </tr>
          <tr>
            <th>E-Mail</th>
            <td><input type="text" id="from_mail" name="${post["MAIL"]}" size="50" maxlength="100" value="${user["mail"]}" /></td>
          </tr>
          <tr>
            <th>URL</th>
            <td><input type="text" id="from_url" name="${post["URL"]}" size="50" maxlength="100" value="${user["url"]}" /></td>
          </tr>
          <tr>
            <th>タイトル</th>
            <td><input type="text" id="from_title" name="${post["TITLE"]}" size="50" maxlength="50" value="Re:${topic["title"]}" /></td>
          </tr>
          <tr>
            <th></th>
            <td id="from_tag_ber" class="none">
              <input type="button"  onclick="tag_enclosure('code');" value="コード">
              <input type="button"  onclick="tag_enclosure('b');" value="太文字">
              <input type="button"  onclick="tag_enclosure('i');" value="斜文字">
              <input type="button"  onclick="tag_enclosure('u');" value="下線">
              <!-- <input type="button"  onclick="tag_enclosure('url', 'link');" value="URL"><input type="text" id="link" size="40" maxlength="100" value="http://"> -->
            </td>
          </tr>

          <tr>
            <th>コメント</th>

            <td><textarea id="from_text" name="${post["TEXT"]}" cols="60" rows="12">${user["text"]}</textarea></td>
          </tr>
          <tr>
            <th>認証キー</th>
            <td><input type="password" name="${post["KEY"]}" size="10" maxlength="8" value="${user["key"]}" />&nbsp;<small>(最大8文字)</small></td>
          </tr>
          <tr>
            <th></th>
            <td>
              <span id="pre_check" class="none"><input type="checkbox" name="${post["PREVIEW"]}" value="checked" id="from_preview" checked="checked" /><small>プレビュー</small></span>
              <input type="checkbox" name="${post["COOKIE"]}" value="checked" ${user["cookie"]} /><small>クッキー保存</small>
              &nbsp;&nbsp;<input type="submit" id="from_write" value="書き込む" />
            </td>
          </tr>
        </table>
      </form>
    </div>
  </div>
  <div id="foot">
  　
  </div>
{include file="../../template/analytics.tpl"}
</body>
</html>
