<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'tasks';

    protected $fillable = [
        'project_id',
        'title',
        'descr',
        'close',
        'status'
    ];

    public function project(){

    }
}
