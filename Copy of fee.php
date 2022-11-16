<?php
session_start();
	ob_start();
	require_once("../include/config.inc.php");	//Includes config files where all the global variables, tablenames 						etc. are defined
	include_once($functionFile);
	
	include_once( $dbLibFile );
	global $dsn,$db;
/****************** connect to database********/
	$db = DB::connect($dsn);
	//echo $db;exit;
	if( DB::isError($db) )
	{
		echo "Could Not Connect To The Database.... :(";
		DB::errorMessage($db);
	}	
	require_once("../class/utility.class.php");
	require_once("../class/student.class.php");
	
	$studentObj=new student();		//Creating Object
	
	$mode = trim($_REQUEST["mode"]);
	$tot =0;$detailflg = 0;
	$stdList = $studentObj->getStandardList();
	/*foreach($stdList as $key => $value)
		{
			$stdListArr[] = 
		}*/
		
	//echo "sadasd";exit;
	if(strtolower($mode) == "list")
	{
	
	$stdList = $studentObj->getStandardList();
	foreach($stdList as $key => $value)
		{
			$strSelStr = "";
			if($_POST){
			 if($_POST['ddlClassName'] == $value['id'])
				$strSelStr = 'selected="selected"';
			}
			$strStdLst .= "<option value='".$value['id']."' $strSelStr >".$value['standard']."</option>";
		}
	if($_POST)
	{
		$selMonth = $_POST['ddlmonth'];
		$SelClass = $_POST['ddlClassName'];		
		if($SelClass == "0") $SelClass ="";
		if($selMonth == "0") $selMonth ="";
		$studentList = $studentObj->getStudentPaymentLst($selMonth,$SelClass);
		
		$unpaidStudentList = $studentObj->getUnpaidStudentLst($selMonth,$SelClass);
		//echo "<pre>";print_r($unpaidStudentList);exit;
	}
	else
	{
	    if($_REQUEST["id"] != null)
			$grno = trim($_REQUEST["id"]);
		if($grno == "")
			$studentList = $studentObj->getStudentPaymentLst("","");
		else	
			{
				$studentList = $studentObj->getPaymentByGrNo($grno);
				$detailflg = 1;
			}
	}
	
	$subjectList = $studentObj->getFeeSubject();
			$mastersubArr = Array();
			$mastersubNameArr = Array();
			foreach($subjectList as $key => $value)
			{
				if(!in_array($value['name'],$mastersubNameArr))
				{
					$mastersubNameArr[$value['id']] = $value['name'];
					$strmastersub .= '<th width="7%">'.$value['name'].'</th>';
				}
				$mastersubArr[$value['id']] = $value['name'];
			}
			//echo "<pre>";print_r($mastersubNameArr);print_r($mastersubArr);exit;
	$monthList = $studentObj->getMonthList();
		foreach($monthList as $key => $value)
		{
			$strSelStr = "";
		    if($_POST){
			 if($_POST['ddlmonth'] == $value['id'])
				$strSelStr = 'selected="selected"';
			}
			$strMonthLst .= "<option value='".$value['id']."' $strSelStr >".$value['name']."</option>";
		}
	if (count($studentList) > 0) {
		$i =1;
		foreach($studentList as $key => $value)
		{
			/*$styleStr = "";
			if($value["status"] == "0")
			{
			  $styleStr =  "style='background-color:rgb(51, 51, 51);'";
			}*/
			$name=stripslashes(strtoupper($value["fname"]))." ".stripslashes(strtoupper($value["mname"]))." ".stripslashes(strtoupper($value["lname"]));
			
			if(isset($_REQUEST['id']))
			{
				//echo "===".$value["MONTH"];//exit;
				//$strDet = $monthList[$value["MONTH"]]['name'];
				$strDetArr = Array();
				/*$monthArr = explode(",",$value["MONTH"]);
				//echo "<pre>";print_r($monthArr);print_r($monthList);exit;
				
				foreach($monthArr as $key2 => $value2){
					array_push($strDetArr,substr($monthList[$value2]['name'],0,3));
				}*/
				//$strDet = implode(",",$strDetArr);
				$strDet = '<a href="payment.php?mode=show&id='.$value["grno"].'&payid='.$value["id"].'">Detail</a>';
			}
			else
			{
				$strDet = '<a href="fee.php?mode=list&id='.$value["grno"].'">Detail</a>';
			}
			$tot += $value["amt"];	
			$paidSubArr = explode(",",$value["subject"]);	
			$paidfeeArr = explode(",",$value["fee"]);	
			//echo "<pre><br />".$strtotSub;	
			//echo "<br />".$strtotfee;
			//print_r($paidSubArr);
			//print_r($paidfeeArr);
			$paidArr = Array();
			for($jk=0;$jk<count($paidSubArr);$jk++)
			{
				if($paidArr[$paidSubArr[$jk]])
				{
					$paidArr[$paidSubArr[$jk]] += max(0,$paidfeeArr[$jk]);
				}
				else
				{
					$paidArr[$paidSubArr[$jk]] = max(0,$paidfeeArr[$jk]);				
				}
			}			
			//print_r($paidArr);
			//exit;
			$strStudentLst .= "<tr $styleStr><td><span style='font-size:10px;'>".$name."</span>(".$value["grno"].")</td>";
			
			foreach($paidArr as $keyp => $valuep)
			{
				$strStudentLst .=  "<td>$valuep</td>";
			}
			//$strStudentLst .= '<td>0</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td>';
			
			$strStudentLst .= '<td>'.$value["amt"].'</td><td>'.$strDet.'</td><td><a title="Add Payment" href="fee.php?mode=add&id='.$value["grno"].'"><img src="../images/pencil.png" alt="Edit"></a></td></tr>';
			$i++;
		}
		$strStudentLst .= "</table>";
		$strtot = "Total amount paid: <b>Rs. $tot</b>";	
		$backlink = "<a href='fee.php?mode=list'>Click here to go Back</a>";
		
		if (count($unpaidStudentList) > 0) {
		$j =1;
		foreach($unpaidStudentList as $key1 => $value1)
		{
			$name=stripslashes(strtoupper($value1["fname"]))." ".stripslashes(strtoupper($value1["mname"]))." ".stripslashes(strtoupper($value1["lname"]));
			$strUnpaidLst .= '<td>'.$value1["grno"].'</td><td>'.$name.'</td><td></tr>';
			$j++;
		}
		}
	} 
	else {
	    //No data found
		if(isset($_REQUEST['id']))
		{
		$strStudentLst = "<h3>No data found.</h3><br /><a style='cursor:pointer' href='fee.php?mode=add&id=".$grno."'>Add New Payment</a><br /><br />";
		}
		else
		{
			$strStudentLst = "<h3>No data found.<br />";
		}
	}
		include_once("../html/feelist.html");
	}
	else
	{//$studentData = $studentObj->getStudentData($_REQUEST["id"]);	
        if($_POST)
		{
			$currentyearArr = $studentObj->getCurrentYear();	//"2012-13";
			$currentyear = $currentyearArr[0]['currentyear'];
			$info = "";
			if(isset($_REQUEST['id']))
				$grno = $_REQUEST['id'];
			
			$totMnt = count($_POST['ddlmonth']);
			//$subArr = explode(",",$_POST['hidsubids']);
			$subArr = explode(",","6,7,8,9,10,11,12,1,2,3,4,5");
			$k =0;
			$monthcnt = 1;
			
			//echo "<pre>";print_r($_POST);exit;
			$tutionFeePaidMnthCnt = count($_POST['ddlmonth']);
			$higherflg = 0;
			foreach($_POST['ddlmonth'] AS $key1=>$value1){
				//echo "<br>Month:==>".$value1;
				$totAmt = 0;
				if($_POST['sub_1'] > 0){
					$mthlyFee = $_POST['sub_1']/$tutionFeePaidMnthCnt;
				}
				else{
					$higherflg = 1;
					//echo "==>".$_POST['sub_5'];
					//echo "==>".$tutionFeePaidMnthCnt;
					$mthlyFee = $_POST['sub_5']/$tutionFeePaidMnthCnt;
				}
				$totAmt += $mthlyFee;
				$feeStr = $mthlyFee.",";
				if(isset($_POST['ddlmonthTerm'])){
					foreach($_POST['ddlmonthTerm'] AS $key2=>$value2){
					  $temrEntFlg = 0;
					  if($_POST['ddlmonthTerm'][$key2] == $value1){	
							$temrEntFlg =1;
							if($higherflg == 1){
							$feeStr .= ($_POST['sub_6']/count($_POST['ddlmonthTerm'])).",";			
							$totAmt += ($_POST['sub_6']/count($_POST['ddlmonthTerm']));
							}
							else{
							$feeStr .= ($_POST['sub_2']/count($_POST['ddlmonthTerm'])).",";			
							$totAmt += ($_POST['sub_2']/count($_POST['ddlmonthTerm']));
							}
							break;
					  }
					}					
					//echo "==>".$_POST['ddlmonthTerm'][$key];
				}
				if($temrEntFlg == 0){
							$feeStr .= "0,";
				}
								
				if(isset($_POST['ddlmonthComp'])){
					foreach($_POST['ddlmonthComp'] AS $key3=>$value3){
					$compEntFlg = 0;
					  if($_POST['ddlmonthComp'][$key3] == $value1){
							$compEntFlg = 1;
							if($higherflg == 1){
							$feeStr .= ($_POST['sub_7']/count($_POST['ddlmonthComp'])).",";		
							$totAmt += ($_POST['sub_7']/count($_POST['ddlmonthComp']));
							}
							else{
							$feeStr .= ($_POST['sub_3']/count($_POST['ddlmonthComp'])).",";		
							$totAmt += ($_POST['sub_3']/count($_POST['ddlmonthComp']));
							}
							break;
					  }
					}	
				}
				if($compEntFlg == 0){
							$feeStr .= "0,";
				}
				
				if(isset($_POST['ddlmonthAct'])){
					foreach($_POST['ddlmonthAct'] AS $key4=>$value4){
					$compActFlg = 0;
					  if($_POST['ddlmonthAct'][$key4] == $value1){
							$compActFlg = 1;
							if($higherflg == 1){
							$feeStr .= ($_POST['sub_8']/count($_POST['ddlmonthAct'])).",";	
							$totAmt += ($_POST['sub_8']/count($_POST['ddlmonthAct']));
							}
							else{
							$feeStr .= ($_POST['sub_4']/count($_POST['ddlmonthAct'])).",";	
							$totAmt += ($_POST['sub_4']/count($_POST['ddlmonthAct']));
							}
							break;
					  }
					}	
				}
				if($compActFlg == 0){
							$feeStr .= "0,";
				}
				
				if($_POST['sub_9']>0 && $value1 == "6"){
					//Payment Will only come in June
					$feeStr .= $_POST['sub_9'].",";
					$totAmt += $_POST['sub_9'];
				}
				else{
					$feeStr .= "0,";
				}
				
				
				if($_POST['sub_10']>0  && $value1 == "6"){
					//Payment Will only come in June
					$feeStr .= $_POST['sub_10']."";
					$totAmt += $_POST['sub_10'];
				}
				else{
					$feeStr .= "0";
				}
				//echo "<br>Fee Str:==>".$feeStr;
				//echo "hr />";
				
				$studentObj->AddPayment($grno,$_POST['hidsubids'],$feeStr,$value1,$totAmt,$currentyear,$info,$_POST['dop']);
				//$monthcnt
			}
			//exit;
			/*exit;
			foreach($subArr AS $key=>$value)
			{
				//$feeArr[] = Array();
				echo "<pre>";
				//print_r($subArr);exit;
				//echo count($_POST["ddlmonth"]);				
				print_r($_POST);exit;
				if($_POST["hidsub_".$value] == 0)
				{//These is annual subject
					if($k == 0)
					{
						$feeArr[] = $_POST["sub_".$value];
						$annualamt += round($_POST["sub_$value"]);	
					}
					
					$restfeeArr[] = 0;
				}
				else
				{
					$amt += round($_POST["sub_$value"]/$totMnt);	
					$feeArr[] = round($_POST["sub_".$value]/$totMnt);
					$restfeeArr[] = round($_POST["sub_".$value]/$totMnt);
				}
							
			}
			$feeStr = implode(",",$feeArr);
			$restfeeStr = implode(",",$restfeeArr);
			$jk=0;
			foreach($_POST['ddlmonth'] AS $key=>$value)
			{//Insert payments records
				if($jk==0){
				//Annual records
					$studentObj->AddPayment($grno,$_POST['hidsubids'],$feeStr,$value,($amt+$annualamt),$currentyear,$info);
				}
				else{
				//echo "==>".$restfeeStr;exit;
					$studentObj->AddPayment($grno,$_POST['hidsubids'],$restfeeStr,$value,$amt,$currentyear,$info);
				}
				
				$jk++;
			}*/
			//exit;
			
			header("Location:fee.php?mode=list");
			exit;
		}
		else
		{		
		if($_REQUEST["id"] != null && $_REQUEST["id"] != "")
		{
			$grno = $_REQUEST["id"];
			$studentList = $studentObj->getStudentData($grno,"");
			foreach($studentList as $key => $value)
			{
				$standardId = $value["class"];
				$standard = $stdList[$standardId]['standard'];
				$strStudent= stripslashes(strtoupper($value["fname"]))." ".stripslashes(strtoupper($value["mname"]))." ".stripslashes(strtoupper($value["lname"]))."&nbsp;&nbsp;&nbsp;GR NO.(<b>".$value["grno"].") Standard: ".$standard."</b>";
			}
			$monthList = $studentObj->getMonthList();
		/*	foreach($monthList as $key => $value)
			{
				$strSelStr = "";
				$strMonthLst .= "<option value='".$value['id']."' $strSelStr >".$value['name']."</option>";
			}*/
			$subjectList = $studentObj->getFeeSubject();
			$strSubAdd = "";
			
			//$annualFeeMonthStr = '<select id="ddlmonthAnn" style="width: 200px;" multiple="multiple" name="ddlmonthAnn[]"><option value="6">June</option>';
			
			$activityFeeMonthStr = '<select id="ddlmonthAct" style="width: 200px;" multiple="multiple" name="ddlmonthAct[]"><option value="7">July</option><option value="12">December</option><option value="4">April</option></select>';
			
			$termFeeMonthStr = '<select id="ddlmonthTerm" style="width: 200px;" multiple="multiple" name="ddlmonthTerm[]"><option value="7">July</option><option value="11">November</option></select>';
			
			//$libraryFeeMonthStr = '<select id="ddlmonthLib" style="width: 200px;" multiple="multiple" name="ddlmonthLib[]"><option value="6">June</option>';
			
			$tutionFeeMonthStr = '<select id="ddlmonth" style="height: 100px; width: 200px;" multiple="multiple" name="ddlmonth[]" onBlur="GetSubjectFee();"><option value="6">June</option><option value="7">July</option>
<option value="8">August</option><option value="9">September</option><option value="10">October</option><option value="11">November</option><option value="12">December</option>
<option value="1">January</option><option value="2">February</option><option value="3">March</option>
<option value="4">April</option><option value="5">May</option></select>';

			$compFeeMonthStr = '<select id="ddlmonthComp" style="width: 200px;" multiple="multiple" name="ddlmonthComp[]"><option value="6">June</option><option value="10">October</option><option value="2">February</option></select>';
			//echo "<pre>";//print_r($subjectList);exit;
			foreach($subjectList as $key => $value)
			{
				$validStdArray = explode(",",$value['standard']);
				if(in_array($standardId,$validStdArray))
				{
				//print_r($value);
				$arrData[] = $value['id'];
				if(strtolower($value['name']) == "tution"){
					$strSubAdd .= "<hr><p><label>Select Month(s)</label>";
					$strSubAdd .= $tutionFeeMonthStr."</p>";
				}
				elseif(strtolower($value['name']) == "term"){
					$strSubAdd .= "<hr><p><label>Select Month(s)</label>";
					$strSubAdd .= $termFeeMonthStr."</p>";
				}
				elseif(strtolower($value['name']) == "computer"){
					$strSubAdd .= "<hr><p><label>Select Month(s)</label>";
					$strSubAdd .= $compFeeMonthStr."</p>";
				}
				elseif(strtolower($value['name']) == "activity"){
					$strSubAdd .= "<hr><p><label>Select Month(s)</label>";
					$strSubAdd .= $activityFeeMonthStr."</p>";
				}
				elseif(strtolower($value['name']) == "annual"){
					$strSubAdd .= "<hr>";
				}
				/*elseif(strtolower($value['name']) == "annual"){
					$strSubAdd .= $annualFeeMonthStr."</p>";
				}
				elseif(strtolower($value['name']) == "library"){
					$strSubAdd .= $libraryFeeMonthStr."</p>";
				}*/
				$strSubAdd .= "<p><label>".$value['name'].":</label>";
				$strSubAdd .= "<span style='margin-left:30px;'><input class='text-input' id='sub_".$value['id']."' name='sub_".$value['id']."' value='0' type='text' /><input id='hidsub_".$value['id']."' name='hidsub_".$value['id']."' value='".round($value['fee'])."' type='hidden' /></span></p>";
				}
			}
			if(count($arrData > 0))
				{
				$str = implode(",",$arrData);
				$strSubAdd .= "<input id='hidsubids' name='hidsubids' value='$str' type='hidden' /></span></p>";
				}
		}
		else
		{
			header("Location:fee.php?mode=list");
			exit;
		}
		}
		include_once("../html/addfee.html");
		
	}
?>