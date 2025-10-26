<?php

namespace App\Http\Controllers\Api\V1\Admin;

use Illuminate\Http\Request;
use App\Helpers\ResponseApiHelper;
use App\Http\Controllers\Controller;
use App\Repository\TransactionRepository;
use App\Http\Resources\TransactionCollection;

class TransactionController extends Controller
{
    private $transactionRepository;
    
    public function __construct(TransactionRepository $transactionRepository) {
        $this->transactionRepository = $transactionRepository;
    }

    public function index()
    {
        $transactions = $this->transactionRepository->get([
            'with' => ['transactionDetails']
        ]);

        // Message for reponse
        $message = request()->name
        ? 'Filtered transactions retrieved successfully.'
        : 'All transactions retrieved successfully.';
        
        return ResponseApiHelper::success($message, new TransactionCollection($transactions));
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
