<?php

namespace App\Models\Cad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo de Incidentes del sistema CAD TiburonCad.
 * Un incidente puede tener múltiples respuestas/despachos.
 */
class Incident extends Model
{
    protected $connection = 'sqlsrv_cad';

    protected $table = 'Incidents';

    protected $primaryKey = 'OID';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'OID',
        'SequenceNumber',
        'Call',
        'Classification',
        'Priority',
        'ILIAddress',
        'Status',
        'PrimaryAgency',
        'CreationTime',
    ];

    protected $casts = [
        'CreationTime' => 'datetime',
    ];

    public function call(): BelongsTo
    {
        return $this->belongsTo(Call::class, 'Call', 'OID');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class, 'Incident', 'OID');
    }

    public function classification(): BelongsTo
    {
        return $this->belongsTo(Classification::class, 'Classification', 'OID');
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class, 'Priority', 'OID');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'Status', 'OID');
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'PrimaryAgency', 'OID');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'ILIAddress', 'OID');
    }
}
