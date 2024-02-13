<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;

class BookAcionController extends Controller
{
    public function getBooks($book_id = null)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
                'data' => null,
            ], 401);
        }

        if (is_null($book_id)) {
            $books = Book::with('category')->latest()->get();

            if ($books->isNotEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Books fetched successfully',
                    'data' => $books
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No books found',
                    'data' => null
                ], 404);
            }
        } else {
            $book = Book::with(['category', 'createdBy'])->find($book_id);

            if ($book) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Book fetched successfully',
                    'data' => $book
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Book not found',
                    'data' => null
                ], 404);
            }
        }
    }


    public function postBooks(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                    'data' => null,
                ], 401);
            }
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'year' => 'required|date',
                'category_id' => 'required',
                'author' => 'required',
                'availability_status' => 'required|in:available,booked,not_in_stock'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data validation failed',
                    'data' => $validator->errors(),
                ]);
            }

            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                    'data' => null,
                ], 401);
            }

            $book = new Book([
                'name' => $request->name,
                'year' => $request->year,
                'author' => $request->author,
                'availability_status' => $request->availability_status,
                'category_id' => $request->category_id,
                'ISBN' => $request->ISBN,
                'created_by' => $user->id,
            ]);

            $book->save();

            $book->load('category', 'createdBy');

            return response()->json([
                'status' => 'success',
                'message' => 'Book created successfully',
                'data' => $book,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'data' => null,
            ]);
        }
    }


    public function updateBook(Request $request, $book_id)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                    'data' => null,
                ], 401);
            }
            $book = Book::findOrFail($book_id);

            if ($book) {
                $validator = Validator::make($request->all(), [
                    'name' => 'required',
                    'year' => 'required|date',
                    'category_id' => 'required',
                    'author' => 'required',
                    'availability_status' => 'required|in:available,booked,not_in_stock'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Data validation failed',
                        'data' => $validator->errors(),
                    ]);
                }

                $book->update([
                    'name' => $request->name,
                    'year' => $request->year,
                    'author' => $request->author,
                    'availability_status' => $request->availability_status,
                    'category_id' => $request->category_id,
                    'ISBN' => $request->ISBN,
                ]);

                $book->load('category', 'createdBy');

                return response()->json([
                    'status' => 'success',
                    'message' => 'Book updated successfully',
                    'data' => $book,
                ], 201);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Book cannot be found',
                    'data' => null,
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'data' => null,
            ]);
        }
    }


    public function deleteBook($book_id)
{
    try {
        $book = Book::findOrFail($book_id);

        if ($book) {
            $deleted_book = $book->delete();

            if ($deleted_book) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Book deleted successfully',
                    'data' => null,
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to delete book',
                    'data' => null,
                ]);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Book not found',
                'data' => null,
            ], 404);
        }

    } catch (\Throwable $th) {
        return response()->json([
            'status' => 'error',
            'message' => $th->getMessage(),
            'data' => null,
        ]);
    }
}

}
