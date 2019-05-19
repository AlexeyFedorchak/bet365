<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SyncKey extends Model
{
    protected $table = 'sync_keys';

    protected $fillable = [
    	'key',
    	'model',
    ];

    public function getSync($model)
    {
    	$this->key = uniqid() . uniqid() . uniqid() . uniqid();
    	$this->model = $model;
    	$this->save();

    	return $this;
    }
}
