<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'book_id',
        'quantity',
        'reserved_quantity',
        'version'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'version' => 'integer'
    ];

    /**
     * Get the available quantity (calculated attribute)
     */
    public function getAvailableQuantityAttribute()
    {
        return $this->quantity - $this->reserved_quantity;
    }

    /**
     * Reserve units with optimistic locking
     */
    public function reserve($quantity)
    {
        if ($this->available_quantity < $quantity) {
            throw new \Exception('Not enough stock available');
        }

        // Usar transacción para asegurar consistencia
        return \DB::transaction(function () use ($quantity) {
            // Reload con lock para evitar race conditions
            $current = self::where('id', $this->id)
                ->lockForUpdate()
                ->first();

            if ($current->available_quantity < $quantity) {
                throw new \Exception('Not enough stock available');
            }

            $current->reserved_quantity += $quantity;
            $current->version += 1;
            $current->save();

            return $current;
        });
    }

    /**
     * Release reserved units
     */
    public function release($quantity)
    {
        if ($this->reserved_quantity < $quantity) {
            throw new \Exception('Cannot release more than reserved');
        }

        return \DB::transaction(function () use ($quantity) {
            $current = self::where('id', $this->id)
                ->lockForUpdate()
                ->first();

            if ($current->reserved_quantity < $quantity) {
                throw new \Exception('Cannot release more than reserved');
            }

            $current->reserved_quantity -= $quantity;
            $current->version += 1;
            $current->save();

            return $current;
        });
    }

    /**
     * Confirm reservation (convert reserved to sold)
     */
    public function confirmReservation($quantity)
    {
        return \DB::transaction(function () use ($quantity) {
            $current = self::where('id', $this->id)
                ->lockForUpdate()
                ->first();

            if ($current->reserved_quantity < $quantity) {
                throw new \Exception('Not enough reserved units');
            }

            $current->quantity -= $quantity;
            $current->reserved_quantity -= $quantity;
            $current->version += 1;
            $current->save();

            return $current;
        });
    }
}