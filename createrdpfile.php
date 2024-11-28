<?php
 if($_REQUEST['domain']=='') { $var['domain']=''; } else { $var['domain']=$_REQUEST['domain']; }
 if($_REQUEST['username']=='') { $var['username']=''; } else { $var['username']=$_REQUEST['username']; }

 if($_REQUEST['autoconnect']==1)
 {
  header("Content-type: application/rdp");
  header("Content-disposition: attachment; filename=".$_REQUEST['name'].".rdp");

 ?>
screen mode id:i:2<?php echo "\r\n"; ?>
compression:i:1<?php echo "\r\n"; ?>
keyboardhook:i:2<?php echo "\r\n"; ?>
audiomode:i:2<?php echo "\r\n"; ?>
redirectdrives:i:0<?php echo "\r\n"; ?>
redirectprinters:i:0<?php echo "\r\n"; ?>
redirectcomports:i:0<?php echo "\r\n"; ?>
redirectsmartcards:i:1<?php echo "\r\n"; ?>
displayconnectionbar:i:1<?php echo "\r\n"; ?>
autoreconnection enabled:i:1<?php echo "\r\n"; ?>
alternate shell:s:<?php echo "\r\n"; ?>
shell working directory:s:<?php echo "\r\n"; ?>
disable wallpaper:i:1<?php echo "\r\n"; ?>
disable full window drag:i:1<?php echo "\r\n"; ?>
disable menu anims:i:1<?php echo "\r\n"; ?>
disable themes:i:1<?php echo "\r\n"; ?>
disable cursor setting:i:0<?php echo "\r\n"; ?>
bitmapcachepersistenable:i:1<?php echo "\r\n"; ?>
session bpp:i:32<?php echo "\r\n"; ?>
smart sizing:i:1<?php echo "\r\n"; ?>
use multimon:i:0<?php echo "\r\n"; ?>
span monitors:i:0<?php echo "\r\n"; ?>
full address:s:<?php echo $_REQUEST['full_address']."\r\n"; ?>
username:s:<?php echo $_REQUEST['username']."\r\n"; ?>
domain:s:<?php echo $_REQUEST['domain']."\r\n"; ?>
<?php
 }
 else //query the user for variables
 {
  ?>

<u><b>Variables</b></u>:
<html><head><title>RDP Builder</title></head><body><form action='<?php echo $_REQUEST['SERVER_URI']; ?>' method='get'>
<table cellpadding=0 cellspacing=0 border=.2>
<tr>
 <td> Server hostname/IP: </td>
 <td> <input name='full_address' type='text' size='20' value='<?php echo $_REQUEST['full_address']; ?>' /> </td>
 <td> (Examples: <b>rdp.example.com</b>, <b>192.168.0.100</b>, <b>192.168.0.100:3389</b>) </td>
</tr>
<tr>
 <td> Alternate hostname/IP: </td>
 <td> <input name='alternate_full_address' type='text' size='20' value='<?php echo $_REQUEST['alternate_full_address']; ?>' /> </td>
 <td> (Same format as above) </td>
</tr>
<tr>
 <td> Domain: </td>
 <td> <input name='domain' type='text' size='20' value='<?php echo $_REQUEST['domain']; ?>' /> </td>
 <td> (Examples: <b>rdp.example.com</b>, <b>MY_HOST</b>, <b>SomeName</b>.  Default: <i>leave blank</b>) </td>
</tr>
<tr>
 <td> Username: </td>
 <td> <input name='username' type='text' size='20' value='<?php echo $_REQUEST['username']; ?>' /> </td>
 <td> (If blank you will be prompted later.  Password is always prompted later.) </td>
</tr>
<tr>
 <td> Initial RD Window Properties </td>
 <td> <select name='screen_mode_id'>
        <option value=''><br /></option>
        <option value='1' <?php if($var['screen_mode_id']==1) { echo "selected"; } ?>>Windowed</option>
        <option value='2' <?php if($var['screen_mode_id']==2) { echo "selected"; } ?>>Fullscreen</option></select> </td>
 <td>  </td>
</tr>
<tr>
 <td> Scale Contents on RD Window Resize </td>
 <td> <select name='smart_sizing'><option value='1' <?php if($var['smart_sizing']==1) { echo "selected"; } ?>>Yes</option>
        <option value='2' <?php if($var['smart_sizing']==2) { echo "selected"; } ?>>No</option></select> </td>
 <td>  </td>
</tr>

<tr>
 <td> Use Multiple Monitors </td>
 <td> <select name='use_multimon'><option value='0' <?php if($var['use_multimon']==0) { echo "selected"; } ?>>No</option>
        <option value='1' <?php if($var['use_multimon']==1) { echo "selected"; } ?>>Yes</option> </td>
 <td> Note:<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;I. Only supported by default in Windows 7 and Windows Server 2008.
        If you are on Vista or XP, upgrade your client for free <a href='http://support.microsoft.com/kb/969084' target='_blank'>here</a>.
        <br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;II. Only supported if the server you connect to is Windows 7 Ultimate or Windows Server 2008 Enterprise+ </td>
</tr>
<tr>
 <td> Use Monitor Spanning </td>
 <td> <select name='span_monitors'><option value='0' <?php if($var['span_monitors']==0) { echo "selected"; } ?>>No</option>
        <option value='1' <?php if($var['span_monitors']==1) { echo "selected"; } ?>>Yes</option> </td>
 <td> Note: Has no bearing if "Use Multiple Monitors" is set to "Yes".  This is for clients and servers that don't support multiple monitors.<br />
        There are resolution restrictions.  See this page </td>
</tr>

<tr>
 <td> Window Width </td>
 <td> <input type='text' name='desktopwidth' size='5' value='<?php echo $_REQUEST['desktopwidth']; ?>' /> </td>
 <td> Note: has no bearing if "Use Multiple Monitors" or "Use Monitor Spanning" is set. </td>
</tr>
<tr>
 <td> Window Height </td>
 <td> <input type='text' name='desktopheight' size='5' value='<?php echo $_REQUEST['desktopheight']; ?>' /> </td>
 <td> Note: has no bearing if "Use Multiple Monitors" or "Use Monitor Spanning" is set. </td>
</tr>
<tr>
 <td>  </td>
 <td>  </td>
 <td>  </td>
</tr>
<tr>
 <td>
  <input type='hidden' name='autoconnect' value='1' />
  <input type='hidden' name='name' value='<?php echo $_REQUEST['name']; ?>' />
  <input type='hidden' name='password_51' value='<?php echo $_REQUEST['password_51']; ?>' />
 </td>
 <td> <input type='submit' value='Launch RD' /> </td>
 <td> </td>
</tr>
</table>
</form>
</body></html>

 <?php
 }

?>
