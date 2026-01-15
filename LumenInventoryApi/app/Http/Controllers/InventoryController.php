<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use App\Inventory;
use App\Services\BookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    use ApiResponser;

    /**
     * The service to consume the book service
     * @var BookService
     */
    public $bookService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(BookService $bookService)
    {
        $this->bookService = $bookService;
    }

    /**
     * Return the list of inventory items
     * @return Illuminate\Http\Response
     */
    public function index()
    {
        $inventory = Inventory::all();
        return $this->successResponse($inventory);
    }

    /**
     * Get inventory for a specific book
     * @return Illuminate\Http\Response
     */
    public function showByBook($book_id)
    {
        $inventory = Inventory::where('book_id', $book_id)->first();
        
        if (!$inventory) {
            return $this->errorResponse('Inventory not found for this book', Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse($inventory);
    }

    /**
     * Create one new inventory item
     * @return Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validar que el libro existe antes de crear inventario
       
        // Validar datos de entrada
        $rules = [
            'book_id' => 'required|integer|min:1|unique:inventory,book_id',
            'quantity' => 'required|integer|min:0',
        ];

        $this->validate($request, $rules);

        // Crear el inventario
        $inventory = Inventory::create($request->all());

        return $this->successResponse($inventory, Response::HTTP_CREATED);
    }

    /**
     * Update an existing inventory item
     * @return Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);

        // Si se actualiza el book_id, validar que el libro existe
        if ($request->has('book_id') && $request->book_id != $inventory->book_id) {
            
        }

        $rules = [
            'book_id' => 'integer|min:1|unique:inventory,book_id,' . $id,
            'quantity' => 'integer|min:0',
            'reserved_quantity' => 'integer|min:0',
        ];

        $this->validate($request, $rules);

        // Validar que reserved_quantity no sea mayor que quantity
        if ($request->has('reserved_quantity') && $request->reserved_quantity > $inventory->quantity) {
            return $this->errorResponse('Reserved quantity cannot exceed total quantity', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $inventory->fill($request->all());

        if ($inventory->isClean()) {
            return $this->errorResponse('At least one value must change', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $inventory->save();

        return $this->successResponse($inventory);
    }

    /**
     * Reserve units from inventory
     * @return Illuminate\Http\Response
     */
    public function reserve(Request $request)
    {
        $rules = [
            'book_id' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:1',
        ];

        $this->validate($request, $rules);

        // Validar que el libro existe
        
        // Buscar inventario del libro
        $inventory = Inventory::where('book_id', $request->book_id)->first();
        
        if (!$inventory) {
            return $this->errorResponse('Inventory not found for this book', Response::HTTP_NOT_FOUND);
        }

        try {
            // Intentar reservar unidades
            $inventory->reserve($request->quantity);
            return $this->successResponse([
                'message' => 'Units reserved successfully',
                'inventory' => $inventory->fresh()
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_CONFLICT);
        }
    }

    /**
     * Release reserved units
     * @return Illuminate\Http\Response
     */
    public function release(Request $request)
    {
        $rules = [
            'book_id' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:1',
        ];

        $this->validate($request, $rules);

        // Buscar inventario del libro
        $inventory = Inventory::where('book_id', $request->book_id)->first();
        
        if (!$inventory) {
            return $this->errorResponse('Inventory not found for this book', Response::HTTP_NOT_FOUND);
        }

        try {
            // Liberar unidades reservadas
            $inventory->release($request->quantity);
            return $this->successResponse([
                'message' => 'Units released successfully',
                'inventory' => $inventory->fresh()
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_CONFLICT);
        }
    }

    /**
     * Get available quantity for a book
     * @return Illuminate\Http\Response
     */
    public function getAvailable($book_id)
    {
        $inventory = Inventory::where('book_id', $book_id)->first();
        
        if (!$inventory) {
            return $this->errorResponse('Inventory not found for this book', Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse([
            'book_id' => $book_id,
            'available_quantity' => $inventory->available_quantity,
            'total_quantity' => $inventory->quantity,
            'reserved_quantity' => $inventory->reserved_quantity
        ]);
    }

    /**
     * Remove an existing inventory item
     * @return Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $inventory = Inventory::findOrFail($id);
        
        // No permitir eliminar si hay unidades reservadas
        if ($inventory->reserved_quantity > 0) {
            return $this->errorResponse('Cannot delete inventory with reserved units', Response::HTTP_CONFLICT);
        }

        $inventory->delete();

        return $this->successResponse($inventory);
    }
}