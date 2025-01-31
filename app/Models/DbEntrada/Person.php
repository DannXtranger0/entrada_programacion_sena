<?php

namespace App\Models\DbEntrada;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;
    protected $connection = 'db_entrada';
    protected $table = 'people';
    protected $guarded = [];


    //Una persona posee un rol(pertenece a un rol)
    // public function roles(){
    //     return $this->belongsTo(Role::class);
    // }


    //Una persona tiene un cargo
    public function positions(){
        return $this->belongsTo(Position::class);
    }
    
    //Una persona tiene muchas entradas y salidas
    public function entrances_exits(){
        return $this->hasMany(EntranceExit::class);
    }

    //Una persona puede tener varios usuarios 
    //(osea, personas con roles diferentes de aprendiz e instructor)
    public function users(){
        return $this->hasMany(User::class);
    }   

    //Una persona PUEDE tener varias notificaciones de inasistencia
    public function notifications_absences(){
        return $this->hasMany(Notification_absence::class);
    }

}
