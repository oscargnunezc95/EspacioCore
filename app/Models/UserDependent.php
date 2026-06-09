<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDependent extends Model
{
    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'national_id', 'country_id', 'relationship', 'status'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // MAGIA 3: Cuando se agrega un familiar, buscamos sus fichas locales
    protected static function booted()
    {
        static::saved(function ($dependent) {
            // Si el dependiente tiene RUT, buscamos si hay fichas de Student huérfanas
            if ($dependent->isDirty('national_id') && !empty($dependent->national_id)) {
                
                $orphans = \App\Models\Student::withoutGlobalScopes()
                            ->where('national_id', $dependent->national_id)
                            ->where('country_id', $dependent->country_id)
                            ->whereNull('user_id')
                            ->get();
                
                if ($orphans->count() > 0) {
                    foreach($orphans as $orphan) {
                        // Al actualizar, se gatillará todo nuestro sistema de cascada
                        $orphan->update(['user_id' => $dependent->user_id]);
                    }
                    
                    // Notificamos al Titular del hallazgo
                    $dependent->user->notify(new \App\Notifications\DependentProfileLinkedNotification($dependent));
                    \Illuminate\Support\Facades\Mail::to($dependent->user->email)->send(new \App\Mail\DependentProfileLinkedMail($dependent));
                }
            }
        });
    }
}