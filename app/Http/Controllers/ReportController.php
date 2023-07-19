<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Customer;
use App\Models\User;

class ReportController extends Controller
{
    public function generateReport(Request $request)
    {
        $fromDate = $request->fromDate;
        $toDate = $request->toDate;

        $orders = Order::whereBetween('created_at', [$fromDate, $toDate])
            ->with(['items.product', 'customer'])
            ->get();

        $data = $orders->map(function ($order) {
            $customerName = $order->customer ? $order->getCustomerName() : 'Walk-in Customer';
            $orderItems = $order->items->map(function ($orderItem) {
                return [
                    'product' => $orderItem->product->name,
                    'quantity' => $orderItem->quantity,
                    'price' => number_format($orderItem->price, 2),
                ];
            });

            return [
                'customer' => $customerName,
                'order_items' => $orderItems,
                'total' => number_format($order->total(), 2),
            ];
        });

        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 30);

        $pdf->Cell(190, 10, 'Sales Report', 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 15);
        $pdf->Cell(190, 10, 'Date Range: ' . $fromDate . ' to ' . $toDate, 0, 1, 'C');
        $pdf->Ln();

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(60, 10, 'Customer', 1, 0, 'C');
        $pdf->Cell(60, 10, 'Product', 1, 0, 'C');
        $pdf->Cell(30, 10, 'Quantity', 1, 0, 'C');
        $pdf->Cell(40, 10, 'Price', 1, 0, 'C');
        $pdf->Ln();

        $totalPrice = 0;
        foreach ($data as $item) {
            $pdf->SetFont('Arial', '', 12);
            foreach ($item['order_items'] as $orderItem) {
                $pdf->Cell(60, 10, $item['customer'], 1, 0, 'C');
                $pdf->Cell(60, 10, $orderItem['product'], 1, 0, 'C');
                $pdf->Cell(30, 10, $orderItem['quantity'], 1, 0, 'C');
                $pdf->Cell(40, 10, $orderItem['price'], 1, 0, 'C');

                $pdf->Ln();

                if (is_numeric(str_replace(',', '', $orderItem['price']))) {
                    $totalPrice += floatval(str_replace(',', '', $orderItem['price']));
                }
            }
        }

        $totalPriceFormatted = number_format($totalPrice, 2);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(150, 10, 'Total', 1, 0, 'C');
        $pdf->Cell(40, 10, $totalPriceFormatted, 1, 0, 'C');
        $pdf->Ln();
        $signature = "Approved by : __________________________";
        $printedName = auth()->user()->first_name . " " . auth()->user()->last_name;
    

        $signatureX = 10; 
        $signatureY = $pdf->GetY() + 10; 
        $printedNameY = $signatureY + 5; 
        $fontSize = 12; 
    
      
        $printedNameWidth = $pdf->GetStringWidth($printedName);
    
        $printedNameXCentered = ($pdf->GetPageWidth() - $printedNameWidth) / 3.25;
    
    
        $pdf->SetFont('Arial', 'B', $fontSize);
        $pdf->SetXY($signatureX, $signatureY);
        $pdf->Cell(190, 10, $signature, 0, 1, 'L');
        $pdf->SetXY($printedNameXCentered, $printedNameY);
        $pdf->Cell($printedNameWidth, 10, $printedName, 0, 1, 'L');
    
        $content = $pdf->Output('report.pdf', 'S');

        return response($content)->header('Content-Type', 'application/pdf');
    }

    public function generateCashierSalesReport(Request $request)
    {
        $cashierId = $request->cashierId;
        $fromDate = $request->fromDate;
        $toDate = $request->toDate;
        $cashier = User::find($cashierId);
        $cashierName = $cashier ? $cashier->first_name . ' ' . $cashier->last_name : 'Unknown Cashier';
        $orders = Order::whereBetween('created_at', [$fromDate, $toDate])
            ->where('user_id', $cashierId)
            ->with(['items.product', 'customer'])
            ->get();

        $data = $orders->map(function ($order) {
            $customerName = $order->customer ? $order->getCustomerName() : 'Walk-in Customer';
            $orderItems = $order->items->map(function ($orderItem) {
                return [
                    'product' => $orderItem->product->name,
                    'quantity' => $orderItem->quantity,
                    'price' => number_format($orderItem->price, 2),
                ];
            });

            return [
                'customer' => $customerName,
                'order_items' => $orderItems,
                'total' => number_format($order->total(), 2),
            ];
        });

        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 30);

        $pdf->Cell(190, 10, 'Sales Report', 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 15);
        $pdf->Cell(190, 10, 'Date Range: ' . $fromDate . ' to ' . $toDate, 0, 1, 'C');
        $pdf->Ln();
        $pdf->Cell(190, 12, 'Prepared by: ' . $cashierName, 0, 1 ); // Display the cashier's name
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(60, 10, 'Customer', 1, 0, 'C');
        $pdf->Cell(60, 10, 'Product', 1, 0, 'C');
        $pdf->Cell(30, 10, 'Quantity', 1, 0, 'C');
        $pdf->Cell(40, 10, 'Price', 1, 0, 'C');
        $pdf->Ln();

        $totalPrice = 0;
        foreach ($data as $item) {
            $pdf->SetFont('Arial', '', 12);
            foreach ($item['order_items'] as $orderItem) {
                $pdf->Cell(60, 10, $item['customer'], 1, 0, 'C');
                $pdf->Cell(60, 10, $orderItem['product'], 1, 0, 'C');
                $pdf->Cell(30, 10, $orderItem['quantity'], 1, 0, 'C');
                $pdf->Cell(40, 10, $orderItem['price'], 1, 0, 'C');

                $pdf->Ln();

                if (is_numeric(str_replace(',', '', $orderItem['price']))) {
                    $totalPrice += floatval(str_replace(',', '', $orderItem['price']));
                }
            }
        }

        $totalPriceFormatted = number_format($totalPrice, 2);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(150, 10, 'Total', 1, 0, 'C');
        $pdf->Cell(40, 10, $totalPriceFormatted, 1, 0, 'C');
        $pdf->Ln();

        $signature = "Approved by : __________________________";
        $printedName = auth()->user()->first_name . " " . auth()->user()->last_name;
    

        $signatureX = 10; 
        $signatureY = $pdf->GetY() + 10; 
        $printedNameY = $signatureY + 5; 
        $fontSize = 12; 
    
      
        $printedNameWidth = $pdf->GetStringWidth($printedName);
    
        $printedNameXCentered = ($pdf->GetPageWidth() - $printedNameWidth) / 3.25;
    
    
        $pdf->SetFont('Arial', 'B', $fontSize);
        $pdf->SetXY($signatureX, $signatureY);
        $pdf->Cell(190, 10, $signature, 0, 1, 'L');
        $pdf->SetXY($printedNameXCentered, $printedNameY);
        $pdf->Cell($printedNameWidth, 10, $printedName, 0, 1, 'L');
    

        $content = $pdf->Output('report.pdf', 'S');

        return response($content)->header('Content-Type', 'application/pdf');
    }
}
