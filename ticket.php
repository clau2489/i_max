<?php

include "core/controller/Core.php";
include "core/controller/Database.php";
include "core/controller/Executor.php";
include "core/controller/Model.php";

include "core/app/model/UserData.php";
include "core/app/model/SellData.php";
include "core/app/model/OperationData.php";
include "core/app/model/ProductData.php";
include "core/app/model/StockData.php";
include "core/app/model/ConfigurationData.php";
include "fpdf/fpdf.php";
session_start();
if(isset($_SESSION["user_id"])){ Core::$user = UserData::getById($_SESSION["user_id"]); }
$symbol = ConfigurationData::getByPreffix("currency")->val;
$title = ConfigurationData::getByPreffix("ticket_title")->val;
$iva_val = ConfigurationData::getByPreffix("imp-val")->val;

$stock = StockData::getPrincipal();
$sell = SellData::getById($_GET["id"]);
$operations = OperationData::getAllProductsBySellId($_GET["id"]);
$user = $sell->getUser();


$pdf = new FPDF($orientation='P',$unit='mm', array(55,100));
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);
$pdf->setMargins(2,2,2);

$pdf->setX(2);
$pdf->SetFont('Arial','',5);
$pdf->Cell(0,3,"X", 0 , 1, "C");
$pdf->Cell(0,3,"DOCUMENTO NO FISCAL", 0 , 1, "C");
$pdf->Ln(3);


$pdf->SetFont('Arial','',7);
$pdf->setX(2);
$pdf->Cell(0,3,strtoupper($title), 0 , 1, "");
$pdf->Cell(0,3,"Fecha y Hora: ".strtoupper($sell->created_at), 0 , 1, "");
$pdf->Cell(0,3,"Venta: ".$sell->id);
$pdf->Ln(3);
$pdf->Cell(0,3,'-------------------------------------------------------------', 0 , 1, "");

$total =0;
$off = 35;
foreach($operations as $op){
$product = $op->getProduct();


$pdf->setX(2);
$pdf->Cell(0,3,"$op->q");
$pdf->setX(8);
$pdf->Cell(0,3,strtoupper(substr($product->name, 0,12)));
$pdf->setX(40);
$pdf->Cell(0,3,"$symbol ".number_format($op->q*$product->price_out,2,".",","), 0 , 1, "" );


$total += $op->q*$product->price_out;
$off+=6;
}


$pdf->Cell(0,3,'-------------------------------------------------------------', 0 , 1, "");
$pdf->Ln(1);
$pdf->setX(2);
$pdf->Cell(0,3,"TOTAL: " );
$pdf->setX(40);
$pdf->Cell(0,3,"$symbol ".number_format($total,2,".",","),0,1,"");
$pdf->setX(2);
$pdf->Cell(0,3,"EFECTIVO: " );
$pdf->setX(40);
$pdf->Cell(0,3,"$symbol ".number_format($sell->cash,2,".",","),0,1,"");
$pdf->setX(2);
$pdf->Cell(0,3,"CAMBIO: " );
$pdf->setX(40);
$pdf->Cell(0,3,"$symbol ".number_format($sell->cash-($total - ($sell->discount)),2,".",","),0,1,"");
$pdf->Ln(5);
$pdf->Cell(0,3,"GRACIAS POR SU COMPRA!",0,1,"C");

$pdf->output();
