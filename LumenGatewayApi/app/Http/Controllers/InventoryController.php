<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\InventoryService;
use App\Services\BookService;

class InventoryController extends Controller
{
    use ApiResponser;

    public $inventoryService;
    public $bookService;

    public function __construct(InventoryService $inventoryService, BookService $bookService)
    {
        $this->inventoryService = $inventoryService;
        $this->bookService = $bookService;
    }

    public function index()
    {
        return $this->successResponse($this->inventoryService->obtainInventory());
    }

    public function showByBook($book_id)
    {
        // Validar que el libro existe
        try {
            $this->bookService->obtainBook($book_id);
        } catch (\Exception $e) {
            return $this->errorResponse('The book does not exist', Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse($this->inventoryService->obtainInventoryByBook($book_id));
    }

    public function getAvailable($book_id)
    {
        // Validar que el libro existe
        try {
            $this->bookService->obtainBook($book_id);
        } catch (\Exception $e) {
            return $this->errorResponse('The book does not exist', Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse($this->inventoryService->getAvailableQuantity($book_id));
    }

    public function store(Request $request)
    {
        // Validar que el libro existe
        if ($request->has('book_id')) {
            try {
                $this->bookService->obtainBook($request->book_id);
            } catch (\Exception $e) {
                return $this->errorResponse('The book does not exist', Response::HTTP_NOT_FOUND);
            }
        }

        return $this->successResponse(
            $this->inventoryService->createInventory($request->all()),
            Response::HTTP_CREATED
        );
    }

    public function update(Request $request, $id)
    {
        // Si se actualiza book_id, validar que el libro existe
        if ($request->has('book_id')) {
            try {
                $this->bookService->obtainBook($request->book_id);
            } catch (\Exception $e) {
                return $this->errorResponse('The book does not exist', Response::HTTP_NOT_FOUND);
            }
        }

        return $this->successResponse(
            $this->inventoryService->updateInventory($id, $request->all())
        );
    }

    public function destroy($id)
    {
        return $this->successResponse($this->inventoryService->deleteInventory($id));
    }

    public function reserve(Request $request)
    {
        // Validar que el libro existe
        if ($request->has('book_id')) {
            try {
                $this->bookService->obtainBook($request->book_id);
            } catch (\Exception $e) {
                return $this->errorResponse('The book does not exist', Response::HTTP_NOT_FOUND);
            }
        }

        return $this->successResponse($this->inventoryService->reserveUnits($request->all()));
    }

    public function release(Request $request)
    {
        // Validar que el libro existe
        if ($request->has('book_id')) {
            try {
                $this->bookService->obtainBook($request->book_id);
            } catch (\Exception $e) {
                return $this->errorResponse('The book does not exist', Response::HTTP_NOT_FOUND);
            }
        }

        return $this->successResponse($this->inventoryService->releaseUnits($request->all()));
    }
}