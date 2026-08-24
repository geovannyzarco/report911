<?php

namespace App\Models\Cad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo de Respuestas/Despachos del sistema CAD TiburonCad.
 * Una respuesta representa una misión de despacho asociada a un incidente.
 */
class Response extends Model
{
    protected $connection = 'sqlsrv_cad';

    protected $table = 'Responses';

    protected $primaryKey = 'OID';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'OID',
        'Incident',
        'Status',
        'Agency',
        'ResponseType',
        'PrimaryUnit',
        'Zone',
        'Address',
        'CreationTime',
        'SequenceNumber',
    ];

    protected $casts = [
        'CreationTime' => 'datetime',
    ];

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class, 'Incident', 'OID');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'Status', 'OID');
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'Agency', 'OID');
    }

    public function responseType(): BelongsTo
    {
        return $this->belongsTo(ResponseType::class, 'ResponseType', 'OID');
    }

    public function primaryUnit(): BelongsTo
    {
        return $this->belongsTo(Resource::class, 'PrimaryUnit', 'OID');
    }

    public function assigns(): HasMany
    {
        return $this->hasMany(Assign::class, 'Response', 'OID');
    }

    public function statusChanges(): HasMany
    {
        return $this->hasMany(RespStatusChange::class, 'RESPONSE', 'OID');
    }
}
