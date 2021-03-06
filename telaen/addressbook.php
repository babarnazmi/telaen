<?php
/************************************************************************
Telaen is a GPL'ed software developed by 

 - The Telaen Group
 - http://jimjag.github.io/telaen/

*************************************************************************/

define('I_AM_TELAEN', basename($_SERVER['SCRIPT_NAME']));

// load session management
require("./inc/init.php");

// meta assigned to smarty
$smarty->assign("pageMetas", $nocache);

$filename = $userfolder."_infos/addressbook.ucf";
$myfile = $UM->_read_file($filename);

if($myfile != "")
	$addressbook = unserialize(base64_decode($myfile));
array_qsort2ic($addressbook,"name");

$jssource = $commonJS;
$jssource .= "
<script type=\"text/javascript\">
//<![CDATA[
function newmsg() { location = 'newmsg.php?pag=$pag&folder=".urlencode($folder)."'; }
function refreshlist() { location = 'addressbook.php' }
function folderlist() { location = 'folders.php?folder=".urlencode($folder)."'}
function search() { location = 'search.php'; }
function emptytrash() { location = 'folders.php?empty=trash&folder=".urlencode($folder)."&goback=true';}
//]]>
</script>
";

$smarty->assign("umJS",$jssource);
$smarty->assign("umGoBack","addressbook.php");

extract(pull_from_array($_GET, Array("opt"), "str"));
extract(pull_from_array($_POST, Array("name", "email", "street", "city", "state", "work", "opt"), "str"));
extract(pull_from_array($_GET, Array("id"), 1));
extract(pull_from_array($_POST, Array("id"), 1));

switch($opt) {
	// save an edited contact

	case "save":
		$addressbook[$id]["name"] = $name;
		$addressbook[$id]["email"] = $email;
		$addressbook[$id]["street"] = $street;
		$addressbook[$id]["city"] = $city;
		$addressbook[$id]["state"] = $state;
		$addressbook[$id]["work"] = $work;

		$UM->_save_file($filename,base64_encode(serialize($addressbook)));

		$smarty->assign("umOpt",1);
		$templatename = "address-results.htm";

		break;

	// add a new contact
	case "add":
		$id = count($addressbook);
		$addressbook[$id]["name"] = $name;
		$addressbook[$id]["email"] = $email;
		$addressbook[$id]["street"] = $street;
		$addressbook[$id]["city"] = $city;
		$addressbook[$id]["state"] = $state;
		$addressbook[$id]["work"] = $work;

		$UM->_save_file($filename,base64_encode(serialize($addressbook)));

		$smarty->assign("umOpt",2);
		$templatename = "address-results.htm";

		break;

	//delete an existing contact
	case "dele":
		unset($addressbook[$id]);
		$newaddr = Array();
		while(list($l,$value) = each($addressbook))
			$newaddr[] = $value;
		$addressbook = $newaddr;
		$UM->_save_file($filename,base64_encode(serialize($addressbook)));

		$smarty->assign("umOpt",3);
		$templatename = "address-results.htm";

		break;

	// show the form to edit
	case "edit":

		$smarty->assign("umAddrName",$addressbook[$id]["name"]);
		$smarty->assign("umAddrEmail",$addressbook[$id]["email"]);
		$smarty->assign("umAddrStreet",$addressbook[$id]["street"]);
		$smarty->assign("umAddrCity",$addressbook[$id]["city"]);
		$smarty->assign("umAddrState",$addressbook[$id]["state"]);
		$smarty->assign("umAddrWork",$addressbook[$id]["work"]);
		$smarty->assign("umOpt","save");
		$smarty->assign("umAddrID",$id);
		$templatename = "address-form.htm";

		break;

	// display the details for an especified contact
	case "display":

		$smarty->assign("umAddrName",$addressbook[$id]["name"]);
		$smarty->assign("umAddrEmail",$addressbook[$id]["email"]);
		$smarty->assign("umAddrStreet",$addressbook[$id]["street"]);
		$smarty->assign("umAddrCity",$addressbook[$id]["city"]);
		$smarty->assign("umAddrState",$addressbook[$id]["state"]);
		$smarty->assign("umAddrWork",$addressbook[$id]["work"]);

		$smarty->assign("umAddrID",$id);
		$templatename = "address-display.htm";


		break;

	// show the form to a new contact
	case "new":

		$templatename = "address-form.htm";

		$smarty->assign("umOpt","add");
		$smarty->assign("umAddrID","N");

		break;

	// export a contact

	case "expo":
		require("./inc/lib.export.php");
		export2ou($addressbook[$id]);
		break;

	// default is list

	default:


		$smarty->assign("umNew","addressbook.php?opt=new");

		$addresslist = Array();
		for($i=0;$i<count($addressbook);$i++) {
			$ind = count($addresslist);
			$addresslist[$ind]["viewlink"] = "addressbook.php?opt=display&id=$i";
			$addresslist[$ind]["composelink"] = "newmsg.php?nameto=".htmlspecialchars($addressbook[$i]["name"])."&mailto=".htmlspecialchars($addressbook[$i]["email"])."";
			$addresslist[$ind]["editlink"] = "addressbook.php?opt=edit&id=$i";
			$addresslist[$ind]["dellink"] = "addressbook.php?opt=dele&id=$i";

			$addresslist[$ind]["name"] = $addressbook[$i]["name"];
			$addresslist[$ind]["email"] = $addressbook[$i]["email"];
		}
		$templatename = "address-list.htm";
		$smarty->assign("umAddressList",$addresslist);
}

$smarty->display("$selected_theme/$templatename");

?>
