<?php

namespace App\Http\Controllers;

use App\Services\ReceiptService;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    protected ReceiptService $receiptService;

    public function __construct(ReceiptService $receiptService)
    {
        $this->receiptService = $receiptService;
    }

    /**
     * Display the printable receipt for a transaction.
     *
     * @param int $transactionId
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(int $transactionId)
    {
        $receipt = $this->receiptService->generateReceiptDataById($transactionId);

        if (!$receipt) {
            return redirect()->back()->with('error', 'Transaction not found.');
        }

        return view('receipt.print', compact('receipt'));
    }

    /**
     * Display the printable receipt by transaction number.
     *
     * @param string $transactionNumber
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showByNumber(string $transactionNumber)
    {
        $receipt = $this->receiptService->generateReceiptDataByNumber($transactionNumber);

        if (!$receipt) {
            return redirect()->back()->with('error', 'Transaction not found.');
        }

        return view('receipt.print', compact('receipt'));
    }
}
