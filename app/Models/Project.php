<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $table = 'projects';

    protected $fillable = [
        'name',
        'user_id'
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class,'project_id','id');
    }
}
