<?php

namespace App\Http\Controllers;

use App\Models\BookTransaction;
use Illuminate\Http\Request;
use Auth;
use App\Models\Book;
use App\Models\User;
use Carbon\Carbon;


class BookTransactionController extends Controller
{
    public function getBookTrasactions($transaction_id = null)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized user',
                    'data' => null,
                ]);
            }

            if (is_null($transaction_id)) {
                $transactions = BookTransaction::with(['createdBy', 'book'])->latest()->get();

                if ($transactions) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Book Transactions fetched successfully',
                        'data' => $transactions,
                    ]);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Failed to fetch book transactions',
                        'data' => null,
                    ]);
                }

            } else {
                $transaction = BookTransaction::with(['createdBy', 'book'])->find($transaction_id);

                if ($transaction) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Book Transaction fetched successfully',
                        'data' => $transaction,
                    ]);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Failed to fetch book transactions',
                        'data' => null,
                    ]);
                }
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'data' => null,
            ]);
        }
    }

    public function lendBooks(Request $request, $book_id)
    {
        $user = Auth::user();

        if (!$user || $user->roles->contains('name', 'User')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized user',
                'data' => null,
            ]);
        }

        $book = Book::find($book_id);

        if (!$book) {
            return response()->json([
                'status' => 'error',
                'message' => 'Book is not available',
                'data' => null,
            ]);
        }

        if ($book->availability_status !== 'available') {
            return response()->json([
                'status' => 'error',
                'message' => 'Book is not available for lending',
                'data' => null,
            ]);
        }

        $dueDate = Carbon::parse($request->due_date);

        if ($dueDate->isPast()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Due date cannot be in the past',
                'data' => null,
            ]);
        }

        $now = Carbon::now();

        $lateDays = $now->diffInDays($dueDate) > 0 ? $now->diffInDays($dueDate) : 0;
        $penaltyAmount = $lateDays * 2;

        $orderNumber = $this->generateUniqueOrderNumber();

        $bookTransaction = new BookTransaction([
            'book_id' => $request->book_id,
            'created_by' => $user->id,
            'user_id' => $request->user_id,
            'reserved' => now(),
            'due_date' => $dueDate,
            'return_date' => $now,
            'late_days' => $lateDays,
            'penalty_amount' => $penaltyAmount,
            'order_number' => $orderNumber,
        ]);

        $bookTransaction->save();

        $book->update([
            'status' => 'borrowed',
            'availability_status' => 'booked',
        ]);

        $bookTransaction->load('createdBy', 'user', 'book');

        return response()->json([
            'status' => 'success',
            'message' => 'Book ordered successfully',
            'data' => [
                'book_transaction' => $bookTransaction,
            ],
        ]);
    }


    private function generateUniqueOrderNumber()
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $orderNumber = 'ORD-';

        do {
            $randomString = '';
            for ($i = 0; $i < 6; $i++) {
                $randomString .= $characters[rand(0, strlen($characters) - 1)];
            }
            $orderNumber = 'ORD-' . $randomString;
        } while ($this->isOrderNumberExists($orderNumber));

        return $orderNumber;
    }

    private function isOrderNumberExists($orderNumber)
    {
        return BookTransaction::where('order_number', $orderNumber)->exists();
    }

    public function returnBook($transaction_id)
    {
        $user = Auth::user();

        if (!$user || $user->roles->contains('name', 'User')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized user',
                'data' => null,
            ]);
        }

        $transaction = BookTransaction::find($transaction_id);

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction not found',
                'data' => null,
            ]);
        }

        // Check if the user is authorized to return this book
        if ($user->id !== $transaction->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to return this book',
                'data' => null,
            ]);
        }

        // Update the book transaction and book status
        $transaction->update([
            'return_date' => now(),
        ]);

        $book = $transaction->book;
        $book->update([
            'status' => 'returned',
            'availability_status' => 'available',
        ]);

        $transaction->load('createdBy', 'user', 'book');

        return response()->json([
            'status' => 'success',
            'message' => 'Book returned successfully',
            'data' => [
                'book_transaction' => $transaction,
            ],
        ]);
    }
}
