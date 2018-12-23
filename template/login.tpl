<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta http-equiv="Content-Script-Type" content="text/javascript" />
  <link rel="shortcut icon" href="../icon.ico" />
  <link rel="stylesheet" type="text/css" href="./style.css" />
  <title>ログイン - ${bbs_title}</title>
  <script type="text/javascript" src="../count.php" charset="UTF-8"></script>
</head>
<body>
  {include file="menu_head.tpl"}
  <div id="body">
    <div id="title" class="center">
      <a href="./"><img src="image/title.gif" alt="${bbs_title}" title="${bbs_title}" width="616" height="57" /></a>
    </div>
    
    <div id="main">
      <h1>ログイン</h1>
      <form method="POST" action="./edit.php">
          <input type="hidden" name="${post["NUMBER"]}" value="${get["no"]}">
          <input type="hidden" name="${post["KIND"]}" value="${get["kind"]}">
          認証キー：<input type="password" name="${post["KEY"]}" size="10" maxlength="8" value="${user["key"]}" />
          &nbsp;&nbsp;<input type="submit" id="from_write" value="送信" />
      </form>
    </div>
  <div id="foot">
  　
  </div>
{include file="../../template/analytics.tpl"}
</body>
</html>
