<?php
session_start();
	ob_start();
	require_once("../include/config.inc.php");	//Includes config files where all the global variables, tablenames 						etc. are defined
	include_once($functionFile);	
	include_once( $dbLibFile );
	
	require_once("../class/utility.class.php");
	require_once("../class/student.class.php");
	
	$studentObj=new student();		//Creating Object
	//echo "sadasd";exit;
	$mode = trim($_REQUEST["mode"]);
	$mygrno = "";$fromDate = "";$toDate = "";$receiptNo = "";$selClass = "";$selDiv = "";$selSub = "";
    if(isset($_REQUEST["id"]))
		$mygrno = trim($_REQUEST["id"]);
	$tot =0;$totcash =0;$totchq =0;$detailflg = 0;$strStdLst = "";$strSubLst = "";
	
	$monthList = $studentObj->getMonthList();
	$stdList = $studentObj->getStandardList();	
	$subFeeList = $studentObj->getFeeSubject();
	$showCancelBtn = 1;
	$strStudentLst = "";
	$strtot = "";
	$backlink = "";
	$windowopen = "";
	$chequePayment = "0";
	/*echo "<pre>";
			print_r($_REQUEST);
			exit;*/
	$selAcc = "";
	if(strtolower($mode) == "list")
	{
	if($_POST)
	{
		$mygrno = "";
		if(isset($_REQUEST["receiptFromDate"]))
			$fromDate = trim($_REQUEST["receiptFromDate"]);
		
		if(isset($_REQUEST["receiptToDate"]))
			$toDate = trim($_REQUEST["receiptToDate"]);
		/*if(isset($_REQUEST["receiptNo"]))
			$receiptNo = trim($_REQUEST["receiptNo"]);*/
		/*if(isset($_REQUEST["selDiv"]))
			$selDiv = trim($_REQUEST["selDiv"]);	
		/*if(isset($_REQUEST["ddlClassName"]))
			$selClass = trim($_REQUEST["ddlClassName"]); */	
		if(isset($_REQUEST["subjectlist"]))
			$selAcc = trim($_REQUEST["subjectlist"]);
	/*/	$windowopen = "1";	
		if($receiptNo != ""){
			$fromDate = "";
			$fromDate = "";
		}*/
	}
	else{
			if($mygrno == ""){
			$fromDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("y")));
			$toDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("y")));
			}
		}
	$studentList = Array();
	if($fromDate != "" || $toDate != "" || $mygrno != "" || $receiptNo != "" || $selClass != "" || $selDiv != "" || $selAcc != "") 
	$studentList = $studentObj->getConcessionAmtPaymentHistory($fromDate,$toDate,$selAcc);
	
	
/*echo "<pre>";
			print_r($studentList);
			exit;	*/
	$subjectList = $studentObj->getFeeSubject();
	foreach($monthList as $key => $value)
		{
			$monthArr[$value['id']] = $value['name'];
	}
	foreach($stdList as $key => $value)
		{
			$strSelStr = "";
			 if($selClass != "" && $selClass == $value['id'])
				$strSelStr = 'selected="selected"';
			$strStdLst .= "<option value='".$value['id']."' $strSelStr >".$value['standard']."</option>";
		}
		
	foreach($subFeeList as $key => $value)
		{
			$strSelStr = "";
			 if($selSub != "" && $selSub == $value['id'])
				$strSelStr = 'selected="selected"';
			$strSubLst .= "<option value='".$value['id']."' $strSelStr >".$value['name']."</option>";
		}	
	
	
	if (count($studentList) > 0) {
		$i =1;
		foreach($studentList as $key => $value)
		{
			$styleStr = "";
			if($value["status"] == "0")
			{
			  $styleStr =  "style='background-color:rgb(51, 51, 51);'";
			}
			else				
				$tot += $value["amt"];
				$totcash  += $value["concessionAmt"];
				//$totchq  += $value["chqAmt"];				
			$name=stripslashes(strtoupper($value["fname"]))." ".stripslashes(strtoupper($value["fathername"]))." ".stripslashes(strtoupper($value["lname"])."(".$stdList[$value["class"]]['standard']."-"."".$value["division"].")");			
			//
			$tempSub = "";$tempSubCycle = "";
			foreach($subjectList as $key2 => $value2)
			{//echo "<br />".$value2['id']." = ".$value["fee_subject"];
				if(isset($value["fee_subject"]) && $value2['id'] == $value["fee_subject"])
				{
					$tempSub = $value2['longname'];
					$tempSubCycle = $value2['feeCycle'];
					break;
				}
			}
			$paidMonthArr = explode(",",$value["mnth"]);
			$paidMonthNameArr = array();
			for($k=0;$k<count($paidMonthArr);$k++)
			{
				@$paidMonthNameArr[$k] = substr($monthArr[$paidMonthArr[$k]],0,3);				
			}
			$mnthStr = implode(",",$paidMonthNameArr);
			$studentChqList = $studentObj->getChequeDetailsByPaymentID($value["paymentid"]);
			$chqStr = "";
			foreach($studentChqList as $key3 => $value3){
				$chqStr .= "".$value3['amt']."(".$value3['chqno'].")"."&nbsp;&nbsp;&nbsp;";
				}
			//print_r($studentChqList);
			//exit;
			
			$strFinalWindow = "window.open('../php/showpaymentreceipt.php?paymentid=".$value["paymentid"]."','mywindow','resizable=1,width=800,scrollbars=1')";
			$strStudentLst .= '<tr $styleStr><td>'.$i.'</td><td>'.$value["receiptNo"].'</td><td>'.date("d-m-y", strtotime($value['receiptDt'])).'</td><td>'.$value["grno"].'</td><td>'.$name.'</td><td>'.$value["amt"].'</td><td>'.$value["concessionAmt"].'</td><td><a title="Print Payment Receipt" style="padding-right:0px;cursor:pointer;" onClick="'.$strFinalWindow.'"><img src="../images/print.gif" style="width:25px;" alt="Print"></a></td></tr>';
			$i++;
		}
		$strStudentLst .= "</table>";
		///$strtot = "Total amount paid: <b>Rs. $tot</b>";	
		$strtot = "<br />Total Concession amount: <b>Rs. $totcash</b>";	
		//$strtot .= "<br />Total cheque amount: <b>Rs. $totchq</b>";	
		$backlink = "<a href='fee.php?mode=list'>Click here to go Back</a>";
	} 
	else {
	    //No data found
		$strStudentLst = "<h3>No data found.</h3><br /><br /><br />";
	}
		
		include_once("../html/showconcessionpayment.html");
	}
	
?>