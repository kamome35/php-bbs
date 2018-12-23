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
</head>
<body>
  {include file="menu_head.tpl"}
  <div id="body">
    <div id="title">
      <a href="./"><img src="image/title.gif" alt="${bbs_title}" title="${bbs_title}" width="616" height="57" /></a>
    </div>
    
    <div id="main">
    
      {include file="menu.tpl"}
      
      <table>
        <tr>
          <th><!-- 新着 --></th>
          <th>トピックタイトル</th>
          <th>最終更新</th>
          <th>記事数</th>
          <th>トピック作成者</th>
          <th>最終投稿者</th>
          <th>状態</th>
        </tr>
        %{FOREACH( $topic as $value ) :}
        
        <!-- ▼トピック - ${value["title"]} -->
        <tr>
          <!-- 新着 -->
          <td class="center">
            %{IF( $value["new_flag"] === true ) : }
              <img src='./image/new.gif'>
            %{ENDIF}
          </td>
          
          <!-- トピックタイトル -->
          <td>
            <!-- タイトル -->
            <a href="./tp${value["id"]}">${value["title"]}</a>
            <!-- 一部引用 -->
            <div>${value["quoted"]}</div>
          </td>
          
          <!--- 最終更新 -->
          <td class="center">${value["time"]}</td>
          
          <!-- 記事数 -->
          <td class="center">${value["res_num"]}</td>
          
          <!-- トピック作成者 -->
          <td class="center">${value["name"]}</td>
          
          <!--- 最終投稿者 -->
          <td class="center">${value["last_person"]}</td>
          
          <!-- 状態 -->
          <td class="center">${value["state"]}</td>
        </tr>
        %{ENDFOREACH}
      </table>
    </div>
  </div>
  <div id="foot">
  　
  </div>
{include file="../../template/analytics.tpl"}
</body>
</html>
