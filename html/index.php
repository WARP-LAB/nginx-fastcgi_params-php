<?php
function testParams($testArr, $ignoreArr = array()) { ?>
  <table>
  <?php foreach ($testArr as $arg) {
        if (in_array($arg, $ignoreArr)) {
          continue;
        }
        $keyName = htmlentities($arg);
        $keySet = isset($_SERVER[$arg]);
        $value = !$keySet ? 'NOT SET' : (empty($_SERVER[$arg]) ? 'empty' : htmlentities($_SERVER[$arg]));
        $style = ($keySet && !empty($_SERVER[$arg])) ? '' : 'text-align:right;font-style:italic;font-weight:bold;color:#404040;';
    ?>
      <tr>
        <td><?php echo $keyName; ?></td>
        <td style="<?php echo $style; ?>"><?php echo $value; ?></td>
      </tr>
  <?php } ?>
  </table>
<?php } ?>

<?php
$rfc3875 = array(
  'AUTH_TYPE',
  'CONTENT_LENGTH',
  'CONTENT_TYPE',
  'GATEWAY_INTERFACE',
  'PATH_INFO',
  'PATH_TRANSLATED',
  'QUERY_STRING',
  'REMOTE_ADDR',
  'REMOTE_HOST',
  'REMOTE_IDENT',
  'REMOTE_USER',
  'REQUEST_METHOD',
  'SCRIPT_NAME',
  'SERVER_NAME',
  'SERVER_PORT',
  'SERVER_PROTOCOL',
  'SERVER_SOFTWARE'
);
?>

<?php
$requiredExtra = array(
  'SCRIPT_FILENAME',
  'REDIRECT_STATUS', 
  'HTTP_PROXY'
);
?>

<?php
$phpManual = array(
  'PHP_SELF',
  'GATEWAY_INTERFACE',
  'SERVER_ADDR',
  'SERVER_NAME',
  'SERVER_SOFTWARE',
  'SERVER_PROTOCOL',
  'REQUEST_METHOD',
  'REQUEST_TIME',
  'REQUEST_TIME_FLOAT',
  'QUERY_STRING',
  'DOCUMENT_ROOT',
  'HTTP_ACCEPT',
  'HTTP_ACCEPT_CHARSET',
  'HTTP_ACCEPT_ENCODING',
  'HTTP_ACCEPT_LANGUAGE',
  'HTTP_CONNECTION',
  'HTTP_HOST',
  'HTTP_REFERER',
  'HTTP_USER_AGENT',
  'HTTPS',
  'REMOTE_ADDR',
  'REMOTE_HOST',
  'REMOTE_PORT',
  'REMOTE_USER',
  'REDIRECT_REMOTE_USER',
  'SCRIPT_FILENAME',
  'SERVER_ADMIN',
  'SERVER_PORT',
  'SERVER_SIGNATURE',
  'PATH_TRANSLATED',
  'SCRIPT_NAME',
  'REQUEST_URI',
  'PHP_AUTH_DIGEST',
  'PHP_AUTH_USER',
  'PHP_AUTH_PW',
  'AUTH_TYPE',
  'PATH_INFO',
  'ORIG_PATH_INFO'
);
?>

<!-- Reuse CSS injected by phpinfo() -->
<style type="text/css">
body {background-color: #fff; color: #222; font-family: sans-serif;}
pre {margin: 0; font-family: monospace;}
table {border-collapse: collapse; border: 0; width: 934px; box-shadow: 1px 2px 3px #ccc;}
td, th {border: 1px solid #666; font-size: 75%; vertical-align: baseline; padding: 4px 5px;}
th {position: sticky; top: 0; background: inherit;}
</style>

<!-- <img src="/image.png" alt="" style="display:block;clear:both;"/> -->

<h1>I am file <u>/index.php</u>, placed at root.</h1>
<p>Inspect variables below.</p>

<h3> Some useful links</h3>
<ul style="text-decoration:underline;">
  <li><a href="/">/</a></li>
  <li><a href="/?q=1">/?q=1</a></li>
  <li><a href="/foo/bar">/foo/bar</a></li>
  <li><a href="/foo/bar?q=1">/foo/bar?q=1</a></li>
  <li><a href="/foo/bar/?q=1">/foo/bar/?q=1</a></li>
  <li><a href="/index.php">/index.php</a></li>
  <li><a href="/index.php/?q=1">/index.php/?q=1</a></li>
  <li><a href="/index.php/foo/bar">/index.php/foo/bar</a></li>
  <li><a href="/index.php/foo/bar?q=1">/index.php/foo/bar?q=1</a></li>
  <li><a href="/index.php/foo/bar/?q=1">/index.php/foo/bar/?q=1</a></li>
</ul>

<h1>Magic constants test</h1>
<table>
    <tr>
      <td>__FILE__</td>
      <td><?php echo __FILE__; ?></td>
    </tr>
    <tr>
      <td>__DIR__</td>
      <td><?php echo __DIR__; ?></td>
    </tr>
</table>

<h1>Standard CGI/1.1 Request Meta-Variables</h1>
<?php testParams($rfc3875); ?>

<h1>Required extras</h1>
<?php testParams($requiredExtra); ?>

<h1>PHP Manual Language Reference Predefined Variables</h1>
<p>Only those variables which are not in <em>Standard</em> and <em>Required extras</em> arrays are printed.</p>
<?php
  testParams(
    $phpManual,
    array_merge(
      $rfc3875,
      $requiredExtra
      )
    ); ?>

<h1>All other $_SERVER variables</h1>
<p>Only those variables which are not in <em>Standard</em>, <em>Required extras</em>, and <em>Predefined Variables</em> arrays are printed.</p>

<table>
<?php
$ignoreKeys = array_merge($rfc3875, $requiredExtra, $phpManual);
foreach ($_SERVER as $k => $v) {
    if (in_array($k, $ignoreKeys)) {
      continue;
    }
    $key = htmlentities($k);
    $value = htmlentities($v);
    ?>
    <tr>
      <td><?php echo $key; ?></td>
      <td><?php echo $value; ?></td>
    </tr>
<?php } ?>
</table>

<h1>phpinfo()</h1>
<?php phpinfo();
