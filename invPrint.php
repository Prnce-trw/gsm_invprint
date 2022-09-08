<?php
require_once("../../config/condb.php");
require_once("../../libarries/ThaiBahtConvers.php");
require_once("../../libarries/fpdf184/fpdf.php");
date_default_timezone_set('Asia/Bangkok');
	
$inv_id = $_GET['inv_id'];

	/*
	$exec_tb_bill=odbc_exec($conn,"SELECT * FROM bill WHERE  bill_id='$bill_id'");
	$result=odbc_fetch_array($exec_tb_bill);
	$bill_promotion=$result['bill_promotion'];
	if(strpos($bill_promotion, '%') == 0 ){
		$bill_promotion=number_format((float)$bill_promotion,2);//�����ǹŴ�繨ӹǹ�Թ ���������� ��ѡ�ѹ 
	}
    */
    $sql = "SELECT inv.* , br.branch_name, br.branch_addr, br.branch_tel, br.branch_invoice_code
    FROM ar_invoice inv INNER JOIN owner_branch br ON inv.branch_id = br.branch_id
    WHERE inv.invoice_id = '$inv_id'; ";
    $query_h = mysqli_query($conn,$sql);
    $result = mysqli_fetch_array($query_h);
	function thai_date($dt){
		$thai_month_arr=array("", "���Ҥ�","����Ҿѹ��","�չҤ�","����¹","����Ҥ�","�Զع�¹","�á�Ҥ�","�ԧ�Ҥ�","�ѹ��¹","���Ҥ�","��Ȩԡ�¹","�ѹ�Ҥ�");
		$date=substr($dt,0,10);
		$Y=(int)substr($date,0,4)+543;
		$date=substr($date,8,10)." ".$thai_month_arr[(int)substr($date,5,7)]." ".$Y;//
		return($date);
	}
	//------------------------------------�Ҩӹǹ�� row determine------------------------//
	$sql_line="SELECT * FROM ar_invoice_line WHERE invoice_id='$inv_id' ; ";
	$query_line = mysqli_query($conn,$sql_line);
	$num_rows = mysqli_num_rows($query_line); // count record
	$per_page=9;//�ӹǹ�ǵ�� 1 ��
	$item_page=$per_page;
	$num_page=1;
	$page=(int)($num_rows / $item_page);
	if($page < 0){
		$page=1;
	}
	else if(($num_rows % $item_page) > 0){
		$page+=1;
	}
//-----------------------------LOOP SHOW ITEM LIST-------------------------------------//
//require('fpdf.php');
$pdf = new FPDF('L','in',[8,5.5]);//array(8,5.5));//��д���ǵ��(P) ˹����� ����(in)��Ҵ 8 x 5.5 ���� 
$pdf->SetLeftMargin(0.945);
$pdf->SetTopMargin(0);
$pdf->AddPage();
$pdf->AddFont("CordiaNew","","cordia.php");
$pdf->AddFont("CordiaNew","B","cordiab.php");
page_header();
page_footer();
$y=2.1;
$y_cell = 1.92;
$i=1;
while($result_line = mysqli_fetch_array($query_line,MYSQLI_ASSOC)){
	$list= iconv( 'UTF-8','Windows-874',$result_line["inv_items_name"]);
	$amount=$result_line["inv_itmes_qty"];
	$unitPrice=$result_line["inv_itmes_unitPrice"];
	$list_promotion=0;
	$money=$result_line["inv_itmes_total"];

	$pdf->Text(0.3125,$y,$list);
	//$pdf->Text(5.6,$y,number_format($amount,2));
    $pdf->SetXY(5.35,$y_cell);
    $pdf->Cell( 0.6, 0.2 , number_format($amount), 0 , 1 , 'C' );
	
    //$pdf->Text(6.5,$y,number_format($unitPrice,2));
    $pdf->SetXY(6.03,$y_cell);
    $pdf->Cell( 0.85, 0.2 , number_format($unitPrice,2), 0 , 1 , 'R' );

    //$pdf->Text(7.2,$y,number_format($money,2));
    $pdf->SetXY(6.92,$y_cell);
    $pdf->Cell( 0.85, 0.2 ,number_format($money,2), 0 , 1 , 'R' );
    
	$pdf->Ln(0.25);
	$y+=0.2;
    $y_cell+=0.2;
    
 if($i==$per_page && $num_page != $page){//���˹������
	$y=2.055;
    $y_cell = 1.91;
	$num_page+=1;
	$per_page+=$item_page;
	$pdf->SetLeftMargin(0.945);
	$pdf->SetTopMargin(0);
	$pdf->AddPage();
	page_header();
	page_footer();
	}
	$i++;

}//end while
//-------------DEFIND page_header() FUNCTION-------------------//
 function page_header(){
	global $result;
	global $page;
	global $num_page;
	global $pdf;

	$pdf->Line(0.1875,0.9375,7.8125,0.9375);//line 1
	$pdf->Line(0.1875,1.71,7.8125,1.71);//line 2
	$pdf->Line(0.1875,1.73,7.8125,1.73);//line 3
	$pdf->Line(0.1875,1.92,7.8125,1.92);//line 4
	$pdf->Line(0.1875,3.8,7.8125,3.8);//line 5
	//$pdf->Line(0.1875,4.125,7.8125,4.125);//line 6
	$pdf->Line(0.1875,4.625,5.3,4.625);//line 7
    $pdf->Line(0.1875,5.3,7.8125,5.3);//line 8

	$pdf->Line(0.1875,0.9375,0.1875,5.3); //line 1
	$pdf->Line(5.3,0.9375,5.3,5.3); //line 1
	$pdf->Line(6.01,1.71,6.01,3.8);//line 2
	$pdf->Line(6.9,1.71,6.9,3.8);//line 4
	$pdf->Line(7.8125,0.9375,7.8125,5.3);//line 4

	$pdf->SetFont("CordiaNew","B",16);
	$pdf->Text(0.3,0.375,'����ѷ ����� ������ê�� �ӡѴ');
	//$pdf->Text(6.35,0.6,'㺡ӡѺ����/�����');
    $pdf->SetXY(6,0.28);
    $pdf->Cell( 1.9, 0.5 , '㺡ӡѺ����', 0 , 1 , 'C' );

    $pdf->Text(6.5,0.8,'TAX INVOICE');
     
	$pdf->SetFont("CordiaNew","",13);
	$pdf->Text(0.3,0.6,'('.$result['branch_invoice_code'].') '.iconv('utf-8','windows-874',$result['branch_name'].' '.$result['branch_addr']));
	$pdf->Text(0.3,0.8,'�� : '.iconv('utf-8','windows-874',$result['branch_tel']));
     
	$pdf->Text(7.3,0.3,'�蹷�� : '.$num_page.'/'.$page);
     
	$pdf->Text(4,0.8,'�Ţ��Шӵ�Ǽ���������� : 0105554035654 ');
	
	$pdf->Text(0.3,1.11,'�١��� : '.iconv('utf-8','windows-874',$result['invoice_cus_name']));
	$pdf->Text(0.36,1.35,'������� : ');
	//$pdf->Ln(1.168);//Referent top margin page with MultiCell//
    //$pdf->SetX(0.65);
    $pdf->SetXY(0.65,1.168);
	$pdf->MultiCell(4,0.18,iconv('utf-8','windows-874',$result['invoice_cus_addr']),0,'L');
	$pdf->Text(0.4,1.66,'�� : '.iconv('utf-8','windows-874',$result['invoice_cus_tel']));
	
	$pdf->Text(6,1.125,'�Ţ��� : '.$result['invoice_id']);
	$pdf->Text(6.03,1.35,'�ѹ��� : '.thai_date($result['invoice_date']));

	$pdf->Text(5.51,1.575,'�Ţ������������� : '.$result['invoice_cus_TaxID']);


	$pdf->Text(2.4,1.8675,'��¡�ê���');
	$pdf->Text(5.5,1.8675,'�ӹǹ');
	$pdf->Text(6.2,1.8675,'�Ҥ�/˹���');
	$pdf->Text(7.1875,1.8675,'���Թ');
}//end;

//-------------DEFIND page_footer() FUNCTION-------------------//
function page_footer(){
	global $result;
	global $page;
	global $num_page;
	global $bill_promotion;
	
	global $pdf;

if($num_page == $page){
    $footerX = 5.8;
    $footerY = 3.85;
    $footerY_step = 0.25;
    $pdf->SetXY($footerX,$footerY);
    $pdf->Cell( 1.1, 0.25 ,'��Ң���(����)', 0 , 0 , 'R' );
    $pdf->Cell( 0.9, 0.25 , number_format($result['inv_transportAdd'],2), 0, 1 , 'R' );
    $footerY+=$footerY_step;
    $pdf->SetXY($footerX,$footerY);
    $pdf->Cell( 1.1, 0.25 ,'��Һ�ԡ������', 0 , 0 , 'R' );
    $pdf->Cell( 0.9, 0.25 ,number_format($result['inv_serviceCharge'],2), 0 , 1 , 'R' );
    $footerY+=$footerY_step;
    $pdf->SetXY($footerX,$footerY);
    $pdf->Cell( 1.1, 0.25 ,'�ѡ ��ǹŴ', 0, 0 , 'R' );
    $pdf->Cell( 0.9, 0.25 ,number_format($result['inv_discount'],2), 0, 1 , 'R' );
    /*
    $pdf->Text(6.5625,4.2,'��Ң���(����)');
    $pdf->Text(6.5625,4.6,'��Һ�ԡ������');
	$pdf->Text(6.5625,4.8,'��ǹŴ');
	*/
    $pdf->Text(6.12,4.75,'��Ť�ҡ�͹ VAT');
    //$pdf->Text(5.4,4.75,'��Ť�ҡ�͹ VAT');
    $pdf->Text(7.05,4.75,number_format($result['inv_NetExclVat'],2));
    $pdf->Text(5.91,5,'������Ť������ VAT');
    //$pdf->Text(5.4,5,'������Ť������ VAT');
    $pdf->Text(7.15,5,number_format($result['inv_Vat'],2));
    $pdf->Text(6.2,5.23,'�ʹ����ط��');
    //$pdf->Text(5.4,5.23,'�ʹ����ط��');  
    $pdf->Text(7.05,5.23,number_format($result['inv_NetTotal'],2));  
    
    $pdf->Text(0.3,4.2,'�ʹ����ط�� ('. iconv('utf-8','windows-874',ThaiBahtConversion($result['inv_NetTotal'])).')');  
    
}else{    
	//$pdf->Text(6.2,4,'���Թ���');
    $footerX = 5.8;
    $footerY = 3.85;
    $footerY_step = 0.25;
    $pdf->SetXY($footerX,$footerY);
    $pdf->Cell( 1.1, 0.25 ,'��Ң���(����)', 0 , 1 , 'R' );
    $footerY+=$footerY_step;
    $pdf->SetXY($footerX,$footerY);
    $pdf->Cell( 1.1, 0.25 ,'��Һ�ԡ������', 0 , 1 , 'R' );
    $footerY+=$footerY_step;
    $pdf->SetXY($footerX,$footerY);
    $pdf->Cell( 1.1, 0.25 ,'��ǹŴ', 0, 1 , 'R' );

    /*
    $pdf->Text(6.5625,4.2,'��Ң���(����)');
    $pdf->Text(6.5625,4.6,'��Һ�ԡ������');
	$pdf->Text(6.5625,4.8,'��ǹŴ');
	*/
    $pdf->Text(6.12,4.75,'��Ť�ҡ�͹ VAT');
    $pdf->Text(5.91,5,'������Ť������ VAT');
    $pdf->Text(6.2,5.23,'�ʹ����ط��');
    
}

	$pdf->Text(3.35,4.5,'������Թ��� :.........................................');
    $pdf->Text(0.5,4.95,'����Ѻ�Թ��� :.........................................');
    $pdf->Text(3,4.95,'������ӹҨŧ��� :.........................................');
    $pdf->Text(0.3,5.21,'���Ѻ�Թ������ͺ�ԡ�õ����¡�â�ҧ��������Ҿ���º������ж١��ͧ����');
}//end

$pdf->Output();
?>