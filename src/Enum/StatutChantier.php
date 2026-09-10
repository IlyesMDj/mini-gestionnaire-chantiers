<?php

declare(strict_types=1);

namespace App\Enum;

enum StatutChantier: string
{
    case EnAttente = 'en_attente';
    case EnCours   = 'en_cours';
    case Termine   = 'termine';

    public function label(): string
    {
        return match ($this) {
            self::EnAttente => 'En attente',
            self::EnCours   => 'En cours',
            self::Termine   => 'Terminé',
        };
    }

    public function badgeClass(): string
    {
        $base = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium';

        return $base . ' ' . match ($this) {
            self::EnAttente => 'bg-slate-100 text-slate-700',
            self::EnCours   => 'bg-blue-100 text-blue-800',
            self::Termine   => 'bg-green-100 text-green-800',
        };
    }
}
