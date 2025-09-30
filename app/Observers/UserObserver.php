<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Invoice;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Create an invoice automatically when a new user is added
        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'user_id' => $user->id,
            'amount' => (float) ($user->price ?? 0),
            'tax_amount' => 0,
            'total_amount' => (float) ($user->price ?? 0),
            'status' => Invoice::STATUS_PAID,
            'issued_at' => now(),
            'due_at' => now(),
            'paid_at' => now(),
            'description' => 'Facture automatique pour nouvel utilisateur',
            'items' => [],
            'notes' => 'Créée automatiquement lors de l’inscription',
        ]);
    }
}
