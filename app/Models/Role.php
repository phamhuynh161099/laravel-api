<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    // Tên bảng trong cơ sở dữ liệu
    protected $table = 'roles';

    // Định nghĩa quan hệ ngược với User
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
