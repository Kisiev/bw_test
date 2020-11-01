<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;
    protected $fillable = [
    	'sequence',
    	'user_id',
    	'execution_time'
    ];
    public function user()
	{
		return $this->belongsTo('App\Models\User');
	}
}
